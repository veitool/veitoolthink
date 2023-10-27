<?php
/**
 * ===========================================================================
 * Veitool 快捷开发框架系统
 * Author: Niaho 26843818@qq.com
 * Copyright (c)2019-2023 www.veitool.com All rights reserved.
 * Licensed: 这不是一个自由软件，不允许对程序代码以任何形式任何目的的再发行
 * ---------------------------------------------------------------------------
 */
namespace tool;

use think\facade\Db;

/**
 * 数据库管理类
 */
class MysqlBackup
{
    /**
     * 数据库配置
     * @var integer
     */
    private $dbconfig = [];

    /**
     * 备份配置
     * @var array
     */
    private $config = [
        'path'     => '',         // 备份地址
        'part'     => 1024*1024,  // 每卷大小
        'compress' => 0,          // 是否压缩
        'level'    => 9,          // 压缩级别
    ];

    /**
     * 初始化
     * @param  array  $config  备份配置
     */
    public function __construct($config = [])
    {
        $this->config['path'] = app()->getRootPath() . 'backup'. VT_DS .'database'. VT_DS;
        $this->config = array_merge($this->config, $config);
        //初始化数据库连接参数
        $this->dbconfig = config('database.connections.' . config('database.default'));
        //检查文件是否可写
        if (!$this->checkPath($this->config['path'])) {
            throw new \Exception("The current directory is not writable");
        }
    }

    /**
     * 数据库链接
     * @return obj
     */
    public static function connect()
    {
        return Db::connect();
    }

    /**
     * 数据库表列表信息
     * @param  string   $table   表名
     * @param  int      $type    有传入表名时获取的类型
     * @return array
     */
    public function dataList($table = null, $type = 1)
    {
        $db = self::connect();
        if(is_null($table)){
            $list = $db->query("SHOW TABLE STATUS");
        }else{
            if($type){
                $list = $db->query("SHOW FULL COLUMNS FROM {$table}");
            }else{
                $list = $db->query("SHOW COLUMNS FROM {$table}");
            }
        }
        return array_map('array_change_key_case', $list);
    }

    /**
     * 数据库表备份处理（多请求模式）
     * @param  array   $tables   数据表集
     * @param  array   $sizes    数据表对应大小集 [数据表名 => 大小]
     * @return int     百分比
     */
    public function doBack(array $tables = [], array $sizes = [])
    {
        if($tables && $sizes){
            $totalSize = 0;
            foreach($tables as $k=>$v){
                $tables[$k] = strip_sql($v, 0);
                $totalSize += $sizes[$v];
            }
            $rs = Db::query("SELECT VERSION() as v");
            $back['version'] = $rs[0]['v']; //获取数据库版本号
            $back['tables']  = $tables;     //数据表集
            $back['sizes']   = $sizes;      //数据表大小集合
            $back['totalTables']     = count($tables);  //数据表总数
            $back['tablesStartKey']  = 0;  //初始数据表集下标
            $back['recordStartNum']  = 0;  //初始数据表记录开始行数
            $back['fileNum']         = 1;  //初始分卷编号
            $back['totalFileNum']    = ceil($totalSize*1024*1024/$this->config['part']); //总分卷数
            $back['backFolderName']  = date('Y.m.d-H.i.s',time()).'-'.strtolower(random(6,'axceumnsaxceumns')); //备份的文件夹名
            session('db_back_info',$back);
        }else{
            $back = session('db_back_info');
        }
        //备份处理
        $sql = '';
        $this->config['part'] = $this->config['compress'] ? $this->config['part'] * 2 : $this->config['part'];
        for($i = $back['tablesStartKey']; $i < $back['totalTables'] && strlen($sql) < $this->config['part']; $i++){
            $sql .= $this->dumpSql($back['tables'][$i], $back['recordStartNum'], strlen($sql));
            $back['recordStartNum'] = 0;
        }
        if(trim($sql)){
            $str  = "-- -----------------------------\n";
            $str .= "-- Veitool MySQL Data Transfer \n";
            $str .= "-- \n";
            $str .= "-- Host     : " . $this->dbconfig['hostname'] . "\n";
            $str .= "-- Port     : " . $this->dbconfig['hostport'] . "\n";
            $str .= "-- Database : " . $this->dbconfig['database'] . "\n";
            $str .= "-- \n";
            $str .= "-- Part : #".$back['fileNum']."\n";
            $str .= "-- Date : " . date("Y-m-d H:i:s") . "\n";
            $str .= "-- -----------------------------\n\n";
            $str .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";
            $sql = $str.$sql;
            //写入文件
            $fildir = $this->config['path'].$back['backFolderName'].'/';
            if(!$this->checkPath($fildir)) return ['code'=>1,'p'=>100,'filenum'=>0,'msg'=>'备份目录没有写入权限'];
            $filename = $fildir.$back['fileNum'].'.sql';
            if($this->config['compress']){
                $filename = "{$filename}.gz";
                $fp = @gzopen($filename, "a{$this->config['level']}");
                @gzwrite($fp, $sql);
                @gzclose($fp);
            }else{
                $fp = @fopen($filename, 'a');
                @fwrite($fp, $sql);
                @fclose($fp);
            }
            $p = ($back['totalFileNum'] > 0 && $back['fileNum'] < $back['totalFileNum']) ? dround($back['fileNum']*100/$back['totalFileNum'], 0, true) : 100;
            $p = $p>=100 ? 100 : ($p < 1 ? 1 : $p);
            session('db_back_info.tablesStartKey',$i-1);        //记录“数据表集”的键值，便于下次请求时定位数据表
            session('db_back_info.fileNum',$back['fileNum']+1); //记录递增的分卷编号
            return ['code'=>0,'p'=>$p,'filenum'=>$back['fileNum']];
        }else{
            $this->dumpEnd($back);
            session('db_back_info',null);
            return ['code'=>1,'p'=>100,'filenum'=>$back['fileNum'],'msg'=>'备份数据成功'];
        }
    }

    /**
     * 获取数据表数据
     * @param   string   $table   表名
     * @param   int      $start   数据记录的开始行数
     * @param   int      $sizes   累计长度
     * @return  string
     */
    public function dumpSql(string $table, int $start = 0, int $sizes = 0)
    {
        $sql = '';
        if($start == 0){
            $rs = Db::query("SHOW CREATE TABLE `$table`");
            $rs = array_map('array_change_key_case', $rs);
            if(isset($rs[0]['create view'])){
                $sql    = "DROP VIEW IF EXISTS `$table`;\n" . trim($rs[0]['create view']) . ";\n\n";
                $back   = session('db_back_info');
                $fildir = $this->config['path'].$back['backFolderName'].'/';
                if($this->checkPath($fildir)){
                    $filename = $fildir.'z.sql';
                    if(is_file($filename)){
                        $old = file_get_contents($filename);
                        $fp = @fopen($filename, 'w');
                        @fwrite($fp, $old.$sql);
                        @fclose($fp);
                    }else{
                        $fp = @fopen($filename, 'a');
                        @fwrite($fp, $sql);
                        @fclose($fp);
                    }
                }
                return '';
            }else{
                $sql = "DROP TABLE IF EXISTS `$table`;\n" . trim($rs[0]['create table']) . ";\n\n";
            }
        }
        $rows = $offset = 100;
        while(($sizes + strlen($sql)) < $this->config['part'] && $rows == $offset){
            $rows = 0;
            /*备份数据记录*/
            $result = Db::query("SELECT * FROM `$table` LIMIT $start, $offset");
            foreach ($result as $row) {
                $str = '';
                foreach($row as $v){
                    $str .= (is_null($v) ? 'null,' : "'".addslashes($v)."',");
                }
                $sql .= "INSERT INTO `{$table}` VALUES(". str_replace(["\r","\n"], ['\\r','\\n'], rtrim($str, ',')) .");\n";
                $rows++;
            }/**/
            $start += $offset;
        }
        session('db_back_info.recordStartNum',$start);
        $sql .= "\n";
        return $sql;
    }

    /**
     * 其他信息备份 存储过程/函数/触发器
     * @param   array   $back   信息集
     * @return  mixd
     */
    function dumpEnd(array $back)
    {
        $txt = '';
        $dbname = $this->dbconfig['database'];

        /*版本判断*/
        if(version_compare($back['version'],'5.7.0','<')){
            $key = 'name';
            $sql = "select name from mysql.proc where db = '".$dbname."' and `type` =";
        }else{
            $key = 'SPECIFIC_NAME';
            $sql = "select * from information_schema.parameters where SPECIFIC_SCHEMA = '".$dbname."' and `ROUTINE_TYPE` =";
        }/**/

        /*备份存储过程*/
        $result = Db::query($sql ." 'PROCEDURE' ");
        for ($i = 0; $i < count($result); $i++) {
            $Pname  = $result[$i][$key];
            $rs     = Db::query("show create procedure {$Pname}");
            $rs     = array_map('array_change_key_case', $rs);
            $Pnamez = $rs[0]['create procedure']; 
            $txt   .= "\r\nDROP PROCEDURE IF EXISTS `{$Pname}`;\r\nDELIMITER;;\r\n{$Pnamez}\r\n;;DELIMITER;\r\n";
        }/**/

        /*备份函数*/
        $result = Db::query($sql ." 'FUNCTION' ");
        for ($i = 0; $i < count($result); $i++) {
            $Pname  = $result[$i][$key];
            $rs     = Db::query("show create function {$Pname}");
            $rs     = array_map('array_change_key_case', $rs);
            $Pnamez = $rs[0]['create function']; 
            $txt   .= "\r\nDROP FUNCTION IF EXISTS `{$Pname}`;\r\nDELIMITER;;\r\n{$Pnamez}\r\n;;DELIMITER;\r\n";
        }/**/

        /*备份触发器*/
        $sql = "SELECT * FROM information_schema.TRIGGERS where trigger_schema = '".$dbname."'";
        $rs = Db::query($sql);
        $rs = array_map('array_change_key_case', $rs);
        for ($i = 0; $i < count($rs); $i++) {
            $trigger_name       = $rs[$i]['trigger_name'];
            $action_timing      = $rs[$i]['action_timing'];
            $event_manipulation = $rs[$i]['event_manipulation'];
            $event_object_table = $rs[$i]['event_object_table'];
            $action_statement   = $rs[$i]['action_statement'];
            $m    = "CREATE TRIGGER `{$trigger_name}` {$action_timing} {$event_manipulation} ON `{$event_object_table}` FOR EACH ROW {$action_statement}";
            $txt .= "\r\nDROP TRIGGER IF EXISTS `{$trigger_name}`;\r\nDELIMITER;;\r\n{$m}\r\n;;DELIMITER;\r\n";
        }/**/

        /*写入文件*/
        $fildir = $this->config['path'].$back['backFolderName'].'/';
        if ($this->checkPath($fildir)) {
            $filename = $fildir.'z.sql';
            $newfname = $fildir.$back['fileNum'].'.sql';
            if(is_file($filename)){
                $txt = file_get_contents($filename) . $txt;
                @unlink($filename);
            }elseif(!$txt){
                return false;
            }
            if($this->config['compress']){
                $newfname = "{$newfname}.gz";
                $fp = @gzopen($newfname, "a{$this->config['level']}");
                @gzwrite($fp, $txt);
                @gzclose($fp);
            }else{
                $fp = @fopen($newfname, 'a');
                @fwrite($fp, $txt);
                @fclose($fp);
            }
        }/**/
    }

    /**
     * 恢复数据
     * @param   string   $dir  备份文件夹
     * @return  array
     */
    public function doImport(string $dir = '')
    {
        if($dir){
            $path = realpath($this->config['path'].$dir).'/';
            if(is_dir($path)){
                $ext = $this->config['compress'] ? '.sql.gz' : '.sql';
                $rs = glob($path.'*'.$ext);
                if(!$rs) return ['code'=>5,'p'=>0,'filenum'=>0,'msg'=>'备份源不存在'];
                $back['files']   = $rs;        //文件集
                $back['total']   = count($rs); //分卷总数
                $back['fileNum'] = 1;          //初始分卷编号
            }else{
                return ['code'=>2,'p'=>0,'filenum'=>0,'msg'=>'参数错误'];
            }
            session('db_back_imp',$back);
        }else{
            $back = session('db_back_imp');
        }
        //导入数据
        $fid = $back['fileNum']-1;
        if(isset($back['files'][$fid])){
            $sql = '';
            $flag = false;
            $sqlFile = $back['files'][$fid];
            $db = self::connect();
            if($this->config['compress']){
                $gz  = gzopen($sqlFile, 'r');
                while(!gzeof($gz)){
                    $sql .= gzgets($gz);
                    $tmp  = trim($sql);
                    if($flag || preg_match('/DELIMITER;;$/', $tmp)){
                        if(preg_match('/;;DELIMITER;$/', $tmp)){
                            $flag = false;
                            $sql  = str_replace(['DELIMITER;;','DELIMITER;',';;'],['','',''], $sql);
                            $db->execute("set global log_bin_trust_function_creators=1;");
                            if($db->execute($sql) === false){
                                return ['code'=>3,'p'=>0,'filenum'=>$back['fileNum'],'msg'=>'卷：'.$back['fileNum'].'导入失败'];
                            }
                            $sql = '';
                        }else{
                            $flag = true;
                        }
                    }elseif(preg_match('/.*;$/', $tmp)){
                        if($db->execute($sql) === false){
                            return ['code'=>3,'p'=>0,'filenum'=>$back['fileNum'],'msg'=>'卷：'.$back['fileNum'].'导入失败'];
                        }
                        $sql = '';
                    }
                }
                gzclose($gz);
            }else{
                $gz = fopen($sqlFile, 'r');
                while(!feof($gz)){
                    $sql .= fgets($gz);
                    $tmp  = trim($sql);
                    if($flag || preg_match('/DELIMITER;;$/', $tmp)){
                        if(preg_match('/;;DELIMITER;$/', $tmp)){
                            $flag = false;
                            $sql  = str_replace(['DELIMITER;;','DELIMITER;',';;'],['','',''], $sql);
                            $db->execute("set global log_bin_trust_function_creators=1;");
                            if($db->execute($sql) === false){
                                return ['code'=>3,'p'=>0,'filenum'=>$back['fileNum'],'msg'=>'卷：'.$back['fileNum'].'导入失败'];
                            }
                            $sql = '';
                        }else{
                            $flag = true;
                        }
                    }elseif(preg_match('/.*;$/', $tmp)){
                        if($db->execute($sql) === false){
                            return ['code'=>3,'p'=>0,'filenum'=>$back['fileNum'],'msg'=>'卷：'.$back['fileNum'].'导入失败'];
                        }
                        $sql = '';
                    }
                 }
                 fclose($gz);
            }
            session('db_back_imp.fileNum',$back['fileNum']+1);
            $p = $back['fileNum'] < $back['total'] ? dround($back['fileNum']*100/$back['total'], 0, true) : 100;
            $p = $p >= 100 ? 100 : ($p < 1 ? 1 : $p);
            return ['code'=>0,'p'=>$p,'filenum'=>$back['fileNum']];
        }else{
            session('db_back_imp',null);
            return ['code'=>1,'p'=>100,'filenum'=>$back['total'],'msg'=>'恢复数据成功'];
        }
    }

    /**
     * 数据库备份文件列表
     * @return array
     */
    public function getBackFile()
    {
        $dbak = $dbaks = array();
        $path = realpath($this->config['path']).'/';
        $sqlfiles = glob($path.'*');
        if(is_array($sqlfiles)){
            foreach($sqlfiles as $id=>$sqlfile){
                $tmp = basename($sqlfile);
                $ftm = fileatime($sqlfile);
                if(is_dir($sqlfile)){
                    $dbak['filename'] = $tmp;
                    $size = $number = 0;
                    $ss = glob($path.$tmp.'/*.sql*');
                    foreach($ss as $s){
                        $size += filesize($s);
                        $number++;
                    }
                    $dbak['filesize'] = round($size/(1024*1024), 2);
                    $dbak['pre'] = $tmp;
                    $dbak['number'] = $number;
                    $dbak['mtime'] = date("Y-m-d H:i:s",$ftm);//str_replace('.', ':', substr($tmp,	0, 19));
                    $dbak['btime'] = substr($dbak['mtime'], 0, -3);
                    $dbaks[] = $dbak;
                }
            }
        }
        return $dbaks;
    }

    /**
     * 数据库备份文件列表
     * @param   array   $files   要删除的文件夹名
     * @return  string/bool
     */
    public function delBackFile(array $files = [])
    {
        $s = '';
        foreach($files as $f){
            $d = $this->config['path'].$f;
            if(is_dir($d)){
                $l = glob($d.'/*');
                if($l){
                    foreach($l as $v){
                        if(is_file($v)) @unlink($v);
                    }
                }
                $r = @rmdir($d);
                if(!$r) $s .= $s ? '|'.$d : $d;
            }else{
                $s .= $s ? '|'.$d : $d;
            }
        }
        return $s ?: true;
    }

    /**
     * 优化表
     * @param   array|string  $tables  数据表名
     * @return  mixed
     */
    public function optimize($tables)
    {
        if($tables){
            $db = self::connect();
            if(is_array($tables)){
                $tables = implode('`,`', $tables);
                $list = $db->query("OPTIMIZE TABLE `{$tables}`");
            }else{
                $list = $db->query("OPTIMIZE TABLE `{$tables}`");
            }
            if(!$list){
                throw new \Exception("data sheet'{$tables}'Repair mistakes please try again!");
            }
        }else{
            throw new \Exception("Please specify the table to be repaired!");
        }
    }

    /**
     * 修复表
     * @param    string|null   $tables   数据表名
     * @return   array
     */
    public function repair($tables = null)
    {
        if($tables){
            $db = self::connect();
            if(is_array($tables)){
                $tables = implode('`,`', $tables);
                $list = $db->query("REPAIR TABLE `{$tables}`");
            }else{
                $list = $db->query("REPAIR TABLE `{$tables}`");
            }
            if($list){
                return $list;
            }else{
                throw new \Exception("data sheet'{$tables}'Repair mistakes please try again!");
            }
        }else{
            throw new \Exception("Please specify the table to be repaired!");
        }
    }

    /**
     * 下载备份文件
     * @param    string   $folder   备份文件夹
     * @param    int      $pid      分卷编号
     * @return   file
     */
    public function downFile(string $folder, int $pid = 0)
    {
        $ext  = $this->config['compress'] ? '.sql.gz' : '.sql';
        $file = $this->config['path'].$folder.'/'.$pid.$ext;
        if(file_exists($file)){
            ob_end_clean();
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Length: '.filesize($file));
            header('Content-Disposition: attachment; filename='.basename($file));
            readfile($file);
        }else{
            throw new \Exception("{$folder} File is abnormal");
        }
    }

    /**
     * 检查目录是否可写
     * @param   string   $path   文件夹路径
     * @return  bool
     */
    protected function checkPath(string $path)
    {
        if(is_dir($path)){
            return true;
        }
        if(mkdir($path, 0755, true)){
            return true;
        }else{
            return false;
        }
    }

}
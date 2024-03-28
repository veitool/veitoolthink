<?php
/**
 * ===========================================================================
 * Veitool 快捷开发框架系统
 * Author: Niaho 26843818@qq.com
 * Copyright (c)2019-2024 www.veitool.com All rights reserved.
 * Licensed: 这不是一个自由软件，不允许对程序代码以任何形式任何目的的再发行
 * ---------------------------------------------------------------------------
 */
namespace veitool\addons;

use think\Exception;
use think\facade\Db;
use think\facade\Cache;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use PhpZip\ZipFile;
use PhpZip\Exception\ZipException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;

/**
 * 插件服务类
 */
class Service
{
    /**
     * 插件单例盒子
     * @var array 
     */
    private static $addon_instance = [];

    /**
     * 获取已装插件
     * @return array
     */
    public static function hasAddon(){
        $list = [];
        $dirs = scandir(ADDON_PATH);
        foreach($dirs as $name){
            if(in_array($name,['.','..','.htaccess'])) continue;
            $path = ADDON_PATH . $name;
            if(is_file($path)) continue;
            $addonDir = $path . VT_DS;
            if(!is_dir($addonDir)) continue;
            if(!is_file($addonDir . ucfirst($name) . '.php')) continue;
            $info_file = $addonDir . 'info.ini';
            if(!is_file($info_file)) continue;
            $info = parse_ini_file($info_file, true, INI_SCANNER_TYPED) ?: [];
            if(!isset($info['name'])) continue;
            $info['config'] = self::getConfigFile($name);
            $list[$name] = $info;
        }
        return $list;
    }

    /**
     * 获取远程全部插件【有10分钟缓存】
     * @param  array  $params  插件参数
     * @return array
     */
    public static function onAddon($params = [])
    {
        $addon = Cache::get("addonsOnline");
        if(!is_array($addon) && self::getServerUrl()){
            $addon = $rs = [];
            try{
                $rs = self::doRequest('/api/addon/index',$params,'GET');
            }catch(\Exception $e){}
            $data = $rs['data'] ?? [];
            foreach($data as $v){
                $addon[$v['name']] = $v;
            }
            Cache::set("addonsOnline",$addon,600);
        }
        return $addon;
    }

    /**
     * 安装插件
     * @param   string    $name      插件名称
     * @param   bool      $force     是否覆盖
     * @param   array     $extend    扩展参数
     * @return  boolean
     * @throws  Exception
     * @throws  AddonException
     */
    public static function install($name, $force = false, $extend = [])
    {
        if(!$name || (is_dir(ADDON_PATH . $name) && !$force)){
            throw new Exception('Addon already exists');
        }
        $tmpFile  = Service::download($name, $extend);
        $addonDir = self::getAddonDir($name);
        try{
            Service::unzip($name);
            if(!$force){Service::noConflict($name);}
            Service::check($name);
        }catch(AddonException $e){
            @rmdirs($addonDir);
            throw new AddonException($e->getMessage(), $e->getCode(), $e->getData());
        }catch(Exception $e){
            @rmdirs($addonDir);
            throw new Exception($e->getMessage());
        }finally{
            @unlink($tmpFile);
        }
        $addon = self::getAddonInstance($name);
        $info = $addon->getInfo($name);
        Db::startTrans();
        try{
            $addon->install();
            Db::commit();
        }catch(Exception $e){
            @rmdirs($addonDir);
            Db::rollback();
            throw new Exception($e->getMessage());
        }
        Service::importsql($name,true);
        return Service::enable($name, $force);
    }

    /**
     * 离线安装
     * @param  string  $file    插件压缩包
     * @param  bool    $force   强制覆盖
     * @param  array   $extend  会员信息
     * @return array
     */
    public static function local($file, $force = false, $extend = [])
    {
        if(!$file || !$file instanceof \think\File){
            throw new Exception('No file upload or server upload limit exceeded');
        }
        try{
            validate(['file' => ['fileSize'=>10*1024*1024,'fileExt'=>'zip']],['file.fileSize' => '文件大小不能超过10M','file.fileExt' => '请上传格式为zip的压缩包',])->check(['file' => $file]);
        }catch(\think\exception\ValidateException $e){
            throw new Exception($e->getMessage());
        }
        $addonsTempDir = self::getAddonsBackupDir();
        try{
            $tmpName = $file->hashName('uniqid');
            $file->move($addonsTempDir,$tmpName);
        }catch(\think\exception\FileException $e){
            throw new Exception($e->getMessage());
        }
        $zip = new ZipFile();
        $tmpFile = $addonsTempDir . $tmpName;
        $newAddonDir = '';
        try{
            $zip->openFile($tmpFile);
            $zipInfo = self::getZipIni($zip);
            $name = $zipInfo['name'] ?? '';
            if(!$name){
                throw new Exception('Addon info file data incorrect');
            }
            if(!is_preg($name,'{3,20}',[1,2])){
                throw new Exception('Addon name incorrect');
            }
            $newAddonDir = self::getAddonDir($name);
            if(is_dir($newAddonDir)){
                throw new Exception('Addon already exists');
            }
            $extend['md5file'] = md5_file($tmpFile);
            $extend['notes'] = $zip->getArchiveComment();
            $params = array_merge($zipInfo, $extend);
            $check = env('app_debug', true) && config('veitool.unknown');
            $check || Service::valid($params);
            @mkdir($newAddonDir, 0755, true);
            $zip->extractTo($newAddonDir);
            if(!$force){Service::noConflict($name);}
            $check || Service::check($name);
        }catch(ZipException $e){
            $zip->close();
            @unlink($tmpFile);
            throw new Exception('Unable to open the zip file');
        }catch(AddonException $e){
            $newAddonDir && @rmdirs($newAddonDir);
            throw new AddonException($e->getMessage(), $e->getCode(), $e->getData());
        }catch(Exception $e){
            $newAddonDir && @rmdirs($newAddonDir);
            throw new Exception($e->getMessage());
        }finally{
            unset($file);
            $zip->close();
            @unlink($tmpFile);
        }
        $addon = self::getAddonInstance($name);
        Db::startTrans();
        try{
            $addon->install();
            Db::commit();
        }catch(Exception $e){
            Db::rollback();
            throw new Exception($e->getMessage());
        }
        Service::importsql($name,true);
        self::setAddonInfo($name,['state'=>0]);
        self::setAddonEvent();
        return true;
    }

    /**
     * 卸载插件
     * @param   string   $name   插件名
     * @param   boolean  $force  是否强制卸载
     * @return  boolean
     * @throws  Exception
     */
    public static function uninstall($name, $force = false)
    {
        if(!$name || !is_dir(ADDON_PATH . $name)){
            throw new Exception('Addon not exists');
        }
        if($force){
            $list = Service::getGlobalFiles($name);
            foreach($list as $v){
                @unlink(ROOT_PATH . ltrim($v,'@'));
            }
        }else{
            Service::noConflict($name);
        }
        try{
            $addon = self::getAddonInstance($name);
            $addon->uninstall();
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
        @rmdirs(ADDON_PATH . $name);
        self::setAddonEvent();
        return true;
    }

    /**
     * 更新升级
     * @param  string  $name    插件名称
     * @param  array   $extend  扩展参数
     */
    public static function upgrade($name, $extend = [])
    {
        $info = self::getAddonInstance($name)->getInfo($name);
        if($info['state']){
            throw new Exception('请先禁用插件后再进行升级操作！');
        }
        $tmpFile = Service::download($name, $extend);
        Service::backup($name);
        $addonDir = self::getAddonDir($name);
        $files = self::getCheckDirs();
        foreach($files as $index => $file){
            @rmdirs($addonDir . $file);
        }
        try{
            Service::unzip($name);
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }finally{
            @unlink($tmpFile);
        }
        Service::importsql($name);
        try{
            $addonName = ucfirst($name);
            $sourceFile = $addonDir . $addonName . ".php";
            $destFile = $addonDir . $addonName . "Upgrade.php";
            $classContent = str_replace("class {$addonName} extends", "class {$addonName}Upgrade extends", file_get_contents($sourceFile));
            file_put_contents($destFile, $classContent);
            $className = "\\addons\\" . $name . "\\" . $addonName . "Upgrade";
            $addon = new $className($name);
            if(method_exists($addon,"upgrade")){
                $addon->upgrade();
            }
            @unlink($destFile);
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
        self::setAddonInfo($name, ['version'=>$extend['version']]);
        self::setAddonEvent();
        return true;
    }

    /**
     * 下载插件
     * @param   string   $name     插件名称
     * @param   array    $extend   扩展参数
     * @return  string   返回下载后的插件临时路径
     */
    public static function download($name, $extend = [])
    {
        $addonsTempDir = self::getAddonsBackupDir();
        $tmpFile = $addonsTempDir . $name . ".zip";
        try{
            $client = self::getClient();
            $response = $client->get('/api/addon/download', ['query' => array_merge(['name' => $name], $extend)]);
            $body = $response->getBody();
            $content = $body->getContents();
            if(substr($content, 0, 1) === '{'){
                $json = (array)json_decode($content, true);
                if($json['data'] && isset($json['data']['url'])){
                    $response = $client->get($json['data']['url']);
                    $body = $response->getBody();
                    $content = $body->getContents();
                }else{
                    throw new AddonException($json['msg'], $json['code'], $json['data']);
                }
            }
        }catch(TransferException $e){
            throw new Exception("Addon package download failed");
        }
        if($write = fopen($tmpFile, 'w')){
            fwrite($write, $content);
            fclose($write);
            return $tmpFile;
        }
        throw new Exception("No permission to write temporary files");
    }

    /**
     * 启用插件
     * @param   string   $name   插件名称
     * @param   bool     $force  强制覆盖
     * @return  bool
     */
    public static function enable($name, $force = false)
    {
        if(!$name || !is_dir(ADDON_PATH . $name)){
            throw new Exception('Addon not exists');
        }
        if(!$force){
            Service::noConflict($name);
        }
        if(config('veitool.back_up')){
            $conflictFiles = self::getGlobalFiles($name,true);
            if($conflictFiles){
                $zip = new ZipFile();
                try{
                    foreach($conflictFiles as $v){
                        $v = ltrim($v,'@');
                        $zip->addFile(ROOT_PATH . $v, $v);
                    }
                    $zip->saveAsFile(self::getAddonsBackupDir() . $name . "-conflict-enable-" . date("YmdHis") . ".zip");
                }catch(Exception $e){
                    throw new Exception($e->getMessage());
                }finally{
                    $zip->close();
                }
            }
        }
        $files = self::getGlobalFiles($name);
        $onDir = self::getCheckDirs();
        $addonDir = self::getAddonDir($name);
        if($files){
            Service::aJson($name, ['files' => $files]);
        }
        foreach($onDir as $dir){
            if(is_dir($addonDir . $dir)){
                copydirs($addonDir . $dir, ROOT_PATH . ltrim($dir,'@'));
            }
        }
        if(config('veitool.clean')){
            foreach($onDir as $dir){
                @rmdirs($addonDir . $dir);
            }
        }
        $addon = self::getAddonInstance($name);
        try{
            if(method_exists($addon, "enable")){
                $addon->enable();
            }
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
        self::setAddonInfo($name, ['state'=>1]);
        self::setAddonEvent();
        return true;
    }

    /**
     * 禁用插件
     * @param   string   $name    插件名称
     * @param   bool     $force   是否强制禁用
     * @return  bool
     * @throws  Exception
     */
    public static function disable($name, $force = false)
    {
        if(!$name || !is_dir(ADDON_PATH . $name)){
            throw new Exception('Addon not exists');
        }
        if(!$force){
            Service::noConflict($name);
        }
        if(config('veitool.back_up')){
            $conflictFiles = self::getGlobalFiles($name,true);
            if($conflictFiles){
                $zip = new ZipFile();
                try{
                    foreach($conflictFiles as $v){
                        $v = ltrim($v,'@');
                        $zip->addFile(ROOT_PATH . $v, $v);
                    }
                    $zip->saveAsFile(self::getAddonsBackupDir() . $name . "-conflict-disable-" . date("YmdHis") . ".zip");
                }catch(Exception $e){
                    throw new Exception($e->getMessage());
                }finally{
                    $zip->close();
                }
            }
        }
        $addonDir = self::getAddonDir($name);
        $list = Service::getGlobalFiles($name);
        if(config('veitool.clean') || !$list){
            $ajson = Service::aJson($name);
            if(isset($ajson['files']) && is_array($ajson['files'])){
                foreach($ajson['files'] as $item){
                    $item = str_replace(['/', '\\'], VT_DS, $item);
                    $file = $addonDir . $item;
                    $fdir = dirname($file);
                    if(!is_dir($fdir)){
                        @mkdir($fdir, 0755, true);
                    }
                    $item = ltrim($item,'@');
                    if(is_file(ROOT_PATH . $item)){
                        @copy(ROOT_PATH . $item, $file);
                    }
                }
                $list = $ajson['files'];
            }
        }
        $dirs = [];
        $list = $list ? str_replace('@','',$list) : [];
        foreach($list as $v){
            $file = ROOT_PATH . $v;
            $dirs[] = dirname($file);
            @unlink($file);
        }
        $dirs = array_filter(array_unique($dirs));
        foreach($dirs as $v){
            remove_empty_folder($v);
        }
        $addon = self::getAddonInstance($name);
        try{
            if(method_exists($addon, "disable")){
                $addon->disable();
            }
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
        self::setAddonInfo($name, ['state'=>0]);
        self::setAddonEvent();
        return true;
    }

    /**
     * 解压插件到插件目录
     * @param   string   $name   插件名称
     * @return  string
     * @throws  Exception
     */
    public static function unzip($name)
    {
        if(!$name){
            throw new Exception('Invalid parameters');
        }
        $file = self::getAddonsBackupDir() . $name . '.zip';
        $zip = new ZipFile();
        try{
            $zip->openFile($file);
        }catch(ZipException $e){
            $zip->close();
            throw new Exception('Unable to open the zip file');
        }
        $dir = self::getAddonDir($name);
        if(!is_dir($dir)){
            @mkdir($dir, 0755);
        }
        try{
            $zip->extractTo($dir);
        }catch(ZipException $e){
            throw new Exception('Unable to extract the file');
        }finally{
            $zip->close();
        }
        return $dir;
    }

    /**
     * 检测插件是否完整
     * @param   string  $name  插件名称
     * @return  bool
     * @throws  Exception
     */
    public static function check($name)
    {
        if(!$name || !is_dir(ADDON_PATH . $name)){
            throw new Exception('Addon not exists');
        }
        $addon = self::getAddonInstance($name);
        if(!$addon->checkInfo()){
            throw new Exception("The configuration file info.ini content is incorrect");
        }
        return true;
    }

    /**
     * 是否有冲突
     * @param   string  $name  插件名称
     * @return  boolean
     * @throws  AddonException
     */
    public static function noConflict($name)
    {
        $list = self::getGlobalFiles($name, true);
        if($list){
            throw new AddonException("Conflicting file found", -3, ['files' => str_replace('@','',$list)]);
        }
        return true;
    }

    /**
     * 导入SQL
     * @param   string  $name  插件名称
     * @param   bool    $conf  导入配置
     * @return  boolean
     */
    public static function importsql($name,$conf = false)
    {
        $sqlFile = self::getAddonDir($name) . 'data' . VT_DS . 'install.sql';
        if(is_file($sqlFile)){
            $prefix = config('database.connections.mysql.prefix');
            $lines = file($sqlFile);
            $sql = '';
            $db = Db::connect();
            foreach($lines as $line){
                if(substr($line, 0, 2) == '--' || $line == '' || substr($line, 0, 2) == '/*'){
                    continue;
                }
                $sql .= $line;
                if(substr(trim($line), -1, 1) == ';'){
                    $sql = str_ireplace('__PREFIX__', $prefix, $sql);
                    $sql = str_ireplace('INSERT INTO ', 'INSERT IGNORE INTO ', $sql);
                    try{
                        $db->execute($sql);
                    }catch(\PDOException $e){
                        throw new Exception($e->getMessage());
                    }
                    $sql = '';
                }
            }
        }
        if($conf){
            $configFile = self::getConfigFile($name,true);
            if(is_file($configFile)){
                try{
                    $data = include_once $configFile;
                    if(is_array($data) && $data){
                        Db::name('setting')->insertAll($data);
                        Cache::delete('VSETTING');
                    }
                }catch(\PDOException $e){
                    throw new Exception($e->getMessage());
                }
            }
        }
        return true;
    }

    /**
     * 获取插件在全局的文件
     * @param   string    $name    插件名称
     * @param   boolean   $flag    是否只返回冲突文件（重名文件内容不同时为冲突文件）
     * @return  array
     */
    public static function getGlobalFiles($name, $flag = false)
    {
        $list = [];
        $addonDir = self::getAddonDir($name);
        $checkDirList = self::getCheckDirs();
        foreach($checkDirList as $dirName){
            if(!is_dir($addonDir . $dirName)){
                continue;
            }
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($addonDir . $dirName, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
            foreach($files as $fileinfo){
                if($fileinfo->isFile()){
                    $filePath = $fileinfo->getPathName();
                    $path = str_replace($addonDir, '', $filePath);
                    if($flag){
                        $destPath = ROOT_PATH . ltrim($path,'@');
                        if(is_file($destPath)){
                            if(filesize($filePath) != filesize($destPath) || md5_file($filePath) != md5_file($destPath)){
                                $list[] = $path;
                            }
                        }
                    }else{
                        $list[] = $path;
                    }
                }
            }
        }
        $list = array_filter(array_unique($list));
        return $list;
    }

    /**
     * 获取插件类的类名
     * @param  string  $name  插件名
     * @param  string  $type  获取类型 controller,hook
     * @param  string  $class 当前类名
     * @return string
     */
    public static function getAddonClass($name, $type = '', $class = null)
    {
        $name = parse_name($name);
        if(!is_null($class) && strpos($class, '.')){
            $class = explode('.', $class);
            $class[count($class) - 1] = parse_name(end($class), 1);
            $class = implode('\\', $class);
        }else{
            $class = parse_name(is_null($class) ? $name : $class, 1);
        }
        $namespace = "\\addons\\" . $name . ($type ? "\\$type\\" : "\\") . $class;
        return class_exists($namespace) ? $namespace : '';
    }

    /**
     * 获取插件配置文件
     * @param   string  $name  插件名称
     * @param   bool    $flag  返回类型 默认返回布尔值 1返回路径
     * @return  string/bool
     */
    protected static function getConfigFile($name,$flag = false)
    {
        $str = ADDON_PATH . $name . VT_DS . 'data' . VT_DS . 'config.php';
        return $flag ? $str : is_file($str);
    }

    /**
     * 获取可变动的全局文件夹目录
     * @return  array
     */
    protected static function getCheckDirs()
    {
        return ['app','extend','public','@view'];
    }

    /**
     * 获取远程服务器
     * @return  string
     */
    protected static function getServerUrl()
    {
        return config('veitool.api_url');
    }

    /**
     * 获取插件备份临时目录
     * @return  string
     */
    public static function getAddonsBackupDir()
    {
        $dir = RUNTIME_PATH . 'addons' . VT_DS;
        if(!is_dir($dir)){
            @mkdir($dir, 0755, true);
        }
        return $dir;
    }

    /**
     * 获取指定插件的目录
     * @return  string   $name   插件名
     */
    public static function getAddonDir($name)
    {
        return ADDON_PATH . $name . VT_DS;
    }

    /**
     * 获取插件的单例
     * @param  string  $name  插件名
     * @return obj|null
     */
    public static function getAddonInstance($name)
    {
        if(isset(self::$addon_instance[$name])){
            return self::$addon_instance[$name];
        }
        $class = self::getAddonClass($name);
        if(class_exists($class)){
            self::$addon_instance[$name] = new $class();
            return self::$addon_instance[$name];
        }else{
            throw new Exception("The addon file does not exist");
        }
    }

    /**
     * 设置基础配置信息
     * @param  string  $name   插件名
     * @param  array   $array  配置数据
     * @return boolean
     * @throws Exception
     */
    public static function setAddonInfo($name, $array)
    {
        $addon = self::getAddonInstance($name);
        $array = $addon->setInfo($name, $array);
        if(!isset($array['name']) || !isset($array['title']) || !isset($array['version'])){
            throw new Exception("插件配置错误");
        }
        $res = [];
        foreach($array as $key=>$val){
            if(is_array($val)){
                $res[] = "[$key]";
                foreach($val as $skey => $sval){
                    $res[] = "$skey = " . $sval;
                }
            }else{
                $res[] = "$key = " . $val;
            }
        }
        $file = ADDON_PATH . $name . VT_DS . 'info.ini';
        if($handle = fopen($file, 'w')){
            fwrite($handle, implode("\n", $res));
            fclose($handle);
        }else{
            throw new Exception("文件没有写入权限");
        }
        return true;
    }

    /**
     * 读取或修改插件资源记录
     * @param  string   $name   插件名
     * @param  array    $news    新数据
     * @return array
     */
    public static function aJson($name, $news = [])
    {
        $addonDir = self::getAddonDir($name);
        $file = $addonDir . 'data' . VT_DS . '.ajson';
        $data = [];
        if(is_file($file)){
            $data = (array)json_decode(file_get_contents($file), true);
        }
        $data = array_merge($data, $news);
        if($news){
            file_put_contents($file, json_encode($data, JSON_UNESCAPED_UNICODE));
        }
        return $data;
    }

    /**
     * 获取插件创建的表
     * @param  string  $name  插件名
     * @return array
     */
    public static function getAddonTables($name)
    {
        $tables = [];
        $addon  = self::getAddonInstance($name);
        if($addon->getInfo($name)){
            $regex   = "/^CREATE\s+TABLE\s+(IF\s+NOT\s+EXISTS\s+)?`?([a-zA-Z_]+)`?/mi";
            $sqlFile = ADDON_PATH . $name . VT_DS . 'data' . VT_DS . 'install.sql';
            if(is_file($sqlFile)){
                preg_match_all($regex, file_get_contents($sqlFile), $matches);
                if($matches && isset($matches[2]) && $matches[2]){
                    $prefix = config('database.connections.mysql.prefix');
                    $tables = array_map(function ($item) use ($prefix) {
                        return str_replace("__PREFIX__", $prefix, $item);
                    }, $matches[2]);
                }
            }
        }
        return $tables;
    }

    /**
     * 重建事件缓存
     * @return array
     */
    public static function setAddonEvent()
    {
        $event_file = ROOT_PATH . 'app' . VT_DS . 'event.php';
        $listen = include $event_file;
        $rs = scandir(ADDON_PATH);
        foreach($rs as $name){
            if(in_array($name,['.','..','.htaccess'])) continue;
            $cfile = ADDON_PATH . $name . VT_DS . 'event' . VT_DS . 'event.php';
            $ifile = ADDON_PATH . $name . VT_DS . 'info.ini';
            if(file_exists($cfile) && is_file($ifile)){
                $info = parse_ini_file($ifile, true, INI_SCANNER_TYPED) ?: [];
                $event = require_once $cfile;
                $event = $event['listen'] ?? [];
                if($event){
                    if(isset($info['state']) && $info['state']==1){
                        $listen['listen'] = array_merge_recursive($listen['listen'],$event);
                    }else{
                        $keys = array_keys($event);
                        foreach($keys as $k){
                            if(isset($listen['listen'][$k])){
                                if($listen['listen'][$k] === $event[$k]){
                                    unset($listen['listen'][$k]);
                                }else{
                                    foreach($listen['listen'][$k] as $i=>$v){
                                        if(strpos('\\'.$v.'\\','\\'.$name.'\\')!==false) unset($listen['listen'][$k][$i]);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        $code = "<?php\nreturn ".var_export($listen,true).";";
        $code = preg_replace('/(?<==> \n).*?(?=array)/si', '', $code);
        $code = str_replace(["array (", "),", ");", "=> \n", "  "], ["[", "],", "];", "=> ", "    "], $code);
        @file_put_contents($event_file, $code);
    }

    /**
     * 匹配配置文件中info信息
     * @param  ZipFile $zip
     * @return array|false
     * @throws Exception
     */
    protected static function getZipIni($zip)
    {
        $config = [];
        try{
            $info = $zip->getEntryContents('info.ini');
            $config = parse_ini_string($info);
        }catch(ZipException $e){
            throw new Exception('Unable to extract the file');
        }
        return $config;
    }

    /**
     * 验证压缩包、依赖验证
     * @param  array  $params
     * @return bool
     * @throws Exception
     */
    public static function valid($params = [])
    {
        $res = self::doRequest('/api/addon/valid', $params);
        if($res && isset($res['code'])){
            if($res['code']==1){
                return true;
            }else{
                throw new AddonException($res['msg'], $res['code'], $res['data']);
            }
        }else{
            throw new Exception("未知的数据格式");
        }
    }

    /**
     * 
     * @param  strimg   $url      请求地址
     * @param  array    $params   参数集合
     * @param  strimg   $method   请求方式默认POST
     * @return array
     */
    protected static function doRequest($url, $params=[], $method='POST')
    {
        $arr = [];
        try{
            $client = self::getClient();
            $options = strtoupper($method)=='POST' ? ['form_params'=>$params] : ['query'=>$params];
            $response = $client->request($method,$url,$options);
            $body = $response->getBody();
            $content = $body->getContents();
            $arr = (array)json_decode($content,true);
        }catch(TransferException $e){
            throw new Exception(config('app_debug') ? $e->getMessage() : '网络连接错误');
        }catch(\Exception $e){
            throw new Exception(config('app_debug') ? $e->getMessage() : '未知的数据格式');
        }
        return $arr;
    }

    /**
     * 获取请求对象
     * @return Client
     */
    protected static function getClient()
    {
        $options = [
            'base_uri'        => self::getServerUrl(),
            'timeout'         => 30,
            'connect_timeout' => 30,
            'verify'          => false,
            'http_errors'     => false,
            'headers'         => [
                'X-REQUESTED-WITH' => 'XMLHttpRequest',
                'Referer'          => dirname(request()->root(true)),
                'User-Agent'       => 'VeitoolAddon',
            ]
        ];
        static $client;
        return empty($client) ? $client = new Client($options) : $client;
    }

    /**
     * 备份插件
     * @param   string  $name  插件名称
     * @return  bool
     */
    public static function backup($name)
    {
        $addonsBackupDir = self::getAddonsBackupDir();
        $file = $addonsBackupDir . $name . '-backup-' . date("YmdHis") . '.zip';
        $zip = new ZipFile();
        try{
            $zip->addDirRecursive(self::getAddonDir($name))->saveAsFile($file)->close();
        }catch(ZipException $e){
            throw new Exception($e->getMessage());
        }finally{
            $zip->close();
        }
        return true;
    }

}
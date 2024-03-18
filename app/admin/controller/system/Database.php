<?php
/**
 * ===========================================================================
 * Veitool 快捷开发框架系统
 * Author: Niaho 26843818@qq.com
 * Copyright (c)2019-2024 www.veitool.com All rights reserved.
 * Licensed: 这不是一个自由软件，不允许对程序代码以任何形式任何目的的再发行
 * ---------------------------------------------------------------------------
 */
namespace app\admin\controller\system;

use think\facade\Db;
use tool\MysqlBackup as BK;
use app\admin\controller\AdminBase;

/**
 * 后台数据库管理控制器
 */
class Database extends AdminBase
{
    /**
     * 数据库实例
     * @var BK
     */
    protected $db;

    /**
     * 控制器初始化
     * @return mixed
     */
    public function __init()
    {
        $config = ['compress'=>1, 'level'=>5]; //compress：是否压缩  level：压缩级别
        $this->db = new BK($config);
    }

    /**
     * 数据表列表
     * @return mixed
     */
    public function index()
    {
        $rs = $this->db->dataList();
        $total_size = $num = 0;
        foreach ($rs as $k => $v){
            $rs[$k]['data_length']  = round($v['data_length']/1024/1024, 3);  //数据大小
            $rs[$k]['index_length'] = round($v['index_length']/1024/1024, 3); //索引大小
            $rs[$k]['data_free']    = round($v['data_free']/1024/1024, 3);    //碎片大小
            $rs[$k]['data_total']   = round($rs[$k]['data_length']+$rs[$k]['index_length'], 3); //合计
            $total_size += $rs[$k]['data_total'];
            $num++;
        }
        $this->assign([
            'tables'    => $num,
            'totalsize' => $total_size,
            'list'      => json_encode($rs)
        ]);
        return $this->fetch();
    }

    /**
     * 数据表备份处理
     * @return json
     */
    public function backup()
    {
        $d = $this->only(['tables/a','sizes/a']);
        return $this->returnMsg($this->db->doBack($d['tables'],$d['sizes']));
    }

    /**
     * 备份数据替换
     * @return json
     */
    public function replace()
    {
        $d = $this->only(['files','old','new','safepass']);
        if($this->manUser['password'] != set_password($d['safepass'],$this->manUser["passsalt"])) return $this->returnMsg(['code'=>1,'p'=>0,'filenum'=>0,'msg'=>'安全密码错误']);
        return $this->returnMsg($this->db->doReplace($d['files'],$d['old'],$d['new']));
    }

    /**
     * 数据备份列表
     * @return json
     */
    public function imports()
    {
        $rs = $this->db->getBackFile();
        if($rs) $rs = array_reverse($rs);
        return $this->returnMsg($rs);
    }

    /**
     * 数据备份删除
     * @return json
     */
    public function del()
    {
        $filenames = $this->request->post("filenames");
        if(!is_array($filenames) || is_null($filenames)) return $this->returnMsg('请选择备份系列');
        if($rs = $this->db->delBackFile($filenames)){
            return $this->returnMsg('删除备份成功',1);
        }
        return $this->returnMsg('删除备份失败:'.$rs);
    }

    /**
     * 数据导入
     * @return json
     */
    public function import()
    {
        @set_time_limit(0);
        $filename = $this->request->post("filename",'','strip_sql');
        return $this->returnMsg($this->db->doImport($filename));
    }

    /**
     * 下载备份
     * @return mixed
     */
    public function download()
    {
        $d = $this->only(['filename','pid/d'],'get');
        $this->db->downFile($d['filename'], $d['pid']);
    }

    /**
     * 数据表注释修改
     * @return json
     */
    public function edit()
    {
        $d = $this->only(['table','note']);
        if(!$d['table'] || !$d['note']) return $this->returnMsg('请选择要修改的表以及注释不能为空');
        Db::query("ALTER TABLE `{$d['table']}` COMMENT='{$d['note']}'");
        return $this->returnMsg('表注释修改成功',1);
    }

    /**
     * 数据表字典
     * @param  array   $table   操作表名
     * @return json
     */
    public function dict($table = '')
    {
        if(!$table) return $this->returnMsg('参数错误');
        $table = strip_sql($table, 0);
        $rs = $this->db->dataList($table);
        return $this->returnMsg($rs);
    }

    /**
     * 数据表修复
     * @return json
     */
    public function xiufu()
    {
        $table = $this->request->post("table",'','strip_sql');
        if(!$table) return $this->returnMsg('请选择需要修复的数据表');
        $this->db->repair($table);
        return $this->returnMsg('修复成功',1);
    }

    /**
     * 数据表优化
     * @return json
     */
    public function youhua()
    {
        $table = $this->request->post("table",'','strip_sql');
        if(!$table) return $this->returnMsg('请选择需要优化的数据表');
        $this->db->optimize($table);
        return $this->returnMsg('优化成功',1);
    }

}
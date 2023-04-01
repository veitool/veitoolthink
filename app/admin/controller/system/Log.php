<?php
/**
 * ===========================================================================
 * Veitool 快捷开发框架系统
 * Author: Niaho 26843818@qq.com
 * Copyright (c)2019-2023 www.veitool.com All rights reserved.
 * Licensed: 这不是一个自由软件，不允许对程序代码以任何形式任何目的的再发行
 * ---------------------------------------------------------------------------
 */
namespace app\admin\controller\system;

use app\admin\controller\AdminBase;
use app\model\system\LoginLog;
use app\model\system\ManagerLog;
use app\model\system\WebLog;

/**
 * 后台管理日志控制器
 */
class Log extends AdminBase
{
    /**
     * 日志管理主面板
     * @return mixed
     */
    public function index()
    {
        $this->assign([
            'limit' => 10,
            'PT' => json_encode(['后台','会员','门店','终端'])
        ]);
        return $this->fetch();
    }

    /**
     * 登录日志
     * @param  string  $do  异步操作
     * @return json
     */
    public function login($do='')
    {
        $Log = new LoginLog();
        if($do=='check'){
            $d = $this->only(['logid/d','password']);
            $rs = $Log->where('logid',$d['logid'])->field('password,passsalt')->find();
            $m = $rs && set_password($d['password'], $rs['passsalt']) == $rs['password'] ? '校验结果匹配' : '校验不匹配';
            return $this->returnMsg($m,1);
        }
        $list = $Log->listQuery()->toArray();
        foreach($list['data'] as &$v){
            $v['password'] = substr($v['password'], 0, 6).'******'.substr($v['password'], 26);
            $iarr = explode('.', $v['loginip']);
            if($this->manUser['userid']>1) $v['loginip'] = $iarr[0].'. *** .'.$iarr[3];
            unset($v['passsalt']);
        }
        return $this->returnMsg($list);
    }

    /**
     * 清理登录日志
     * @return json
     */
    public function ldel()
    {
        $time = VT_TIME - 30*86400;
        if(LoginLog::del("logintime < $time")){
            return $this->returnMsg("清理登录日志成功！", 1);
        }else{
            return $this->returnMsg("没有满足条件的登录日志可清理！");
        }
    }

    /**
     * 管理日志
     * @return json
     */
    public function manager()
    {
        $list = (new ManagerLog())->listQuery()->toArray();
        foreach($list['data'] as &$v){
            $iarr = explode('.', $v['ip']);
            if($this->manUser['userid']>1) $v['ip'] = $iarr[0].'. *** .'.$iarr[3];
        }
        return $this->returnMsg($list);
    }

    /**
     * 清理管理日志
     * @return json
     */
    public function mdel()
    {
        $time = VT_TIME - 7*86400;
        if(ManagerLog::del("logtime < $time")){
            return $this->returnMsg("清理管理日志成功！", 1);
        }else{
            return $this->returnMsg("没有满足条件的管理日志可清理！");
        }
    }
    
    /**
     * 网站日志
     * @return json
     */
    public function web()
    {
        $list = (new WebLog())->listQuery()->toArray();
        foreach($list['data'] as &$v){
            $iarr = explode('.', $v['ip']);
            if($this->manUser['userid']>1) $v['ip'] = $iarr[0].'. *** .'.$iarr[3];
        }
        return $this->returnMsg($list);
    }

    /**
     * 清理网站日志
     * @return json
     */
    public function wdel()
    {
        $pre = config('database.connections.'.config('database.default').'.prefix');
        \think\facade\Db::query("truncate {$pre}web_log");
        return $this->returnMsg("清理管理日志成功！", 1);
    }

}
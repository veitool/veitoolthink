<?php
/**
 * ===========================================================================
 * Veitool 快捷开发框架系统
 * Author: Niaho 26843818@qq.com
 * Copyright (c)2019-2025 www.veitool.com All rights reserved.
 * Licensed: 这不是一个自由软件，不允许对程序代码以任何形式任何目的的再发行
 * ---------------------------------------------------------------------------
 */
namespace app\admin\controller;

use app\BaseController;
use app\model\system\SystemManager as Manager;
use app\model\system\SystemLoginLog as LoginLog;
use app\model\system\SystemOnline as Online;
use tool\Lock;

/**
 * 后台登录
 */
class Login extends BaseController
{
    /**
     * 映射路径
     * @var string
     */
    protected $appMap = '';

    /**
     * 覆盖无需业务 & 初始映射路径
     */
    protected function __home(){
        $this->appMap = VT_DIR . '/' . (array_search("admin", config('app.app_map')) ?: 'admin');
    }

    /**
     * 登录首页
     */
    public function index()
    {
        if(!empty(session(VT_MANAGER))) return $this->redirect($this->appMap);
        $this->assign([
            "appMap" => $this->appMap
        ]);
        return $this->fetch();
    }

    /**
     * 退出系统
     */
    public function logout()
    {
        if($User = session(VT_MANAGER)) Online::del(['uid'=>$User['uid'],'userid'=>$User['userid']]); //删除在线数据
        session(null);
        return $this->redirect($this->appMap);
    }

    /**
     * 解锁屏处理
     * @return  json
     */
    public function unlock()
    {
        if(is_null($us=session(VT_MANAGER))) return $this->returnMsg('还未登录');
        $password = $this->request->post('password','');
        if($us['password'] === set_password($password,$us['passsalt'])){
            return $this->returnMsg('success',1);
        }else{
            return $this->returnMsg('解锁密码错误');
        }
    }

    /**
     * 登录验证
     * @return  json
     */
    public function check()
    {
        //多次尝试验证
        $ip = $this->request->ip();
        if(Lock::check(['key'=>'LOGIN_'.$ip])) return $this->returnMsg(Lock::msg());
        $d = $this->only(['username/*/u/管理帐号','password/*/p/登录密码','captcha']);
        if(vconfig('admin_captcha',1) && !captcha_check($d['captcha'])) return $this->returnMsg('验证码错误！');
        $username = $d['username'];
        $password = $d['password'];
        //查询用户数据
        $rs = Manager::get(compact('username'));
        if(empty($rs)){
            LoginLog::add($username, $password, '', '账号错误');
            Lock::add();
            return $this->returnMsg('帐号或密码错误！');
        }
        if($rs->state == 0) return $this->returnMsg('帐号已被停用！');
        if($rs['password'] === set_password($password,$rs['passsalt'])){
            $rs->logintime = time();
            $rs->loginip   = $ip;
            $rs->logins ++;
            $rs->save();
            $rs = $rs->toArray();
            $rs['uid'] = 'AM-'.uniqid(); //设置编号
            LoginLog::add($username, $password, $rs['passsalt']);
            session(VT_MANAGER,$rs);
            Lock::del();
            return $this->returnMsg('登录成功！',1,['url'=>($this->appMap ?: '/')]);
        }
        LoginLog::add($username, $password, $rs['passsalt'], '密码错误');
        Lock::add();
        return $this->returnMsg('帐号或密码错误！');
    }

}
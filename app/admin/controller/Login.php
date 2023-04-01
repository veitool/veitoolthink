<?php
/**
 * ===========================================================================
 * Veitool 快捷开发框架系统
 * Author: Niaho 26843818@qq.com
 * Copyright (c)2019-2023 www.veitool.com All rights reserved.
 * Licensed: 这不是一个自由软件，不允许对程序代码以任何形式任何目的的再发行
 * ---------------------------------------------------------------------------
 */
namespace app\admin\controller;

use app\BaseController;
use app\model\system\Manager;
use app\model\system\LoginLog;
use app\model\system\Online;
use think\facade\Session;

/**
 * 后台登录
 */
class Login extends BaseController
{
    /**
     * 登录首页
     */
    public function index()
    {
        if(!empty(session(VT_MANAGER))) return $this->redirect(APP_MAP);
        return $this->fetch();
    }

    /**
     * 退出系统
     */
    public function logout()
    {
        $User = session(VT_MANAGER);
        Online::where([['userid','=',$User['userid']],['ip','=',VT_IP]])->delete(); //删除在线数据
        session(VT_MANAGER,null); //清空Session
        return $this->redirect(url('admin/login/index')->build());
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
        $d = $this->only(['username/*/u/管理帐号','password/*/p/登录密码','captcha']);
        if(vconfig('admin_captcha',1) && !captcha_check($d['captcha'])) return $this->returnMsg('验证码错误！');
        $username = $d['username'];
        $password = $d['password'];
        //多次尝试验证
        $lock = new \tool\ErrLock();
        if($lock->check()) return $this->returnMsg($lock->msg);
        //查询用户数据
        $rs = Manager::get(compact('username'));
        if(empty($rs)){
            LoginLog::add($username, $password, '', '账号错误');
            $lock->add();
            captcha('admin'); //重建验证码
            return $this->returnMsg('帐号或密码错误！');
        }
        if($rs->state == 0) return $this->returnMsg('帐号已被停用！');
        if($rs['password'] === set_password($password,$rs['passsalt'])){
            $rs->token     = set_token(VT_TIME);
            $rs->tokentime = VT_TIME;
            $rs->logintime = VT_TIME;
            $rs->loginip   = VT_IP;
            $rs->logins ++;
            $rs->save();
            LoginLog::add($username, $password, $rs['passsalt']);
            session(VT_MANAGER,$rs->toArray());
            $lock->del();
            return $this->returnMsg('登录成功！',1,['url'=>APP_MAP]);
        }
        LoginLog::add($username, $password, $rs['passsalt'], '密码错误');
        $lock->add();
        captcha('admin'); //重建验证码
        return $this->returnMsg('帐号或密码错误！');
    }

}
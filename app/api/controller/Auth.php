<?php
/**
 * ===========================================================================
 * Veitool 快捷开发框架系统
 * Author: Niaho 26843818@qq.com
 * Copyright (c)2019-2025 www.veitool.com All rights reserved.
 * Licensed: 这不是一个自由软件，不允许对程序代码以任何形式任何目的的再发行
 * ---------------------------------------------------------------------------
 */
namespace app\api\controller;

use app\BaseController;
use app\model\system\SystemManager as Manager;
use app\model\system\SystemLoginLog as LoginLog;
use jwt\JwtToken;
use tool\Lock;

/**
 * 登录、鉴权控制器
 */
class Auth extends BaseController
{
    /**
     * 登录
     */ 
    public function login()
    {
        return ''; // 测试请删除该行
        //多次尝试验证
        $ip = $this->request->ip();
        if(Lock::check(['key'=>'LOGIN_'.$ip])) return $this->returnMsg(Lock::msg());
        $d = $this->only(['username/*/u/管理帐号','password/*/p/登录密码','captcha','type']);
        if(vconfig('admin_captcha',1) && !captcha_check($d['captcha'])) return $this->returnMsg('验证码错误！');
        $username = $d['username'];
        $password = $d['password'];
        //查询用户数据
        $rs = Manager::one(compact('username'));
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
            $rs->uid = 'AM-'.uniqid(); //设置编号
            LoginLog::add($username, $password, $rs['passsalt']);

            // 生成token
            $access_exp = config('veitool.jwt.access_exp', 7200);
            $token = JwtToken::generateToken([
                'access_exp' => $access_exp,   // token有效期
                'username'   => $rs->username, // 用户名
                'uid'        => $rs->uid,      // 用户UID
                'id'         => $rs->userid,   // 用户ID
                'type'       => $d['type'],    // 登录类型 pc|h5|app
                'plat'       => 'veitool',     // 所属平台
            ]);

            // 解除锁定
            Lock::del();

            // 签发 token
            return $this->returnMsg('登录成功！', 1, ['token' => $token, 'access_exp' => $access_exp]);
        }
        LoginLog::add($username, $password, $rs['passsalt'], '密码错误');
        Lock::add();
        return $this->returnMsg('帐号或密码错误！');
    }

    /**
     * 签发Token
     */
    public function send()
    {
        return ''; // 测试请删除该行
        $access_exp = 7200;
        $token = JwtToken::generateToken([
            'access_exp' => $access_exp,    // token有效期
            'username'   => 'admin',        // 用户名
            'uid'        => 'AM-'.uniqid(), // $rs->uid,用户UID
            'id'         => 1,              // 用户ID
            'type'       => 'pc',           // 登录类型 pc|h5|app
            'plat'       => 'veitool',      // 所属平台
        ]);
        // 签发 token
        return $this->returnMsg('登录成功！', 1, ['token' => $token, 'access_exp' => $access_exp]);
    }

    /**========================== 以下接口可以采用 vscode 中的插件 Postcode 进行测试 ==========================**/

    /**
     * 验证Token
     * @return void
     */
    public function test1()
    {
        try {
            $arr = JwtToken::verify();
            echo '<pre>';print_r($arr);
            return 'OK';
        } catch (\Exception $e){
            return 'Auth验证反馈：'. $e->getMessage() .'【'.$e->getCode().'】';
        }
    }
 
    /**
     * 刷新Token
     * @return void
     */
    public function test2()
    {
        try {
            $oldToken = [];
            $token = JwtToken::refreshToken($oldToken);
            echo '<pre>';print_r($oldToken); echo '<hr/>';print_r($token);
            return 'OK';
        } catch (\Exception $e){
            return 'Auth刷新反馈：'. $e->getMessage() .'【'.$e->getCode().'】';
        }
    }

    /**
     * 清除Token
     * @return void
     */
    public function test3()
    {
        try {
            $flage = JwtToken::clear();
            return $flage ? '清除成功1' : '缓存已被清除';
        } catch (\Exception $e){
            return 'Auth清除反馈：'. $e->getMessage() .'【'.$e->getCode().'】';
        }
    }

    /**
     * 获取用户信息
     * @return void
     */
    public function test4()
    {
        try {
            $user = JwtToken::getUser();
            echo '<pre>';print_r($user);
            return 'OK';
        } catch (\Exception $e){
            return 'Auth用户信息反馈：'. $e->getMessage() .'【'.$e->getCode().'】';
        }
    }

    /**
     * 获取令牌有效期剩余时长
     * @return void
     */
    public function test5()
    {
        try {
            $time = JwtToken::getTokenExp();
            echo '<pre>';print_r($time);
            return ' = OK';
        } catch (\Exception $e){
            return 'Auth令牌时长反馈：'. $e->getMessage() .'【'.$e->getCode().'】';
        }
    }

    /**
     * 获取令牌
     * @return void
     */
    public function test6()
    {
        try {
            $rs = JwtToken::getExtend();
            echo '<pre>';print_r($rs);
            return ' = OK';
        } catch (\Exception $e){
            return 'Auth获取令牌反馈：'. $e->getMessage() .'【'.$e->getCode().'】';
        }
    }

    /**
     * 验证令牌
     * @return void
     */
    public function test7()
    {
        try {
            $token = JwtToken::verify();
            echo '<pre>';print_r($token);
            return ' = OK';
        } catch (\Exception $e){
            return 'Auth验证令牌反馈：'. $e->getMessage() .'【'.$e->getCode().'】';
        }
    }

}
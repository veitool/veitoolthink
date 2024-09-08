<?php
/**
 * ===========================================================================
 * Veitool 快捷开发框架系统
 * Author: Niaho 26843818@qq.com
 * Copyright (c)2019-2024 www.veitool.com All rights reserved.
 * Licensed: 这不是一个自由软件，不允许对程序代码以任何形式任何目的的再发行
 * ---------------------------------------------------------------------------
 */
namespace app\admin\controller;

use app\BaseController;
use app\model\system\SystemRoles as Roles;
use app\model\system\SystemManager as Manager;

/**
 *【后台控制器抽象基类】
 */
abstract class AdminBase extends BaseController
{
    /**
     * 后台管理员信息
     * @var array
     */
    protected $manUser = [];

    /**
     * 映射路径
     * @var string
     */
    protected $appMap = '';

    /**
     * 当前路由uri
     * @var string
     */
    protected $routeUri = '';

    /**
     * Token 名
     * @var string
     */
    protected $tokenName = '__admin__';

    /**
     * 覆盖无需业务
     */
    protected function __home(){}

    /**
     * 后台控制器验证初始化
     */
    protected final function __auth()
    {
        //映射路径
        $this->appMap = VT_DIR . '/' . (array_search("admin", config('app.app_map')) ?: 'admin');
        //验证登录
        $this->isLogin();
        //载入权限菜单
        $this->loadMenusRoles();
        //构组路由: 控制器 + 方法 + （参数action的传值）
        $this->routeUri = strtolower(config()['ADDON_APP'][0].$this->request->controller()."/".$this->request->action().(($action = $this->request->get('action')) ? '/'.$action : ''));
        //验证权限
        $this->isPower();
    }

    /**
     * 登录判断
     */
    private function isLogin()
    {
        if(is_null($this->manUser = session(VT_MANAGER))){
            $url = $this->appMap.'/login/index';
            if($this->request->isAjax()){
                $this->exitMsg('您还未登录或已过期，请先登录！',401,['url'=>$url]);
            }else{
                $this->redirect($url);
            }
        }
    }

    /**
     * 载入管理员角色权限菜单
     */
    private function loadMenusRoles()
    {
        $us = Manager::get("username = '{$this->manUser['username']}' AND state > 0");
        if($us && $this->manUser['password'] == $us['password'] && $this->manUser['passsalt'] == $us['passsalt']){
            //禁止同帐号同时异地登录处理
            if(in_array(vconfig('ip_login',0),[2,3]) && $us['loginip'] != $this->request->ip()){
                session(null);
                $url = $this->appMap.'/login/index';
                $this->exitMsg('您的帐号已在其他终端登录！',$this->request->isAjax() ? 401 : 303,['url'=>$url]);
            }
            $us = $us->toArray();
            $us['role_menuid'] = '';
            $us['role_name'] = '超级管理员';
            $us['uid'] = $this->manUser['uid'];
            //非超级管理员载入角色权限['roleid'=>角色ID,'role_name'=>+角色名,'role_menuid'=>+拥有的菜单ID串'actions'=>+权限记录集]
            $this->manUser = $us['userid']>1 ? array_merge($us, Roles::cache($us['roleid'])) : $us;
        }else{
            session(null);
            $url = $this->appMap.'/login/index';
            $this->exitMsg('您还未登录或已过期，请先登录！',$this->request->isAjax() ? 401 : 303,['url'=>$url]);
        }
    }

    /**
     * 权限判断
     */
    private function isPower()
    {
        if($this->manUser['userid']>1 && !in_array($this->routeUri,$this->manUser['actions'])){
            $this->exitMsg('抱歉，您没有该项权限请联系管理员！',$this->request->isAjax() ? 401 : 400);
        }
    }

    /**
     * 日志/在线处理
     * @access  protected
     * @param   sting   $tip   提示
     */
    protected function logon(string $tip = '')
    {
        //操作日志
        if(vconfig('admin_log',0)){
            \app\model\system\SystemManagerLog::add(['url'=>$this->routeUri.($tip ? ' '.$tip : ''),'username'=>$this->manUser['username'],'ip'=>$this->request->ip()]);
        }
        //在线统计 【online_on = 0:关闭全部 1:开启后台 2:开启会员 3:开启全部】 
        if(in_array(vconfig('online_on',0),[1,3])){
            \app\model\system\SystemOnline::recod($this->manUser, $this->routeUri, 0);
        }
    }

}
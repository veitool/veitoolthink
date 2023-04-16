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
use app\model\system\Roles;
use app\model\system\Manager;

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
     * 当前路由uri
     * @var string
     */
    protected $routeUri = '';

    /**
     * 覆盖无需业务
     */
    protected function __home(){}

    /**
     * 后台控制器验证初始化
     */
    protected function __auth()
    {
        //验证登录
        $this->isLogin();
        //载入权限菜单
        $this->loadMenusRoles();
        //获取控制器名
        $controller = $this->request->controller();
        //合取方法
        $url = $controller."/".$this->request->action();
        //构组路由: 控制器 + 方法 + （参数action的传值）
        $this->routeUri = strtolower(ADDON_APP.$url.(($action = $this->request->get('action')) ? '/'.$action : ''));
        //验证权限
        $this->isPower();
    }

    /**
     * 登录判断
     */
    private function isLogin()
    {
        if(is_null($this->manUser=session(VT_MANAGER))){
            $url = ADDON_APP ? '/' : APP_MAP.'/login/index';
            if($this->request->isAjax()){
                $this->exitMsg('您还未登录或已过期，请先登录！',401,['url'=>$url]);
            }else{
                exit(header("Location:".$url));
            }
        }
    }

    /**
     * 载入管理员角色权限菜单
     */
    private function loadMenusRoles()
    {
        $us = $this->manUser;
        $rs = Manager::get("username='$us[username]' AND state>0");
        if($rs && $us['password']==$rs['password'] && $us['passsalt']==$rs['passsalt']){
            //禁止同帐号同时异地登录处理
            if(in_array(vconfig('ip_login',0),[2,3]) && $rs['loginip']!=VT_IP){
                session(VT_MANAGER,null);
                $url = ADDON_APP ? '/' : APP_MAP.'/login/index';
                if($this->request->isAjax()){
                    $this->exitMsg('您的帐号已在其他终端登录！',401,['url'=>$url]);
                }else{
                    $this->exitMsg('您的帐号已在其他终端登录！',303,['url'=>$url]);
                }
            }
            $us = $rs->toArray();
            session(VT_MANAGER,$us);
            $us['role_menuid'] = '';
            $us['role_name']   = '超级管理员';
            //非超级管理员载入角色权限['roleid'=>角色ID,'role_name'=>+角色名,'role_menuid'=>+拥有的菜单ID串'actions'=>+权限记录集]
            $this->manUser = $us['userid']>1 ? array_merge($us, Roles::cache($us['roleid'])) : $us;
        }else{
            session(VT_MANAGER,null);
            $url = ADDON_APP ? '/' : APP_MAP.'/login/index';
            if($this->request->isAjax()){
                $this->exitMsg('您还未登录或已过期，请先登录！',401,['url'=>$url]);
            }else{
                $this->exitMsg('您还未登录或已过期，请重新登录！',303,['url'=>$url]);
            }
        }
    }

    /**
     * 权限判断
     */
    private function isPower()
    {
        if($this->manUser['userid']>1 && !in_array($this->routeUri,$this->manUser['actions'])){
            if($this->request->isAjax()){
                $this->exitMsg('抱歉，您没有该项权限请联系管理员！',401);
            }else{
                header("Content-type:text/html;charset=utf-8");
                exit('抱歉，您没有该项权限请联系管理员！');
            }
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
            \app\model\system\ManagerLog::add(['url'=>$this->routeUri.($tip ? ' '.$tip : ''),'username'=>$this->manUser['username'],'ip'=>VT_IP]);
        }
        //在线统计 【online_on = 0:关闭全部 1:开启后台 2:开启会员 3:开启全部】 模型中支持 replace 为 create 的第3个参数设为 true 或者 \think\facade\Db::name('online')->replace()->insert([数据集]) 
        if(in_array(vconfig('online_on',0),[1,3])){
            \app\model\system\Online::create(['userid'=>$this->manUser['userid'],'username'=>$this->manUser['username'],'url'=>$this->routeUri,'etime'=>VT_TIME,'ip'=>VT_IP],['userid','username','url','etime','ip'],true);
        }
    }

}
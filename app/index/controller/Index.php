<?php
/**
 * ===========================================================================
 * Veitool 快捷开发框架系统
 * Author: Niaho 26843818@qq.com
 * Copyright (c)2019-2024 www.veitool.com All rights reserved.
 * Licensed: 这不是一个自由软件，不允许对程序代码以任何形式任何目的的再发行
 * ---------------------------------------------------------------------------
 */
namespace app\index\controller;

use think\Response;
use think\exception\HttpResponseException;

/**
 * 前台控制器
 */
class Index extends \app\BaseController
{
    /**
     * 首页
     */ 
    public function index(){
        $re = Response::create(app()->getRootPath().'app/v_msg.tpl','view')->assign(['msg'=>'欢迎使用 Veitool 后台管理开发框架！这是前台首页展示内容。','site'=>vconfig('site_title')])->header();
        throw new HttpResponseException($re);
    }

}
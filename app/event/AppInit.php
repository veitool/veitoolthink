<?php
/**
 * ===========================================================================
 * Veitool 快捷开发框架系统
 * Author: Niaho 26843818@qq.com
 * Copyright (c)2019-2024 www.veitool.com All rights reserved.
 * Licensed: 这不是一个自由软件，不允许对程序代码以任何形式任何目的的再发行
 * ---------------------------------------------------------------------------
 */
namespace app\event;

use think\app\Service;
use think\facade\Route;
use think\facade\Cache;
use think\facade\Event;

/**
 * 初始化配置
 */
class AppInit extends Service
{
    public function handle()
    {
        /* 路由处理 旧模式不兼容nginx：$url = request()->pathinfo(); */
        $url = str_replace(".".config('route.url_html_suffix'), '', ltrim($this->app->request->url(), '/'));
        $url = strpos($url, '?') ? strstr($url, '?', true) : $url;
        $arr = explode('/', $url);
        $addon = $arr[0];
        $module = '';
        /* 插件应用处理 */
        if(in_array($addon,config('veitool.addons'))){
            $this->app->config->set(['app_express'=>false], 'app');
            $module = $addon ?: 'index';
            $contr  = isset($arr[1]) && $arr[1] ? $arr[1] : 'index';
            $method = isset($arr[2]) && $arr[2] ? $arr[2] : 'index';
            $this->app->setNamespace("addons\\" . $module);
            $this->app->setAppPath($this->app->getRootPath() . 'addons' . VT_DS . $module . VT_DS);
            is_file($file = ADDON_PATH . $addon . VT_DS . 'data' . VT_DS . 'route.php') && require_once($file);
            Route::rule($url, $module . '/' . $contr . '/' . $method);
            $module .= '/';
        }/**/
        /* 插件应用名 用于AdminBase.php中兼容插件权限 */
        define('ADDON_APP', $module);
    }

}
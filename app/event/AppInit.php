<?php
/**
 * ===========================================================================
 * Veitool 快捷开发框架系统
 * Author: Niaho 26843818@qq.com
 * Copyright (c)2019-2023 www.veitool.com All rights reserved.
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
        // 版本信息
        define('VT_VERSION', '1.0.0');
        // 后台管理员 session 标识
        define('VT_MANAGER', 'V_MANAGER');
        // 前台会员 session 标识
        define('VT_MEMBER', 'V_MEMBER');
        // 时间戳
        define('VT_TIME', time());
        // 分隔符
        define('VT_DS', DIRECTORY_SEPARATOR);
        // 项目目录
        define('ROOT_PATH', $this->app->getRootPath());
        // 插件目录
        define('ADDON_PATH', ROOT_PATH . 'addons' . VT_DS);
        // 临时目录
        define('RUNTIME_PATH', ROOT_PATH . 'runtime' . VT_DS);
        // 站点目录
        define('VT_PUBLIC', ROOT_PATH . 'public' . VT_DS);
        // IP地址
        define('VT_IP', $this->app->request->ip());
        // 资源目录 运行目录为根目录时请设为空\其他二级或多级目录时后面无斜杠如：/mydir 或 /mydir/xyz
        define('VT_DIR', config('view.tpl_replace_string.{PUBLIC__PATH}'));

        /* 路由处理 旧模式不兼容nginx：$url = request()->pathinfo(); */
        $url = str_replace(".".config('route.url_html_suffix'), '', ltrim($this->app->request->url(), '/'));
        $url = strpos($url, '?') ? strstr($url, '?', true) : $url;
        $arr = explode('/', str_replace('.', '/', $url));
        $addon = $arr[0];
        $module = '';

        /* 非系统应用定位到插件目录为应用目录 app.php 配置中需设置 'app_express' => false */
        $excl = array_merge(config('veitool.sys_app', ['admin','index','api','']), array_flip(config('app.app_map')));
        if(!in_array($addon,$excl)){
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

        /*插件事件初始化*/
        $listen = Cache::get("addon_event_list");
        if(!empty($listen)){
            foreach($listen as $k => $v){
                if(!empty($v)){
                    Event::listenEvents($v);
                }
            }
        }/**/
    }

}
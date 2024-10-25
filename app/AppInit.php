<?php
declare(strict_types=1);

namespace app;

use Closure;
use think\Request;
use think\Response;
use think\facade\Route;

/**
 * App初始化
 */
class AppInit
{
    /**
     * 前置中间件
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        $App = app();
        /* 路由处理 旧模式不兼容nginx：$url = request()->pathinfo(); */
        $url = VT_DIR ? str_replace([".".config('route.url_html_suffix'),VT_DIR.'/'], '', $App->request->url()) : str_replace(".".config('route.url_html_suffix'), '', ltrim($App->request->url(), '/'));
        $url = strpos($url, '?') ? strstr($url, '?', true) : $url;
        $arr = explode('/', $url);
        $addon = $arr[0];
        $module = '';
        /* 插件应用处理 */
        if(in_array($addon,config('veitool.addons'))){
            $App->config->set(['app_express'=>false], 'app');
            $module = $addon ?: 'index';
            $contr  = isset($arr[1]) && $arr[1] ? $arr[1] : 'index';
            $method = isset($arr[2]) && $arr[2] ? $arr[2] : 'index';
            $App->setNamespace("addons\\" . $module);
            $App->setAppPath($App->getRootPath() . 'addons' . VT_DS . $module . VT_DS);
            is_file($file = ADDON_PATH . $addon . VT_DS . 'data' . VT_DS . 'route.php') && require_once($file);
            Route::rule($url, $module . '/' . $contr . '/' . $method);
            $module .= '/';
        }/**/
        /* 插件应用名 用于 AdminBase.php 中兼容插件权限 */
        $request->ADDON_APP = $module;

        return $next($request);
    }

}
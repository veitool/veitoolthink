<?php
declare(strict_types=1);

namespace app;

use Closure;
use think\Request;
use think\Response;
use think\facade\Route;
use tool\DataEncryptor;

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
        /* 前置解密处理 【采用比赋值符"="优先级低的 "or"】 */
        if(($data = $request->post('encrypt_data') or $data = $request->get('encrypt_data')) && ($key = $request->header('VeitoolAdminxKeySecret'))){
            try {
                $KeySecret = DataEncryptor::rsaDecrypt($key);
                $KeySecret = str_split($KeySecret, 32);
                $request->aes_key = $KeySecret[0];
                $request->aes_iv  = $KeySecret[1];
                // 其他地方 在拿到 Request 后可以进行加密 返回给终端 ['encrypt_data'=>DataEncryptor::aesEncrypt('你好，这是加密的原文', $this->request->aes_key, $this->request->aes_iv)]
                // 用 key & iv 解密数据 并 合并到对应数据集
                $data = DataEncryptor::aesDecrypt((string)$data, $request->aes_key, $request->aes_iv);
                if($request->method(true) === 'GET'){
                    $request->withGet(array_merge($request->get(), $data));
                }else{
                    $request->withPost(array_merge($request->post(), $data));
                }
            } catch (\Exception $e) {
                throw new \Exception("数据解密失败：{$e->getMessage()}");
            }
        }/**/

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
            Route::rule($url, $contr . '/' . $method);
            $module .= '/';
        }/**/
        /* 插件应用名 用于 AdminBase.php 中兼容插件权限 */
        $request->ADDON_APP = $module;

        return $next($request);
    }

}
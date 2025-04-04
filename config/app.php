<?php
// +----------------------------------------------------------------------
// | 应用设置
// +----------------------------------------------------------------------

return [
    // 应用地址
    'app_host'         => '',
    // 应用的命名空间
    'app_namespace'    => '',
    // 是否启用路由
    'with_route'       => true,
    // 是否启用事件
    'with_event'       => true,
    // 默认应用
    'default_app'      => 'index',
    // 默认时区
    'default_timezone' => 'Asia/Shanghai',
    // 应用映射（自动多应用模式有效）
    'app_map'          => ['admin888'=>'admin'],
    // 域名绑定（自动多应用模式有效）
    'domain_bind'      => [
        //'www'=>'index'
    ],
    // 开启应用快速访问 Route::rule('demo','index/abc/demo') 这样就可以  www.veitool.com/demo 去快速访问 www.veitool.com/index/abc/demo
    'app_express'      => true,
    // 禁止URL访问的应用列表（自动多应用模式有效）
    'deny_app_list'    => ['model','event'],
    // 异常页面的模板文件
    'exception_tmpl'   => ROOT_PATH .(env('app_debug', true) ? 'app/v_err.tpl' : 'app/v_msg.tpl'),
    // 错误显示信息,非调试模式有效
    'error_message'    => '页面错误！请稍后再试～',
    // 显示错误信息
    'show_error_msg'   => false,
];
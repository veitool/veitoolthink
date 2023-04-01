<?php
use app\model\system\Manager;

return [
    // 扩展自身需要的配置
    'protocol'       => 'websocket', // 协议 支持 tcp udp unix http websocket text
    'host'           => '127.0.0.1', // 监听地址
    'port'           => 2345, // 监听端口
    'socket'         => '', // 完整监听地址
    'context'        => [], // socket 上下文选项
    'worker_class'   => '', // 自定义Workerman服务类名 支持数组定义多个服务

    // 支持workerman的所有配置参数
    'name'           => 'thinkphp',
    'count'          => 4,
    'daemonize'      => false,
    'pidFile'        => '',

    // 支持事件回调
    // onWorkerStart 子进程启动时的回调函数
    'onWorkerStart'  => function ($worker) {

    },
    // onWorkerReload 收到reload信号后执行的回调
    'onWorkerReload' => function ($worker) {

    },
    // onConnect 建立连接时(TCP三次握手完成后)触发的回调函数
    'onConnect'      => function ($connection) {
        $connection->send('123');
    },
    // onMessage 当客户端发来信息时触发
    'onMessage'      => function ($connection, $data) {
        $rs = Manager::get("userid=1");
        $connection->send('receive success-'.$rs->username.'_'.$data);
    },
    // onClose 断开时触发的回调函数
    'onClose'        => function ($connection) {

    },
    // onError 连接上发生错误时触发
    'onError'        => function ($connection, $code, $msg) {
        echo "error [ $code ] $msg\n";
    },
];
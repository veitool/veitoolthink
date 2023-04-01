<?php

return [
    // 扩展自身需要的配置
    'protocol'              => 'websocket', // 协议 支持 tcp udp unix http websocket text
    'host'                  => '0.0.0.0', // 监听地址
    'port'                  => 8282, // 监听端口
    'socket'                => '', // 完整监听地址
    'context'               => [], // socket 上下文选项
    'register_deploy'       => true, // 是否需要部署register
    'businessWorker_deploy' => true, // 是否需要部署businessWorker
    'gateway_deploy'        => true, // 是否需要部署gateway

    // Register配置 服务注册地址
    'registerAddress'       => '127.0.0.1:1236',

    // Gateway配置
    'name'                  => 'VEITOOL', // gateway名称，status方便查看
    'count'                 => 1, // gateway进程数
    'lanIp'                 => '127.0.0.1', //本机ip，分布式部署时使用内网ip
    'startPort'             => 2900, // 内部通讯起始端口，假如$gateway->count=4，起始端口为4000 则一般会使用4000 4001 4002 4003 4个端口作为内部通讯端口 
    'daemonize'             => false,
    //'pingInterval'          => 30, // 心跳间隔
    //'pingNotResponseLimit'  => 0, // 终端未发送心跳，服务端是否断开0表示不断开，1表示30*1后断开
    //'pingData'              => '{"type":"ping"}', // 心跳数据

    // BusinsessWorker配置
    'businessWorker'        => [
        'name'         => 'BusinessWorker',
        'count'        => 1,
        'eventHandler' => '\app\event\GatewayWorke',
    ],
];
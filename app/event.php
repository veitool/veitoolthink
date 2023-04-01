<?php
// 事件定义文件  bind:作用为给listen中的事件名取个别名 如：bind => ['AI'=>'AppInit']
return [
    'bind'   => [],
    'listen' => [
        //应用初始化标签位
        'AppInit'  => [
            'app\event\AppInit',
        ],
        //应用开始标签位
        'HttpRun'  => [],
        //应用结束标签位
        'HttpEnd'  => [],
        //
        'LogLevel' => [],
        //日志write方法标签位
        'LogWrite' => [],
    ],
    'subscribe' => [],
];

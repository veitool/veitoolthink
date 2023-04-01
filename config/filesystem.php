<?php

return [
    // 默认磁盘
    'default' => env('filesystem.driver', 'public'),
    // 磁盘列表
    'disks'   => [
        'local'  => [
            'type' => 'local',
            'root' => app()->getRuntimePath() . 'storage',
        ],
        'public' => [
            // 磁盘类型
            'type'       => 'local',
            // 磁盘路径
            'root'       => app()->getRootPath() . 'public/file/upload',
            // 磁盘路径对应的外部URL路径
            'url'        => '/file/upload',
            // 可见性
            'visibility' => 'public',
        ],
        'addon' => [
            // 磁盘类型
            'type'       => 'local',
            // 磁盘路径
            'root'       => app()->getRootPath() . 'backup/download',
            // 磁盘路径对应的外部URL路径
            'url'        => '',
            // 可见性
            'visibility' => 'public',
        ],
    ],
];
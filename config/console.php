<?php
// +----------------------------------------------------------------------
// | 控制台配置
// +----------------------------------------------------------------------

return [
    // 指令定义
    'commands' => [
        'make:controller' => veitool\make\Controller::class,
        'make:model' => veitool\make\Model::class,
    ],
];
<?php
// 全局中间件定义文件
return [
    // 全局请求缓存
    // \think\middleware\CheckRequestCache::class,
    // 多语言加载
    // \think\middleware\LoadLangPack::class,
    // Session初始化 或者 'think\middleware\SessionInit'
    \think\middleware\SessionInit::class,
    // 初始化
    \app\AppInit::class
];
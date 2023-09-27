<?php
/**
 * 【应用入口文件】
 * ===========================================================================
 * Veitool 快捷开发框架系统
 * Author: Niaho 26843818@qq.com
 * Copyright (c)2019-2023 www.veitool.com All rights reserved.
 * Licensed: 这不是一个自由软件，不允许对程序代码以任何形式任何目的的再发行
 * ---------------------------------------------------------------------------
 */
namespace think;

// 检测PHP环境
if(version_compare(PHP_VERSION,'8.1.0','<')) die('require PHP >= 8.1.0!');

// 安装引导
if (is_dir(__DIR__ . '/install') && !is_file(__DIR__ . '/install/install.lock')) exit(header('Location:/install/'));

// 引入自动加载类
require __DIR__ . '/../vendor/autoload.php';

// 执行HTTP应用并响应
$http = (new App())->http;
$response = $http->run();
$response->send();
$http->end($response);
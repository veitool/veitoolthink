<?php
/**
 * ===========================================================================
 * Veitool 快捷开发框架系统
 * Author: Niaho 26843818@qq.com
 * Copyright (c)2019-2026 www.veitool.com All rights reserved.
 * Licensed: 这不是一个自由软件，不允许对程序代码以任何形式任何目的的再发行
 * ---------------------------------------------------------------------------
 */
namespace veitool\make;

use think\console\input\Option;
use think\console\input\Argument;

/**
 * 命令构建控制器
 */
class Controller extends Make
{
    /**
     * 定义类型
     */
    protected $type = "Controller";

    /**
     * 实现 configure 方法
     */
    protected function configure()
    {
        parent::configure();
        $this->setName('make:controller')
            ->addOption('admin', null, Option::VALUE_NONE, 'Generate an admin controller class.')
            ->addOption('index', null, Option::VALUE_NONE, 'Generate an empty controller class.')
            ->addOption('api',   null, Option::VALUE_NONE, 'Generate an api controller class.')
            ->addArgument('remarks', Argument::OPTIONAL, 'Your remarks.')
            ->setDescription('Create a new controller class');
    }

    /**
     * 实现 configure 方法
     */
    protected function getStub():string
    {
        $stubPath = __DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR;
        if ($this->input->getOption('admin')) {
            return $stubPath . 'controller.admin.stub';
        }
        if ($this->input->getOption('index')) {
            return $stubPath . 'controller.index.stub';
        }
        if ($this->input->getOption('api')) {
            return $stubPath . 'controller.api.stub';
        }
        return $stubPath . 'controller.index.stub';
    }

    /**
     * 实现 getClassName 方法
     */
    protected function getClassName(string $name):string
    {
        return parent::getClassName($name) . ($this->app->config->get('route.controller_suffix') ? 'Controller' : '');
    }

    /**
     * 实现 getNamespace 方法
     */
    protected function getNamespace(string $app):string
    {
        return parent::getNamespace($app) . '\\controller';
    }

}
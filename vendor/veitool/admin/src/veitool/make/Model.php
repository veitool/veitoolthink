<?php
/**
 * ===========================================================================
 * Veitool 快捷开发框架系统
 * Author: Niaho 26843818@qq.com
 * Copyright (c)2019-2025 www.veitool.com All rights reserved.
 * Licensed: 这不是一个自由软件，不允许对程序代码以任何形式任何目的的再发行
 * ---------------------------------------------------------------------------
 */
namespace veitool\make;

use think\console\input\Argument;

/**
 * 命令构建模型
 */
class Model extends Make
{
    /**
     * 定义类型
     */
    protected $type = "Model";

    /**
     * 实现 configure 方法
     */
    protected function configure()
    {
        parent::configure();
        $this->setName('make:model')
             ->addArgument('remarks', Argument::OPTIONAL, 'Your remarks.')
             ->setDescription('Create a new model class');
    }

    /**
     * 实现 getStub 方法
     */
    protected function getStub(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'model.stub';
    }

    /**
     * 实现 getNamespace 方法
     */
    protected function getNamespace(string $app): string
    {
        return parent::getNamespace($app) . '\\model';
    }
}
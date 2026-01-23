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

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;

abstract class Make extends Command
{
    protected $type;

    /**
     * 
     */
    abstract protected function getStub();

    /**
     * 
     */
    protected function configure()
    {
        $this->addArgument('name', Argument::REQUIRED, "The name of the class");
    }

    /**
     * 
     */
    protected function execute(Input $input, Output $output)
    {
        $name = trim($input->getArgument('name'));
        $classname = $this->getClassName($name);
        $pathname = $this->getPathName($classname);
        if (is_file($pathname)) {
            $output->writeln('<error>' . $this->type . ':' . $classname . ' already exists!</error>');
            return false;
        }
        if (!is_dir(dirname($pathname))) {
            mkdir(dirname($pathname), 0755, true);
        }
        file_put_contents($pathname, $this->buildClass($classname));
        $output->writeln('<info>' . $this->type . ':' . $classname . ' created successfully.</info>');
    }

    /**
     * 
     */
    protected function buildClass(string $name)
    {
        $stub = file_get_contents($this->getStub());
        $namespace = trim(implode('\\', array_slice(explode('\\', $name), 0, -1)), '\\');
        $class = str_replace($namespace . '\\', '', $name);
        return str_replace(['{%className%}', '{%actionSuffix%}', '{%namespace%}', '{%app_namespace%}', '{%remarks%}'], [
            $class,
            $this->app->config->get('route.action_suffix'),
            $namespace,
            $this->app->getNamespace(),
            $this->input->getArgument('remarks'),
        ], $stub);
    }

    /**
     * 
     */
    protected function getPathName(string $name): string
    {
        $name = str_replace('app\\', '', $name);
        return $this->app->getBasePath() . ltrim(str_replace('\\', DIRECTORY_SEPARATOR, $name), '/') . '.php';
    }

    /**
     * 
     */
    protected function getClassName(string $name): string
    {
        if (strpos($name, '\\') !== false) {
            return $name;
        }
        if (strpos($name, '@')) {
            [$app, $name] = explode('@', $name);
        } else {
            $app = '';
        }
        if (strpos($name, '/') !== false) {
            $name = str_replace('/', '\\', $name);
        }
        return $this->getNamespace($app) . '\\' . $name;
    }

    /**
     * 
     */
    protected function getNamespace(string $app): string
    {
        return 'app' . ($app ? '\\' . $app : '');
    }

}
<?php
/**
 * ===========================================================================
 * Veitool 快捷开发框架系统
 * Author: Niaho 26843818@qq.com
 * Copyright (c)2019-2025 www.veitool.com All rights reserved.
 * Licensed: 这不是一个自由软件，不允许对程序代码以任何形式任何目的的再发行
 * ---------------------------------------------------------------------------
 */
namespace veitool\storage;

use think\Exception;

/**
 * 存储模块驱动
 * Class driver
 * @package veitool\com\storage
 */
class Driver
{
    private $config;    // upload 配置
    private $engine;    // 当前存储引擎类

    /**
     * 构造方法
     * Driver constructor.
     * @param $config
     * @param null|string $storage 指定存储方式，如不指定则为系统默认
     * @throws Exception
     */
    public function __construct($config, $storage = null)
    {
        $this->config = $config;
        // 实例化当前存储引擎
        $this->engine = $this->getEngineClass($storage);
    }

    /**
     * 设置上传的文件信息
     * @param string $name
     * @return mixed
     */
    public function setUploadFile($name = 'file')
    {
        return $this->engine->setUploadFile($name);
    }

    /**
     * 执行文件上传
     */
    public function upload()
    {
        return $this->engine->upload();
    }

    /**
     * 执行文件删除
     * @param $fileName
     * @return mixed
     */
    public function delete($fileName)
    {
        return $this->engine->delete($fileName);
    }

    /**
     * 获取错误信息
     * @return mixed
     */
    public function getError()
    {
        return $this->engine->getError();
    }

    /**
     * 获取文件路径
     * @return mixed
     */
    public function getFileName()
    {
        return $this->engine->getFileName();
    }

    /**
     * 返回文件信息
     * @return mixed
     */
    public function getFileInfo()
    {
        return $this->engine->getFileInfo();
    }

    /**
     * 获取当前的存储引擎
     * @param null|string $storage 指定存储方式，如不指定则为系统默认
     * @return mixed
     * @throws Exception
     */
    private function getEngineClass($storage = null)
    {
        $engineName = is_null($storage) ? $this->config['default'] : $storage;
        $classSpace = __NAMESPACE__ . '\\engine\\' . ucfirst($engineName);// ucfirst 首字符转换为大写
        //$classSpace = app\common\library\storage\engine\Local
        if (!class_exists($classSpace)) {
            throw new Exception('未找到存储引擎类: ' . $engineName);
        }
        return new $classSpace($this->config['engine'][$engineName]);
    }

}
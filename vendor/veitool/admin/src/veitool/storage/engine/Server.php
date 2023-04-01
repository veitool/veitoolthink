<?php
/**
 * ===========================================================================
 * Veitool 快捷开发框架系统
 * Author: Niaho 26843818@qq.com
 * Copyright (c)2019-2023 www.veitool.com All rights reserved.
 * Licensed: 这不是一个自由软件，不允许对程序代码以任何形式任何目的的再发行
 * ---------------------------------------------------------------------------
 */
namespace veitool\storage\engine;

use think\Exception;

/**
 * 存储引擎抽象类
 * Class server
 * @package app\common\library\storage\drivers
 */
abstract class Server
{
    /* @var $file \think\File */
    protected $file;
    protected $error;
    protected $fileName;
    protected $fileInfo;

    /**
     * 构造函数
     * Server constructor.
     */
    protected function __construct(){}

    /**
     * 设置上传的文件信息
     * @param string $name
     * @throws Exception
     */
    public function setUploadFile($name)
    {
        // 接收上传的文件
        $this->file = request()->file($name);
        if (empty($this->file)) {
            throw new Exception('未找到上传文件的信息');
        }
        // 生成保存文件名
        $this->fileName = $this->buildSaveName();
        // 文件信息
        $this->fileInfo = ['name'=>$this->file->getFilename(),'oname'=> $this->file->getOriginalName(),'size'=>$this->file->getSize(),'type' => $this->file->getMime(),'ext' => $this->file->getOriginalExtension()];
    }

    /**
     * 文件上传
     * @return mixed
     */
    abstract protected function upload();

    /**
     * 文件删除
     * @param $fileName
     * @return mixed
     */
    abstract protected function delete($fileName);

    /**
     * 返回上传后文件路径
     * @return mixed
     */
    abstract public function getFileName();

    /**
     * 返回文件信息
     * @return mixed
     */
    public function getFileInfo()
    {
        return $this->fileInfo;
    }

    /**
     * 返回错误信息
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * 生成保存文件名
     */
    private function buildSaveName()
    {
        // 要上传图片的本地路径
        $realPath = $this->file->getRealPath();
        // 扩展名
        $ext = $this->file->getOriginalExtension();
        // 自动生成文件名
        return date('YmdHis') . substr(md5($realPath), 0, 5) . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT) . '.' . $ext;
    }

}
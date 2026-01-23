<?php
/**
 * ===========================================================================
 * Veitool 快捷开发框架系统
 * Author: Niaho 26843818@qq.com
 * Copyright (c)2019-2026 www.veitool.com All rights reserved.
 * Licensed: 这不是一个自由软件，不允许对程序代码以任何形式任何目的的再发行
 * ---------------------------------------------------------------------------
 */
namespace veitool\storage\engine;

use think\facade\Filesystem;

/**
 * 本地文件驱动
 * @package app\common\library\storage\drivers
 */
class Local extends Server
{
    private $config;

    /**
     * 构造方法
     * @param $config
     */
    public function __construct($config)
    {
        parent::__construct();
        $this->config = $config;
    }

    /**
     * 上传图片文件
     * @return array|bool
     */
    public function upload()
    {
        $type = $this->config['type'];
        $type = isset($this->config[$type]) && is_array($this->config[$type]) ? $type : 'image';
        $ext  = $this->config[$type]['ext'];
        $size = $this->config[$type]['size'];
        $thum = $this->config['thum'];
        $pre  = $this->config['pre'] ?? '';
        try{
            validate(['file'=>[
                'fileSize' => $size * 1024 * 1024,
                'fileExt'  => $ext
            ]],[
                'file.fileSize' => '上传的文件大小不能超过'.$size.'M',
                'file.fileExt'  => '请上传后缀为:'.$ext.'的文件'
            ])->check(['file'=> $this->file]);
        }catch(\think\exception\ValidateException $e){
            $this->error = $e->getError();
            return false;
        }
        $info = Filesystem::putFile($type, $this->file, function()use($thum,$pre){return $pre.date('Ymd').'/'.uniqid().($thum ? '_b' : '');});
        if($info){
            $this->fileName = str_replace('\\', '/', $info);
            if($this->checkHex()){
                if($thum && $type=='image'){ // 生成缩略图
                    $arr = explode('|', $thum);
                    $w = intval($arr[0]);
                    $h = isset($arr[1]) ? intval($arr[1]) : 0;
                    $w = $w>0 ? $w : 150;
                    $h = $h>0 ? $h : 150;
                    $timg  = config('filesystem.disks.public.root').'/'.$info;
                    $image = \think\Image::open($timg);
                    $timg  = str_replace('_b.', '_x.', $timg);
                    $image->thumb($w, $h)->save($timg);
                }
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    /**
     * 16进制文件安全检测
     */
    public function checkHex(){
        $file = trim(public_path(),'\\').$this->config['domain'].'/'.$this->fileName;
        if(file_exists($file)){
            $resource = fopen($file, 'rb');
            $fileSize = filesize($file);
            fseek($resource, 0);
            if($fileSize > 512){
                $hexCode = bin2hex(fread($resource, 512));
                fseek($resource, $fileSize - 512);
                $hexCode .= bin2hex(fread($resource, 512));
            }else{
                $hexCode = bin2hex(fread($resource, $fileSize));
            }
            fclose($resource);
            /* 整个类检测木马脚本的核心  通过匹配十六进制代码检测是否存在木马脚本 匹配16进制中的 <% ( ) %> 、<? ( ) ?> 、<script | /script> 大小写亦可 */
            if(preg_match("/(3c25.*?28.*?29.*?253e)|(3c3f.*?28.*?29.*?3f3e)|(3C534352495054)|(2F5343524950543E)|(3C736372697074)|(2F7363726970743E)/is", $hexCode)){
                @unlink($file);
                return false;
            }
            return true;
        }else{
            return false;
        }
    }

    /**
     * 删除文件
     * @param  $fileName
     * @return bool|mixed
     */
    public function delete($fileName)
    {
        // 文件所在目录
        $filePath = WEB_PATH . "uploads/{$fileName}";
        return !file_exists($filePath) ?: unlink($filePath);
    }

    /**
     * 返回文件路径
     * @return mixed
     */
    public function getFileName()
    {
        return $this->fileName;
    }

}
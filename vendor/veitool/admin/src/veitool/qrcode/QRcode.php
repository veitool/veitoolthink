<?php
/**
 * ===========================================================================
 * Veitool 快捷开发框架系统
 * Author: Niaho 26843818@qq.com
 * Copyright (c)2019-2025 www.veitool.com All rights reserved.
 * Licensed: 这不是一个自由软件，不允许对程序代码以任何形式任何目的的再发行
 * ---------------------------------------------------------------------------
 */
namespace veitool\qrcode;

use think\Exception;
use think\facade\Config;
use think\facade\Request;

/**
 * 二维码生成类
 * @package veitool\qrcode
 */
class QRcode
{
    /**
     * 初始化配置信息
     * @var array 
     */
    protected $config = [
        'cache_dir'  => 'file/card/qrcode',
        'background' => ''
    ];
    
    protected  $cache_dir = '';     //二维码缓存
    protected  $outfile   = '';     //输出二维码文件

    /**
     * 构造方法
     */
    public function __construct(){
        $qr = Config::get('qrcode.');
        $this->config = array_merge($this->config,($qr ?: []));
        $this->cache_dir = $this->config['cache_dir'];
        if(!file_exists($this->cache_dir)){
            mkdir($this->cache_dir,0775,true);
        }
        require("phpqrcode/qrlib.php");
    }

    /**
     * 生成普通二维码
     * @param   string  $url      生成url地址
     * @param   bool    $outfile  指定生成(相对或绝对)路径
     * @param   string  $evel     二维码容错率 L(QR_ECLEVEL_L，7%)，M(QR_ECLEVEL_M，15%)，Q(QR_ECLEVEL_Q，25%)，H(QR_ECLEVEL_H，30%)
     * @param   int     $size     图片大小
     * @return  Obj     $this
     */
    public function png($url,$outfile=false,$evel='H',$size=5){
        $this->outfile = $outfile ?: $this->cache_dir.'/'.time().'.png';
        \QRcode::png($url,$this->outfile,$evel,$size,2);
        return $this;
    }

    /**
     * 添加logo到二维码中
     * @param  $logo
     * @return bool|mixed
     */
    public function logo($logo){
        $QR = imagecreatefromstring(file_get_contents($this->outfile));
        $logo = imagecreatefromstring(file_get_contents($logo));
        $QR_width = imagesx($QR);//二维码图片宽度
        $QR_height = imagesy($QR);//二维码图片高度
        $logo_width = imagesx($logo);//logo图片宽度
        $logo_height = imagesy($logo);//logo图片高度
        $logo_qr_width = $QR_width / 5;
        $scale = $logo_width/$logo_qr_width;
        $logo_qr_height = $logo_height/$scale;
        $from_width = ($QR_width - $logo_qr_width) / 2;
        //重新组合图片并调整大小
        imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);
        $this->outfile = $this->cache_dir.'/'.time().'.png';
        imagepng($QR, $this->outfile);
        imagedestroy($QR);
        return $this;
    }

    /**
     * 添加背景图
     * @param int     $x          二维码在背景图X轴未知
     * @param int     $y          二维码在背景图Y轴未知
     * @param string  $dst_path   背景路径
     * @return $this
     */
    public function background($x=200,$y=500,$dst_path = ''){
        if($dst_path==''){
            $dst_path = $this->config['background'];
        }
        $src_path = $this->outfile;//覆盖图
        //创建图片的实例
        $dst = imagecreatefromstring(file_get_contents($dst_path));
        $src = imagecreatefromstring(file_get_contents($src_path));
        //获取覆盖图图片的宽高
        list($src_w, $src_h) = getimagesize($src_path);
        //将覆盖图复制到目标图片上，最后个参数100是设置透明度（100是不透明），这里实现不透明效果
        imagecopymerge($dst, $src, $x, $y, 0, 0, $src_w, $src_h, 100);
        $this->outfile = $this->cache_dir.'/'.time().'.png';
        imagepng($dst, $this->outfile);//根据需要生成相应的图片
        imagedestroy($dst);
        return $this;
    }
}
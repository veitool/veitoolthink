<?php
/**
 * ===========================================================================
 * Veitool 快捷开发框架系统
 * Author: Niaho 26843818@qq.com
 * Copyright (c)2019-2023 www.veitool.com All rights reserved.
 * Licensed: 这不是一个自由软件，不允许对程序代码以任何形式任何目的的再发行
 * ---------------------------------------------------------------------------
 */
namespace tool;

use think\facade\Cache;

/**
 * 多次错误锁定处理
 */
class ErrLock
{
    /**
     * 初始配置
     * times   失败的次数
     * time    失败后锁定时长（秒）
     * tips    提示
     * key     缓存KEY
     * @var array
     */
    private $config = array(
        'times' => 5,
        'time'  => 1800,
        'tips'  => '登录',
        'key'   => 'al'
    );

    /**
     * 记录的数据
     * @var array 
     */
    private $data = [];

    /**
     * 缓存KEY
     * @var strimg 
     */
    private $ckey = '';

    /**
     * 反馈信息
     * @var strimg 
     */
    public $msg = '';

    /**
     * 构造函数初始化 [times:失败的次数/默认5次,time:失败后锁定时长或(是否频繁操作的时长)/默认1800秒,tips:提示/默认登录,key:缓存KEY/默认al]
     */
    public function __construct($c=[])
    {
        $this->config = array_merge($this->config,$c);
        $this->ckey   = $this->config['key'].'_'.VT_IP;
        $this->data   = Cache::get($this->ckey);
    }

    /**
     * 检查是否已锁定
     * @return boolean
     */
    public function check()
    {
        $flag = false;
        if(isset($this->data['times']) && $this->data['times']>=$this->config['times'] && (VT_TIME-$this->data['time'])<$this->config['time']){
            $this->msg = '多次'.$this->config['tips'].'失败，您已被锁定'.(intval($this->config['time']/60)).'分钟';
            $flag = true;
        }
        return $flag;
    }

    /**
     * 检查是否频繁操作
     * @return boolean
     */
    public function often()
    {
        return (isset($this->data['time']) && (VT_TIME-$this->data['time'])<$this->config['time']) ? true : false;
    }

    /**
     * 追加次数/记录时间
     * @return mixed
     */
    public function add()
    {
        $arr['times'] = isset($this->data['times']) ? $this->data['times']+1 : 1;
        $arr['time']  = VT_TIME;
        Cache::set($this->ckey, $arr);
    }

    /**
     * 清除记录
     * @return mixed
     */
    public function del()
    {
        Cache::delete($this->ckey);
    }

}
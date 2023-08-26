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
 * 锁定处理
 */
class Lock
{
    /**
     * 初始配置
     * @var array
     */
    private static $config = ['times' => 5, 'time' => 1800, 'tips' => '登录', 'key' => 'ToolLock', 'msg' => '', 'add' => false];

    /**
     * 检查是否已锁定
     * @param  array  $c  [times:失败的次数 time:失败后锁定时长[默认1800秒] tips:提示 key:缓存KEY msg:信息 add:是否检查时立即追加缓存记录 默认false]
     * @return boolean
     */
    public static function check($c = [])
    {
        self::$config = array_merge(self::$config,$c);
        $data = Cache::get(self::$config['key']);
        $flag = false;
        if(isset($data['times']) && $data['times'] >= self::$config['times'] && (VT_TIME - $data['time']) < self::$config['time']){
            self::$config['msg'] = self::$config['msg'] ?: '多次'.self::$config['tips'].'失败，您已被锁定'.(intval(self::$config['time']/60)).'分钟';
            $flag = true;
        }elseif(self::$config['add']){
            self::add();
        }
        return $flag;
    }

    /**
     * 检查是否频繁操作
     * @param  array  $c  [time:重复操作的时间间隔[默认1800秒] tips:提示 key:缓存KEY msg:信息]
     * @return boolean
     */
    public static function often($c = [])
    {
        self::$config = array_merge(self::$config,$c);
        $key  = self::$config['key'];
        $data = Cache::get($key);
        $flag = false;
        if(isset($data['time']) && (VT_TIME - $data['time']) < self::$config['time']){
            self::$config['msg'] = '请勿重复操作';
            $flag = true;
        }else{
            Cache::set($key, ['time'=>VT_TIME]);
        }
        return $flag;
    }

    /**
     * 追加次数和记录时间
     * @return mixed
     */
    public static function add()
    {
        $key  = self::$config['key'];
        $data = Cache::get($key);
        Cache::set($key, ['times'=>(isset($data['times']) ? $data['times'] + 1 : 1), 'time'=>VT_TIME]);
    }

    /**
     * 清除缓存
     * @return mixed
     */
    public static function del()
    {
        Cache::delete(self::$config['key']);
    }

    /**
     * 获取信息
     * @return mixed
     */
    public static function msg()
    {
        return self::$config['msg'];
    }

}
<?php
/**
 * ===========================================================================
 * Veitool 快捷开发框架系统
 * Author: Niaho 26843818@qq.com
 * Copyright (c)2019-2025 www.veitool.com All rights reserved.
 * Licensed: 这不是一个自由软件，不允许对程序代码以任何形式任何目的的再发行
 * ---------------------------------------------------------------------------
 */
namespace app\model;

use think\facade\Db;
use think\Model;

/**
 * 模型公用类
 */
class Base extends Model
{

    /**
     * 获取单条数据
     * @param  string|array  $where   查询条件
     * @param  string        $field   查询字段
     * @param  string|array  $order   排序
     * @return obj|null
     */
    public static function get(string|array $where = '', string $field = '*', string|array $order = [])
    {
        return self::where($where)->field($field)->order($order)->find();
    }

    /**
     * 获取多条数据
     * @param  string|array  $where   查询条件
     * @param  string        $field   查询字段
     * @param  string|array  $order   排序
     * @return obj|array
     */
    public static function all(string|array $where = '', string $field = '*', string|array $order = [])
    {
        return self::where($where)->field($field)->order($order)->select();
    }

    /**
     * 添加单条数据
     * @param  array          $data    添加的数据
     * @param  array|string   $field   允许的字段
     * @param  bool|int       $getid   是否返回ID
     * @return int   添加数或ID
     */
    public static function inadd(array $data = [], array|string $field = [], bool|int $getid = false)
    {
        if($field){
            $field = is_array($field) ? $field : explode(',', (string)$field); 
            foreach($data as $k=>$v){
                if(!in_array($k,$field)) unset($data[$k]);
            }
        }
        return $getid ? self::strict(false)->insertGetId($data) : self::strict(false)->insert($data);
    }

    /**
     * 删除数据
     * @param  string|array  $where   查询条件
     * @return int   删除的条数，0表示未删除任何数据
     */
    public static function del(string|array $where = '')
    {
        return self::where($where)->delete();
    }

    /**
     * 开启事务
     */
    public static function beginTrans()
    {
        Db::startTrans();
    }

    /**
     * 提交事务
     */
    public static function commitTrans()
    {
        Db::commit();
    }

    /**
     * 回滚事务
     */
    public static function rollbackTrans()
    {
        Db::rollback();
    }

    /**
     * 根据结果提交滚回事务
     * @param   bool|int   $res   结果状态
     * @return  mixed
     */
    public static function checkTrans(bool|int $res = false)
    {
        if($res){
            self::commitTrans();
        }else{
            self::rollbackTrans();
        }
    }

}
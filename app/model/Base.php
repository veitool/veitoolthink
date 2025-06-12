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
 * 
 * 支持自动时间戳的方法有
 * 1. (new P)->save($d) 返回 布尔值  (new P)->saveAll($d) 返回的是包含新增模型（带自增ID）的数据集对象
 * 2. P::create($d, ['允许的字段1','允许的字段2'..]) 返回 当前模型的对象实例, 默认会过滤不是数据表的字段信息。
 * 软删除 P::destroy(主键ID); 或  P::destroy([主键ID1,主键ID2,主键ID3..]) 或 $p = P::find(1); $p->delete();
 * 真实删除: P::destroy(主键ID, true); 或  P::destroy([主键ID1,主键ID2,主键ID3..], true) 或 $p = P::find(1); $p->force()->delete();
 * 闭包删除：P::destroy(function($query){$query->where('id','>',10);});
 */
class Base extends Model
{
    /**
     * 添加时间
     * @var string
     */
    protected $createTime = 'add_time';

    /**
     * 更新时间
     * @var string
     */
    protected $updateTime = 'upd_time';

    /**
     * 删除时间
     * @var string
     */
    protected $deleteTime = 'del_time';

    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = ['del_time'];

    /**
     * 只读字段
     * @var attay
     */
    protected $readonly = ['add_time'];

    /**
     * 软删除字段的默认值
     * @var mixed
     */
    protected $defaultSoftDelete = 0;

    /**
     * 获取单条数据
     * @param  string|array  $where   查询条件
     * @param  string        $field   查询字段
     * @param  string|array  $order   排序
     * @return obj|null
     */
    public static function one(string|array $where = '', string $field = '*', string|array $order = [])
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
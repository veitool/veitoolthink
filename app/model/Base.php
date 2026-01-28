<?php
/**
 * ===========================================================================
 * Veitool 快捷开发框架系统
 * Author: Niaho 26843818@qq.com
 * Copyright (c)2019-2026 www.veitool.com All rights reserved.
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
     * 删除数据【返回删除条数】(若无须返回删除数量则可用 Model::destroy($id) 或闭包 Model::destroy(function($query){$query->where('id','>',10);}); 进行删除)
     * @param  mixed $where 可是键值:1/'1' 或 主键值数组[1,2]/['1','2'] 或 键值对:['id'=>'1'] 或 闭包:function($query)use($id){$query->where("id=$id");} 或字符串查询条件
     * @param  bool  $force 是否真删 默认:软删除(前提是模型中引用了：use \think\model\concern\SoftDelete;即开启了软删)，设为true则不受限真实删除
     * @return int 删除的记录数
     */
    public static function del(mixed $where, bool $force = false)
    {
        if(empty($where) && 0 !== $where){ // 传入空值（包括空字符串和空数组）的时候不会做任何的数据删除操作，但传入0则是有效的
            return false;
        }

        $query = (new static())->db();
        if($force){
            $query->removeOption('soft_delete');
        }
        if((is_string($where) && strpos($where, ' ') !== false) || (is_array($where) && key($where) !== 0)){ //查询字符串 或 键值对形式
            $query->where($where);
            $where = [];
        }elseif ($where instanceof \Closure){ //闭包形式
            call_user_func_array($where, [ &$query]);
            $where = [];
        }

        $result = $query->select((array)$where);
        $i = 0;
        foreach($result as $rs){
            $i += $rs->force($force)->delete();
        }
        return $i;
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
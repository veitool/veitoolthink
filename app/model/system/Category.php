<?php
/**
 * ===========================================================================
 * Veitool 快捷开发框架系统
 * Author: Niaho 26843818@qq.com
 * Copyright (c)2019-2024 www.veitool.com All rights reserved.
 * Licensed: 这不是一个自由软件，不允许对程序代码以任何形式任何目的的再发行
 * ---------------------------------------------------------------------------
 */
namespace app\model\system;

use app\model\Base;

/**
 *【公用分类模型】
 */
class Category extends Base
{
    /**
     *定义主键
     * @var string 
     */
    protected $pk = 'catid';

    /**
     * 获取内容公用类别数据
     * @param   string/array    $where     条件
     * @param   int             $iskey     是否以主键作为索引（键）
     * @param   string          $field     查询的字段(主键作为索引时只查询1个字段时为1维数组)
     * @return  array
     */
    public static function catList(string|array $where = '', int $iskey = 0, string $field = '*')
    {
        $where = is_array($where) ? $where : [['type','=',$where]];
        $obj = self::where($where)->order('listorder', 'asc');
        return $iskey ? $obj->column($field,'catid') : $obj->field($field)->select()->toArray();
    }

    /**
     * 获取所有子类ID串
     * @param  int   $catid   ID
     * @return string
     */
    public static function getChild(int $catid = 0)
    {
        $catid = abs($catid);
        if($catid>0){
            $rs = self::where("FIND_IN_SET($catid,arrparentid)")->column('catid');
            $rs[] = $catid;
            $catid = implode(',', $rs);
        }
        return $catid;
    }

    /**
     * 类别添加
     * @param  string   $type   标识
     * @return array
     */
    public static function cadd(string $type = '')
    {
        $d = request()->post(['title','icon','parentid','listorder'],'','strip_sql');
        if(!$d['title']) return ['msg'=>'请输入类别名称'];
        $d['type'] = $type;
        $d['listorder'] = intval($d['listorder']);
        $d['parentid']  = intval($d['parentid']);
        $rs = self::get("catid = $d[parentid]");
        $d['arrparentid'] = $rs ? ($rs['arrparentid'] ? $rs['arrparentid'].','.$rs['catid'] : $rs['catid']) : '';//echo '<pre>';print_r($d);die;
        $rs = self::insert($d);
        if($rs){
            $rs = list_tree(self::catList($type),0,['catid','parentid','title']);
            return ['msg'=>'添加类别成功','code'=>1, 'data'=>$rs];
        }else{
            return ['msg'=>'添加类别失败'];
        }
    }

    /**
     * 类别编辑
     * @param  string   $do     快编
     * @param  string   $type   标识
     * @return array
     */
    public static function cedit(string $do = '', string $type = '')
    {
        $d = request()->post(['catid','title','icon','parentid','listorder','av','af'],'','strip_sql');
        $catid = $d['catid'] = intval($d['catid']);
        if(!$catid) return ['msg'=>'参数错误'];
        $Myobj = self::get("catid = $catid");
        if(!$Myobj) return ['msg'=>'数据不存在'];
        if($do=='up'){
            $value = $d['av'];
            $field = $d['af'];
            if(!in_array($field,['sign','listorder'])) return ['msg'=>'参数错误'];
            if($field == 'listorder') $value = intval($value);
            $rs = $Myobj->save([$field=>$value]);
            if($rs){
                return ['msg'=>'设置成功','code'=>1, 'data'=>list_tree(self::catList($type),0,['catid','parentid','title'])];
            }else{
                return ['msg'=>'设置失败'];
            }
        }else{
            $arr = []; //改上级ID时所用到的所有子类新数据
            if(!$d['title']) return ['msg'=>'请输入类别名称'];
            $parentid = $d['parentid'] = intval($d['parentid']);
            if($catid==$parentid) return ['msg'=>'上级ID不能为本身ID'];
            //获取当前类数据
            $rs = self::get("catid = $catid");
            if(!$rs) return ['msg'=>'参数错误2'];
            //上级类别有改动
            if($rs['parentid'] != $parentid){
                //旧的所有上级ID串
                $old_arrparentid = $rs['arrparentid'] ? $rs['arrparentid'].','.$catid : $catid;
                //获取上级类数据
                $rs = $parentid ? self::get("catid = $parentid") : ['arrparentid'=>'','catid'=>''];
                if(!$rs) return ['msg'=>'上级ID不存在'];
                //构造数据
                $d['arrparentid'] = $rs['arrparentid'] ? $rs['arrparentid'].','.$rs['catid'] : $rs['catid'];
                //新的所有上级ID串
                $new_arrparentid = $d['arrparentid'] ? $d['arrparentid'].','.$catid : $catid;
                //子类处理
                $rs = self::all("FIND_IN_SET($catid,arrparentid)")->toArray();
                foreach($rs as $v){
                    if($v['catid']==$parentid) return ['msg'=>'上级ID不能设为子类ID'];
                    //替换旧上级ID串为新上级ID串
                    $arrparentid = str_replace($old_arrparentid,$new_arrparentid,$v['arrparentid']);
                    $arr[] = ['catid'=>$v['catid'],'arrparentid'=>$arrparentid];
                }
            }
            unset($d['av'],$d['af']);
            $d['listorder'] = intval($d['listorder']);
            $rs = $Myobj->save($d);
            if($rs){
                if($arr) (new self)->saveAll($arr);
                return ['msg'=>'编辑成功','code'=>1, 'data'=>list_tree(self::catList($type),0,['catid','parentid','title'])];
            }else{
                return ['msg'=>'编辑失败'];
            }
        }
    }

    /**
     * 类别删除
     * @param  string   $type   标识
     * @return array
     */
    public static function cdel(string $type = '')
    {
        $catid = request()->post('catid','','intval');
        $catid = is_array($catid) ? implode(',',$catid) : $catid;
        if(!$catid) return ['msg'=>'参数错误'];
        $rs = self::del("CONCAT(',',CONCAT(arrparentid,',')) LIKE '%,{$catid},%' OR catid IN($catid)");
        if($rs){
            return ['msg'=>'删除成功','code'=>1, 'data'=>list_tree(self::catList($type),0,['catid','parentid','title'])];
        }else{
            return ['msg'=>'删除失败'];
        }
    }

}
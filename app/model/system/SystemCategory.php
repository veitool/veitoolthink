<?php
/**
 * ===========================================================================
 * Veitool 快捷开发框架系统
 * Author: Niaho 26843818@qq.com
 * Copyright (c)2019-2025 www.veitool.com All rights reserved.
 * Licensed: 这不是一个自由软件，不允许对程序代码以任何形式任何目的的再发行
 * ---------------------------------------------------------------------------
 */
namespace app\model\system;

use app\model\Base;

/**
 *【公用分类模型】
 */
class SystemCategory extends Base
{
    /**
     * 启用软删除操作
     */
    use \think\model\concern\SoftDelete; /**/

    /* *
     * 全局已开启自动时间戳，取消注释则会关闭该模型
     * @var bool
     * /
    protected $autoWriteTimestamp = false; /**/

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
     * @param  string   $type      标识
     * @param  string   $creator   创建
     * @return array
     */
    public static function cadd(string $type = '', string $creator = '')
    {
        $d = request()->post(['title','icon','parentid/d','listorder/d'],'','strip_sql');
        if(!$d['title']) return ['msg'=>'请输入类别名称'];
        $d['creator'] = $creator;
        $d['type'] = $type;
        $rs = self::one(['catid'=>$d['parentid']]);
        $d['arrparentid'] = $rs ? ($rs['arrparentid'] ? $rs['arrparentid'].','.$rs['catid'] : $rs['catid']) : '';
        self::create($d);
        $rs = list_tree(self::catList($type),0,['catid','parentid','title']);
        return ['msg'=>'添加类别成功','code'=>1, 'data'=>$rs];
    }

    /**
     * 类别编辑
     * @param  string   $do      快编
     * @param  string   $type    标识
     * @param  string   $editor  编辑
     * @return array
     */
    public static function cedit(string $do = '', string $type = '', string $editor = '')
    {
        $d = request()->post(['catid/d','title','icon','parentid/d','listorder/d','av','af'],'','strip_sql');
        $catid = $d['catid'];
        if(!$catid) return ['msg'=>'参数错误'];
        if(!$Myobj = self::one(['catid'=>$catid])) return ['msg'=>'数据不存在'];
        if($do=='up'){
            $value = $d['av'];
            $field = $d['af'];
            if(!in_array($field,['sign','listorder'])) return ['msg'=>'参数错误'];
            if($field == 'listorder') $value = intval($value);
            if($Myobj->save([$field=>$value,'editor'=>$editor])){
                return ['msg'=>'设置成功','code'=>1, 'data'=>list_tree(self::catList($type),0,['catid','parentid','title'])];
            }else{
                return ['msg'=>'设置失败'];
            }
        }else{
            $arr = []; //改上级ID时所用到的所有子类新数据
            if(!$d['title']) return ['msg'=>'请输入类别名称'];
            $parentid = $d['parentid'];
            if($catid==$parentid) return ['msg'=>'上级ID不能为本身ID'];
            //上级类别有改动
            if($Myobj['parentid'] != $parentid){
                //旧的所有上级ID串
                $old_arrparentid = $Myobj['arrparentid'] ? $Myobj['arrparentid'].','.$catid : $catid;
                //获取上级类数据
                $rs = $parentid ? self::one(['catid'=>$parentid]) : ['arrparentid'=>'','catid'=>''];
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
            $d['editor'] = $editor;
            if($Myobj->save($d)){
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
        $rs = self::destroy(function($query)use($catid){
            $query->where("CONCAT(',',CONCAT(arrparentid,',')) LIKE '%,{$catid},%' OR catid IN($catid)");
        });
        if($rs){
            return ['msg'=>'删除成功','code'=>1, 'data'=>list_tree(self::catList($type),0,['catid','parentid','title'])];
        }else{
            return ['msg'=>'删除失败'];
        }
    }

}
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
use think\facade\Db;

/**
 *【字典模型】
 */
class Dict extends Base
{
    /**
     *定义主键
     * @var string 
     */
    protected $pk = 'id';

    /**
     * 获取所有子类ID串
     * @param  int   $id   ID
     * @return string
     */
    public static function getChild($id=0)
    {
        $id = abs($id);
        if($id>0){
            $rs = self::where("(id = $id OR FIND_IN_SET($id,arrparentid))")->column('id');
            $id = $rs ? implode(',', $rs) : '';
        }
        return $id;
    }

    /**
     * 系统数据字典缓存 [字典标识1=>[字典项集合],字典标识n=>[字典项集合n],..]
     * @param   int   $s   是否重置缓存
     * @return  array
     */
    public static function cache($s=0)
    {
        $k = 'DICTS_ARRAY';
        $r = cache($k);
        if(!$r || $s){
            $r = [];
            $g = DictGroup::all("groupid > 0",'id,code,sql');
            $p = config('database.connections.'.config('database.default').'.prefix');
            foreach($g as $v){
                if($v['sql']){
                    $sql = str_ireplace(['update','replace','delete','drop','vt_'], ['@@','@@','@@','@@',$p], $v['sql']);
                    if(strpos($sql,'@@') === false) $r[$v['code']] = Db::query("{$sql}");
                }else{
                    $r[$v['code']] = self::where("groupid = $v[id] AND state = 1")->order(['parentid'=>'asc','listorder'=>'asc','id'=>'asc'])->column('id,name,value,parentid as pid,arrparentid as pids');
                }
            }
            cache($k,$r);
        }
        return $r;
    }

}
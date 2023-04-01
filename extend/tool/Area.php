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

use think\facade\Db;

/**
 * 地区表重建处理
 */
class Area
{
    /**
     * 缓存
     * @var array 
     */
    private static $obj = [];

    /**
     * 地区修复
     * @return string
     */
    public function area()
    {
        set_time_limit(0);
        $i = 1;
        $rs = Db::name('area')->column('*');
        self::into([0],[0],$rs);
        return 'OK';
    }

    /**
     * 插入数据
     * @param  array  $pids
     * @param  array  $nids
     * @param  array  $rs
     * @return mixed
     */
    public static function into($pids,$nids,$rs)
    {
        foreach ($pids as $k => $pid){
            $allpid = $newids = [];
            $newpid = $nids[$k];
            $arrparentid = 0;
            $i = 1;
            foreach ($rs as $v){
                if($v['parentid'] == $pid){
                    $allpid[] = intval($v['areaid']);unset($v['areaid']);
                    if($newpid){
                        if(isset(self::$obj[$newpid])){
                            $res = self::$obj[$newpid];
                        }else{
                            $res = Db::name('areaok')->where("areaid = $newpid")->find();
                            self::$obj[$newpid] = $res;
                        }
                        $arrparentid = $res['arrparentid'] ? $res['arrparentid'].','.$res['areaid'] : $res['areaid'];
                    }
                    $v['parentid']    = $newpid;
                    $v['arrparentid'] = $arrparentid;
                    $v['listorder']   = $i;
                    $newids[] = Db::name('areaok')->insertGetId($v);
                    $i++;
                }
            }
            self::into($allpid,$newids,$rs);
        }
    }

}
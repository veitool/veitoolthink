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
 *【菜单模型】
 * 菜单缓存标识说明：VMENUS_1：后台全部菜单  VMENUS_2：会员全部菜单
 * 后台角色权限缓存：VMENUS_1_角色ID 在角色模型中处理
 * 会员分组权限缓存：VMENUS_2_分组ID 在分组模型中处理
 */
class Menus extends Base
{
    /**
     *定义主键
     * @var string 
     */
    protected $pk = 'menuid';

    /**
     * 获取层级管理菜单（递归 用于左侧栏目 调用）
     * @param   array    $data    菜单集或会员信息集(必须含键：userid、role_menuid/group_menuid)
     * @param   int      $pid     上级ID
     * @return  array
     */
    public static function getMenus(array $data = [])
    {
        $arr = []; $str = ',';
        if(isset($data['userid'])){
            if(isset($data['role_menuid'])){
                $tp = 1; $md = $data['role_menuid']; //后台管理菜单
            }else{
                $tp = 2; $md = $data['group_menuid']; //会员管理菜单
            }
            $userid = $data['userid'];
            //获取后台或会员全部菜单
            $data = self::cache(0,$tp);
            foreach($data as $k=>$v){
                $flag = (($userid>1 || $tp==2) && strpos(",$md,",",$k,")===false);
                if(!$flag && $v['role_url']) $str .= $v['role_url'].',';
                //过滤掉未开启的菜单，以及后台非顶管、前台会员没有的管理权限菜单
                if(!$v['state'] || $flag) continue;
                $arr[] = [
                    'id'     => $v['menuid'],
                    'icon'   => $v['icon'],
                    'name'   => $v['menu_name'],
                    'catid'  => $v['catid'],
                    'pid'    => $v['parent_id'],
                    'url'    => $v['menu_url'] ? '#/'.$v['menu_url'] : '',
                    'iframe' => $v['link_url']
                ];
            }
        }
        return ['menus'=>$arr,'roles'=>$str];
    }

    /**
     * 系统管理菜单缓存
     * @param   int     $s    是否重置缓存
     * @param   int     $t    菜单区分 默认1 1:后台菜单 2:会员菜单
     * @return  array
     */
    public static function cache(int $s = 0, int $t = 1)
    {
        $k = 'VMENUS_'.$t;
        $r = cache($k);
        if(!$r || $s){
            $r = self::where("type=$t")->order(['parent_id'=>'asc','listorder'=>'asc','menuid'=>'asc'])->column('menuid,menu_name,role_name,link_url,menu_url,role_url,icon,catid,parent_id,state','menuid');
            cache($k,$r);
        }
        return $r;
    }

}
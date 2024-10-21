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
use app\model\system\SystemMenus as M;

/**
 * 菜单操作类（主用于插件管理调用）
 */
class Menus
{
    /**
     * 创建菜单
     * @param   array    $menus    菜单数组
     * @param   int      $parent   父类ID
     * @param   string   $name     插件标识名
     * @return  mixed
     */
    public static function create(array $menus = [], int $parent = 0, string $name = '')
    {
        $old = [];
        self::menuUpdate($menus, $parent, $name);
        M::cache(1);
    }

    /**
     * 删除菜单
     * @param   string  $name  插件名
     * @return  int  删除的记录数
     */
    public static function delete(string $name)
    {
        $i = 0;
        if($name){
            $i = M::del(['name'=>$name]);
            if($i>0){
                M::cache(1);
            }
        }
        return $i;
    }

    /**
     * 菜单升级
     * @param  array    $newMenu    新菜单数据
     * @param  int      $parentid   父类ID
     * @param  string   $name       插件标识名
     * @return mixed
     */
    private static function menuUpdate(array $newMenu = [], int $parentid = 0, string $name = '')
    {
        $time = time();
        $allow = ['catid'=>'1','name'=>'','menu_name'=>'莫名菜单','role_name'=>'','link_url'=>'','menu_url'=>'','role_url'=>'','link_url'=>'','icon'=>'','ismenu'=>'0','listorder'=>10,'state'=>'0','type'=>'1'];
        foreach($newMenu as $k => $v){
            $data = array_intersect_key($v, $allow);
            $data = array_merge($allow, $data);
            $data['addtime']   = $time;
            $data['parent_id'] = $parentid;
            $data['role_name'] = $data['role_name'] ?: $data['menu_name'];
            $data['name'] = $name ?: $data['name'];
            $menu = M::create($data);
            if(isset($v['sublist']) && $v['sublist']){
                self::menuUpdate($v['sublist'], $menu['menuid'], $name);
            }
        }
    }

    /**
     * 菜单导出
     * @param  int            $menuid   菜单ID
     * @param  string|array   $where    查询条件
     * @return array
     */
    public static function menuOut(int $menuid = 0, string|array $where = '')
    {
        global $_MES;
        $_MES = $_MES ?: M::where($where)->order(['listorder'=>'asc','menuid'=>'asc'])->column('*');
        $data = [];
        foreach ($_MES as $v){
            if($v['parent_id']==$menuid){
                $v['sublist'] = self::menuOut($v['menuid']);
                if(!$v['icon']) unset($v['icon']);
                if(!$v['link_url']) unset($v['link_url']);
                if(!$v['menu_url']) unset($v['menu_url']);
                if(!$v['sublist']) unset($v['sublist']);
                unset($v['menuid'],$v['addtime'],$v['parent_id']);
                $data[] = $v;
            }
        }
        return $data;
    }

    /**
     * 启用插件菜单(返回更新的记录数)
     * @param   string  $name   插件名
     * @return  int
     */
    public static function enable(string $name)
    {
        $i = 0;
        if($name){
            $i = M::where(['name'=>$name,'ismenu'=>'1'])->update(['state' => '1']);
            if($i>0){
                M::cache(1);
            }
        }
        return $i;
    }

    /**
     * 禁用插件菜单(返回更新的记录数)
     * @param   string  $name   插件名
     * @return  int
     */
    public static function disable(string $name)
    {
        $i = 0;
        if($name){
            $i = M::where(['name'=>$name])->update(['state' => '0']);
            if($i>0){
                M::cache(1);
            }
        }
        return $i;
    }

    /**
     * 根据插件名获取ID串
     * @param   string   $name   插件名
     * @return  string
     */
    public static function getAuthRuleIdsByName(string $name)
    {
        return implode(',', M::where('name',$name)->column('menuid'));
    }

    /**
     * 菜单数据库记录按顺序重建
     * @param   int     $id   顶级ID
     * @param   int     $pd   上级ID
     * @param   array   $rs   菜单集
     * @return  string
     */
    public static function reset(int $id = 0, int $pd = 0, array $rs = [])
    {
        if(!$rs){
            $rs = Db::name('system_menus')->order(['listorder'=>'asc','menuid'=>'asc'])->column('*');
            if($rs){
                $prefix = config('database.connections.'.config('database.default').'.prefix');
                Db::query("truncate {$prefix}system_menus");
                self::reset($id,$pd,$rs);
                M::cache(1);
            }
            return 'ok';
        }else{
            $data = [];
            $time = time();
            foreach($rs as $v){
                if($v['parent_id']==$id){
                    $menuid = $v['menuid']; unset($v['menuid']);
                    $v['addtime']   = $time;
                    $v['parent_id'] = $pd;
                    $data[$menuid]  = Db::name('system_menus')->insertGetId($v);
                }
            }
            foreach($data as $k=>$v){
                self::reset($k, $v, $rs);
            }
        }
    }

}
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
 *【地区模型】
 */
class Area extends Base
{
    /**
     * 定义主键
     * @var string 
     */
    protected $pk = 'areaid';

    /**
     * 地区数组：地区ID => 地区名
     * @var string 
     */
    protected static $area = [];

    /**
     * 获取地区名称串
     * @param  int     $aid     地区ID串
     * @param  int     $fg      分隔符
     * @return string  返回地区名，如：广东-广州-番禺
     */
    public static function getAreaStr($aid='', $fg='-')
    {
        if(empty(self::$area)){
            $rs = self::cache();
            foreach ($rs as $v){
                self::$area[$v['areaid']] = $v['areaname'];
            }
        }
        $str = '';
        $arr = explode(',', $aid);
        foreach ($arr as $v){
            if(isset(self::$area[$v])){
                $str .= $str ? $fg.self::$area[$v] : self::$area[$v];
            }
        }
        return $str;
    }

    /**
     * 获取层级地区JSON数据（递归 用于手机版 调用 该方法已废用）
     * @param   array    $data  地区数据
     * @param   int      $pid   上级ID
     * @return  array
     */
    public static function getAreaJson($data=[], $pid=0)
    {
        if(!$data){
            $data = self::cache(0);
        }
        $arr = [];
        foreach($data as $v){
            if($v['parentid']==$pid){
                $a = [
                    'text'     => $v['areaname'],
                    'value'    => (string)$v['areaid'],
                    'children' => self::getAreaJson($data,$v['areaid'])
                ];
                if(!$a['children']) unset($a['children']);
                $arr[] = $a;
            }
        }
        return $arr;
    }

    /**
     * 缓存地区数据
     * @param   int    $reset    是否重置缓存
     * @return  array
     */
    public static function cache($reset=0)
    {
        $key = 'VAREAS';
        $rs = cache($key);
        if(!$rs || $reset){
            $rs = self::order('listorder','asc')->column('areaid,areaname,parentid,arrparentid,childs,listorder','areaid');
            if(!$rs) return $rs;
            cache($key,$rs,31536000);
            //生成JS文件
            $myfile = @fopen(VT_PUBLIC."static/script/cityData.js", "w");
            if($myfile){
                $txt = json_encode(self::getAreaJson(),JSON_UNESCAPED_UNICODE);
                fwrite($myfile, 'var cityData = '. str_replace(['"text"','"value"','"children"'], ["text","value","children"], $txt).';');
                fclose($myfile);
            }
        }
        return $rs;
    }

}
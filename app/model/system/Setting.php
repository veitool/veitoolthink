<?php
/**
 * ===========================================================================
 * Veitool 快捷开发框架系统
 * Author: Niaho 26843818@qq.com
 * Copyright (c)2019-2023 www.veitool.com All rights reserved.
 * Licensed: 这不是一个自由软件，不允许对程序代码以任何形式任何目的的再发行
 * ---------------------------------------------------------------------------
 */
namespace app\model\system;

use app\model\Base;

/**
 *【设置模型】
 */
class Setting extends Base
{
    /**
     * 列表(分页)
     * @param  string  $where  查询条件
     * @return obj
     */
    public function listQuery($where='')
    {
        return $this->field('*')->where($where)->order('listorder', 'asc')->paginate(input('limit/d'));
    }

    /**
     * 列表
     * @param  string  $where  查询条件
     * @param  string  $field  查询的字段
     * @return array
     */
    public function listArray($where='', $field='*')
    {
        return $this->where($where)->order('listorder', 'asc')->column($field);
    }

    /**
     * 获取配置信息
     * @param  string $name  配置名
     * @return mixed
     */
    public static function getSetting($name='')
    {
        $configs = self::column('value,type,name,addon');
        $result = [];
        foreach($configs as $config){
            if($config['type'] == 'array'){
                $val = parse_attr($config['value']);
            }elseif($config['type'] == 'checkbox'){
                $val = $config['value']!='' ? explode(',', $config['value']) : [];
            }else{
                $val = $config['value'];
            }
            if($config['addon']){
                $result['@'.$config['addon']][$config['name']] = $val;
            }else{
                $result[$config['name']] = $val;
            }
        }
        return $name != '' ? $result[$name] : $result;
    }

    /**
    * 载入配置数据
    * @param   int    $reset   是否重置缓存
    * @return  array
    */
    public static function cache($reset=0)
    {
        $key = 'VSETTING';
        $rs = cache($key);
        if(!$rs || $reset){
            $rs = self::getSetting();
            cache($key,$rs);
        }
        return $rs;
    }

}
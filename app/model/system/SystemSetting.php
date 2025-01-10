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
 *【设置模型】
 */
class SystemSetting extends Base
{
    /**
     * 列表(分页)
     * @param  string/array  $where  查询条件
     * @return obj
     */
    public function listQuery(string|array $where = '')
    {
        return $this->where($where)->field('*')->order('listorder', 'asc')->paginate(input('limit/d'));
    }

    /**
     * 列表
     * @param  string/array  $where  查询条件
     * @param  string        $field  查询的字段
     * @return array
     */
    public function listArray(string|array $where = '', string $field = '*')
    {
        return $this->where($where)->order('listorder', 'asc')->column($field);
    }

    /**
     * 获取配置信息
     * @param  string  $name  配置名
     * @return array
     */
    public static function getSetting(string $name = null)
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
        return is_null($name) ? $result : $result[$name];
    }

    /**
    * 载入配置数据
    * @param   int   $reset   是否重置缓存
    * @return  array
    */
    public static function cache(int $reset = 0)
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
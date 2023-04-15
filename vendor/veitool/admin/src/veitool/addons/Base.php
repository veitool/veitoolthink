<?php
/**
 * ===========================================================================
 * Veitool 快捷开发框架系统
 * Author: Niaho 26843818@qq.com
 * Copyright (c)2019-2023 www.veitool.com All rights reserved.
 * Licensed: 这不是一个自由软件，不允许对程序代码以任何形式任何目的的再发行
 * ---------------------------------------------------------------------------
 */
namespace veitool\addons;

use think\facade\View;

/**
 * 插件抽象基类
 * @package veitool\addons
 */
abstract class Base
{
    protected $addonName = '';
    protected $addonPath = '';
    protected $infoBox = [];

    /**
     * 构造函数
     * @param  string  $name  插件名
     * @access public
     */
    public function __construct($name = null)
    {
        $name = is_null($name) ? $this->getName() : $name;
        $this->addonName = $name;
        $this->addonPath = ADDON_PATH . $name . VT_DS;
        View::config(['view_path' => $this->addonPath]);
        if(method_exists($this, '__init')){
            $this->__init();
        }
    }

    /**
     * 读取基础配置信息
     * @param  string  $name   插件名
     * @param  bool    $force  是否重读
     * @return array
     */
    final public function getInfo($name = '', $force = false)
    {
        $info = [];
        $name = $name ?: $this->getName();
        if(!$force){
            if(isset($this->infoBox[$name])){
                $info = $this->infoBox[$name];
            }
        }
        if(!$info){
            $ifile = $this->addonPath . 'info.ini';
            if(is_file($ifile)){
                $info = parse_ini_file($ifile, true, INI_SCANNER_TYPED) ?: [];
                $this->infoBox[$name] = $info;
            }
        }
        return $info;
    }

    /**
     * 设置插件信息数据
     * @param  string  $name   插件名
     * @param  array   $data   内容值
     * @return array
     */
    final public function setInfo($name = '', $data = [])
    {
        $name = $name ?: $this->getName();
        $info = $this->getInfo($name);
        $info = array_merge($info, $data);
        $this->infoBox[$name] = $info;
        return $info;
    }

    /**
     * 获取当前模块名 如：addons\abc\Abc 获取得就是Abc 这个类名 abc(转为了小写)
     * @return string
     */
    final public function getName()
    {
        if($this->addonName){
            return $this->addonName;
        }
        $data = explode('\\', get_class($this));
        return strtolower(array_pop($data));
    }

    /**
     * 检查基础配置信息是否完整(插件包中ini文件中必须要有的：name、title、intro、author、version、state 属性)
     * @return bool
     */
    final public function checkInfo()
    {
        $info = $this->getInfo();
        $keys = ['name', 'title', 'version', 'intro', 'author', 'state'];
        foreach($keys as $k){
            if(!array_key_exists($k, $info)){
                return false;
            }
        }
        return true;
    }

    /**
     * 追加/剔除插件应用路由（标识）
     * @param  array   $data   要追加/剔除的标识集
     * @param  bool    $type   方式true表追加/false表剔除 默认true
     * @return mixed
     */
    final public function setAddons($data = [], $type = true)
    {
        if(is_array($data) && $data){
            $arr = config('veitool.addons',[]);
            $val = $type ? array_unique(array_merge($arr, $data)) : array_diff($arr, $data);
            $val = "['".implode("', '", $val)."'";
            $str = file_get_contents(ROOT_PATH . '/config/veitool.php');
            $str = preg_replace('/addons(.*?)]/', "addons' => {$val}]", $str);
            $fop = fopen(ROOT_PATH . '/config/veitool.php', 'w');
            fwrite($fop, $str);
            fclose($fop);
        }
    }

    abstract public function install();

    abstract public function uninstall();
}
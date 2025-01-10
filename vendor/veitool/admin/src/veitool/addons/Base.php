<?php
/**
 * ===========================================================================
 * Veitool 快捷开发框架系统
 * Author: Niaho 26843818@qq.com
 * Copyright (c)2019-2025 www.veitool.com All rights reserved.
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
    //插件标识名
    protected $addonName = '';
    //插件目录
    protected $addonPath = '';
    //插件信息盒子
    protected $infoBox = [];

    /**
     * 构造函数
     * @param  string  $name  插件名
     * @access public
     */
    public function __construct(string $name = null)
    {
        $name = is_null($name) ? $this->getName() : $name;
        //设置插件标识
        $this->addonName = $name;
        //获取当前插件目录
        $this->addonPath = ADDON_PATH . $name . VT_DS;
        //配置视图路径
        View::config(['view_path' => $this->addonPath]);
        //控制器初始化
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
    final public function getInfo(string $name = '', bool $force = false)
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
    final public function setInfo(string $name = '', array $data = [])
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
    final public function setAddons(array $data = [], bool $type = true)
    {
        if(is_array($data) && $data){
            $arr = config('veitool.addons',[]);
            $val = $type ? array_unique(array_merge($arr, $data)) : array_diff($arr, $data);
            $val = $val ? "'".implode("', '", $val)."'" : '';
            $str = file_get_contents(ROOT_PATH . '/config/veitool.php');
            $str = preg_replace('/addons(.*?)]/', "addons' => [{$val}]", $str);
            $fop = fopen(ROOT_PATH . '/config/veitool.php', 'w');
            fwrite($fop, $str);
            fclose($fop);
        }
    }

    /**
     * 合并函数库
     * @param   string   $addon    插件标识
     * @param   string   $tofile   目标文件
     * @param   string   $form     来源文件【为空时则会清空 相关合并的函数库】
     */
    final public function mergeFun(string $addon = '', string $tofile = '', string $form = ''){
        if($addon == '' || !is_file($tofile)) return;
        $f1 = file_get_contents($tofile);
        $f2 = $form && is_file($form) ? str_replace(['<?php',"<?php\n"],['',''],file_get_contents($form)) : '';
        $sin1 = '/*====插件'.$addon.'公共函数====*/';
        $sin2 = '/*====插件'.$addon.'公共函数END====*/';
        if(strpos($f1, $sin1) === false){
            if(!$f2) return;
            $f1 .= "\n".$sin1.$f2."\n".$sin2;
        }else{
            $f1 = preg_replace("/(\n\/\*)====插件".$addon."([\s\S]*)插件".$addon."公共函数END====(\*\/)/", $f2 ? "\n".$sin1.$f2."\n".$sin2 : '', $f1);
        }
        $fop = fopen($tofile, 'w');
        fwrite($fop, $f1);
        fclose($fop);
    }

    //必须实现的插件安装方法
    abstract public function install();

    //必须实现的插件卸载方法
    abstract public function uninstall();
}
<?php
/**
 * ===========================================================================
 * Veitool 快捷开发框架系统
 * Author: Niaho 26843818@qq.com
 * Copyright (c)2019-2024 www.veitool.com All rights reserved.
 * Licensed: 这不是一个自由软件，不允许对程序代码以任何形式任何目的的再发行
 * ---------------------------------------------------------------------------
 */
namespace app\admin\controller;

use think\facade\Db;
use think\Exception;
use veitool\addons\Service;
use veitool\addons\AddonException;
use app\model\system\SystemSetting as S;

/**
 * 插件管理控制器
 */
class Addon extends AdminBase
{
    /**
     * 插件列表
     * @return mixed
     */
    public function index()
    {
        $this->assign([
            'limit'  => 10,
            'addons' => json_encode(Service::hasAddon())
        ]);
        return $this->fetch();
    }

    /**
     * 本地已装插件
     * @return json
     */
    public function exist()
    {
        $d = $this->only(['kw','uid/d','catid/d','version'=>'0','all'=>'list'],'get');
        $kw = $d['kw'];
        $catid = $d['catid'];
        $d['catid'] = -1;
        $addonsOn = Service::onAddon($d);
        $addons = Service::hasAddon();
        $data = [];
        foreach($addons as $k=>$v){
            if($kw && stripos($k,$kw)===false && stripos($v['title'],$kw)===false && stripos($v['intro'],$kw)===false) continue;
            if(isset($addonsOn[$k])){
                $v = array_merge($v,$addonsOn[$k]);
            }else{
                $v['catid'] = 0;
                $v['down'] = '-';
                $v['price'] = '-';
                $v['require'] = '';
                $v['releaselist'] = [];
            }
            if($catid > -1 && $catid != $v['catid']) continue;
            $data[] = $v;
        }
        return $this->returnMsg($data);
    }

    /**
     * 插件安装
     * @return json
     */
    public function install()
    {
        $d = $this->only(['name/*/a','uid/d','token','version'=>'1.0.0']);
        try{
            $d['vversion'] = VT_VERSION;
            Service::install($d['name'], config('veitool.force',1), $d);
        }catch(AddonException $e){
            return $this->returnMsg($e->getMessage().'_01',$e->getCode(),$e->getData());
        }catch(Exception $e){
            return $this->returnMsg($e->getMessage().'_02',$e->getCode());
        }
        return $this->returnMsg('安装成功', 1, ['addons' => Service::hasAddon()]);
    }

    /**
     * 本地上传
     * @return json
     */
    public function local()
    {
        $d = $this->only(['uid/d','token']);
        if(!$d["uid"] || !$d["token"]) return $this->returnMsg('请先登录Veitool会员后再进行离线安装！',2);
        try{
            $d["vversion"] = VT_VERSION;
            Service::local($this->request->file('file'), config('veitool.force',1), $d);
        }catch(AddonException $e){
            return $this->returnMsg($e->getMessage().'_01',$e->getCode(),$e->getData());
        }catch(Exception $e){
            return $this->returnMsg($e->getMessage().'_02');
        }
        return $this->returnMsg('安装成功', 1, ['addons' => Service::hasAddon()]);
    }

    /**
     * 卸载插件
     * @return json
     */
    public function uninstall()
    {
        $d = $this->only(['@token'=>'','name/*/a']); 
        try{
            //只有开启调试且为超级管理员方可删除相关 数据表 和 配置项
            $tables = config('veitool.ddata',1) && env('app_debug') && $this->manUser['userid']==1 ? Service::getAddonTables($d['name']) : [];
            Service::uninstall($d['name'], config('veitool.force',1));
            if($tables){
                $prefix = config('database.connections.'.config('database.default').'.prefix');
                foreach($tables as $index => $table){
                    if(!preg_match("/^{$prefix}{$d['name']}/", $table)) continue;
                    Db::execute("DROP TABLE IF EXISTS `{$table}`");
                }
                Db::name('system_setting')->where('addon',$d['name'])->delete();
            }
        }catch(AddonException $e){
            return $this->returnMsg($e->getMessage(),$e->getCode(),$e->getData());
        }catch(Exception $e){
            return $this->returnMsg($e->getMessage(),$e->getCode());
        }
        return $this->returnMsg('卸载成功', 1, ['addons' => Service::hasAddon()]);
    }

    /**
     * 禁用启用插件
     * @return json
     */
    public function state()
    {
        $d = $this->only(['name/*/a','state/d']);
        try{
            $action = $d['state'] ? 'enable' : 'disable';
            Service::$action($d['name'], config('veitool.force',1));
        }catch(AddonException $e){
            return $this->returnMsg($e->getMessage(),$e->getCode(),$e->getData());
        }catch(Exception $e){
            return $this->returnMsg($e->getMessage());
        }
        return $this->returnMsg('设置成功', 1, ['addons' => Service::hasAddon()]);
    }

    /**
     * 更新升级
     * @return json
     */
    public function upgrade()
    {
        $d = $this->only(['name/*/a','uid/d','token','version'=>'1.0.0']);
        try{
            $d["vversion"] = VT_VERSION;
            Service::upgrade($d['name'], $d);
        }catch(AddonException $e){
            return $this->returnMsg($e->getMessage().'_01',$e->getCode(),$e->getData());
        }catch(Exception $e){
            return $this->returnMsg($e->getMessage().'_02',$e->getCode());
        }
        return $this->returnMsg('升级成功', 1, ['addons' => Service::hasAddon()]);
    }

    /**
     * 插件配置管理
     * @param  string   $do      操作参数
     * @param  string   $addon   插件名称
     * @param  string   $group   配置分组
     * @return mixed
     */
    public function setting(string $do = '', string $addon = '', string $group = '')
    {
        $groups = (array) vconfig('@'.$addon.'.'.'group',[]); reset($groups);
        if($do=='json'){
            $group = $group ? $group : key($groups);
            $where = [];
            $where[] = ['state','=',1];
            $where[] = ['addon','=',$addon];
            if($group) $where[] = ['group','=',$group];
            $rs = (new S())->listArray($where,'name,title,value,type,options,private,relation,tips');
            foreach($rs as &$v){
                if($v['type'] == 'images'){
                    $v['value'] = $v['value'] ? json_decode($v['value']) : [];
                }elseif(in_array($v['type'],['year','month','date','time','datetime'])){
                    $v['range'] = $v['options'];
                }elseif($v['private']){
                    $v['value'] = half_replace($v['value']);
                }
                $v['placeholder'] = $v['tips'];
                if($v['options']) $v['options'] = parse_attr($v['options']);
            }
            return $this->returnMsg($rs,1);
        }
        $this->assign([
            'addon'  => $addon,
            'groups' => json_encode($groups)
        ]);
        return $this->fetch();
    }

    /**
     * 插件配置更新
     * @return json
     */
    public function setup()
    {
        $this->only(['@token'=>'']);
        $d = $this->request->post();
        $group = $d['__g'] ?? '';
        $addon = $d['__a'] ?? '';
        if(!$addon) return $this->returnMsg("参数错误");
        $where = [];
        $where[] = ['state','=',1];
        $where[] = ['addon','=',$addon];
        if($group) $where[] = ['group','=',$group];
        $rs = (new S())->listArray($where,'name,type,private');
        if($rs){
            unset($d['__g'],$d['__a']);
            foreach ($rs as $v){
                $name = $v['name'];
                if(in_array($name, ['sys_group','sys_type'])) continue; // 系统关键配置项不可修改 开发时请注释该行
                if($v['type'] == 'checkbox'){
                    $data['value'] = isset($d[$name]) && is_array($d[$name]) ? implode(',', $d[$name]) : '';
                }elseif($v['type'] == 'image'){
                    $data['value'] = $d[$name] ?? '';
                }elseif($v['type'] == 'images'){
                    $data['value'] = isset($d[$name]) && is_array($d[$name]) ? json_encode($d[$name]) : '';
                }else{
                    $data['value'] = $d[$name] ?? 0;
                    if($v['private'] && strpos($data['value'], '***') !== false) continue; // 隐私项含星号不可更新
                }
                S::where("name='$name'")->update($data);
            }
            S::cache(1);
            return $this->returnMsg("设置成功", 1);
        }else{
            return $this->returnMsg('参数错误或未找到相关记录');
        }
    }

}
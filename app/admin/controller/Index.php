<?php
/**
 * ===========================================================================
 * Veitool 快捷开发框架系统
 * Author: Niaho 26843818@qq.com
 * Copyright (c)2019-2025 www.veitool.com All rights reserved.
 * Licensed: 这不是一个自由软件，不允许对程序代码以任何形式任何目的的再发行
 * ---------------------------------------------------------------------------
 */
namespace app\admin\controller;

use app\model\system\SystemDict as Dict;
use app\model\system\SystemMenus as Menus;
use app\model\system\SystemCategory as Category;

/**
 * 后台主控制器
 */
class Index extends AdminBase
{
    /**
     * 后台首页
     * @return mixed
     */
    public function index()
    {
        $this->assign([
            "appMap"    => $this->appMap,
            "tokenName" => $this->tokenName
        ]);
        return $this->fetch('','',false);
    }

    /**
     * 后台主面板
     * @return mixed
     */
    public function main()
    {
        return $this->fetch();
    }

    /**
     * 获取左侧菜单和用户信息
     * @param  int   $do   是否更新字典缓存0否1是
     * @return json
     */
    public function json(int $do = 0)
    {
        $arr = [];
        $cat = Category::catList([['state','=',1],['type','=','01']],0,'title,icon,catid'); // 获取菜单分类
        $data = Menus::getMenus(array_intersect_key($this->manUser, ['userid'=>"",'role_menuid'=>""])); // 获取拥有的菜单数据
        $rs =[
            'menus' => $cat ? ['cat'=>$cat,'menus'=>$data['menus']]: $data['menus'],
            'user'  => $this->manUser + ['roles' => $data['roles']] + ['rolem' => \app\model\system\SystemRoles::where("state = 1 AND roleid IN(".$this->manUser['roleids'].")")->column("roleid id,role_name name")] + ['dict' => Dict::cache((int)$do)]
        ];
        unset($rs['user']['password'],$rs['user']['passsalt']);
        return json($rs);
    }
 
    /**
     * 清空缓存
     * @return json
     */
    public function clear(){
        \think\facade\Cache::clear(); 
        return $this->returnMsg("清理缓存成功!");
    }

    /**
     * 查询IP所在地区
     * @return mixed
     */
    public function ip()
    {
        $url  = 'https://whois.pconline.com.cn/ipJson.jsp?callback='.input('callback').'&ip='.input('ip');
        $cont = trim(file_get_contents($url));
        $cont = iconv("gb2312","utf-8//IGNORE",$cont);
        return $cont;
    }

}
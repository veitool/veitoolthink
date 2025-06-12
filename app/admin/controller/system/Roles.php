<?php
/**
 * ===========================================================================
 * Veitool 快捷开发框架系统
 * Author: Niaho 26843818@qq.com
 * Copyright (c)2019-2025 www.veitool.com All rights reserved.
 * Licensed: 这不是一个自由软件，不允许对程序代码以任何形式任何目的的再发行
 * ---------------------------------------------------------------------------
 */
namespace app\admin\controller\system;

use app\admin\controller\AdminBase;
use app\model\system\SystemRoles as R;
use app\model\system\SystemMenus;

/**
 * 后台角色控制器
 */
class Roles extends AdminBase
{
    /**
     * 角色列表
     * @param  string  $do  异步数据
     * @return mixed
     */
    public function index(string $do = '')
    {
        if($do=='json'){
            return $this->returnMsg((new R())->listQuery());
        }elseif($do=='mjson'){
            $ids  = '';
            $data = [];
            $roleid = $this->request->get('roleid/d');
            if($roleid){
                $rs = R::one(['roleid'=>$roleid]);
                $ids = empty($rs) ? '' : $rs['role_menuid'];
            }
            $rs = SystemMenus::cache(); // 获取后台菜单缓存 构建zTree Json数据
            foreach($rs as $v){
                $flag = (strpos(",$ids,",",$v[menuid],")!==false) ? true : false;
                $data[] = ['id'=>$v['menuid'],'pId'=>$v['parent_id'],'name'=>$v['role_name'].' '.$v['role_url'],'checked'=>$flag,'open'=>true];
            }
            return $this->returnMsg($data);
        }
        $this->assign('limit', 10);
        return $this->fetch();
    }

    /**
     * 角色添加
     * @return json
     */
    public function add()
    {
        $d = $this->only(['@token'=>'','role_name/*/{2,30}/角色名称','listorder/d','state/d','role_menuid','role_ext']);
        $d['role_menuid'] = is_array($d['role_menuid']) ? implode(',', array_map('intval', $d['role_menuid'])) : '';
        $d['creator'] = $this->manUser['username'];
        $obj = R::create($d);
        R::cache(['roleid'=>$obj->roleid,'role_name'=>$d['role_name'],'role_menuid'=>$d['role_menuid'],'role_ext'=>$d['role_ext']],1);
        return $this->returnMsg("添加成功", 1);
    }

    /**
     * 角色编辑
     * @param  array   $do   快编操作
     * @return json
     */
    public function edit(string $do = '')
    {
        $d = $this->only($do ? ['@token'=>'','roleid/d/参数错误','av','af'] : ['@token'=>'','roleid/d/参数错误','role_name/*/{2,30}/角色名称','listorder/d','state/d','role_menuid','role_ext']);
        $roleid = $d['roleid'];
        $Myobj = R::one("roleid = $roleid");
        if(!$Myobj) return $this->returnMsg("数据不存在");
        if($do=='up'){
            $value = $d['av'];
            $field = $d['af'];
            if(!in_array($field,['role_name','state','listorder'])) return $this->returnMsg("参数错误");
            if($field=='role_name'){
                $this->only(['av/*/{2,30}/角色名称']);
            }else{
                $value = intval($value);
            }
            if($Myobj->save([$field=>$value,'editor'=>$this->manUser['username']])){
                R::cache($roleid,1);
                return $this->returnMsg("设置成功", 1);
            }else{
                return $this->returnMsg("设置失败");
            }
        }else{
            $d['role_menuid'] = is_array($d['role_menuid']) ? implode(',', array_map('intval', $d['role_menuid'])) : '';
            $d['editor'] = $this->manUser['username'];
            if($Myobj->save($d)){
                R::cache(['roleid'=>$roleid,'role_name'=>$d['role_name'],'role_menuid'=>$d['role_menuid'],'role_ext'=>$d['role_ext']],1);
                return $this->returnMsg("编辑成功", 1);
            }else{
                return $this->returnMsg("编辑失败");
            }
        }
    }

    /**
     * 角色删除
     * @return json
     */
    public function del()
    {
        $roleid = $this->only(['@token'=>'','roleid'])['roleid'];
        $roleid = is_array($roleid) ? $roleid : [$roleid];
        if(!$roleid) return $this->returnMsg('参数错误');
        R::destroy($roleid);
        foreach($roleid as $id){
            cache('VMENUS_1_'.$id, null);
        }
        return $this->returnMsg("删除成功", 1);
    }

}
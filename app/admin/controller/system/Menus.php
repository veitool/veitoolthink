<?php
/**
 * ===========================================================================
 * Veitool 快捷开发框架系统
 * Author: Niaho 26843818@qq.com
 * Copyright (c)2019-2024 www.veitool.com All rights reserved.
 * Licensed: 这不是一个自由软件，不允许对程序代码以任何形式任何目的的再发行
 * ---------------------------------------------------------------------------
 */
namespace app\admin\controller\system;

use app\admin\controller\AdminBase;
use app\model\system\SystemMenus as M;
use app\model\system\SystemCategory as C;
use tool\Menus as T;

/**
 * 后台菜单管理控制器
 */
class Menus extends AdminBase
{
    /**
     * 菜单列表
     * @param  string   $do   异步数据
     * @return mixed
     */
    public function index(string $do = '')
    {
        if($do=='json'){
            $catid = $this->request->get('catid/d',0);
            return $this->returnMsg(M::where("type=1 AND catid=$catid")->order(['listorder'=>'asc','menuid'=>'asc'])->select());
        }
        $this->assign([
            'category' => json_encode(C::catList('01',0,'title,catid'))
        ]);
        return $this->fetch();
    }

    /**
     * 菜单数据重构
     * @return mixed
     */
    public function reset()
    {
        $rs = T::reset() == 'ok' ? 1 : 0;
        return $this->returnMsg("构建成功", $rs);
    }

    /**
     * 菜单添加
     * @return json
     */
    public function add()
    {
        $d = $this->only(['@token'=>'','catid/d','menu_name/*/{2,20}/菜单名称','role_name/*/{2,20}/权限名称','link_url/u','menu_url/u','role_url/u','icon/u','parent_id/d','listorder/d','ismenu/d','state/d']);
        $d['addtime'] = time();
        if(M::insert($d)){
            M::cache(1);
            return $this->returnMsg("添加菜单成功", 1);
        }else{
            return $this->returnMsg('添加菜单失败');
        }
    }

    /**
     * 菜单批量添加
     * @return json
     */
    public function adds()
    {
        $d = $this->only(['@token'=>'','titles/h','pid/d','catid/d']);
        if(!$d['titles']) return $this->returnMsg("请输入菜单名称");
        $id = $d['pid'];
        $rs = M::get(['menuid'=>$id]);
        if($id==0 || $rs){
            $data = [];
            $arr  = explode("\n", $d['titles']);
            foreach($arr as $v){
                if(!is_preg($v,'{2,20}')) continue;
                $data[] = ['menu_name'=>$v,'role_name'=>$v,'catid'=>$d['catid'],'parent_id'=>$id,'state'=>0,'listorder'=>10,'addtime'=>time()];
            }
            if(M::insertAll($data)){
                M::cache(1);
                return $this->returnMsg("批量添加成功", 1);
            }else{
                return $this->returnMsg('批量添加失败');
            }
        }else{
            return $this->returnMsg("上级ID不存在");
        }
    }

    /**
     * 菜单编辑
     * @param  string   $do   快编参数
     * @return json
     */
    public function edit(string $do = '')
    {
        $d = $this->only($do ? ['@token'=>'','menuid/d/参数错误','av/u','af'] : ['@token'=>'','menuid/d/参数错误','catid/d','ocatid/d','menu_name/*/{2,20}/菜单名称','role_name/*/{2,20}/权限名称','link_url/u','menu_url/u','role_url/u','icon/u','parent_id/d','listorder/d','ismenu/d','state/d']);
        $menuid = $d['menuid'];
        $Myobj = M::get("menuid = $menuid");
        if(!$Myobj) return $this->returnMsg("数据不存在");
        if($do=='up'){
            $value = $d['av'];
            $field = $d['af'];
            if(!in_array($field,['menu_name','menu_url','role_url','link_url','listorder','ismenu','state'])) return $this->returnMsg("参数错误");
            if($field=='menu_name'){
                $this->only(['av/*/{2,20}/菜单名称']);
            }elseif(in_array($field,['listorder','ismenu','state'])){
                $value = intval($value);
            }
            $data = $field == 'menu_name' ? [$field=>$value,'role_name'=>$value] : [$field=>$value];
            if($Myobj->save($data)){
                M::cache(1);
                return $this->returnMsg("设置成功", 1);
            }else{
                return $this->returnMsg("设置失败");
            }
        }else{
            $d['addtime'] = time();
            $ocatid = intval($d['ocatid']); //旧Catid
            unset($d['ocatid']);
            if($Myobj->save($d)){
                $arr = M::cache(1);
                if($d['catid'] != $ocatid){
                    $ids = get_subclass($menuid, $arr,'menuid','parent_id');
                    M::where('menuid','in',$ids)->update(['catid'=>$d['catid']]);
                    M::cache(1);
                }
                return $this->returnMsg("编辑菜单成功", 1);
            }else{
                return $this->returnMsg("编辑菜单失败");
            }
        }
    }

    /**
     * 菜单删除
     * @return json
     */
    public function del()
    {
        $menuid = $this->only(['@token'=>'','menuid'])['menuid'];
        $menuid = is_array($menuid) ? implode(',',$menuid) : $menuid;
        if(!$menuid) return $this->returnMsg('参数错误');
        $rs = M::where("parent_id IN($menuid)")->column("parent_id");
        $menuid = $rs ? implode(',',array_diff(explode(',',$menuid), $rs)) : $menuid;
        if(!$menuid) return $this->returnMsg('删除结构错误');
        if(M::del("menuid IN($menuid) AND type = 1")){
            M::cache(1);
            return $this->returnMsg("删除成功", 1);
        }else{
            return $this->returnMsg("删除失败");
        }
    }

    /**
     * 菜单导出
     * @return json
     */
    public function out()
    {
        $menuid = $this->request->post('menuid/d',0);
        $data = T::menuOut($menuid,'type=1');
        $rs = M::get("menuid = '$menuid'");
        if($rs){
            $rs = $rs->toArray();
            $rs['sublist'] = $data;
            unset($rs['menuid'],$rs['addtime']);
            $data = [$rs];
        }
        $msg = '无数据导出';
        if($data){
            $file = 'sysMenus_'.$menuid.'.php';
            $content = "<?php\nreturn ".var_export($data,true).";";
            $content = preg_replace('/(?<==> \n).*?(?=array)/si', '', $content);
            $content = str_replace(["array (", "),", ");", "=> \n"], ["[", "],", "];", "=> "], $content);
            @file_put_contents(RUNTIME_PATH.$file, $content);
            $msg = '导出成功位置:/runtime/'.$file;
        }
        return $this->returnMsg($msg);
    }

    /**
     * 菜单导入
     * @return json
     */
    public function up()
    {
        set_time_limit(0);
        $code = 0;
        $file = RUNTIME_PATH.'sysMenus.php';
        if(is_file($file)){
            try{
                $data = include($file);
                T::create($data);
                M::cache(1);
                $msg = '导入成功';
                $code = 1;
            }catch(\think\db\exception\PDOException $e){
                $msg = $e->getMessage();
            }
        }else{
            $msg = '找不到菜单数据:/runtime/sysMenus.php';
        }
        return $this->returnMsg($msg,$code);
    }

    /**
     * 分类管理
     * @return mixed
     */
    public function category()
    {
        $this->assign([
            'list' => json_encode(C::catList('01'))
        ]);
        return $this->fetch();
    }

    /**
     * 分类添加
     * @return json
     */
    public function catadd()
    {
        $d = $this->only(['@token'=>'','title/*/{2,20}/类别名称','icon/u','listorder/d']);
        $d['type'] = '01';
        if(C::insert($d)){
            return $this->returnMsg('添加类别成功',1,C::catList('01'));
        }else{
            return $this->returnMsg('添加类别失败');
        }
    }

    /**
     * 分类编辑
     * @param  string   $do   操作参数
     * @return json
     */
    public function catedit(string $do = '')
    {
        $d = $this->only($do ? ['@token'=>'','catid/d/参数错误','av','af'] : ['@token'=>'','catid/d/参数错误','title/*/{2,20}/类别名称','icon/u','listorder/d']);
        $Myobj = C::get("catid = $d[catid]");
        if(!$Myobj) return $this->returnMsg("数据不存在");
        if($do=='up'){
            $value = $d['av'];
            $field = $d['af'];
            if(!in_array($field,['title','listorder','state'])) return $this->returnMsg("参数错误");
            if($field=='title'){
                $this->only(['av/*/{2,20}/类别名称']);
            }else{
                $value = intval($value);
            }
            if($Myobj->save([$field=>$value])){
                return $this->returnMsg('设置成功',1,C::catList('01'));
            }else{
                return $this->returnMsg('设置失败');
            }
        }else{
            if($Myobj->save($d)){
                return $this->returnMsg('编辑成功',1,C::catList('01'));
            }else{
                return $this->returnMsg('编辑失败');
            }
        }
    }

    /**
     * 分类删除
     * @return json
     */
    public function catdel()
    {
        $catid = $this->only(['@token'=>'','catid'])['catid'];
        $catid = is_array($catid) ? implode(',',$catid) : $catid;
        if(!$catid) return $this->returnMsg('参数错误');
        if(M::get("catid IN ($catid)")) return $this->returnMsg('所删类别下存在菜单');
        if(C::del("catid IN ($catid)")){
            return $this->returnMsg('删除成功',1,C::catList('01'));
        }else{
            return $this->returnMsg('删除失败');
        }
    }

}
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
use app\model\system\Area;
use app\model\system\Roles;
use app\model\system\Organ;
use app\model\system\Manager as M;

/**
 * 后台用户控制器
 */
class Manager extends AdminBase
{
    /**
     * 用户列表
     * @param  string  $do      操作参数
     * @param  string  $action  操作参数 有权限限制
     * @return mixed
     */
    public function index($do='', $action='')
    {
        if($action=='info'){ //个人中心
            if(!Area::cache()) $this->exitMsg('请到地区管理设置或导入地区数据',400);
            $User = $this->manUser;
            $User['areaname'] = Area::getAreaStr($User['areaid'], ' - ');
            $this->assign("User",$User);
            return $this->fetch('info');
        }elseif($action=='role'){ //角色切换
            $roleid = $this->request->get('roleid/d');
            if($roleid!=$this->manUser['roleid'] && in_array($roleid, explode(',',$this->manUser['roleids']))){
                M::update(['userid'=>$this->manUser['userid'],'roleid'=>$roleid]);
            }
            return $this->redirect($this->appMap);
        }
        $organ = Organ::order(['listorder'=>'asc'])->column('*');
        if($do){
            if($do=='json'){ //异步管理员列表数据
                return $this->returnMsg((new M())->listQuery([],'password,passsalt,token'));
            }elseif($do=='organ'){ //组织机构JSON数据
                return $organ;
            }elseif($do=='info'){ //用户信息
                $username = $this->request->get('username','','trim');
                $rs = M::get("username = '$username'");
                if($rs){
                    $rs->password = $rs->passsalt = $rs->token = '';
                    $rs->areaname = Area::getAreaStr($rs->areaid, ' - ');
                    $rs->role_name = Roles::cache($rs->roleid)['role_name'];
                    return $this->returnMsg($rs,1);
                }else{
                    return $this->returnMsg('用户不存在');
                }
            }elseif($do=='check'){ //判断用户名称是否已被占用
                $userid   = $this->request->post('userid',0,'intval');
                $username = $this->request->post('username','','trim');
                $where[]  = ['username','=',$username];
                if($userid) $where[] = ['userid','<>',$userid];
                $rs  = M::get($where);
                $msg = $rs ? ['code'=>1,'msg'=>'用户【'.$username.'】已经存在'] : ['code'=>0,'msg'=>'可用'];
                return $this->returnMsg($msg);
            }
        }
        $this->assign([
            'limit' => 10,
            'organ' => json_encode($organ),
            'roles' => json_encode(Roles::where("state > 0")->order('listorder','asc')->column('role_name','roleid')) //角色ID=>角色名
        ]);
        $this->assign('limit', 10);
        return $this->fetch();
    }

    /**
     * 用户添加
     * @return json
     */
    public function add()
    {
        $d = $this->only(['@token'=>'','username/*/u/管理帐号','password/*/p/登录密码','groupid/d/请选择所属机构','roleids/*/i/请选择所属角色','truename/?/n','mobile/?/m','email/?/e']);
        if(M::get("username = '$d[username]'")) return $this->returnMsg("该用户帐号已经存在");
        $d["passsalt"] = random(8);
        $d["password"] = set_password($d["password"],$d["passsalt"]);
        $d["edit"]     = $this->manUser['username'];
        $d["addtime"]  = time();
        $d["roleid"]   = explode(",",$d['roleids'])[0];
        if(M::insert($d)){
            return $this->returnMsg("添加用户成功", 1);
        }else{
            return $this->returnMsg('添加用户失败');
        }
    }

    /**
     * 用户编辑
     * @param  array   $do   快编操作
     * @return json
     */
    public function edit($do='')
    {
        $d = $this->only($do ? ['@token'=>'','userid/d/参数错误','av','af'] : ['@token'=>'','userid/d/参数错误','username/*/u/管理帐号','groupid/d/请选择所属机构','roleids/*/i/请选择所属角色','truename/?/n','mobile/?/m','email/?/e']);
        $userid = $d['userid'];
        $Myobj = M::get("userid = $userid");
        if(!$Myobj) return $this->returnMsg("数据不存在");
        if($do=='up'){
            $value = $d['av'];
            $field = $d['af'];
            if(!in_array($field,['truename','mobile','email','face','state'])) return $this->returnMsg("参数错误");
            if($field=='truename'){
                $this->only(['av/?/n']);
            }elseif($field=='mobile'){
                $this->only(['av/?/m']);
            }elseif($field=='email'){
                $this->only(['av/?/e']);
            }elseif($field=='state'){
                $value = $userid==1 ? 1 : intval($value);
            }
            return $this->returnMsg($Myobj->save([$field=>$value]) ? "设置成功" : '设置失败', 1);
        }else{
            if($userid == 1 && $userid != $this->manUser['userid']) return $this->returnMsg("您的身份不能修改超级用户的信息");
            if(M::get("username='$d[username]' AND userid<>$userid")) return $this->returnMsg("帐号【".$d['username']."】已经存在");
            $d["edittime"] = time();
            $d["roleid"]   = explode(",",$d['roleids'])[0];
            if($Myobj->save($d)){
                return $this->returnMsg("编辑用户成功", 1);
            }else{
                return $this->returnMsg("编辑用户失败");
            }
        }
    }

    /**
     * 用户中心个人信息编辑
     * @return json
     */
    public function edits()
    {
        $d = $this->only(['@token'=>'','nickname/*/n/昵称','truename/*/n','email/?/e','mobile/?/m','areaid/?/i/地区','address/?/{2,100}/详细地址','gender/d']);
        $d["userid"] = $this->manUser['userid'];
        $d['gender'] = in_array($d['gender'],[1,2]) ? $d['gender'] : 1;
        $d["edittime"] = time();
        if(M::update($d)){
            return $this->returnMsg("修改成功", 1);
        }else{
            return $this->returnMsg('修改失败');
        }
    }

    /**
     * 用户删除
     * @return json
     */
    public function del()
    {
        $userid = $this->only(['@token'=>'','userid'])['userid'];
        $userid = is_array($userid) ? implode(',',$userid) : $userid;
        if(!$userid) return $this->returnMsg('参数错误');
        $ids = explode(',', $userid);
        if(in_array(1,$ids))return $this->returnMsg('不允许删除最高用户');
        if(in_array($this->manUser['userid'],$ids))return $this->returnMsg('不允许删除当前用户');
        if(M::del("userid IN($userid)")){
            return $this->returnMsg("删除成功", 1);
        }else{
            return $this->returnMsg("删除失败");
        }
    }

    /**
     * 个人修改密码
     * @return json
     */
    public function changpwd()
    {
        $d = $this->only(['oldPassword/*/p/原登录密码','newPassword/*/p/新登录密码']);
        $userid = $this->manUser['userid'];
        $rs = M::get(compact('userid'));
        if(!$rs) return $this->returnMsg('用户不存在');
        if($rs['password'] != set_password($d['oldPassword'],$rs["passsalt"])) return $this->returnMsg('原登录密码错误');
        $rs->passsalt = random(8);
        $rs->password = set_password($d['newPassword'],$rs->passsalt);
        if($rs->save()){
            return $this->returnMsg("修改成功", 1);
        }else{
            return $this->returnMsg("修改失败");
        }
    }

    /**
     * 重置密码
     * @return json
     */
    public function resetpwd()
    {
        $d = $this->only(['@token'=>'','userid/d/参数错误','newPassword/*/p/新登录密码']);
        if($d["userid"]==1) return $this->returnMsg('超级管理员禁止重置密码');
        $d["passsalt"] = random(8);
        $d["password"] = set_password($d["newPassword"],$d["passsalt"]);
        unset($d["newPassword"]);
        if(M::update($d)){
            return $this->returnMsg("重置密码成功", 1);
        }else{
            return $this->returnMsg("重置密码失败");
        }
    }

    /**
     * 组织机构添加
     * @return json
     */
    public function oadd()
    {
        $d = $this->only(['@token'=>'','title/*/{2,10}/机构简称','titles/*/{2,50}/机构全称','parentid/d','listorder/d','note/h']);
        $rs = Organ::get("id = $d[parentid]");
        $d['arrparentid'] = $rs ? (empty($rs['arrparentid']) ? $rs['id'] : $rs['arrparentid'].','.$rs['id']) : '';
        if(Organ::insert($d)){
            return $this->returnMsg("添加机构成功", 1, Organ::order(['listorder'=>'asc'])->column('*'));
        }else{
            return $this->returnMsg("添加机构失败");
        }
    }

    /**
     * 组织机构编辑
     * @return json
     */
    public function oedit()
    {
        $d = $this->only(['@token'=>'','id/d/参数错误','title/*/{2,10}/机构简称','titles/*/{2,50}/机构全称','parentid/d','listorder/d','note/h']);
        $id = $d['id'];
        $arr = []; //改上级ID时所用到的所有子类新数据
        $parentid = $d['parentid'];
        if($id==$parentid) return $this->returnMsg("上级ID不能为本身ID");
        $Myobj = Organ::get("id = $id");
        if(!$Myobj) return $this->returnMsg("数据不存在");
        if($Myobj['parentid'] != $parentid){
            //旧的所有上级ID串
            $old_arrparentid = $Myobj['arrparentid'] ? $Myobj['arrparentid'].','.$id : $id;
            //获取上级类数据
            $rs = $parentid ? Organ::get("id = $parentid") : ['arrparentid'=>'','id'=>''];
            if(!$rs) return $this->returnMsg("上级ID不存在");
            //构造数据
            $d['arrparentid'] = $rs['arrparentid'] ? $rs['arrparentid'].','.$rs['id'] : $rs['id'];
            //新的所有上级ID串
            $new_arrparentid = $d['arrparentid'] ? $d['arrparentid'].','.$id : $id;
            //子类处理
            $rs = Organ::where("FIND_IN_SET($id,arrparentid)")->column("*");
            foreach($rs as $v){
                if($v['id']==$parentid) return $this->returnMsg("上级ID不能设为子类ID");
                //替换旧上级ID串为新上级ID串
                $arrparentid = str_replace($old_arrparentid,$new_arrparentid,$v['arrparentid']);
                $arr[] = ['id'=>$v['id'],'arrparentid'=>$arrparentid];
            }
        }
        if($Myobj->save($d)){
            if($arr) (new Organ)->saveAll($arr);
            return $this->returnMsg("编辑成功",1,Organ::order(['listorder'=>'asc'])->column('*'));
        }else{
            return $this->returnMsg("编辑失败");
        }
    }

    /**
     * 组织机构删除
     * @return json
     */
    public function odel()
    {
        $id = $this->only(['@token'=>'','id/d/参数错误'])['id'];
        if($id==1) return $this->returnMsg("顶级组织机构不可删除");
        $ids = Organ::getChild($id);
        if(M::get("groupid IN($ids)")) return $this->returnMsg("该组织机构下存在用户不可删除");
        $rs = Organ::del("CONCAT(',',CONCAT(arrparentid,',')) LIKE '%,{$id},%' OR id = $id");
        if($rs){
            return $this->returnMsg("删除成功",1,Organ::order(['listorder'=>'asc'])->column('*'));
        }else{
            return $this->returnMsg("删除失败");
        }
    }

}
<?php
/**
 * ===========================================================================
 * Veitool 快捷开发框架系统
 * Author: Niaho 26843818@qq.com
 * Copyright (c)2019-2023 www.veitool.com All rights reserved.
 * Licensed: 这不是一个自由软件，不允许对程序代码以任何形式任何目的的再发行
 * ---------------------------------------------------------------------------
 */
namespace app\admin\controller\system;

use app\admin\controller\AdminBase;
use app\model\system\Dict as D;
use app\model\system\DictGroup as DG;

/**
 * 后台字典控制器
 */
class Dict extends AdminBase
{
    /**
     * 字典列表
     * @param  string  $do  操作参数
     * @return mixed
     */
    public function index($do='')
    {
        $Types = DG::where("groupid = 0")->order(['id'=>'asc'])->column('id,title,parentid');
        if($do){
            if($do=='json'){ //异步字典组列表数据
                return $this->returnMsg((new DG())->listQuery());
            }elseif($do=='dict'){ //字典组JSON数据
                return $Types;
            }elseif($do=='check'){ //判断字典编码是否已被占用
                $id   = $this->request->post('id',0,'intval');
                $code = $this->request->post('code','','trim');
                $where[]  = ['code','=',$code];
                if($id) $where[] = ['id','<>',$id];
                $rs  = DG::get($where);
                $msg = $rs ? ['code'=>1,'msg'=>'编码【'.$code.'】已经存在'] : ['code'=>0,'msg'=>'可用'];
                return $this->returnMsg($msg);
            }
        }
        $this->assign([
            'limit' => 10,
            'Types' => json_encode($Types)
        ]);
        $this->assign('limit', 10);
        return $this->fetch();
    }

    /**
     * 字典添加
     * @return json
     */
    public function add()
    {
        $d = $this->only(['@token'=>'','title/*/{2,100}/字典名称','code/*/{2,30}/字典编码/1,2,3/_','groupid/d','@sql/s','note/h']);
        if($d['groupid'] == 1) return $this->returnMsg("所属类型不能为顶级类型");
        if(DG::get("code = '$d[code]'")) return $this->returnMsg("字典编码【{$d['code']}】已经存在");
        $d["editor"]   = $this->manUser['username'];
        $d["addtime"]  = VT_TIME;
        if(DG::insert($d)){
            return $this->returnMsg("添加字典成功", 1);
        }else{
            return $this->returnMsg('添加字典失败');
        }
    }

    /**
     * 字典编辑
     * @param  string   $do   快编操作
     * @return json
     */
    public function edit($do='')
    {
        $d = $this->only($do ? ['@token'=>'','id/d/参数错误','av','af'] : ['@token'=>'','id/d/参数错误','title/*/{2,100}/字典名称','code/*/{2,30}/字典编码/1,2,3/_','groupid/d','@sql/s','note/h']);
        $id = $d['id'];
        $Myobj = DG::get("id = $id AND groupid > 0");
        if(!$Myobj) return $this->returnMsg("数据不存在");
        if($do=='up'){
            $value = $d['av'];
            $field = $d['af'];
            if(!in_array($field,['title','code','note'])) return $this->returnMsg("参数错误");
            if($field=='title' && $value==''){
                return $this->returnMsg("字典名称不能为空");
            }elseif($field=='code'){
                if($value==''){
                    return $this->returnMsg("字典编码不能为空");
                }elseif(DG::get("code = '$value' AND id <> $id")){
                    return $this->returnMsg("字典编码【{$value}】已经存在");
                }
            }
            D::cache(1);
            return $this->returnMsg($Myobj->save([$field=>$value]) ? "设置成功" : '设置失败', 1);
        }else{
            if($d['groupid'] == 1) return $this->returnMsg("所属类型不能为顶级类型");
            if(DG::get("code = '$d[code]' AND id <> $id")) return $this->returnMsg("字典编码【{$d['code']}】已经存在");
            $d["editor"] = $this->manUser['username'];
            if($Myobj->save($d)){
                D::cache(1);
                return $this->returnMsg("编辑字典成功", 1);
            }else{
                return $this->returnMsg("编辑字典失败");
            }
        }
    }

    /**
     * 字典删除
     * @return json
     */
    public function del()
    {
        $id = $this->only(['@token'=>'','id'])['id'];
        $id = is_array($id) ? implode(',',$id) : $id;
        if(!$id) return $this->returnMsg('参数错误');
        if(DG::del("id IN($id)")){
            D::del("groupid IN($id)");
            return $this->returnMsg("删除成功", 1);
        }else{
            return $this->returnMsg("删除失败");
        }
    }

    /**
     * 字典组添加
     * @return json
     */
    public function gadd()
    {
        $d = $this->only(['@token'=>'','title/*/{2,10}/类型名称','parentid/d','note/h']);
        $rs = DG::get("id = $d[parentid]");
        $d['arrparentid'] = $rs ? (empty($rs['arrparentid']) ? $rs['id'] : $rs['arrparentid'].','.$rs['id']) : '';
        $d['addtime'] = VT_TIME;
        $d["editor"]  = $this->manUser['username'];
        if(DG::insert($d)){
            return $this->returnMsg("添加成功", 1, DG::where("groupid = 0")->order(['id'=>'asc'])->column('id,title,parentid'));
        }else{
            return $this->returnMsg("添加失败");
        }
    }

    /**
     * 字典组编辑
     * @return json
     */
    public function gedit()
    {
        $d = $this->only(['@token'=>'','id/d/参数错误','title/*/{2,10}/类型名称','parentid/d','note/h']);
        $id = $d['id'];
        $arr = []; //改上级ID时所用到的所有子类新数据
        $parentid = $d['parentid'];
        if($id == $parentid) return $this->returnMsg("上级ID不能为本身ID");
        $Myobj = DG::get("id = $id AND groupid = 0");
        if(!$Myobj) return $this->returnMsg("数据不存在");
        if($Myobj['parentid'] != $parentid){
            //旧的所有上级ID串
            $old_arrparentid = $Myobj['arrparentid'] ? $Myobj['arrparentid'].','.$id : $id;
            //获取上级类数据
            $rs = $parentid ? DG::get("id = $parentid") : ['arrparentid'=>'','id'=>''];
            if(!$rs) return $this->returnMsg("上级ID不存在");
            //构造数据
            $d['arrparentid'] = $rs['arrparentid'] ? $rs['arrparentid'].','.$rs['id'] : $rs['id'];
            //新的所有上级ID串
            $new_arrparentid = $d['arrparentid'] ? $d['arrparentid'].','.$id : $id;
            //子类处理
            $rs = DG::where("FIND_IN_SET($id,arrparentid)")->column("*");
            foreach($rs as $v){
                if($v['id']==$parentid) return $this->returnMsg("上级ID不能设为子类ID");
                //替换旧上级ID串为新上级ID串
                $arrparentid = str_replace($old_arrparentid,$new_arrparentid,$v['arrparentid']);
                $arr[] = ['id'=>$v['id'],'arrparentid'=>$arrparentid];
            }
        }
        $d["editor"] = $this->manUser['username'];
        if($Myobj->save($d)){
            if($arr) (new DG)->saveAll($arr);
            return $this->returnMsg("编辑成功",1,DG::where("groupid = 0")->order(['id'=>'asc'])->column('id,title,parentid'));
        }else{
            return $this->returnMsg("编辑失败");
        }
    }

    /**
     * 字典组删除
     * @return json
     */
    public function gdel()
    {
        $id = $this->only(['@token'=>'','id/d/参数错误'])['id'];
        if($id < 4) return $this->returnMsg("系统基础类型禁止删除");
        if(!$ids = DG::getChild($id)) return $this->returnMsg("数据不存在");
        $rs = DG::del("CONCAT(',',CONCAT(arrparentid,',')) LIKE '%,{$id},%' OR groupid IN($ids) OR id = $id");
        if($rs){
            return $this->returnMsg("删除成功",1,DG::where("groupid = 0")->order(['id'=>'asc'])->column('id,title,parentid'));
        }else{
            return $this->returnMsg("删除失败");
        }
    }

    /**
     * 字典项列表管理
     * @param  string  $do        操作参数
     * @param  int     $groupid   所属字典ID
     */
    function items($do='',$groupid=0)
    {
        if($do=='json'){
            return $this->returnMsg(D::where("groupid=".(int)$groupid)->order(['listorder'=>'asc','id'=>'asc'])->select());
        }
        $this->assign([
            'groupid' => $groupid
        ]);
        return $this->fetch();
    }
    
    /**
     * 字典项添加
     * @return json
     */
    public function iadd()
    {
        $d = $this->only(['@token'=>'','groupid/d','parentid/d','name/*/{1,100}/字典项名','value/*/{1,100}/字典项值','listorder/d','state/d']);
        $rs = D::get("id = $d[parentid]");
        $d['arrparentid'] = $rs ? (empty($rs['arrparentid']) ? $rs['id'] : $rs['arrparentid'].','.$rs['id']) : '';
        $d['addtime'] = VT_TIME;
        $d["editor"]  = $this->manUser['username'];
        if(D::insert($d)){
            D::cache(1);
            return $this->returnMsg("添加字典项成功", 1);
        }else{
            return $this->returnMsg('添加字典项失败');
        }
    }

    /**
     * 字典项批量添加
     * @return json
     */
    public function iadds()
    {
        $d = $this->only(['@token'=>'','titles/s','pid/d','groupid/d']);
        if(!$d['titles']) return $this->returnMsg("请输入字典项名");
        $id = $d['pid'];
        $rs = D::get("id = $id");
        if($id==0 || $rs){
            $data = [];
            $arr  = explode("\n", $d['titles']);
            $arrparentid = $rs ? (empty($rs['arrparentid']) ? $rs['id'] : $rs['arrparentid'].','.$rs['id']) : '';
            foreach($arr as $v){
                if(word_count($v) < 1) continue;
                $vs = explode('|', $v);
                $data[] = ['name'=>$vs[0],'value'=>$vs[1] ?? $vs[0],'groupid'=>$d['groupid'],'parentid'=>$id,'arrparentid'=>$arrparentid,'listorder'=>100,'addtime'=>VT_TIME,'editor'=>$this->manUser['username']];
            }
            if(D::insertAll($data)){
                D::cache(1);
                return $this->returnMsg("批量添加成功", 1);
            }else{
                return $this->returnMsg('批量添加失败');
            }
        }else{
            return $this->returnMsg("上级ID不存在");
        }
    }

    /**
     * 字典项编辑
     * @param  string   $do   快编参数
     * @return json
     */
    public function iedit($do='')
    {
        $d = $this->only($do ? ['@token'=>'','id/d/参数错误','av/u','af'] : ['@token'=>'','id/d/参数错误','groupid/d','parentid/d','name/*/{1,100}/字典项名','value/*/{1,100}/字典项值','listorder/d','state/d']);
        $id = $d['id'];
        $Myobj = D::get("id = $id");
        if(!$Myobj) return $this->returnMsg("数据不存在");
        if($do=='up'){
            $value = $d['av'];
            $field = $d['af'];
            if(!in_array($field,['name','value','listorder','state'])) return $this->returnMsg("参数错误");
            if($field=='name'){
                $this->only(['av/*/{1,100}/字典项名']);
            }elseif($field=='value'){
                $this->only(['av/*/{1,100}/字典项值']);
            }else{
                $value = intval($value);
            }
            if($Myobj->save([$field=>$value])){
                D::cache(1);
                return $this->returnMsg("设置成功", 1);
            }else{
                return $this->returnMsg("设置失败");
            }
        }else{
            $arr = []; //改上级ID时所用到的所有子类新数据
            $parentid = $d['parentid'];
            if($id == $parentid) return $this->returnMsg("上级ID不能为本身ID");
            if($Myobj['parentid'] != $parentid){
                //旧的所有上级ID串
                $old_arrparentid = $Myobj['arrparentid'] ? $Myobj['arrparentid'].','.$id : $id;
                //获取上级类数据
                $rs = $parentid ? D::get("id = $parentid") : ['arrparentid'=>'','id'=>''];
                if(!$rs) return $this->returnMsg("上级ID不存在");
                //构造数据
                $d['arrparentid'] = $rs['arrparentid'] ? $rs['arrparentid'].','.$rs['id'] : $rs['id'];
                //新的所有上级ID串
                $new_arrparentid = $d['arrparentid'] ? $d['arrparentid'].','.$id : $id;
                //子类处理
                $rs = D::where("FIND_IN_SET($id,arrparentid)")->column("*");
                foreach($rs as $v){
                    if($v['id']==$parentid) return $this->returnMsg("上级ID不能设为子类ID");
                    //替换旧上级ID串为新上级ID串
                    $arrparentid = str_replace($old_arrparentid,$new_arrparentid,$v['arrparentid']);
                    $arr[] = ['id'=>$v['id'],'arrparentid'=>$arrparentid];
                }
            }
            $d["editor"] = $this->manUser['username'];
            if($Myobj->save($d)){
                if($arr) (new D)->saveAll($arr);
                D::cache(1);
                return $this->returnMsg("编辑字典项成功", 1);
            }else{
                return $this->returnMsg("编辑字典项失败");
            }
        }
    }

    /**
     * 字典项删除
     * @return json
     */
    public function idel()
    {
        $id = $this->only(['@token'=>'','id'])['id'];
        $id = is_array($id) ? implode(',',$id) : $id;
        if(!$id) return $this->returnMsg('参数错误');
        $rs = D::where("parentid IN($id)")->column("parentid");
        $id = $rs ? implode(',',array_diff(explode(',',$id), $rs)) : $id;
        if(!$id) return $this->returnMsg('删除结构错误');
        if(D::del("id IN($id)")){
            D::cache(1);
            return $this->returnMsg("删除成功", 1);
        }else{
            return $this->returnMsg("删除失败");
        }
    }

    /**
     * 获取字典集
     * @param  string  $code  字典编码
     * @return json
     */
    public function json($code='')
    {
        $rs = D::cache();
        return $this->returnMsg($code && isset($rs[$code]) ? $rs[$code] : $rs);
    }

}
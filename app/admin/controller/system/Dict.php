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
use app\model\system\SystemDict as D;
use app\model\system\SystemDictGroup as DG;

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
    public function index(string $do = '')
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
                $rs  = DG::one($where);
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
        if(DG::one("code = '$d[code]'")) return $this->returnMsg("字典编码【{$d['code']}】已经存在");
        $d["creator"] = $this->manUser['username'];
        DG::create($d);
        D::cache(1);
        return $this->returnMsg("添加字典成功", 1);
    }

    /**
     * 字典编辑
     * @param  string   $do   快编操作
     * @return json
     */
    public function edit(string $do = '')
    {
        $d = $this->only($do ? ['@token'=>'','id/d/参数错误','av','af'] : ['@token'=>'','id/d/参数错误','title/*/{2,100}/字典名称','code/*/{2,30}/字典编码/1,2,3/_','groupid/d','@sql/s','note/h']);
        $id = $d['id'];
        $Myobj = DG::one("id = $id AND groupid > 0");
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
                }elseif(DG::one("code = '$value' AND id <> $id")){
                    return $this->returnMsg("字典编码【{$value}】已经存在");
                }
            }
            D::cache(1);
            return $this->returnMsg($Myobj->save([$field=>$value,'editor'=>$this->manUser['username']]) ? "设置成功" : '设置失败', 1);
        }else{
            if($d['groupid'] == 1) return $this->returnMsg("所属类型不能为顶级类型");
            if(DG::one("code = '$d[code]' AND id <> $id")) return $this->returnMsg("字典编码【{$d['code']}】已经存在");
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
        $id = is_array($id) ? $id : [$id];
        if(!$id) return $this->returnMsg('参数错误');
        DG::destroy($id);
        D::destroy(function($query)use($id){
            $query->whereIn('groupid',$id);
        });
        D::cache(1);
        return $this->returnMsg("删除成功", 1);
    }

    /**
     * 字典组添加
     * @return json
     */
    public function gadd()
    {
        $d = $this->only(['@token'=>'','title/*/{2,10}/类型名称','parentid/d','note/h']);
        $rs = DG::one("id = $d[parentid]");
        $d['arrparentid'] = $rs ? (empty($rs['arrparentid']) ? $rs['id'] : $rs['arrparentid'].','.$rs['id']) : '';
        $d["creator"] = $this->manUser['username'];
        DG::create($d);
        return $this->returnMsg("添加成功", 1, DG::where("groupid = 0")->order(['id'=>'asc'])->column('id,title,parentid'));
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
        $Myobj = DG::one("id = $id AND groupid = 0");
        if(!$Myobj) return $this->returnMsg("数据不存在");
        if($Myobj['parentid'] != $parentid){
            //旧的所有上级ID串
            $old_arrparentid = $Myobj['arrparentid'] ? $Myobj['arrparentid'].','.$id : $id;
            //获取上级类数据
            $rs = $parentid ? DG::one("id = $parentid") : ['arrparentid'=>'','id'=>''];
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
        if($id < 4) return $this->returnMsg("字典基础类型禁止删除");
        if(!$ids = DG::getChild($id)) return $this->returnMsg("数据不存在");
        // 删除所有字典项
        D::destroy(function($query)use($ids){
            $query->alias('a')->join('system_dict_group b', 'a.groupid = b.id')->where("b.groupid IN($ids)");
        });
        // 删除所有层级子类、所有子类下的字典 以及 本身
        DG::destroy(function($query)use($id,$ids){
            $query->where("CONCAT(',',CONCAT(arrparentid,',')) LIKE '%,{$id},%' OR groupid IN($ids) OR id = $id");
        });
        return $this->returnMsg("删除成功", 1, DG::where("groupid = 0")->order(['id'=>'asc'])->column('id,title,parentid'));
    }

    /**
     * 字典项列表管理
     * @param  string  $do        操作参数
     * @param  int     $groupid   所属字典ID
     * @return mixed
     */
    function items(string $do = '', int $groupid = 0)
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
        $d = $this->only(['@token'=>'','groupid/d','parentid/d','name/s/字典项名','value/s/字典项值','listorder/d','state/d']);
        $rs = D::one("id = $d[parentid]");
        $d['arrparentid'] = $rs ? (empty($rs['arrparentid']) ? $rs['id'] : $rs['arrparentid'].','.$rs['id']) : '';
        $d["creator"] = $this->manUser['username'];
        D::create($d);
        D::cache(1);
        return $this->returnMsg("添加字典项成功", 1);
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
        $rs = D::one("id = $id");
        if($id==0 || $rs){
            $data = [];
            $arr  = explode("\n", $d['titles']);
            $arrparentid = $rs ? (empty($rs['arrparentid']) ? $rs['id'] : $rs['arrparentid'].','.$rs['id']) : '';
            foreach($arr as $v){
                if(word_count($v) < 1) continue;
                $vs = explode('|', $v);
                $data[] = ['name'=>$vs[0],'value'=>$vs[1] ?? $vs[0],'groupid'=>$d['groupid'],'parentid'=>$id,'arrparentid'=>$arrparentid,'listorder'=>100,'creator'=>$this->manUser['username']];
            }
            if(D::saveAll($data)){
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
    public function iedit(string $do = '')
    {
        $d = $this->only($do ? ['@token'=>'','id/d/参数错误','av/u','af'] : ['@token'=>'','id/d/参数错误','groupid/d','parentid/d','name/s/请输入字典项名','value/s/请输入字典项值','listorder/d','state/d']);
        $id = $d['id'];
        $Myobj = D::one("id = $id");
        if(!$Myobj) return $this->returnMsg("数据不存在");
        if($do=='up'){
            $value = $d['av'];
            $field = $d['af'];
            if(!in_array($field,['name','value','listorder','state'])) return $this->returnMsg("参数错误");
            if($field=='listorder' || $field=='state'){
                $value = intval($value);
            }
            if($Myobj->save([$field=>$value,'editor'=>$this->manUser['username']])){
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
                $rs = $parentid ? D::one("id = $parentid") : ['arrparentid'=>'','id'=>''];
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
        $id = is_array($id) ? $id : [$id];
        if(!$id) return $this->returnMsg('参数错误');
        $rs = D::whereIn('parentid', $id)->column("parentid");
        $id = $rs ? array_values(array_diff($id, $rs)) : $id;
        if(!$id) return $this->returnMsg('删除结构错误');
        D::destroy($id);
        D::cache(1);
        return $this->returnMsg("删除成功", 1);
    }

    /**
     * 获取字典集
     * @param  string  $code  字典编码
     * @return json
     */
    public function json(string $code = '')
    {
        $rs = D::cache();
        return $this->returnMsg($code && isset($rs[$code]) ? $rs[$code] : $rs);
    }

}
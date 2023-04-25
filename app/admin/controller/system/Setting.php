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
use app\model\system\Setting as S;

/**
 * 后台设置控制器
 */
class Setting extends AdminBase
{
    private $pname  = 'name/*/v';
    private $ptype  = 'type/*/v/配置类型';
    private $pgroup = 'group/?/v/配置组名';
    private $ptitle = 'title/*/{2,30}/配置标题';
    private $ptips  = 'tips/?/{2,100}/配置说明/0/,，';
    private $prelation = 'av/?/v/关联项';

    /**
     * 系统设置
     * @param  array   $do   异步数据
     * @return mixed
     */
    public function index($do='')
    {
        $groups = vconfig('sys_group',[]); reset($groups);
        if($do=='json'){
            $group = $this->request->get('group',key($groups));
            $where[] = ['state','=',1];
            $where[] = ['addon','=',''];
            if($group) $where[] = ['group','=',strip_sql($group)];
            $rs = (new S())->listArray($where,'name,title,value,type,options,private,relation,tips');
            foreach($rs as &$v){
                if($v['type']=='checkbox'){
                    $v['value'] = explode(',', $v['value']);
                }elseif($v['type'] == 'images'){
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
            'groups' => json_encode($groups)
        ]);
        return $this->fetch();
    }

    /**
     * 设置更新
     * @return json
     */
    public function edit()
    {
        $d = $this->request->post();
        $group = $d['__g'] ?? 'system';
        $where = [];
        $where[] = ['state','=',1];
        $where[] = ['addon','=',''];
        $where[] = ['group','=',strip_sql($group)];
        $rs = (new S())->listArray($where,'name,type,private');
        if($rs){
            unset($d['__g']);
            foreach ($rs as $v){
                $name = $v['name'];
                if(in_array($name, ['sys_group','sys_type'])) continue; //系统关键配置项不可修改 开发模式请注释该行
                if($v['type'] == 'checkbox'){
                    $data['value'] = isset($d[$name]) && is_array($d[$name]) ? implode(',', $setting[$name]) : '';
                }elseif($v['type'] == 'image'){
                    $data['value'] = isset($d[$name]) ? $d[$name] : '';
                }elseif($v['type'] == 'images'){
                    $data['value'] = isset($d[$name]) && is_array($d[$name]) ? json_encode($d[$name]) : '';
                }else{
                    $data['value'] = isset($d[$name]) ? $d[$name] : 0;
                    if($v['private'] && strpos($data['value'], '***') !== false) continue;
                }
                S::where("name='$name'")->update($data);
            }
            S::cache(1);
            return $this->returnMsg("设置成功", 1);
        }else{
            return $this->returnMsg('参数错误或未找到相关记录');
        }
    }

    /**
     * 系统配置构建列表
     * @param  string   $do   异步数据/操作
     * @return json
     */
    public function build($do='')
    {
        $groups = vconfig('sys_group');
        $types  = vconfig('sys_type');
        if($do=='json'){ //配置列表数据
            $where[] = ['addon','=',''];
            $group = $this->request->get('group',0);
            if(isset($groups[$group])) $where[] = ['group','=',$group];
            $rs = (new S())->listQuery($where);
            foreach($rs as $k=>$v){
                $rs[$k]['typename'] = isset($types[$v['type']]) ? $types[$v['type']] : '';
                if($v['private']){
                    $rs[$k]['value'] = half_replace($v['value']);
                }
            }
            return $this->returnMsg($rs);
        }elseif($do=='check'){ //检测配置名称是否被占用
            $d = $this->only(['id/d',$this->pname]);
            $id = $d['id'];
            $where[] = ['name','=',$d['name']];
            $where[] = ['addon','=',''];
            if($id) $where[] = ['id','<>',$id];
            $rs = S::get($where);
            $msg = $rs ? ['code'=>0,'msg'=>'配置名称【'.$d['name'].'】已被占用！'] : ['code'=>1,'msg'=>'可用'];
            return $this->returnMsg($msg);
        }
        //重构<配置类型>数据
        foreach($types as $k=>&$v){$v = $v.'：'.$k;}
        $this->assign([
            'limit' => 10,
            'datas' => json_encode(['groups'=>$groups,'types'=>$types]) //配置分组和配置类型
        ]);
        return $this->fetch();
    }

    /**
     * 配置项添加
     * @return json
     */
    public function badd()
    {
        $d = $this->only([$this->ptype,$this->pname,$this->ptitle,$this->pgroup,$this->ptips,'value/u','options/u','listorder/d']);
        if(S::get("name = '$d[name]' AND addon = ''")) return $this->returnMsg("该配置名称已经存在");
        $d["addtime"] = VT_TIME;
        if(S::insert($d)){
            S::cache(1);
            return $this->returnMsg("添加配置项成功", 1);
        }else{
            return $this->returnMsg('添加配置项失败');
        }
    }

    /**
     * 配置项编辑
     * @param  array   $do   快编操作
     * @return json
     */
    public function bedit($do='')
    {
        $d = $this->only($do ? ['id/d/ID参数错误','av','af'] : ['id/d/ID参数错误',$this->ptype,$this->pname,$this->ptitle,$this->pgroup,$this->ptips,'value/u','options/u','listorder/d']);
        $id = $d['id'];
        if(in_array($id, [1,2])) return $this->returnMsg("系统关键配置项不可修改");
        $Myobj = S::get("id = $id");
        if(!$Myobj) return $this->returnMsg("数据不存在");
        if($do=='up'){
            $value = $d['av'];
            $field = $d['af'];
            if(!in_array($field,['name','title','listorder','relation','private','state'])) return $this->returnMsg("参数错误");
            if($field=='name'){
                $this->only([str_replace('name','av',$this->pname)]);
                if(S::get("name = '$value' AND addon = '' AND id<>$id")) return $this->returnMsg("该配置名称已经存在");
            }elseif($field=='title'){
                $this->only([str_replace('title','av',$this->ptitle)]);
            }elseif($field=='relation'){
                $this->only([$this->prelation]);
            }else{
                $value = intval($value);
            }
            if($Myobj->save([$field=>$value])){
                S::cache(1);
                return $this->returnMsg("设置成功", 1);
            }else{
                return $this->returnMsg("设置失败");
            }
        }else{
            if(S::get("name = '$d[name]' AND addon = '' AND id<>$id")) return $this->returnMsg("该配置名称已经存在");
            if(strpos($d['value'], '***') !== false) unset($d['value']);
            $d["edittime"] = VT_TIME;
            if($Myobj->save($d)){
                S::cache(1);
                return $this->returnMsg("编辑成功", 1);
            }else{
                return $this->returnMsg("编辑失败");
            }
        }
    }

    /**
     * 配置项删除
     * @return json
     */
    public function bdel()
    {
        $id = $this->request->post('id','','intval');
        $id = is_array($id) ? implode(',',$id) : $id;
        if(!$id) return $this->returnMsg('参数错误');
        $ids = explode(',', $id);
        if(in_array(1,$ids) || in_array(2,$ids)) return $this->returnMsg("系统关键配置项不可删除");
        if(S::del("id IN($id)")){
            S::cache(1);
            return $this->returnMsg("删除成功", 1);
        }else{
            return $this->returnMsg("删除失败");
        }
    }

}
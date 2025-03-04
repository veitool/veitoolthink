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
use app\model\system\SystemSequence as MD;

/**
 * 单据前缀控制器
 */
class Sequence extends AdminBase
{
    /**
     * 单据前缀列表
     * @param  string   $do   异步数据
     * @return mixed
     */
    public function index(string $do = '')
    {
        if($do=='json'){
            return $this->returnMsg((new MD())->listQuery());
        }
        $this->assign([
            'limit'=>20,
        ]);
        return $this->fetch();
    }

    /**
     * 单据前缀添加
     * @return json
     */
    public function add()
    {
        $d = $this->only(['@token'=>'','name/h','code/h','prefix/h']);
        $d['addtime'] = time();
        $d['day']  = strtotime(date('Y-m-d'));
        $d['edit'] = $this->manUser['username'];
        if(MD::inadd($d)){
            return $this->returnMsg("添加成功", 1);
        }else{
            return $this->returnMsg('添加失败');
        }
    }

    /**
     * 单据前缀编辑
     * @param  string   $do   快编操作
     * @return json
     */
    public function edit(string $do = '')
    {
        $d = $this->only($do ? ['@token'=>'','id','av','af'] : ['@token'=>'','id','name/h','code/h','prefix/h']);
        $Myobj = MD::get("id = $d[id]");
        if(!$Myobj) return $this->returnMsg("数据不存在");
        if($do == 'up'){
            $value = $d['av'];
            $field = $d['af'];
            if(!in_array($field,['name'])) return $this->returnMsg("参数错误");
            if($field == 'name'){
                $value = $this->only(['av/h'])['av'];
            }
            return $this->returnMsg($Myobj->save([$field=>$value]) ? "设置成功" : '设置失败', 1);
        }else{
            $d['edit'] = $this->manUser['username'];
            if($Myobj->save($d)){
                return $this->returnMsg("编辑成功", 1);
            }else{
                return $this->returnMsg("编辑失败");
            }
        }
    }

    /**
     * 单据前缀删除
     * @return json
     */
    public function del()
    {
        $id = $this->only(['@token'=>'','id'])['id'];
        $id = is_array($id) ? implode(',',$id) : $id;
        if(!$id) return $this->returnMsg('参数错误');
        if(MD::del("id IN($id)")){
            return $this->returnMsg("删除成功", 1);
        }else{
            return $this->returnMsg("删除失败");
        }
    }

}
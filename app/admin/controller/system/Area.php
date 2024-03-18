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
use app\model\system\Area as A;

/**
 * 后台地区控制器
 */
class Area extends AdminBase
{
    /**
     * 地区列表
     * @param  int  $pid  主体视图上级ID
     * @return mixed
     */
    public function index($pid='')
    {
        $rs = A::all("parentid=".intval($pid),'*',['listorder'=>'asc']);
        if($pid==''){
            $this->assign('list', json_encode($rs));
            return $this->fetch();
        }else{
            if(isset($rs[0])){
                $r = A::where('areaid','in',$rs[0]['arrparentid'])->column('areaid,areaname');
                $rs[0]['arrparentid'] = '0|顶级';
                foreach ($r as $v){
                    $rs[0]['arrparentid'] .= ','.$v['areaid'].'|'.$v['areaname'];
                }
            }
            A::where('areaid',$pid)->update(['childs'=>count($rs)]);
            return $this->returnMsg($rs);
        }
    }

    /**
     * 地区添加
     * @return json
     */
    public function add()
    {
        $d = $this->only(['@token'=>'','parentid/d','listorder/d','areaname/*/{2,30}/地区名称']);
        $parentid  = $d['parentid'];
        $listorder = $d['listorder'];
        $arrparentid = 0;
        if($parentid>0){
            $rs = A::get(['areaid'=>$parentid]);
            if(is_null($rs)) return $this->returnMsg("上级地区ID不存在");
            $arrparentid = $rs['arrparentid'] ? $rs['arrparentid'].','.$rs['areaid'] : $rs['areaid'];
        }
        $data = [];
        $area = explode("\n",$d['areaname']);
        foreach($area as $v){
            $v = strip_html($v);
            if(!$v) continue;
            $data[] = ['areaname'=>$v,'parentid'=>$parentid,'listorder'=>$listorder,'arrparentid'=>$arrparentid];
            $listorder ++;
        }
        if(A::insertAll($data)){
            A::cache(1);
            return $this->returnMsg("添加地区成功", 1);
        }else{
            return $this->returnMsg('添加地区失败');
        }
    }

    /**
     * 地区编辑
     * @return json
     */
    public function edit()
    {
        $d = $this->only(['@token'=>'','areaid/d/参数错误','av','af']);
        $value = $d['av'];
        $field = $d['af'];
        if(!in_array($field,['areaname','listorder'])) return $this->returnMsg("参数错误2");
        if($field=='areaname'){
            $this->only(['av/*/{2,30}/地区名称']);
        }else{
            $value = intval($value);
        }
        if(A::update([$field=>$value,'areaid'=>$d['areaid']])){
            A::cache(1);
            return $this->returnMsg("设置成功", 1);
        }else{
            return $this->returnMsg("设置失败");
        }
    }

    /**
     * 数据导入
     * @return json
     */
    public function import()
    {
        if(A::get(1)){
            return $this->returnMsg("地区表非空，不可导入");
        }else{
            @set_time_limit(0);
            $file = VT_PUBLIC . 'install/data/area_data.sql';
            $prefix = config('database.connections.'.config('database.default').'.prefix');
            if(is_file($file)){
                $sql = explode("\n", trim(str_replace(["\r\n", "\r", "vt_"], ["\n", "\n", $prefix], file_get_contents($file))));
                foreach($sql as $v){
                    \think\facade\Db::execute($v);
                }
                A::cache(1);
                return $this->returnMsg("导入成功",1);
            }else{
                return $this->returnMsg("安装目录下无 data/area_data.sql 内置数据文件");
            }
        }
    }

    /**
     * 地区删除
     * @return json
     */
    public function del()
    {
        $areaid = $this->only(['@token'=>'','areaid'])['areaid'];
        $areaid = is_array($areaid) ? implode(',',$areaid) : $areaid;
        $rs = A::get("parentid IN ($areaid)");
        if($rs) return $this->returnMsg("该地区存在子地区不能删除！");
        if(A::del("areaid IN ($areaid)")){
            A::cache(1);
            return $this->returnMsg("删除成功", 1);
        }else{
            return $this->returnMsg("删除失败");
        }
    }

}
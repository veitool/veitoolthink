<?php
/**
 * ===========================================================================
 * Veitool 快捷开发框架系统
 * Author: Niaho 26843818@qq.com
 * Copyright (c)2019-2024 www.veitool.com All rights reserved.
 * Licensed: 这不是一个自由软件，不允许对程序代码以任何形式任何目的的再发行
 * ---------------------------------------------------------------------------
 */
namespace app\model\system;

use app\model\Base;

/**
 *【管理员模型】
 */
class Manager extends Base
{
    /**
     *定义主键
     * @var string 
     */
    protected $pk = 'userid';

    /**
     * 管理员列表（分页）
     * @param  array   $where    查询条件
     * @param  string  $fields   排除字段
     * @param  int     $limit    查询条数
     * @param  array   $order    查询排序
     * @return obj
     */
    public function listQuery($where = [], $fields = '', $limit = 0, $order = ['userid'=>'asc'])
    {
        $d = request()->get('','','strip_sql');
        $kw = $d['kw'] ?? '';
        $fds = ['username','truename','mobile','loginip'];
        $field = isset($d['fields']) && isset($fds[$d['fields']]) ? $d['fields'] : -1;
        $sotime  = $d['sotime'] ?? '';
        $roleid  = isset($d['roleid']) ? intval($d['roleid']) : 0;
        $areaid  = isset($d['areaid']) ? intval($d['areaid']) : 0;
        $groupid = $d['groupid'] ?? '';
        $state = $d['state'] ?? '';
        $limit = $limit>0 ? $limit : (isset($d['limit']) ? intval($d['limit']) : 10);
        if($kw!=''){
            if($field>-1){
                $where[] = [$fds[$field],'=',$kw];
            }else{
                $where[] = [implode('|',$fds),'LIKE', '%'.$kw.'%'];
            }
        }
        if(strpos($sotime,' - ')!==false){
            $t = explode(' - ',$sotime);
            $where[] = ['logintime','>=',strtotime($t[0]." 00:00:00")];
            $where[] = ['logintime','<=',strtotime($t[1]." 23:59:59")];
        }
        if($roleid) $where[] = ['roleid','=',$roleid];
        if($areaid) $where[] = [\think\facade\Db::raw("CONCAT(areaid,',')"), 'LIKE', $areaid.',%'];
        if(is_numeric($groupid)) $where[] = ['groupid','IN', Organ::getChild($groupid)];
        if(is_numeric($state))   $where[] = ['state','=',$state];
        return $this->where($where)->order($order)->withoutField($fields)->paginate($limit);
    }

}
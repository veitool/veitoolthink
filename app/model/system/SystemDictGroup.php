<?php
/**
 * ===========================================================================
 * Veitool 快捷开发框架系统
 * Author: Niaho 26843818@qq.com
 * Copyright (c)2019-2026 www.veitool.com All rights reserved.
 * Licensed: 这不是一个自由软件，不允许对程序代码以任何形式任何目的的再发行
 * ---------------------------------------------------------------------------
 */
namespace app\model\system;

use app\model\Base;

/**
 *【字典组模型】
 */
class SystemDictGroup extends Base
{
    /**
     * 启用软删除操作
     */
    use \think\model\concern\SoftDelete; /**/

    /**
     *定义主键
     * @var string 
     */
    protected $pk = 'id';

    /**
     * 字典组列表（分页）
     * @param  array          $where    查询条件
     * @param  string         $fields   排除字段
     * @param  int            $limit    查询条数
     * @param  array/string   $order    查询排序
     * @return obj
     */
    public function listQuery(array $where = [], string $fields = '', int $limit = 0, array|string $order = ['id'=>'asc'])
    {
        $d = request()->get('','','strip_sql');
        $kw = $d['kw'] ?? '';
        $fds = ['title','code','sql','editor','note'];
        $field = isset($d['fields']) && isset($fds[$d['fields']]) ? $d['fields'] : -1;
        $sotime  = $d['sotime'] ?? '';
        $groupid = $d['groupid'] ?? '';
        $limit = $limit>0 ? $limit : (isset($d['limit']) ? intval($d['limit']) : 10);
        $where[] = ['groupid','>',0];
        if($kw!=''){
            if($field>-1){
                $where[] = [$fds[$field],'=',$kw];
            }else{
                $where[] = [implode('|',$fds),'LIKE', '%'.$kw.'%'];
            }
        }
        if(strpos($sotime,' - ')!==false){
            $t = explode(' - ',$sotime);
            $where[] = ['add_time','>=',strtotime($t[0]." 00:00:00")];
            $where[] = ['add_time','<=',strtotime($t[1]." 23:59:59")];
        }
        if(is_numeric($groupid)) $where[] = ['groupid','IN', SystemDictGroup::getChild($groupid)];
        return $this->where($where)->order($order)->withoutField($fields)->paginate($limit);
    }

    /**
     * 获取所有子类ID串
     * @param  int   $id   ID
     * @return string
     */
    public static function getChild(int $id = 0)
    {
        $id = abs($id);
        if($id>0){
            $rs = self::where("groupid = 0 AND (id = $id OR FIND_IN_SET($id,arrparentid))")->column('id');
            $id = $rs ? implode(',', $rs) : '';
        }
        return $id;
    }

}
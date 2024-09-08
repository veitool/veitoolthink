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
 *【短信模型】
 */
class SystemSms extends Base
{
    /**
     *定义主键
     * @var string 
     */
    protected $pk = 'itemid';

    /**
     * 短信记录（分页）
     * @param  array          $where    条件
     * @param  array/string   $order    排序
     * @param  string         $fields   字段
     * @param  int            $limit    条数
     * @return obj
     */
    public function listQuery(array $where = [], array|string $order = ['itemid'=>'desc'], string $fields = '*', int $limit = 0)
    {
        $d = request()->get('','','strip_sql');
        $kw = $d['kw'] ?? '';
        $fds = ['mobile','message','editor','code'];
        $field = isset($d['fields']) && isset($fds[$d['fields']]) ? $d['fields'] : -1;
        $sotime = $d['sotime'] ?? '';
        $limit = $limit>0 ? $limit : (isset($d['limit']) ? intval($d['limit']) : 10);
        if($kw!=''){
            if($field>-1){
                $where[] = $field==1 ? [$fds[$field],'LIKE', '%'.$kw.'%'] : [$fds[$field],'=',$kw];
            }else{
                $where[] = [implode('|',$fds),'LIKE', '%'.$kw.'%'];
            }
        }
        if(strpos($sotime,' - ')!==false){
            $t = explode(' - ',$sotime);
            $where[] = ['sendtime','>=',strtotime($t[0]." 00:00:00")];
            $where[] = ['sendtime','<=',strtotime($t[1]." 23:59:59")];
        }
        return $this->where($where)->order($order)->field($fields)->paginate($limit);
    }

}
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
 *【单据前缀模型】
 */
class SystemSequence extends Base
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
     * 单据前缀列表（分页）
     * @param  array   $where    查询条件
     * @param  string  $fields   查询字段
     * @param  array   $order    默认排序
     * @param  int     $limit    每页条数
     * @return obj
     */
    public function listQuery(array $where = [], string $fields = '*', array|string $order = [], int $limit = 0)
    {
        $d = request()->get('','','strip_sql');
        $limit = $limit > 0 ? $limit : (isset($d['limit']) ? intval($d['limit']) : 10);
        return $this->where($where)->field($fields)->order($order)->paginate($limit);
    }

}
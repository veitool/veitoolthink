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
 *【上传文件记录模型】
 */
class SystemUploadFile extends Base
{
    /**
     * 定义主键
     * @var string 
     */
    protected $pk = 'fileid';

    /**
     * 获取上传文件记录(分页)
     * @param  string/array   $where    条件
     * @param  string/array   $order    排序
     * @param  string         $field    字段
     * @return obj
     */
    public function listQuery(string|array $where = '', string|array $order = ['fileid'=>'desc'], string $field = '*')
    {
        $d = request()->param();
        $limit = isset($d['limit']) ? intval($d['limit']) : (isset($d['size']) ? intval($d['size']) : 10); // 兼容百度编辑器附件列表
        return $this->where($where)->order($order)->field($field)->paginate($limit);
    }

}
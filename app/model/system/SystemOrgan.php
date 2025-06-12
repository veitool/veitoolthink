<?php
/**
 * ===========================================================================
 * Veitool 快捷开发框架系统
 * Author: Niaho 26843818@qq.com
 * Copyright (c)2019-2025 www.veitool.com All rights reserved.
 * Licensed: 这不是一个自由软件，不允许对程序代码以任何形式任何目的的再发行
 * ---------------------------------------------------------------------------
 */
namespace app\model\system;

use app\model\Base;

/**
 *【组织机构模型】
 */
class SystemOrgan extends Base
{
    /**
     * 启用软删除操作
     */
    use \think\model\concern\SoftDelete; /**/

    /**
     * 获取所有子类ID串
     * @param  int   $id   ID
     * @return string
     */
    public static function getChild(int $id = 0)
    {
        $id = abs($id);
        if($id>0){
            $rs = self::where("FIND_IN_SET($id,arrparentid)")->column('id');
            $rs[] = $id;
            $id = implode(',', $rs);
        }
        return $id;
    }

}
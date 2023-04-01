<?php
/**
 * ===========================================================================
 * Veitool 快捷开发框架系统
 * Author: Niaho 26843818@qq.com
 * Copyright (c)2019-2023 www.veitool.com All rights reserved.
 * Licensed: 这不是一个自由软件，不允许对程序代码以任何形式任何目的的再发行
 * ---------------------------------------------------------------------------
 */
namespace app\api\controller;

use app\BaseController;
use app\model\system\Area as A;

/**
 * 地区信息控制器
 */
class Area extends BaseController
{
    /**
     * 获取地区信息
     * @return json
     */
    public function index()
    {
        $k = 0;
        $arr = [];
        $pid = $this->request->param('pid/d',0);
        $rs = A::cache();
        foreach ($rs as $v){
            if($v['parentid'] == $pid){
                $arr[$k]['value'] = $v['areaid'];
                $arr[$k]['label'] = $v['areaname'];
                if($v['childs']>0) $arr[$k]['haveChildren'] = true;
                $k++;
            }
        }
        return $this->returnMsg($arr);
    }

}
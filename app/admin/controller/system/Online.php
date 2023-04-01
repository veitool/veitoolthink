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
use app\model\system\Online as OL;

/**
 * 在线用户制器
 */
class Online extends AdminBase
{
    /**
     * 登录日志
     * @param  array   $do   异步数据
     * @return mixed
     */
    public function index($do='')
    {
        if($do=='json'){
            return $this->returnMsg((new OL())->listQuery());
        }
        $this->assign([
            'limit' => 10
        ]);
        return $this->fetch();
    }

}
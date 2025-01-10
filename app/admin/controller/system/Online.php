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
use app\model\system\SystemOnline as OL;

/**
 * 在线用户制器
 */
class Online extends AdminBase
{
    /**
     * 在线日志
     * @param  string   $do   异步数据
     * @return mixed
     */
    public function index(string $do = '')
    {
        if($do=='json'){
            $list = (new OL())->listQuery();
            if($this->manUser['userid']>1){
                foreach($list['data'] as &$v){
                    $ip = explode('.', $v['ip']);
                    $v['ip'] = $ip[0].'. *** .'.$ip[3];
                }
            }
            return $this->returnMsg($list);
        }
        $this->assign([
            'limit' => 10
        ]);
        return $this->fetch();
    }

}
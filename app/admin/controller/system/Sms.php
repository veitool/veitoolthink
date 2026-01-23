<?php
/**
 * ===========================================================================
 * Veitool 快捷开发框架系统
 * Author: Niaho 26843818@qq.com
 * Copyright (c)2019-2026 www.veitool.com All rights reserved.
 * Licensed: 这不是一个自由软件，不允许对程序代码以任何形式任何目的的再发行
 * ---------------------------------------------------------------------------
 */
namespace app\admin\controller\system;

use app\admin\controller\AdminBase;
use app\model\system\SystemSms as S;
use tool\SendSms;

/**
 * 短信控制器
 */
class Sms extends AdminBase
{
    /**
     * 登录日志
     * @param  string  $do  异步数据
     * @return mixed
     */
    public function index(string $do = '')
    {
        if($do=='json'){
            return $this->returnMsg((new S())->listQuery());
        }
        $this->assign('limit', 10);
        return $this->fetch();
    }

    /**
     * 发送短信
     * @return json
     */
    public function send()
    {
        $d = $this->only(['@token'=>'','mobile/*/m','message/h']);
        $SMS  = new SendSms();
        $method = vconfig('sms_type').'_send';
        if(method_exists($SMS, $method)){
            return $this->returnMsg($SMS->$method($d['mobile'], $d['message']));
        }else{
            return $this->returnMsg('短信发送接口类型不存在');
        }
    }

    /**
     * 删除短信记录
     * @return json
     */
    public function del()
    {
        $id = $this->only(['@token'=>'','id'])['id'];
        $id = is_array($id) ? $id : [$id];
        S::destroy($id);
        return $this->returnMsg("删除成功", 1);
    }

}
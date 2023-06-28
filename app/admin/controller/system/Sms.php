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
use app\model\system\Sms as S;
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
    public function index($do='')
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
        if(vconfig('sms_type')=='smsbao'){
            return $this->returnMsg($SMS->smsbao_send($d['mobile'], $d['message']));
        }else{
            return $this->returnMsg($SMS->qiniu_send([$d['mobile']], $d['message']));
        }
    }

    /**
     * 删除短信记录
     * @return json
     */
    public function del()
    {
        $itemid = $this->only(['@token'=>'','itemid'])['itemid'];
        $itemid = is_array($itemid) ? implode(',',$itemid) : $itemid;
        if(S::del("itemid IN($itemid)")){
            return $this->returnMsg("删除成功", 1);
        }else{
            return $this->returnMsg("删除失败");
        }
    }

}
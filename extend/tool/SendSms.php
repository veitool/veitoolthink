<?php
/**
 * ===========================================================================
 * Veitool 快捷开发框架系统
 * Author: Niaho 26843818@qq.com
 * Copyright (c)2019-2023 www.veitool.com All rights reserved.
 * Licensed: 这不是一个自由软件，不允许对程序代码以任何形式任何目的的再发行
 * ---------------------------------------------------------------------------
 */
namespace tool;

use think\facade\Db;

/**
 * 短信发送类
 */
class SendSms
{
    /**
     * 短信发送时间缓存
     * @var strimg 
     */
    private $cache_key = '';

    /**
     * 短信接口配置
     * sms_url   接口地址
     * sms_user  接口帐号
     * sms_pass  帐号密码
     * sms_temp  短信模板号
     */
    private $config = array(
        'sms_state' => 0,
        'sms_url'   => '',
        'sms_user'  => '',
        'sms_pass'  => '',
        'sms_temp'  => 0
    );

    /**
     * 构造函数初始化
     */
    public function __construct()
    {
        $this->cache_key = 'sms_'. request()->ip();
    }

    /**
    * 清除发送时间缓存
    * @access  public
    */
    public function clear_time_cache()
    {
        cache($this->cache_key,null);
    }

    /**
     * 七牛发送短信
     * @access  public
     * @param   array       $mobile        手机号
     * @param   string      $message       短信内容
     * @param   int         $tpid          短信模板ID
     * @param   string      $tips          提示
     * @param   int         $lent          间隔时间(秒)
     * @return  array
     */
    public function qiniu_send(array $mobile = [], string $message = '', int $tpid = 0, string $tips = '', int $lent = 0)
    {
        $time = time();
        //屏蔽频繁发送
        $arr = cache($this->cache_key);
        $lent = $lent ? $lent : vconfig('sms_times');
        if(isset($arr['time']) && ($time-$arr['time'])<$lent) return ['msg'=>'发送过于频繁！','code'=>0];
        //整合配置
        $this->config = array_merge($this->config, vconfig());
        if(!$this->config['sms_state']) return ['msg'=>'短信接口未开启','code'=>0];
        if(!$mobile) return ['msg'=>'手机号不能为空','code'=>0];
        foreach($mobile as $v){
            if(!is_preg($v,'mobile')) return ['msg'=>'手机号不正确','code'=>0];
        }
        if(!$message) return ['msg'=>'短信内容不能为空','code'=>0];
        $auth = new \Qiniu\Auth($this->config['sms_user'], $this->config['sms_pass']);
        $client = new \Qiniu\Sms\Sms($auth);
        $client->queryTemplate();
        //发送信息模块
        $template_id = $tpid ? $tpid : $this->config['sms_temp'];
        $mobiles     = $mobile;
        $code        = array('code' => $message);
        try{
            //发送短信
            list($ret,$err) = $client->sendMessage($template_id, $mobiles, $code);
            if($err !== null){
                $txt = '失败，请检查短信帐号或密码是否正确！';$code = 0;
                $this->clear_time_cache();
            }else{
                $txt = '成功';$code = 1;
                //记入发送成功时间缓存，防止下次频繁发送
                cache($this->cache_key,['time'=>$time]);
            }
        }catch(\Exception $e){
            $txt = '失败';$code = 0;
            $this->clear_time_cache();
        }
        $word = function_exists('mb_strlen') ? mb_strlen($message,'utf8') : 0;
        $data = ['mobile'=> implode(',',$mobile),'message'=>$tips.$message,'word'=>$word,'editor'=>'system','sendtime'=>$time,'code'=>$txt];
        Db::name('system_sms')->data($data)->insert();
        return ['msg'=>'发送'.$txt,'code'=>$code];
    }

    /**
     * 短信宝发送短信
     * @access  public
     * @param   string    $mobile     手机号
     * @param   string    $message    短信内容
     * @param   int       $word       发送的字数
     * @param   int       $lent       间隔时间(秒)
     * @return  array
     */
    public function smsbao_send(string $mobile = '', string $message = '', int $word = 0, int $lent = 0)
    {
        $time = time();
        //屏蔽频繁发送
        $arr = cache($this->cache_key);
        $lent = $lent ? $lent : vconfig('sms_times');
        if(isset($arr['time']) && ($time-$arr['time'])<$lent) return ['msg'=>'发送过于频繁！','code'=>0];
        //整合配置
        $this->config = array_merge($this->config, vconfig());
        if(!$this->config['sms_state']) return ['msg'=>'短信接口未开启','code'=>0];
        if(!$mobile) return ['msg'=>'手机号不能为空','code'=>0];
        $m = explode(',', $mobile);
        foreach($m as $v){
            if(!is_preg($v,'mobile')) return ['msg'=>'手机号不正确','code'=>0];
        }
        if(!$message) return ['msg'=>'短信内容不能为空','code'=>0];
        $message = vconfig('sms_pre','【微特】').$message;
        //短信内容处理
        $word or $word = function_exists('mb_strlen') ? mb_strlen($message,'utf8') : 0;
        $sms_message = rawurlencode($message);
        $arr = ['{s_user}','{s_pass}','{s_mobile}','{s_message}'];
        $brr = [$this->config['sms_baouser'], md5($this->config['sms_baopass']),$mobile,$sms_message];
        $url = str_replace($arr, $brr, 'http://www.smsbao.com/sms?u={s_user}&p={s_pass}&m={s_mobile}&c={s_message}');
        $fp  = fopen($url, 'r');
        $key = '';
        if($fp){
            while(!feof($fp)){$key .= fgets($fp)."";}
            fclose($fp);
        }else{
            $this->clear_time_cache();
            return ['msg'=>'打开接口失败','code'=>0];
        }
        $key = intval(trim($key));
        $keys = array(
            '0'  => '成功',
            '30' => '密码错误',
            '40' => '账号不存在',
            '41' => '余额不足',
            '42' => '帐号过期',
            '43' => 'IP地址限制',
            '50' => '内容含有敏感词',
            '51' => '手机号码不正确'
        );
        $txt = $keys[$key];
        $data = ['mobile'=>$mobile,'message'=>$message,'word'=>$word,'editor'=>'system','sendtime'=>$time,'code'=>$txt];
        Db::name('system_sms')->data($data)->insert();
        //记入发送成功时间缓存，防止下次频繁发送
        cache($this->cache_key,['time'=>$time]);
        return ['msg'=>$txt,'code'=>($key==0 ? 1 : 0)];
    }

}
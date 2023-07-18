<?php
/**
 * ===========================================================================
 * Veitool 快捷开发框架系统
 * Author: Niaho 26843818@qq.com
 * Copyright (c)2019-2023 www.veitool.com All rights reserved.
 * Licensed: 这不是一个自由软件，不允许对程序代码以任何形式任何目的的再发行
 * ---------------------------------------------------------------------------
 */
declare (strict_types=1);
namespace app;

use think\App;
use think\Response;
use think\facade\View;
use think\exception\HttpResponseException;

/**
 * 控制器抽象基类
 */
abstract class BaseController
{
    /**
     * 应用实例
     * @var App
     */
    protected $app;

    /**
     * Request实例
     * @var \think\Request
     */
    protected $request;

    /**
     * Token
     * @var string
     */
    protected $token = '';

    /**
     * Token 名
     * @var string
     */
    protected $tokenName = '__token__';

    /**
     * 信息模板
     * @var string
     */
    protected $msgTpl = '';

    /**
     * 控制器中间件
     * @var array
     */
    protected $middleware = [];

    /**
     * 前台会员信息
     * @var array
     */
    protected $memUser = [];

    /**
     * 构造方法
     * @param App $app
     */
    public function __construct(App $app)
    {
        // 应用实例
        $this->app = $app;
        // 请求对象
        $this->request = $this->app->request;
        // 映射路径
        defined('APP_MAP') or define('APP_MAP', $this->request->root());
        // 前台集中业务
        $this->__home();
        // 验证（登录、权限）
        $this->__auth();
        // 控制器初始化
        $this->__init();
    }

    /**
     * 前台集中业务
     */
    protected function __home()
    {
        // 前台统一开关 需后台配置参数 开关类型:site_close 和 文本域类型:site_close_tip
        if(vconfig('site_close')) $this->exitMsg(vconfig('site_close_tip','系统升级维护中，请稍后访问！'),400);
        // 获取会员信息
        $this->memUser = session(VT_MEMBER);
    }

    /**
     * 验证（登录、权限）
     */ 
    protected function __auth(){}

    /**
     * 初始化
     */ 
    protected function __init(){}

    /**
     * 空方法
     */ 
    public function __call($name, $arg)
    {
        $this->exitMsg('Method does not exist',400);
    }

    /**
     * 日志/在线处理
     * @access  protected
     * @param   sting   $tip   提示
     */
    protected function logon(string $tip = ''){
        $flag1 = vconfig('home_log',0);
        $flag2 = in_array(vconfig('online_on',0),[2,3]);
        if($flag1 || $flag2) $url = substr(vhtmlspecialchars(strip_sql($this->request->url())),0,200);
        /*访问日志*/
        if($flag1){
            \app\model\system\WebLog::add(['url'=>$url,'username'=>$this->memUser['username'] ?? '','ip'=>VT_IP]);
        }/**/
        /*在线统计【0:关闭全部 1:开启后台 2:开启会员 3:开启全部】模型中支持 replace 为 create 的第3个参数设为 true 或者 \think\facade\Db::name('online')->replace()->insert([数据集])*/
        if($flag2){
            $Online = $this->memUser ? ['userid'=>'m'.$this->memUser['userid'],'username'=>$this->memUser['username']] : session('VT_ONLINE');
            if(!$Online){
                $Online = ['userid'=>uniqid(),'username'=>'游客'];
                session('VT_ONLINE',$Online);
            }
            \app\model\system\Online::create(['userid'=>$Online['userid'],'username'=>$Online['username'],'url'=>$url,'etime'=>VT_TIME,'ip'=>VT_IP,'type'=>1],['userid','username','url','etime','ip','type'],true);
        }/**/
    }

    /**
     * 模板赋值
     * @access  protected
     * @param   sting/array  $vars  赋值表达式/数组
     */
    protected function assign(...$vars)
    {
        View::assign(...$vars);
    }

    /**
     * 模板渲染
     * @access  protected
     * @param   string  $tmp   模板名称
     * @param   sting   $tip   提示
     */
    protected function fetch(string $tmp = '', string $tip = '')
    {
        $this->logon($tip);
        return View::fetch($tmp);
    }

    /**
     * 重定向
     * @access  protected
     * @param   string  $args  重定向地址
     * @throws  HttpResponseException
     */
    protected function redirect(...$args)
    {
        throw new HttpResponseException(redirect(...$args));
    }

    /**
     * 中断反馈信息
     * @access  protected
     * @param   string   $m   信息字符
     * @param   int      $c   状态值 400前台关闭 401 Ajax请求未登陆 303网址请求未登录
     * @param   array    $d   数组信息
     * @param   array    $h   发送的Header信息
     * @throws  HttpResponseException
     */
    protected function exitMsg($m, $c = 0, $d = [], $h = [])
    {
        if($c==400){
            $re = Response::create(ROOT_PATH . 'app/v_msg.tpl','view')->assign(['msg'=>$m,'site'=>vconfig('site_title')])->header($h);
        }else if($c==303){
            $re = Response::create(ROOT_PATH . 'app/v_msg.tpl','view')->assign(['msg'=>$m,'site'=>vconfig('site_title'),'url'=>$d['url']])->header($h);
        }else{
            $rs = json_encode(['code'=>$c,'msg'=>$m,'data'=>$d,'token'=>$this->token]);
            $re = Response::create($rs)->header($h);
        }
        throw new HttpResponseException($re);
    }

    /**
     * 返回组信息
     * @access  protected
     * @param   string/array/obj    $msg      信息字符
     * @param   int                 $code     状态码
     * @param   array               $data     数组信息
     * @param   int                 $scode    页头状态码
     * @param   array               $header   头部
     * @param   array               $options  参数
     * @return  array/json
     */
    protected function returnMsg($msg = '', $code = 0, $data = [], $scode = 200, $header = [], $options = [])
    {
        $msg = is_object($msg) ? $msg->toArray() : $msg;
        if(is_array($msg)){
            if(isset($msg['total'])){ //分页模式
                $data  = $msg['data'];
                $count = $msg['total'];
                $data['msg'] = $msg['msg'] ?? '';
            }else{
                $data = $msg;
            }
            $msg  = $data['msg'] ?? ''; unset($data['msg']);
            $code = $data['code'] ?? $code; unset($data['code']);
            $data = $data['data'] ?? $data;
        }else{
            $this->logon($msg);
        }
        $token = $this->token;
        $count = isset($count) ? $count : (is_array($data) ? count($data) : 1);
        if($this->msgTpl){
            $this->assign(compact('code', 'msg', 'data', 'count', 'token'));
            return $this->fetch($this->msgTpl);
        }else{
            return json(compact('code', 'msg', 'data', 'count', 'token'), $scode, $header, $options);
        }
    }

    /**
     * 带模板反馈提示
     * @access   protected
     * @param    string    $msg    提示信息
     * @param    int       $tpl    提示模板
     * @param    string    $url    跳转的地址
     */
    protected function returnTpl($msg = '', $tpl = '', $url = '')
    {
        $tpl = $tpl ?: ($this->request->isMobile() ? 'err' : ROOT_PATH . 'app/v_msg.tpl');
        $this->assign(['msg'=>$msg,'url'=>$url]);
        return $this->fetch($tpl);
    }

    /**
     * 获取指定的参数 过滤方法后执行【key / *或?表非空时验证或$表非空时验证不规范则置空不中断 / 规则(e邮箱m手机c身份证p密码u帐号n姓名i数串a插件名v配置名)或位数范围如{1,3} / 提示(传入优先) / 合法的字符集0,1..串 默认0:字母数字汉字下划线 1:数字 2:小写字母 3:大写字母 4:汉字 5:任何非空白字符 / 允许的字符】
     * @access protected
     * @param  array         $name    变量名 /a转数组 /d整数 /f浮点 /b布尔 /s字符串 /u网址净化 /h全净化去标签 /c转为HTML实体 /r转为2位小数 /*验证【默认允许：汉字|字母|数字|下划线|空格.#-】
     * @param  mixed         $type    方法类型 默认 post
     * @param  string|array  $filter  过滤方法 默认 strip_sql
     * @param  bool          $bin     是否以传入数组为准 默认是
     * @return array
     */
    protected function only($name = [], $type = 'post', $filter = 'strip_sql', $bin = true)
    {
        if(isset($name['@token'])){
            $arr = array_merge([$this->tokenName,[]],(array)$name['@token']);
            if($this->request->checkToken($arr[0],$arr[1]) === false) return $this->exitMsg("Token错误");
            $this->token = token($this->tokenName);
            unset($name['@token']);
            if(!$name) return [];
        }
        $item = [];
        $data = $this->request->$type(false);
        $preg = [
            'e'=>[2=>'email',3=>'邮箱地址格式错误',4=>'',5=>''],
            'm'=>[2=>'mobile',3=>'手机号码格式错误',4=>'',5=>''],
            'c'=>[2=>'idcard',3=>'身份证号格式错误',4=>'',5=>''],
            'p'=>[2=>'{6,16}',3=>'密码',4=>'5',5=>''],
            'u'=>[2=>'{4,30}',3=>'帐号',4=>'1,2,3',5=>'._@'],
            'n'=>[2=>'{2,30}',3=>'姓名',4=>'0',5=>' .'],
            'i'=>[2=>'{1,30}',3=>'数串',4=>'1',5=>','],
            'a'=>[2=>'{3,20}',3=>'插件名',4=>'1,2',5=>''],
            'v'=>[2=>'{2,20}',3=>'配置名',4=>'1,2,3',5=>'_']
        ];
        foreach($name as $key => $val){
            $default = '';
            $sub = ['','','','','0',' .#-']; // 对应['key','转换类型|验证符*或?','验证规则','提示','合法的字符集','允许的字符']
            if(strpos($val, '/')){
                $sub = explode('/', $val) + $sub;
                $val = $sub[0];
            }
            if(is_int($key)){
                $key = $val;
                if(!key_exists($key,$data) && !$sub[1]){
                    $item[$key] = $default;
                    continue;
                }
            }else{
                $default = $val;
            }
            $v = $data[$key] ?? $default;
            if($sub[1]){
                $must = $msg = true; // $must:是否必须验证  $msg:是否验证不规范时中断反馈提示
                if(in_array($sub[1],['?','$'])){$must = $v ? true : false; if($sub[1] == '$') $msg = false; $sub[1] = '*';}
                switch($sub[1]){
                    case 'a':
                        $v = $v ? (array) $v : [];
                        break;
                    case 'd':
                        $v = (int) $v;
                        break;
                    case 'u':
                        $v = strip_html($v,0);
                        break;
                    case 'h':
                        $v = strip_html($v);
                        break;
                    case 'c':
                        $v = vhtmlspecialchars($v);
                        break;
                    case 'r':
                        $v = dround($v);
                        break;
                    case '*':
                        if($sub[2]=='p') $must = is_md5($v) ? false : $must;
                        $tip = $sub[3]; if(isset($preg[$sub[2]])){$sub = $preg[$sub[2]] + $sub; $tip = $tip ?: $sub[3];}
                        $reg = explode(',',$sub[4]);
                        if($must && !is_preg($v,$sub[2],$reg,$sub[5])){
                            if($msg){
                                $tip = $tip ?: "字段{$key}不合规范"; $txt = ['汉字字母数字下划线','数字','小写字母','大写字母','汉字','任何非空白字符'];
                                if($reg[0]!==''){
                                    $str = ''; foreach($reg as $i){$str .= ($txt[$i] ?? '').'、';}
                                    $tip = $tip.'必须由'.(str_replace(['{',',','}'],['','-',''],$sub[2])).'位'.rtrim($str,'、').($sub[5] ? '和'.str_replace(' ', '空格', $sub[5]) : '').'组成';
                                }
                                $this->exitMsg($tip);
                            }else{
                                $v = '';
                            }
                        }
                        break;
                    case 'f':
                        $v = (float) $v;
                        break;
                    case 'b':
                        $v = (boolean) $v;
                        break;
                    case 's':
                        if(is_scalar($v)){
                            $v = (string) $v;
                        }else{
                            throw new \InvalidArgumentException('variable type error：' . gettype($v));
                        }
                        break;
                }
                if($sub[1] != '*' && $sub[2] && !$v)  $this->exitMsg($sub[2]);
            }
            $item[$key] = call_user_func($filter, $v);
        }
        return $bin ? $item : $item + $data;
    }

}
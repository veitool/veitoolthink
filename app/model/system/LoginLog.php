<?php
/**
 * ===========================================================================
 * Veitool 快捷开发框架系统
 * Author: Niaho 26843818@qq.com
 * Copyright (c)2019-2023 www.veitool.com All rights reserved.
 * Licensed: 这不是一个自由软件，不允许对程序代码以任何形式任何目的的再发行
 * ---------------------------------------------------------------------------
 */
namespace app\model\system;

use app\model\Base;

/**
 *【登录日志】
 */
class LoginLog extends Base
{
    /**
     *定义主键
     * @var string 
     */
    protected $pk = 'logid';

    /**
     * 获取日志（分页）
     * @param  array   $where    条件
     * @param  array   $order    排序
     * @param  string  $fields   字段
     * @param  int     $limit    条数
     * @return array
     */
    public function listQuery($where=[], $order=['logid'=>'desc'], $fields = '*', $limit=0)
    {
        $d = request()->get('','','strip_sql');
        $kw = $d['kw'] ?? '';
        $fds = ['username','loginip','password','agent'];
        $field = isset($d['fields']) && isset($fds[$d['fields']]) ? $d['fields'] : -1;
        $sotime = $d['sotime'] ?? '';
        $admin = $d['admin'] ?? '';
        $message = $d['message'] ?? '';
        $limit = $limit>0 ? $limit : (isset($d['limit']) ? intval($d['limit']) : 10);
        if($kw!=''){
            if($field>-1){
                $where[] = $field>1 ? [$fds[$field],'LIKE', '%'.$kw.'%'] : [$fds[$field],'=',$kw];
            }else{
                $where[] = [implode('|',$fds),'LIKE', '%'.$kw.'%'];
            }
        }
        if(strpos($sotime,' - ')!==false){
            $t = explode(' - ',$sotime);
            $where[] = ['logintime','>=',strtotime($t[0]." 00:00:00")];
            $where[] = ['logintime','<=',strtotime($t[1]." 23:59:59")];
        }
        if(is_numeric($admin)) $where[] = ['admin','=',$admin];
        if($message) $where[] = ['message','LIKE','%'.$message.'%'];
        return $this->where($where)->order($order)->field($fields)->paginate($limit);
    }

    /**
     * 创建登录日志
     * @param   string      $u     帐号
     * @param   string      $p     密码
     * @param   string      $s     秘钥
     * @param   string      $m     提示
     * @param   int         $h     类型 0后台 1会员 2门店 3终端
     * @return  mixed
     */
    public static function add($u, $p, $s, $m='成功', $h=0)
    {
        $p = set_password($p, $s);
        $a = substr(addslashes(vhtmlspecialchars(strip_sql($_SERVER['HTTP_USER_AGENT']))),0,200);
        $d = ['username' => $u, 'password' => $p, 'passsalt' => $s, 'admin' => $h, 'loginip' => VT_IP, 'logintime' => VT_TIME, 'message' => $m, 'agent' => $a];
        return self::create($d);
    }

}
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
 *【在线记录模型】
 */
class SystemOnline extends Base
{
    /**
     *定义主键
     * @var string 
     */
    protected $pk = 'userid';

    /**
     * 列表（分页）
     * @param  array          $where    条件
     * @param  array/string   $order    排序
     * @param  string         $fields   字段
     * @param  int            $limit    条数
     * @return array
     */
    public function listQuery(array $where = [], array|string $order = ['last_time'=>'desc'], string $fields = '*', int $limit = 0)
    {
        $d = request()->get('','','strip_sql');
        $kw = $d['kw'] ?? '';
        $fds = ['username','url','ip'];
        $field  = isset($d['fields']) && isset($fds[$d['fields']]) ? $d['fields'] : -1;
        $sotime = $d['sotime'] ?? '';
        $type   = $d['type'] ?? '';
        $limit  = $limit>0 ? $limit : (isset($d['limit']) ? intval($d['limit']) : 10);
        if($kw!=''){
            if($field>-1){
                $where[] = $field==1 ? [$fds[$field],'LIKE', '%'.$kw.'%'] : [$fds[$field],'=',$kw];
            }else{
                $where[] = [implode('|',$fds),'LIKE', '%'.$kw.'%'];
            }
        }
        if(strpos($sotime,' - ')!==false){
            $t = explode(' - ',$sotime);
            $where[] = ['last_time','>=',strtotime($t[0]." 00:00:00")];
            $where[] = ['last_time','<=',strtotime($t[1]." 23:59:59")];
        }
        if(is_numeric($type)) $where[] = ['type','=',$type];
        //统计以及清除处理
        $msg = '';
        $page = $d['page'] ?? 1;
        if($page==1){
            //5分钟未活动的删除
            \think\facade\Db::name('system_online')->where('last_time', '<', time() - 300)->delete();
            //$this->where('last_time', '<', time() - 3000)->delete();
        }
        $rs = $this->where($where)->order($order)->field($fields)->paginate($limit)->toArray();
        $rs['msg'] = $rs['total'];
        return $rs;
    }
    
    /**
     * 记录在线数据
     * @param  array   $user  用户session信息
     * @param  string  $url   在线地址
     * @param  int     $type  在线类型默认1前台 0后台 
     */
    public static function recod(array $user, string $url = '', int $type = 1)
    {
        if($Online = $user){
            if($yk = session(VT_VISITOR)){ //删除登录前的游客在线
                session(VT_VISITOR,null);
                self::del(['uid'=>$yk['uid'],'userid'=>$yk['userid']]);
            }
        }elseif(!$Online = session(VT_VISITOR)){
            $uid = uniqid();
            $Online = ['uid'=>'YK-'.$uid,'userid'=>$uid,'username'=>'游客'];
            session(VT_VISITOR,$Online);
        }
        // 模型中支持 replace 为 create 的第3个参数设为 true 或者 \think\facade\Db::name('online')->replace()->insert([数据集])
        self::create(['uid'=>$Online['uid'],'userid'=>$Online['userid'],'username'=>$Online['username'],'url'=>$url,'last_time'=>time(),'ip'=>request()->ip(),'type'=>$type],['uid','userid','username','url','last_time','ip','type'],true);
    }

}
<?php 
/**
 * ===========================================================================
 * Veitool 快捷开发框架系统
 * Author: Niaho 26843818@qq.com
 * Copyright (c)2019-2025 www.veitool.com All rights reserved.
 * Licensed: 这不是一个自由软件，不允许对程序代码以任何形式任何目的的再发行
 * ---------------------------------------------------------------------------
 */
namespace app\event;

use GatewayWorker\Lib\Gateway;
use Workerman\Worker;
use think\facade\Db;

/**
 * Worker 命令行服务类
 */
class GatewayWorke
{
    /**
     * onWorkerStart 事件回调
     * 当businessWorker进程启动时触发。每个进程生命周期内都只会触发一次
     *
     * @access public
     * @param  \Workerman\Worker    $businessWorker
     * @return void
     */
    public static function onWorkerStart(Worker $businessWorker)
    {
        //$app = new \think\App();
        //$app->initialize();
    }

    /**
     * onConnect 事件回调
     * 当客户端连接上gateway进程时(TCP三次握手完毕时)触发
     *
     * @access public
     * @param  int       $client_id
     * @return void
     */
    public static function onConnect($client_id)
    {
        //Gateway::sendToCurrentClient("Your client_id is $client_id");//向当前客户端发送信息
        Gateway::sendToAll("$client_id login\r\n");
    }

    /**
     * onWebSocketConnect 事件回调
     * 当客户端连接上gateway完成websocket握手时触发
     *
     * @param  integer  $client_id  连接的客户端client_id
     * @param  mixed    $data
     * @return void
     */
    public static function onWebSocketConnect($client_id, $data)
    {
        var_export($data);
    }

    /**
     * onMessage 事件回调
     * 当客户端发来数据(Gateway进程收到数据)后触发
     *
     * @access public
     * @param  int       $client_id
     * @param  mixed     $data
     * @return void
     */
    public static function onMessage($client_id, $data)
    {
        $IP = $_SERVER['REMOTE_ADDR'];
        $TIME = time();
        $res = json_decode($data, true);
        $uid = intval($res['userid']);
        $us = Db::name('manager')->find($uid);
        if(!is_null($us)){
            if($us['token']==$res['token'] && ($us['tokentime'] + config('session.expire')) > $TIME){
                //绑定用户ID
                Gateway::bindUid($client_id, $uid);
                $type = $res['data']['to']['type'] ?? '';
                if($type=='friend'){ //朋友间发送
                    $toid = $res['data']['to']['id'];
                    $fs = Db::name('chat_friend')->where("userid = $uid AND friendid = $toid")->find();
                    if(!is_null($fs)){
                        //插入聊天记录
                        Db::name('chat_log')->insert(['fromid'=>$uid,'fromuser'=>$us['username'],'toid'=>$toid,'touser'=>$fs['frienduser'],'content'=>$res['data']['mine']['content'],'add_time'=>$TIME,'ip'=>$IP]);
                        $data = ['code'=>2,'msg'=>'发送成功','data'=>$res['data']];
                        Gateway::sendToUid($res['data']['to']['id'], json_encode($data));
                    }else{
                        $data = ['code'=>0,'msg'=>'非朋友不可发送','data'=>''];
                        Gateway::sendToClient($client_id, json_encode($data));
                    }
                }elseif($type=='group'){ //群发送
                    
                }else{
                    $data = ['code'=>1,'msg'=>'连接成功','data'=>''];
                    Gateway::sendToUid($uid, json_encode($data));
                    //Gateway::sendToClient($uid, json_encode($data));
                    // 向所有人发送 
                    //Gateway::sendToAll("$client_id said $message\r\n");
                    //$req_data = json_decode($message, true);
                    //Gateway::bindUid($client_id, $req_data['uid']);
                }
            }else{
                $data = ['code'=>0,'msg'=>'Token错误或已超时','data'=>''];
                Gateway::sendToClient($client_id, json_encode($data));
            }
        }else{
            $data = ['code'=>0,'msg'=>'连接失败','data'=>''];
            Gateway::sendToClient($client_id, json_encode($data));
        }
    }

    /**
     * onClose 事件回调 当用户断开连接时触发的方法
     *
     * @param  integer $client_id 断开连接的客户端client_id
     * @return void
     */
    public static function onClose($client_id)
    {
        GateWay::sendToAll("client[$client_id] logout\n");
    }

    /**
     * onWorkerStop 事件回调
     * 当businessWorker进程退出时触发。每个进程生命周期内都只会触发一次。
     *
     * @param  \Workerman\Worker    $businessWorker
     * @return void
     */
    public static function onWorkerStop(Worker $businessWorker)
    {
        echo "WorkerStop\n";
    }
}

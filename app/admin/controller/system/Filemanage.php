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
use app\model\system\UploadFile;
use app\model\system\UploadGroup;

/**
 * 文件管理控制器
 */
class Filemanage extends AdminBase
{
    /**
     * 文件列表
     * @param  string  $do  异步数据
     * @return mixed
     */
    public function index($do='')
    {
        if($do=='json'){
            $d = $this->only(['kw','fields','sotime','groupid','isdel','limit'=>'10/d'],'get');
            $kw = $d['kw'];
            $fds = ['filename','username'];
            $field = isset($fds[$d['fields']]) ? $d['fields'] : -1;
            $sotime = $d['sotime'];
            $groupid = $d['groupid'];
            $isdel = $d['isdel'];
            $limit = $d['limit'];
            $where = [];
            if($kw!=''){
                if($field>-1){
                    $where[] = $field>0 ? [$fds[$field],'=',$kw] : [$fds[$field],'LIKE', '%'.$kw.'%'];
                }else{
                    $where[] = [implode('|',$fds),'LIKE', '%'.$kw.'%'];
                }
            }
            if(strpos($sotime,' - ')!==false){
                $t = explode(' - ',$sotime);
                $where[] = ['u.addtime','>=',strtotime($t[0]." 00:00:00")];
                $where[] = ['u.addtime','<=',strtotime($t[1]." 23:59:59")];
            }
            if(is_numeric($groupid)) $where[] = ['u.groupid','=',$groupid];
            if(is_numeric($isdel))   $where[] = ['u.isdel','=',$isdel];
            return $this->returnMsg(UploadFile::alias('u')->leftJoin('upload_group g','u.groupid=g.groupid')->where($where)->order(['fileid'=>'desc'])->field('u.*,g.groupname')->paginate($limit));
        }
        $this->assign([
            'limit' => 10,
            'group' => json_encode(UploadGroup::where("isdel = 0")->column('groupname','groupid'))
        ]);
        return $this->fetch();
    }

    /**
     * 编辑文件
     * @return json
     */
    public function edit($do='')
    {
        $d = $this->only(['@token'=>'','fileid/d/参数错误','av','af']);
        $fileid = $d['fileid'];
        $Myobj = UploadFile::get("fileid = $fileid");
        if(!$Myobj) return $this->returnMsg("数据不存在");
        $value = $d['av'];
        $field = $d['af'];
        if(!in_array($field,['filename'])) return $this->returnMsg("参数错误");
        if($field=='filename'){
            $this->only(['av/*/{2,100}/文件标题/0/.']);
        }
        $rs = $Myobj->save([$field=>$value]);
        return $this->returnMsg($rs ? "设置成功" : '设置失败', 1);
    }

    /**
     * 删除文件
     * @return json
     */
    public function del()
    {
        $fileid = $this->only(['@token'=>'','fileid'])['fileid'];
        $fileid = is_array($fileid) ? implode(',',$fileid) : $fileid;
        if(!$fileid) return $this->returnMsg('参数错误');
        $rs = UploadFile::where("fileid IN ($fileid)")->update(['isdel'=>1]);
        if($rs){
            return $this->returnMsg("删除成功1！", 1);
        }else{
            return $this->returnMsg("删除失败！");
        }
    }

    /**
     * 恢复文件
     * @return json
     */
    public function reset()
    {
        $fileid = $this->only(['@token'=>'','fileid'])['fileid'];
        $fileid = is_array($fileid) ? implode(',',$fileid) : intval($fileid);
        if(!$fileid) return $this->returnMsg('参数错误');
        $rs = UploadFile::where("fileid IN ($fileid)")->update(['isdel'=>0]);
        if($rs){
            return $this->returnMsg("恢复成功！", 1);
        }else{
            return $this->returnMsg("恢复失败！");
        }
    }

    /**
     * 清理文件
     * @return json
     */
    public function clear()
    {
        $this->only(['@token'=>'']);
        $rs = (new UploadFile())->listQuery('isdel=1')->toArray();
        if($rs['data']){
            $fileid = [];
            $path = ROOT_PATH . 'public';
            foreach ($rs['data'] as $v){
                if($v['storage']=='local'){
                    $file = $path.$v['fileurl'];
                    if(is_file($file)){
                        if(@unlink($file)){
                            $fileid[] = $v['fileid']; 
                        }
                    }else{
                        $fileid[] = $v['fileid'];
                    }
                }else{
                    $fileid[] = $v['fileid'];
                }
            }
            if($fileid){
                UploadFile::del("fileid IN (". implode(',', $fileid) .")");
            }
            return $this->returnMsg("文件清理完毕！",1);
        }else{
            return $this->returnMsg("没有满足条件的文件可清理！");
        }
    }

}
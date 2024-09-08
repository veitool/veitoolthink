<?php
/**
 * ===========================================================================
 * Veitool 快捷开发框架系统
 * Author: Niaho 26843818@qq.com
 * Copyright (c)2019-2024 www.veitool.com All rights reserved.
 * Licensed: 这不是一个自由软件，不允许对程序代码以任何形式任何目的的再发行
 * ---------------------------------------------------------------------------
 */
namespace app\admin\controller\system;

use app\admin\controller\AdminBase;
use app\model\system\SystemUploadFile as UploadFile;
use app\model\system\SystemUploadGroup as UploadGroup;
use veitool\storage\Driver as StorageDriver;

/**
 * 上传管理
 */
class Upload extends AdminBase
{
    /**
     * 配置信息
     * @var array 
     */
    private $config;

    /**
     * 初始化配置信息
     * @var array 
     */
    private $CF = [
        'upload_engine'=>'local',
        'upload_image_type'=> 'jpg,png,gif,jpeg',
        'upload_image_size'=> 2,
        'upload_file_type'=> 'rar,zip,pdf,docx,doc,xlsx,xls',
        'upload_file_size'=> 10,
        'upload_video_type'=> 'mp4,flv,wmv,avi,mov,mpeg',
        'upload_video_size'=> 20,
        'upload_audio_type'=> 'mp3',
        'upload_audio_size'=> 10,
        //七牛云
        'qiniu_bucket'=> '',
        'access_key'=> '',
        'qiniu_secret_key'=> '',
        'qiniu_domain'=> '',
        //阿里云
        'aliyun_bucket'=> '',
        'access_key_id'=> '',
        'access_key_secret'=> '',
        'aliyun_domain'=> '',
        //腾讯云
        'qcloud_bucket'=> '',
        'region'=> '',
        'secret_id'=> '',
        'qcloud_secret_key'=> '',
        'qcloud_domain'=> '',
    ];

    /**
     * 控制器初始化
     * @return mixed
     */
    private function init()
    {
        $this->CF = array_merge($this->CF, vconfig());
        $this->config = array(
            'default'=>$this->CF['upload_engine'],
            'engine'=> array(
                'local'=>array(
                    'domain'=> '/static/file/upload',//上传的本地地址
                    'image' => array('ext'=>$this->CF['upload_image_type'],'size'=>$this->CF['upload_image_size']),
                    'file'  => array('ext'=>$this->CF['upload_file_type'],'size'=>$this->CF['upload_file_size']),
                    'video' => array('ext'=>$this->CF['upload_video_type'],'size'=>$this->CF['upload_video_size']),
                    'audio' => array('ext'=>$this->CF['upload_audio_type'],'size'=>$this->CF['upload_audio_size']),
                ),
                'qiniu'=>array(
                    'bucket'=>$this->CF['qiniu_bucket'],
                    'access_key'=>$this->CF['access_key'],
                    'secret_key'=>$this->CF['qiniu_secret_key'],
                    'domain'=>$this->CF['qiniu_domain']
                ),
                'aliyun'=>array(
                    'bucket'=>$this->CF['aliyun_bucket'],
                    'access_key_id'=>$this->CF['access_key_id'],
                    'access_key_secret'=>$this->CF['access_key_secret'],
                    'domain'=>$this->CF['aliyun_domain']
                ),
                'qcloud'=>array(
                    'bucket'=>$this->CF['qcloud_bucket'],
                    'region'=>$this->CF['region'],
                    'secret_id'=>$this->CF['secret_id'],
                    'secret_key'=>$this->CF['qcloud_secret_key'],
                    'domain'=>$this->CF['qcloud_domain']
                )
            )
        );
    }

    /**
     * 文件上传接口
     * @param   string   $file     上传文件form名称
     * @param   int      $groupid  上传的文件分组ID
     * @param   string   $action   上传的文件类型
     * @param   string   $thum     是否生成缩略图,如:100|100
     * @return  json
     * @throws \think\Exception
     */
    public function upfile(string $file = 'file', int $groupid = 0, string $action = '', string $thum = '')
    {
        if(!$action) return $this->returnMsg('参数错误');
        $this->init();
        $engine = $this->config['default'];
        $this->config['engine'][$engine]['type'] = $action;
        $this->config['engine'][$engine]['thum'] = $thum;
        $domain = $this->config['engine'][$engine]['domain'];
        //实例化存储驱动
        $StorageDriver = new StorageDriver($this->config);
        try{
            //设置上传文件的信息
            $StorageDriver->setUploadFile($file);
        }catch(\think\Exception $e){
            return $this->returnMsg('上传失败！'.$e->getMessage());
        }
        //上传图片
        if(!$StorageDriver->upload()) return $this->returnMsg('上传失败！'.$StorageDriver->getError());
        //图片上传路径
        $fileName = $StorageDriver->getFileName();
        //获取图片信息
        $fileInfo = $StorageDriver->getFileInfo();
        //保存到数据库
        $data['storage']  = $engine;
        $data['fileurl']  = VT_DIR.$domain.'/'.$fileName;
        $data['filename'] = $fileInfo['oname'];
        $data['filesize'] = round($fileInfo['size']/1024,2);
        $data['filetype'] = $action;
        $data['groupid']  = intval($groupid)>=0 ? intval($groupid) : 0;
        $data['fileext']  = $fileInfo['ext'];
        $data['addtime']  = time();
        $data['username'] = $this->manUser['username'];
        $data['fileid']   = UploadFile::insertGetId($data);
        //压缩容量
        if($data['filesize']>300 && $engine == 'local'){
            $pic = app()->getRootPath().'public'.$data['fileurl'];
            if($data['fileext']=='jpg'){
                $pics = Imagecreatefromjpeg($pic);
                Imagejpeg($pics,$pic,70);
                imagedestroy($pics);
            }elseif($data['fileext']=='png'){
                $pics = imagecreatefrompng($pic);
                imagepng($pics,$pic,9);
                imagedestroy($pics);
            }
        }
        return $this->returnMsg('上传成功！', 1, $data);
    }

    /**
     * 百度编辑器
     * @param   string   $file     上传文件form名称
     * @param   int      $groupid  上传的文件分组ID
     * @param   string   $action   上传的文件类型 或 其他动作
     * @return  json
     */
    public function ueditor(string $file = 'file', int $groupid = 0, string $action = '')
    {
        if(!$action) return $this->returnMsg('参数错误');
        if($action == 'config'){ //百度编辑器获取配置
            $this->CF = array_merge($this->CF, vconfig());
            $imageAllowFiles = explode(',', '.'.str_replace(',', ',.', $this->CF['upload_image_type']));
            $videoAllowFiles = explode(',', '.'.str_replace(',', ',.', $this->CF['upload_video_type']));
            $fileAllowFiles  = explode(',', '.'.str_replace(',', ',.', $this->CF['upload_file_type']));
            $data = [
                /* 上传图片配置项 */
                "imageActionName"=>"image", /* 执行上传图片的action名称 */
                "imageFieldName"=>"file", /* 提交的图片表单名称 */
                "imageMaxSize"=>$this->CF['upload_image_size']*1024*1024, /* 上传大小限制，单位B */
                "imageAllowFiles"=>$imageAllowFiles, /* 上传图片格式显示 */
                "imageCompressEnable"=>true, /* 是否压缩图片,默认是true */
                "imageCompressBorder"=>1600, /* 图片压缩最长边限制 */
                "imageInsertAlign"=>"none", /* 插入的图片浮动方式 */
                "imageUrlPrefix"=>"", /* 图片访问路径前缀 */
                "imagePathFormat"=>"/file/upload/", /* 上传保存路径,可以自定义保存路径和文件名格式 image/{yyyy}{mm}{dd}/{time}{rand:6} */
                /* 涂鸦图片上传配置项 */
                "scrawlActionName"=>"image", /* 执行上传涂鸦的action名称 */
                "scrawlFieldName"=>"file", /* 提交的图片表单名称 */
                "scrawlPathFormat"=>"/file/", /* 上传保存路径,可以自定义保存路径和文件名格式 */
                "scrawlMaxSize"=>$this->CF['upload_image_size']*1024*1024, /* 上传大小限制，单位B */
                "scrawlUrlPrefix"=>"", /* 图片访问路径前缀 */
                "scrawlInsertAlign"=>"none",
                /* 截图工具上传 */
                "snapscreenActionName"=>"image", /* 执行上传截图的action名称 */
                "snapscreenPathFormat"=>"/file/", /* 上传保存路径,可以自定义保存路径和文件名格式 */
                "snapscreenUrlPrefix"=>"", /* 图片访问路径前缀 */
                "snapscreenInsertAlign"=>"none", /* 插入的图片浮动方式 */
                /* 抓取远程图片配置 */
                "catcherLocalDomain"=>["127.0.0.1", "localhost", "img.baidu.com"],
                "catcherActionName"=>"image", /* 执行抓取远程图片的action名称 */
                "catcherFieldName"=>"source", /* 提交的图片列表表单名称 */
                "catcherPathFormat"=>"/file/", /* 上传保存路径,可以自定义保存路径和文件名格式 */
                "catcherUrlPrefix"=>"", /* 图片访问路径前缀 */
                "catcherMaxSize"=>$this->CF['upload_image_size']*1024*1024, /* 上传大小限制，单位B */
                "catcherAllowFiles"=>$imageAllowFiles, /* 抓取图片格式显示 */
                /* 上传视频配置 */
                "videoActionName"=>"video", /* 执行上传视频的action名称 */
                "videoFieldName"=>"file", /* 提交的视频表单名称 */
                "videoPathFormat"=>"/file/", /* 上传保存路径,可以自定义保存路径和文件名格式 */
                "videoUrlPrefix"=>"", /* 视频访问路径前缀 */
                "videoMaxSize"=>$this->CF['upload_video_size']*1024*1024, /* 上传大小限制，单位B，默认100MB */
                "videoAllowFiles"=>$videoAllowFiles, /* 上传视频格式显示 */
                /* 上传文件配置 */
                "fileActionName"=>"file", /* controller里,执行上传视频的action名称 */
                "fileFieldName"=>"file", /* 提交的文件表单名称 */
                "filePathFormat"=>"/file/", /* 上传保存路径,可以自定义保存路径和文件名格式 */
                "fileUrlPrefix"=>"", /* 文件访问路径前缀 */
                "fileMaxSize"=>$this->CF['upload_file_size']*1024*1024, /* 上传大小限制，单位B，默认50MB */
                "fileAllowFiles"=>$fileAllowFiles, /* 上传文件格式显示 */
                /* 列出指定目录下的图片 */
                "imageManagerActionName"=>"listimage", /* 执行图片管理的action名称 */
                "imageManagerListPath"=>"/file/upload/", /* 指定要列出图片的目录 */
                "imageManagerListSize"=>20, /* 每次列出文件数量 */
                "imageManagerUrlPrefix"=>"", /* 图片访问路径前缀 */
                "imageManagerInsertAlign"=>"none", /* 插入的图片浮动方式 */
                "imageManagerAllowFiles"=>$imageAllowFiles, /* 列出的文件类型 */
                /* 列出指定目录下的文件 */
                "fileManagerActionName"=>"listfile", /* 执行文件管理的action名称 */
                "fileManagerListPath"=>"/file/upload/", /* 指定要列出文件的目录 */
                "fileManagerUrlPrefix"=>"", /* 文件访问路径前缀 */
                "fileManagerListSize"=>20, /* 每次列出文件数量 */
                "fileManagerAllowFiles"=>$fileAllowFiles
            ];
            return json($data);
        }elseif($action == 'listimage'){ //百度编辑器列出图片
            $file = new UploadFile();
            $where[] = ['isdel','=',0];
            $where[] = ['filetype','=','image'];
            $rs = $file->listQuery($where)->toArray();
            if($rs['total']>0){
                $data['start'] = $this->request->param('start',0);
                $data['state'] = 'SUCCESS';
                $data['total'] = $rs['total'];
                foreach($rs['data'] as $v){
                    $data['list'][] = ['url'=>$v['fileurl'],'mtime'=>$v['addtime']];
                }
            }else{
                $data['start'] = 0;
                $data['state'] = 'no match file';
                $data['total'] = 0;
                $data['list']  = [];
            }
            return json($data);
        }elseif($action == 'listfile'){ //百度编辑器列出附件
            $file = new UploadFile();
            $where[] = ['isdel','=',0];
            $where[] = ['filetype','=','file'];
            $rs = $file->listQuery($where)->toArray();
            if($rs['total']>0){
                $data['start'] = $this->request->param('start',0);
                $data['state'] = 'SUCCESS';
                $data['total'] = $rs['total'];
                foreach($rs['data'] as $v){
                    $data['list'][] = ['url'=>$v['fileurl'],'mtime'=>$v['addtime']];
                }
            }else{
                $data['start'] = 0;
                $data['state'] = 'no match file';
                $data['total'] = 0;
                $data['list']  = [];
            }
            return json($data);
        }else{
            $this->init();
            $engine = $this->config['default'];
            $this->config['engine'][$engine]['type'] = $action;
            $this->config['engine'][$engine]['thum'] = 0;
            $domain = $this->config['engine'][$engine]['domain'];
            //实例化存储驱动
            $StorageDriver = new StorageDriver($this->config);
            //设置上传文件的信息
            $StorageDriver->setUploadFile($file);
            //上传图片
            if(!$StorageDriver->upload()) return $this->returnMsg('上传失败！'.$StorageDriver->getError());
            //图片上传路径
            $fileName = $StorageDriver->getFileName();
            //获取图片信息
            $fileInfo = $StorageDriver->getFileInfo();
            //保存到数据库
            $data['storage']  = $engine;
            $data['fileurl']  = VT_DIR.$domain.'/'.$fileName;
            $data['filename'] = $fileInfo['oname'];
            $data['filesize'] = round($fileInfo['size']/1024,2);
            $data['filetype'] = $action;
            $data['groupid']  = intval($groupid)>=0 ? intval($groupid) : 0;
            $data['fileext']  = $fileInfo['ext'];
            $data['addtime']  = time();
            $data['username'] = $this->manUser['username'];
            $data['fileid']   = UploadFile::insertGetId($data);
            //百度编辑器返回数据
            $UE['original'] = '';
            $UE['size']  = $fileInfo['size'];
            $UE['state'] = "SUCCESS";
            $UE['title'] = $data['filename'];
            $UE['type']  = '.'.$fileInfo['ext'];
            $UE['url']   = $data['fileurl'];
            return json($UE);
        }
    }

    /**
     * 文件管理
     * @param   string   $action    操作参数
     * @param   string   $type      文件类型
     * @return  json
     */
    public function files(string $action = '', string $type = 'image')
    {
        if($action=='move'){
            $d = $this->only(['groupid/d','fileids/a']);
            $fileids = implode(',', array_map('intval',$d['fileids']));
            $rs = UploadFile::update(['groupid'=>$d['groupid']],[['fileid','in',$fileids]]);
            if($rs){
                return $this->returnMsg("移动成功", 1);
            }else{
                return $this->returnMsg("移动失败");
            }
        }elseif($action=='del'){
            $d = $this->only(['fileids/a']);
            $fileids = implode(',',array_map('intval',$d['fileids']));
            $rs = UploadFile::update(['isdel'=>1],[['fileid','in',$fileids]]);
            if($rs){
                return $this->returnMsg("删除成功", 1);
            }else{
                return $this->returnMsg("删除失败");
            }
        }
        //获取文件记录
        $groupid = $this->request->get('groupid/d',-1);
        $file = new UploadFile();
        $where[] = ['isdel','=',0];
        $where[] = ['filetype','=',$type];
        if($groupid>-1) $where[] = ['groupid','=',$groupid];
        $data['file_list'] = $file->listQuery($where)->toArray();
        //获取文件分类
        $where = [];
        $where[] = ['grouptype','=',$type];
        $where[] = ['isdel','=',0];
        $data['group_list'] = UploadGroup::where($where)->column('groupid,groupname');
        //返回json数据
        return $this->returnMsg($data,1);
    }

    /**
     * 文件分组管理
     * @param   string   $action   操作参数(有权限)
     * @return  json
     */
    public function group(string $action = '')
    {
        if(!$action) return $this->returnMsg('参数错误');
        $d = $this->only(['groupid/d','groupname/h','grouptype/h']);
        if($action=='add'){
            if(!$d['groupname']) return $this->returnMsg("分组名称不能为空");
            $d["addtime"] = time();
            $d["listorder"] = 10;
            $id = UploadGroup::insertGetId($d);
            if($id){
                $d['msg'] = '添加成功';
                $d['groupid'] = $id;
                return $this->returnMsg($d, 1);
            }else{
                return $this->returnMsg("添加失败");
            }
        }elseif($action=='edit'){
            if(!$d['groupname']) return $this->returnMsg("分组名称不能为空");
            $d["edittime"] = time();
            unset($d['grouptype']);
            $rs = UploadGroup::update($d);
            if($rs !== false){
                return $this->returnMsg("编辑成功", 1);
            }else{
                return $this->returnMsg("编辑失败");
            }
        }elseif($action=='del'){
            $groupid = $d['groupid'];
            $rs = UploadGroup::del("groupid IN($groupid)");
            if($rs){
                return $this->returnMsg("删除成功", 1);
            }else{
                return $this->returnMsg("删除失败");
            }
        }
    }

}
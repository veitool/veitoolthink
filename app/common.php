<?php
/** 全局公用
 * ===========================================================================
 * Veitool 快捷开发框架系统
 * Author: Niaho 26843818@qq.com
 * Copyright (c)2019-2023 www.veitool.com All rights reserved.
 * Licensed: 这不是一个自由软件，不允许对程序代码以任何形式任何目的的再发行
 * ---------------------------------------------------------------------------
 */

/**
 * md5判断
 * @param   string   $w   字符
 * @return  bool
 */
function is_md5($w){
    return preg_match("/^[a-f0-9]{32}$/", $w);
}

/**
 * 判断字符是否合乎规则
 * @param   string   $s   目标字符串
 * @param   string   $f   正则类型 ip,mobile,email 或者 允许有的位数范围,如:{1,3}
 * @param   array    $t   合法的字符集0:字母数字汉字下划线 1:数字 2:小写字母 3:大写字母 4:汉字 5:任何非空白字符
 * @param   string   $o   允许有字符
 * @return  bool
 */
function is_preg($s,$f='',$t=[0],$o=''){
    if($s=='' || is_array($s)) return false;
    $s = str_replace([chr(10),chr(13),"\t"],['','',''],$s);
    $p = [
        'ip'    => "/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/",
        'mobile'=> "/^1[3|4|5|6|7|8|9]{1}[0-9]{9}$/",
        'email' => "/^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/",
        'class' => "/^[A-Z]{1}[a-zA-Z0-9]{1,15}$/",
        'idcard'=> "/^([1-6][1-9]|50)\d{4}(19|20)\d{2}((0[1-9])|10|11|12)(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx]$/"
    ];
    if(isset($p[$f])) return preg_match($p[$f],$s);
    $m = '/^[';
    $p = ['\w',"0-9",'a-z','A-Z','\x{4e00}-\x{9fa5}','\S'];
    foreach($t as $v){
        $m .= $p[$v] ?? '';
    }
    $m .= $o ? $o.']'.$f.'+$/u' : ']'.$f.'+$/u';
    return preg_match($m,$s);
}

/**
 * 返回字符串长度
 * @param   string  $s  目标源
 * @return  int
 */
function word_count($s){
    return function_exists('mb_strlen') ? mb_strlen($s, 'utf8') : strlen($s);
}

/**
 * 生成指定长度的随机字符
 * @param   int       $l    指定长度
 * @param   string    $c    源字符集
 * @return  string
 */
function random($l,$c = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz'){
    $h = '';
    $m = strlen($c) - 1;
    for($i = 0; $i < $l; $i++){$h .= $c[mt_rand(0, $m)];}
    return $h;
}

/**
 * 生成密码
 * @param   string    $p   密码
 * @param   string    $s   密钥
 * @return  string
 */
function set_password($p,$s){
    return md5((is_md5($p) ? md5($p) : md5(md5($p))).$s);
}

/**
 * 设置订单号
 * @retrun  string 
 */
function set_order_id(){
    return date('YmdHis',time()).substr(microtime(),2,6).sprintf('%03d',rand(0,999));
}

/**
 * 字符串中间替换为星号
 * @param   string    $s    字符串
 * @param   string    $n    星号数 0:按字符算
 * @return  string
 */
function half_replace($s,$n=0){
    $l = mb_strlen($s, 'UTF-8');
    if($l<=2) return $s;
    $l = $n>0 ? $n : $l-2;
    return mb_substr($s, 0, 1, 'UTF-8') . str_repeat('*',$l) . mb_substr($s, -1, 1, 'UTF-8');
}

/**
 * 回车、换行、空格字符过滤
 * @param   string    $s   目标字符
 * @return  string
 */
function vtrim($s){
    return str_replace([chr(10),chr(13),"\t",' '],['','','',''],$s);
}

/**
 * 字符过滤 【空值或中间未出现空格均不过滤】
 * @param   string|array    $s    目标字符
 * @param   int             $t    过滤类型 默认1 1转码 0解码
 * @return  string|array
 */
function strip_sql($s,$t=1){
    if(is_array($s)){
        return array_map('strip_sql', $s);
    }else{
        if(empty($s) || strripos($s,' ') == 0) return trim($s);
        if($t){
            $p = 'vt_';
            $s = preg_replace("/\/\*([\s\S]*?)\*\//", "", trim($s));
            $s = preg_replace("/0x([a-f0-9]{2,})/i", '0&#120;\\1', $s);
            $s = preg_replace_callback("/(select|update|replace|delete|drop)([\s\S]*?)(".$p."|from)/i", 'strip_wd', $s);
            $s = preg_replace_callback("/(load_file|substring|substr|reverse|trim|space|left|right|mid|lpad|concat|concat_ws|make_set|ascii|bin|oct|hex|ord|char|conv)([^a-z]?)\(/i", 'strip_wd', $s);
            $s = preg_replace_callback("/(union|where|having|outfile|dumpfile|".$p.")/i", 'strip_wd', $s);
            return $s;
        }else{
            return str_replace(['&#95;','&#100;','&#101;','&#103;','&#105;','&#109;','&#110;','&#112;','&#114;','&#115;','&#116;','&#118;','&#120;'], ['_','d','e','g','i','m','n','p','r','s','t','v','x'], trim($s));
        }
    }
}

/**
 * 转ASCII码 (以上方法 strip_sql 中用于转换ASCII码仿SQL注入)
 * @param   array   $m   对象
 * @return  string
 */
function strip_wd($m){
    if(is_array($m) && isset($m[1])){
        $wd = substr($m[1], 0, -1).'&#'.ord(strtolower(substr($m[1], -1))).';';
        if(isset($m[3])) return $wd.$m[2].$m[3];
        if(isset($m[2])) return $wd.$m[2].'(';
        return $wd;
    }
    return '';
}

/**
 * HTML过滤
 * @param   string|array   $str    目标
 * @param   int            $low    级别 默认1全过滤，0简单标签过滤
 * @return  string|array
 */
function strip_html($str,$low = 1){
    if(is_array($str)){
        return array_map('strip_html', $str, ['low'=>$low]);
    }elseif(!empty($str)){
        $str = htmlspecialchars_decode(trim($str));
        $str = strip_tags($str);
        if($low){
            $str = str_replace(['"',"\\","'","/","..","../","./","//"],'',$str);
            $no = '/<!--.*-->/';
            $str = preg_replace("$no",'',$str);
            $no = '/%0[0-8bcef]/';
            $str = preg_replace($no,'',$str);
            $no = '/%1[0-9a-f]/';
            $str = preg_replace($no,'',$str);
            $no = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S';
            $str = preg_replace($no,'',$str);
        }
    }
    return $str;
}

/**
 * 转换HTML实体(双引号、单引号均转换)
 * @param   string   $s   目标字符
 * @return  string
 */
function vhtmlspecialchars($s){
    if(is_array($s)){
        return array_map('vhtmlspecialchars', $s);
    }else{
        $s = htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
        return str_replace('&amp;', '&', $s);
    }
}

/**
 * 数字格式转换
 * @param    float   $v   数值
 * @param    int     $p   小数点后位数
 * @param    bool    $s   是否格式化为字符串
 * @return   float/string
 */
function dround($v, $p=2, $s=false){
    $v = round(floatval($v), $p);
    if($s) $v = sprintf('%.'.$p.'f', $v);
    return $v;
}

/**
 * 获取扩展名
 * @param    string    $f    文件路径串
 * @return   string
 */
function file_ext($f){
    if(strpos($f, '.') === false) return '';
    $ext = strtolower(trim(substr(strrchr($f, '.'), 1)));
    return preg_match("/^[a-z0-9]{1,10}$/", $ext) ? $ext : '';
}

/**
 * 删除文件夹
 * @param  string  $dirname  目录
 * @param  bool    $self     是否删除自身
 * @return bool
 */
function rmdirs($dirname, $self = true){
   if(!is_dir($dirname)){
       return false;
   }
   $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dirname, RecursiveDirectoryIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST);
   foreach($files as $fileinfo){
       $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
       $todo($fileinfo->getRealPath());
   }
   if($self){
       @rmdir($dirname);
   }
   return true;
}

/**
 * 复制文件夹
 * @param  string  $source  源文件夹
 * @param  string  $dest    目标文件夹
 */
function copydirs($source, $dest){
   if(!is_dir($dest)){
       mkdir($dest, 0755, true);
   }
   $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),RecursiveIteratorIterator::SELF_FIRST);
   foreach($iterator as $item){
       if($item->isDir()){
           $sontDir = $dest . VT_DS . $iterator->getSubPathName();
           if(!is_dir($sontDir)){
               mkdir($sontDir, 0755, true);
           }
       }else{
           copy($item, $dest . VT_DS . $iterator->getSubPathName());
       }
   }
}

/**
 * 移除空目录
 * @param   string   $dir  目录
 * @return
 */
function remove_empty_folder($dir){
    try{
        $isDirEmpty = !(new \FilesystemIterator($dir))->valid();
        if($isDirEmpty){
            @rmdir($dir);
            remove_empty_folder(dirname($dir));
        }
    }catch(\UnexpectedValueException $e){
    }catch(\Exception $e){
    }
}

/**
 * 键串转换键值串
 * @param   string   $ids   键串
 * @param   array    $arr   数组
 * @return  string
 */
function idstoname($ids,$arr){
    $str = '';
    $a = explode(',', $ids);
    foreach($a as $i){
        $t = $arr[$i] ?? '';
        if($t) $str .= $str ? '，'.$t : $t;
    }
    return $str;
}

/**
 * 获取站点配置
 * @param  string  $name     配置键【支持:域1.域2，插件配置获取:@插件名.键名】
 * @param  string  $default  缺省值
 * @return array|string
 */
function vconfig($name='',$default=''){
    global $_VCF;
    $_VCF = $_VCF ?: \app\model\system\Setting::cache();
    if($name){
        $dt = explode('.', $name);
        $rs = isset($_VCF[$dt[0]]) ? $_VCF[$dt[0]] : '';
        if(isset($dt[1])){
            $rs = isset($rs[$dt[1]]) ? $rs[$dt[1]] : '';
        }
        $rs = is_null($rs) || $rs === '' ?  $default : $rs;
    }else{
        $rs = $_VCF;
    }
    return $rs;
}

/**
 * 时间格式
 * @param  int  $time   时间戳
 * @return string
 */
function show_time($time){
    $rtime = date("Y-m-d H:i", $time);
    $time = VT_TIME - $time;
    if($time < 60){
        $str = '刚刚';
    }elseif($time < 3600){
        $str = floor($time / 60) . '分钟前';
    }elseif($time < 86400){
        $str = floor($time / 3600) . '小时前';
    }elseif($time < 259200){
        $str = floor($time / 86400) == 1 ? '昨天' : '前天';
    }else{
        $str = $rtime;
    }
    return $str;
}

/**
 * 配置项解析 Setting 后台设置模块中用到
 * @param   string   $value   配置值
 * @return  array|string
 */
function parse_attr($value = ''){
   $array = preg_split('/[,;\r\n]+/', trim($value, ",;\r\n"));
   if(strpos($value, ':')){
       $value = [];
       foreach($array as $val){
           list($k, $v) = explode(':', $val);
           $value[$k] = $v;
       }
   }else{
       $value = $array;
   }
   return $value;
}

/**
 * 获取所有子类ID
 * @param  int     $pid    上级ID
 * @param  array   $box    数据源
 * @param  string  $ikey   ID键
 * @param  string  $pkey   上级键
 * @return string  ID串
 */
function get_subclass($pid,$box,$ikey='id',$pkey='pid'){
    $str = '';
    foreach($box as $item){
        if($item[$pkey]==$pid){
            $str .= $str ? ','.$item[$ikey] : $item[$ikey];
            $get = get_subclass($item[$ikey],$box,$ikey,$pkey);
            if($get){
                $str .= ','.$get;
            }
        }
    }
    return $str;
}

/**
 * 小地区往上查询
 * @param   int       $areaid    地区ID
 * @param   string    $str       分隔符
 * @param   int       $deep      查找深度
 * @param   int       $start     查找开始
 * @return  bool
 */
function area_pos($areaid, $str = ' &raquo; ', $deep = 0, $start = 0){
    $areaid = intval($areaid);
    $area = \app\model\system\Area::cache();
    if(!$areaid || !$area) return '';
    $arrparentid = $area[$areaid]['arrparentid'] ? explode(',', $area[$areaid]['arrparentid']) : [];
    $arrparentid[] = $areaid;
    $pos = '';
    if($deep) $i = 1;
    $j = 0;
    foreach($arrparentid as $areaid){
        if(!$areaid || !isset($area[$areaid])) continue;
        if($j++ < $start) continue;
        if($deep){
            if($i > $deep) continue;
            $i++;
        }
        $pos .= $area[$areaid]['areaname'].$str;
    }
    $_len = strlen($str);
    if($str && substr($pos, -$_len, $_len) === $str) $pos = substr($pos, 0, strlen($pos)-$_len);
    return $pos;
}

/**
 * 多级列表构造（有递归）
 * @param  array   $rs     所有菜单数组集
 * @param  int     $pid    开始的父级ID
 * @param  array   $key    3要素 ['id','parentid','title'] 顺序不能变
 * @param  int     $t      填充符
 * @param  int     $j      层级数
 * @param  string  $s      缩进符
 * @param  array   $ids    某id键的子类个数集
 * @param  array   $arr    返回的父子重构顺序集 相对 $rs 多了 new_title 键
 * @return array
 */
function list_tree($rs=[],$pid=0,$key=['id','parentid','title'],$t=1,$j=0,$s='',$ids=[],$arr=[]){
    if(empty($rs)) return $arr;
    $ids = empty($ids) ? array_count_values(array_column($rs,$key[1])) : $ids;
    $i = 1;
    $k = $j;
    $a = $s;
    if($t==1){
        $c = ["&nbsp;&nbsp;&nbsp;│ ",'&nbsp;&nbsp;&nbsp;├─ ','&nbsp;&nbsp;&nbsp;└─ ','&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'];
    }else{
        $c = [" - "," - "," - "," - "];
    }
    foreach($rs as $v){
        if($pid == $v[$key[1]]){
            $n = '';
            if($k>0){
                $n = ($ids[$pid]==$i) ? $a.$c[2] : $a.$c[1];
                $s = ($ids[$pid]==$i) ? $a.$c[3] : $a.$c[0];
            }
            $v['new_title'] = $n.$v[$key[2]];
            $arr[] = $v;
            $id = $v[$key[0]];
            if(isset($ids[$id])){
                $j = $k+1;
                $arr = list_tree($rs,$id,$key,$t,$j,$s,$ids,$arr);
            }
            $i++;
        }
    }
    return $arr;
}
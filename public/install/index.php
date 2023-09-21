<?php
/**
 * ===========================================================================
 * Veitool 快捷开发框架系统
 * Author: Niaho 26843818@qq.com
 * Copyright (c)2019-2023 www.veitool.com All rights reserved.
 * Licensed: 这不是一个自由软件，不允许对程序代码以任何形式任何目的的再发行
 * ---------------------------------------------------------------------------
 */
header('Content-Type:text/html; charset=utf-8');
// 检测php版本号
if(version_compare(PHP_VERSION,'8.1.0','<')){
    exit('很抱歉，由于您的PHP版本过低，不能安装本软件，为了系统功能全面可用，请升级到PHP8.1.0或更高版本再安装，谢谢！');
}

// 不限制响应时间 error_reporting(0);
set_time_limit(0);

// 设置系统路径
define('INSTALL_PATH', str_replace('\\', '/', dirname(__FILE__)));
define('ROOT_DIR', dirname(INSTALL_PATH, 2));

// 提示已经安装
if(is_file(INSTALL_PATH . '/install.lock')){
    exit('已经安装过');
}

// 版权信息设置
$copyright = '© 2023 veitool.com 版权所有';

// 获取当前步骤
$s = isset($_GET['s']) && in_array($_GET['s'], [1,2,3,4,5,6]) ? $_GET['s'] : 1 ;

// 环境检测
if($s == 2){
    // 初始通过
    $isOK = true;
    // 检测是否可写的路径
    $iswrite_array = ['/.env','/public/file/'];
    // 获取检测的函数数据
    $exists_array = ['curl_init', 'bcadd', 'mb_substr', 'simplexml_load_string'];
    // 获取扩展要求数据
    $extendArray = getExtendArray();
}elseif($s == 3){
    $isOK = $_POST['isOK'] ?? false;
    if(!$isOK) header("Location: ?s=2");
    $currentHost = ($_SERVER['SERVER_PORT'] == 443 ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/';
}elseif($s == 4){
    // 设置无缓冲输出
    header('X-Accel-Buffering: no');
    ob_implicit_flush(true);

    // 初始化信息
    $dbhost = $_GET['dbhost'] ?? '';
    $dbname = $_GET['dbname'] ?? '';
    $dbpre  = $_GET['dbpre'] ?? 'vt_';
    $dbuser = $_GET['dbuser'] ?? '';
    $dbpwd  = $_GET['dbpwd'] ?? '';
    $dbport = $_GET['dbport'] ?? 3306;
    $adminmap  = $_GET['adminmap'] ?? 'admin';
    $adminuser = $_GET['adminuser'] ?? 'admin';
    $adminpass = $_GET['adminpass'] ?? '123456';
    // 连接证数据库
    try{
        $dsn = "mysql:host={$dbhost};port={$dbport};charset=utf8";
        $pdo = new PDO($dsn, $dbuser, $dbpwd);
        $pdo->query("SET NAMES utf8"); // 设置数据库编码
    }catch(Exception $e){
        tipMsg('数据库连接错误，请检查！',1);
        exit(header('HTTP/1.0 500 Internal Server Error'));
    }
    // 查询数据库
    $res = $pdo->query('show Databases');
    // 遍历所有数据库，存入数组
    $dbnameArr = [];
    foreach($res->fetchAll(PDO::FETCH_ASSOC) as $row){
        $dbnameArr[] = $row['Database'];
    }
    // 检查数据库是否存在，没有则创建数据库
    if(!in_array(trim($dbname), $dbnameArr)){
        if(!$pdo->exec("CREATE DATABASE `$dbname`")){
            tipMsg("创建数据库失败，请检查权限或联系管理员！",1);
            exit(header('HTTP/1.0 500 Internal Server Error'));
        }
    }
    // 更新app配置
    $config_str = getConfigs($adminmap);
    $fp = fopen(ROOT_DIR . '/config/app.php', 'w');
    fwrite($fp, $config_str);
    fclose($fp);
    // 数据库创建完成，开始连接
    $pdo->query("USE `$dbname`");
    // 获取.env模板内容
    $env_str = getEnvs();
    $env_str = str_replace('~db_host~', $dbhost, $env_str);
    $env_str = str_replace('~db_name~', $dbname, $env_str);
    $env_str = str_replace('~db_user~', $dbuser, $env_str);
    $env_str = str_replace('~db_pwd~',  $dbpwd, $env_str);
    $env_str = str_replace('~db_port~', $dbport, $env_str);
    $env_str = str_replace('~db_pre~', $dbpre, $env_str);
    // 写入.env配置文件
    $fp = fopen(ROOT_DIR . '/.env', 'w');
    fwrite($fp, $env_str);
    fclose($fp);

    tipMsg("数据库连接文件创建完成！");
    ob_flush();
    usleep(100000);

    // 导入安装数据
    $data_str = file_get_contents(INSTALL_PATH . '/data/install_data.sql');
    $data_arr = parseSql($data_str, $dbpre, 'vt_');
    foreach($data_arr as $v){
        $pdo->exec(trim($v));
        if($txt = strstr($v,'COMMENT=')){
            $txt = str_replace(['COMMENT=','\'',';'],['','',''],$txt);
            tipMsg("创建【{$txt}】表完成！");
            ob_flush();
            usleep(100000);
        }
    }

    tipMsg("系统初始数据导入完成！");
    ob_flush();
    usleep(100000);

    // 更新管理员信息
    include (ROOT_DIR . '/app/common.php');
    $passsalt = random(8);
    $adminpass = set_password($adminpass,$passsalt);
    $pdo->exec("UPDATE {$dbpre}manager SET `username` ='{$adminuser}',`password`='{$adminpass}',`passsalt`='{$passsalt}' WHERE userid = 1");

    tipMsg("管理员信息设置完成！");
    ob_flush();

    // 结束缓存区
    ob_end_flush();
    exit();

}elseif($s == 5){
    $fp = fopen(INSTALL_PATH . '/install.lock', 'w');
    fwrite($fp, '程序已正确安装，重新安装请删除本文件');
    fclose($fp);
}elseif($s == 6){ //异步检查数据库密码
    $dbhost = $_GET['dbhost'] ?? '';
    $dbuser = $_GET['dbuser'] ?? '';
    $dbpwd  = $_GET['dbpwd'] ?? '';
    $dbport = $_GET['dbport'] ?? '';
    try{
        $dsn = "mysql:host=$dbhost;charset=utf8";
        $pdo = new PDO($dsn, $dbuser, $dbpwd);
        exit('true');
    }catch(Exception $e){
        exit('false');
    }
}

// 设置是否允许下一步
function setOk($val)
{
    global $isOK;
    $isOK = $val;
}

// 测试可写性
function isWrite($file)
{
    if(is_writable(ROOT_DIR . $file)){
        echo '可写';
    }else{
        echo '<span>不可写</span>';
        setOk(false);
    }
}

// 测试函数是否存在
function isFunExists($func)
{
    $state = function_exists($func);
    if($state === false){
        setOk(false);
    }
    return $state;
}

// 测试函数是否存在
function isFunExistsTxt($func)
{
    if(isFunExists($func)){
        echo '无';
    }else{
        echo '<span>需安装</span>';
        setOk(false);
    }
}

/**
 * 获取扩展要求数据
 * @return array
 */
function getExtendArray()
{
    $data = [
        [
            'name' => 'CURL',
            'status' => extension_loaded('curl'),
        ],
        [
            'name' => 'OpenSSL',
            'status' => extension_loaded('openssl'),
        ],
        [
            'name' => 'PDO Mysql',
            'status' => extension_loaded('PDO') && extension_loaded('pdo_mysql'),
        ],
        [
            'name' => 'Mysqlnd',
            'status' => extension_loaded('mysqlnd'),
        ],
        [
            'name' => 'JSON',
            'status' => extension_loaded('json')
        ],
        [
            'name' => 'Fileinfo',
            'status' => extension_loaded('fileinfo')
        ],
        [
            'name' => 'GD',
            'status' => extension_loaded('gd'),
        ],
        [
            'name' => 'BCMath',
            'status' => extension_loaded('bcmath'),
        ],
        [
            'name' => 'Mbstring',
            'status' => extension_loaded('mbstring'),
        ],
        [
            'name' => 'SimpleXML',
            'status' => extension_loaded('SimpleXML'),
        ]
    ];
    foreach($data as $item){
        !$item['status'] && setOk(false);
    }
    return $data;
}

/**
 * 输出提示
 * @param  string  $str   追加的文本
 * @param  int     $err   是否终止
 */
function tipMsg($str,$err=0)
{
    echo $err ? '<p class="red">'. $str .'</p>' : '<p>'. $str .'</p>';
}

/**
 * Sql数据处理
 * @param   string   $sql    sql数据
 * @param   string   $to     要替换成的前缀
 * @param   strint   $from   默认前缀
 * @return  array
 */
function parseSql($sql, $to, $from)
{
    list($pure_sql, $comment) = [[], false];
    $sql = explode("\n", trim(str_replace(["\r\n", "\r"], "\n", $sql)));
    foreach ($sql as $key => $line) {
        if ($line == '') {
            continue;
        }
        if (preg_match("/^(#|--)/", $line)) {
            continue;
        }
        if (preg_match("/^\/\*(.*?)\*\//", $line)) {
            continue;
        }
        if (substr($line, 0, 2) == '/*') {
            $comment = true;
            continue;
        }
        if (substr($line, -2) == '*/') {
            $comment = false;
            continue;
        }
        if ($comment) {
            continue;
        }
        if ($from != '') {
            $line = str_replace('`' . $from, '`' . $to, $line);
        }
        if ($line == 'BEGIN;' || $line == 'COMMIT;') {
            continue;
        }
        array_push($pure_sql, $line);
    }
    $pure_sql = implode("\n",$pure_sql);
    $pure_sql = explode(";\n", $pure_sql);
    return $pure_sql;
}

/**
 * 获取config配置数据
 * @param  string  $map  映射地址
 * @return string
 */
function getConfigs($map)
{
    return <<<EOT
<?php
// +----------------------------------------------------------------------
// | 应用设置
// +----------------------------------------------------------------------

return [
    // 应用地址
    'app_host'         => env('app.host', ''),
    // 应用的命名空间
    'app_namespace'    => '',
    // 是否启用路由
    'with_route'       => true,
    // 是否启用事件
    'with_event'       => true,
    // 默认应用
    'default_app'      => 'index',
    // 默认时区
    'default_timezone' => 'Asia/Shanghai',
    // 应用映射（自动多应用模式有效）
    'app_map'          => ['{$map}'=>'admin'],
    // 域名绑定（自动多应用模式有效）
    'domain_bind'      => [
        //'www'=>'index'
    ],
    // 开启应用快速访问 Route::rule('demo','index/abc/demo') 这样就可以  www.veitool.com/demo 去快速访问 www.veitool.com/index/abc/demo
    'app_express'      => true,
    // 禁止URL访问的应用列表（自动多应用模式有效）
    'deny_app_list'    => ['model','event'],
    // 异常页面的模板文件
    'exception_tmpl'   => app()->getRootPath().'app/v_err.tpl',
    // 错误显示信息,非调试模式有效
    'error_message'    => '页面错误！请稍后再试～',
    // 显示错误信息
    'show_error_msg'   => false,
];
EOT;
}

/**
 * 获取Env配置数据
 * @return string
 */
function getEnvs()
{
    return <<<EOT
APP_DEBUG = true
APP_TRACE = false

[APP]
DEFAULT_TIMEZONE = Asia/Shanghai

[DATABASE]
TYPE     = mysql
HOSTNAME = ~db_host~
DATABASE = ~db_name~
USERNAME = ~db_user~
PASSWORD = ~db_pwd~
HOSTPORT = ~db_port~
PREFIX   = ~db_pre~
CHARSET  = utf8
DEBUG    = true

[CACHE]
DRIVER = file

[REDIS]
HOSTNAME = 127.0.0.1
HOSTPORT = 6379
PASSWORD =
SELECT = 0

[LANG]
default_lang = zh-cn
EOT;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Veitool快捷开发框架系统 安装向导</title>
<script type="text/javascript" src="/static/layui/layui.js"></script>
<script type="text/javascript">var $=layui.$,jQuery=layui.jquery;</script>
<link href="/static/layui/css/layui.css" type="text/css" rel="stylesheet">
<link href="tpl/style/install.css" type="text/css" rel="stylesheet"/>
</head>
<body>
<div class="header"></div>
<?php require (INSTALL_PATH . '/tpl/step_'.$s.'.php');?>
</body>
</html>
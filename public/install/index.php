<?php
/**
 * ===========================================================================
 * Veitool 快捷开发框架系统
 * Author: Niaho 26843818@qq.com
 * Copyright (c)2019-2026 www.veitool.com All rights reserved.
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
$copyright = '© 2025 veitool.com 版权所有';

// 获取当前步骤
$s = isset($_GET['s']) && in_array($_GET['s'], [1,2,3,4,5,6]) ? $_GET['s'] : 1 ;

// 环境检测
if($s == 2){
    // 初始通过
    $isOK = true;
    // 检测是否可写的路径
    $iswrite_array = [['/.env',644],['/config/app.php',644],['/runtime/',755],['/public/install/',755],['/public/static/file/',755]];
    // 获取检测的函数数据
    $exists_array = ['curl_init', 'bcadd', 'mb_substr', 'simplexml_load_string'];
    // 获取扩展要求数据
    $extendArray = getExtendArray();
}elseif($s == 3){
    $isOK = $_POST['isOK'] ?? false;
    if(!$isOK) header("Location: ?s=2");
    $currentHost = ($_SERVER['SERVER_PORT'] == 443 ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/';
}elseif($s == 4){
    // 初始化信息
    $dbhost = trim($_GET['dbhost'] ?? '');
    $dbname = trim($_GET['dbname'] ?? '');
    $dbpre  = trim($_GET['dbpre'] ?? 'vt_');
    $dbuser = trim($_GET['dbuser'] ?? '');
    $dbpwd  = trim($_GET['dbpwd'] ?? '');
    $dbport = trim($_GET['dbport'] ?? 3306);
    $overwrite = intval($_GET['overwrite'] ?? 0);
    $adminmap  = trim($_GET['adminmap'] ?? 'admin');
    $adminuser = trim($_GET['adminuser'] ?? 'admin');
    $adminpass = trim($_GET['adminpass'] ?? '123456');
    // 连接证数据库
    try{
        $pdo = getPDO($dbhost, $dbuser, $dbpwd, $dbport);
    }catch(Exception $e){
        tipMsg('数据库连接错误，请检查！',1);
        exit(header('HTTP/1.0 500 Internal Server Error'));
    }
    // 查询数据库是否存在
    $res = $pdo->query("show databases like '$dbname'");
    if (empty($res->fetchAll())) {
        if(!$pdo->exec("CREATE DATABASE `$dbname`")){
            tipMsg("创建数据库失败，请检查权限或联系管理员！",1);
            exit(header('HTTP/1.0 500 Internal Server Error'));
        }
    }
    // 指定操作目标库
    $pdo->query("USE `$dbname`");
    // 清空全部表 或 表重名检查
    if($overwrite){
        $tables_install = [
            $dbpre.'system_area',
            $dbpre.'system_category',
            $dbpre.'system_dict',
            $dbpre.'system_dict_group',
            $dbpre.'system_login_log',
            $dbpre.'system_manager',
            $dbpre.'system_manager_log',
            $dbpre.'system_menus',
            $dbpre.'system_online',
            $dbpre.'system_organ',
            $dbpre.'system_roles',
            $dbpre.'system_sequence',
            $dbpre.'system_setting',
            $dbpre.'system_sms',
            $dbpre.'system_upload_file',
            $dbpre.'system_upload_group',
            $dbpre.'system_web_log',
        ];
        $tables_tips = '';
        $tables = $pdo->query("show tables")->fetchAll();
        foreach ($tables as $table) {
            $table = current($table);
            if ($overwrite == 1) {
                $pdo->exec("DROP TABLE `$table`");
            } elseif ($overwrite == 2 && in_array($table, $tables_install)) {
                $tables_tips .= '<p>数据表【'.$table.'】</p>';
            }
        }
        if ($tables_tips) {
            tipMsg("<p>数据库【{$dbname}】中以下表已经存在</p>". $tables_tips. "<p>如需覆盖请选择 覆盖重名表 或 清空全部表！</p>");
            exit(header('HTTP/1.0 500 Internal Server Error'));
        }
    }
    // 更新app配置
    $config_str = getConfigs($adminmap);
    $fp = fopen(ROOT_DIR . '/config/app.php', 'w');
    fwrite($fp, $config_str);
    fclose($fp);
    // 更新veitool配置
    $keys = getRSAKey();
    $keys['access_secret_key']  = md5(uniqid());
    $keys['refresh_secret_key'] = md5(uniqid());
    $keys['domain'] = $_SERVER['HTTP_HOST'];
    $veitool_str = getVeitool($keys);
    $fp = fopen(ROOT_DIR . '/config/veitool.php', 'w');
    fwrite($fp, $veitool_str);
    fclose($fp);
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

    // 设置无缓冲输出
    header('X-Accel-Buffering: no');
    ob_implicit_flush(true);

    tipMsg("数据库连接文件创建完成！");
    ob_flush();
    usleep(100000);

    /*--安装数据解析导入处理--*/
    $sql  = '';
    $flag = $comment = false;
    $data = file_get_contents(INSTALL_PATH . '/data/install_data.sql');
    $data = explode("\n", trim(str_replace(["\r\n", "\r", '`vt_'], ["\n", "\n", '`'.$dbpre], $data)));
    foreach ($data as $line) {
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
        if ($line == 'BEGIN;' || $line == 'COMMIT;') {
            continue;
        }
        $sql .= $line."\n";
        $tmp  = trim($sql);
        $exec = '';
        if($flag || preg_match('/DELIMITER;;$/', $tmp)){
            if(preg_match('/;;DELIMITER;$/', $tmp)){
                $flag = false;
                $sql = str_replace(['DELIMITER;;','DELIMITER;',';;'],['','',''], $sql);
                //$pdo->exec("set global log_bin_trust_function_creators=1;");
                $exec = $sql;
                $sql = '';
            }else{
                $flag = true;
            }
        }elseif(preg_match('/.*;$/', $tmp)){
            $exec = $sql;
            $sql = '';
        }
        if ($exec) {
            $pdo->exec(trim($exec));
            if($txt = strstr($exec,'COMMENT=')){
                $txt = str_replace(['COMMENT=','\'',';',"\n"],'',$txt);
                tipMsg("创建【{$txt}】表完成！");
                ob_flush();
                usleep(100000);
            }
        }
    }/*--END--*/

    tipMsg("系统初始数据导入完成！");
    ob_flush();
    usleep(100000);

    // 更新管理员信息
    include (ROOT_DIR . '/app/common.php');
    $passsalt = random(8);
    $adminpass = set_password($adminpass,$passsalt);
    $pdo->exec("UPDATE {$dbpre}system_manager SET `username` ='{$adminuser}',`password`='{$adminpass}',`passsalt`='{$passsalt}' WHERE userid = 1");

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
    $dbhost = trim($_GET['dbhost'] ?? '');
    $dbport = trim($_GET['dbport'] ?? '');
    $dbuser = trim($_GET['dbuser'] ?? '');
    $dbpwd  = trim($_GET['dbpwd'] ?? '');
    try{
        getPDO($dbhost, $dbuser, $dbpwd, $dbport);
        exit('true');
    }catch(Exception $e){
        exit('false');
    }
}

/**
* 获取pdo连接
* @param string $host     数据库地址
* @param string $username 数据库账号
* @param string $password 数据库密码
* @param string $port     数据库端口
* @param string $database 数据库名称
* @return PDO
*/
function getPDO($host, $username, $password, $port, $database = null)
{
   $dsn = "mysql:host={$host};port={$port};".($database ? "dbname={$database}" : "");
   $params = [
       PDO::MYSQL_ATTR_INIT_COMMAND => "set names utf8mb4",
       PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
       PDO::ATTR_EMULATE_PREPARES => false,
       PDO::ATTR_TIMEOUT => 5,
       PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
   ];
   return new PDO($dsn, $username, $password, $params);
}

/**
 * 设置步骤全局状态 $isOK 的值
 * @global bool $isOK
 * @param  bool $val
 */
function setOk(bool $val)
{
    global $isOK;
    $isOK = $val;
}

/**
 * 测试可写性
 * @param  string $path 路径
 * @param  int    $p    权限值
 * @return string
 */
function isWrite(string $path, int $p = 0)
{
    if (!@file_exists(ROOT_DIR . $path)) {
        $perms = 0;
    } else {
        $perms = (int)substr(sprintf('%o', @fileperms(ROOT_DIR . $path)), -3);
    }
    if ($perms >= $p) {
        echo '<b class="green">符合('.$perms.')</b>';
    } else {
        echo '<span>不符合('.$perms.')</span>';
        setOk(false);
    }
}

/**
 * 测试函数是否存在
 * @param  string  $func  函数名
 * @return bool
 */
function isFunExists(string $func)
{
    $state = function_exists($func);
    if($state === false){
        setOk(false);
    }
    return $state;
}

/**
 * 测试函数是否存在
 * @param  string  $func  函数名
 * @return string
 */
function isFunExistsTxt(string $func)
{
    if(isFunExists($func)){
        echo '<b class="layui-icon green">&#xe697;</b>';
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
 * @return string
 */
function tipMsg(string $str, int $err = 0)
{
    echo $err ? '<p class="red">'. $str .'</p>' : '<p>'. $str .'</p>';
}

/**
 * 生成 RSA 密钥对
 * @param  int $bits 密钥长度，默认 2048（推荐 2048 或 4096）
 * @return array ['private' => string, 'public' => string] PEM 格式
 * @throws Exception 生成失败时抛出异常
 */
function getRSAKey(int $bits = 2048): array
{
    try {
        $flag = PHP_OS_FAMILY === 'Windows';
        $config = [
            "private_key_bits" => $bits,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ];
        if ($flag) $config['config'] = "nul";
        $res = openssl_pkey_new($config);
        $flag ? openssl_pkey_export($res, $privateKeyPem, null, $config) : openssl_pkey_export($res, $privateKeyPem);
        $publicKeyPem = openssl_pkey_get_details($res)['key'];
        return [
            'privateKeyPem' => $privateKeyPem,
            'publicKeyPem'  => $publicKeyPem
        ];
    } catch (Exception $e) {
        return [
            'privateKeyPem' => '',
            'publicKeyPem'  => ''
        ];
    }
}

/**
 * 获取config配置数据
 * @param  string  $map  映射地址
 * @return string
 */
function getConfigs(string $map)
{
    return <<<EOT
<?php
// +----------------------------------------------------------------------
// | 应用设置
// +----------------------------------------------------------------------

return [
    // 应用地址
    'app_host'         => '',
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
    'exception_tmpl'   => ROOT_PATH .(env('app_debug', true) ? 'app/v_err.tpl' : 'app/v_msg.tpl'),
    // 错误显示信息,非调试模式有效
    'error_message'    => '页面错误！请稍后再试～',
    // 显示错误信息
    'show_error_msg'   => false,
];
EOT;
}

/**
 * 获取config配置数据
 * @param  array  $keys  RSA密钥对、令牌密钥组、Domain
 * @return string
 */
function getVeitool(array $keys)
{
    return <<<EOT
<?php

return [
    //API接口地址 末尾不要加 /
    'api_url' => 'https://www.veitool.com',
    //插件强行卸载、覆盖 false 则会检查冲突文件
    'force'   => true,
    //插件是否备份有冲突的全局文件
    'back_up' => true,
    //是否删除插件原可动资源目录
    'clean'   => true,
    //是否允许未知来源的插件压缩包【当.env中APP_DEBUG = true 同时 unknown = true 时可安装未知来源插件。用于插件开发者调试】
    'unknown' => false,
    //插件卸载时是否删除相关数据表和配置
    'ddata'   => true,
    //插件应用路由
    'addons'  => [],
    //服务端解密私钥，左边不要有空格，百度“rsa密钥在线生成”，需2048位PKCS1格式
    'rsa_pri_key' => <<<EOF
{$keys['privateKeyPem']}EOF,
    //加密公钥：用作前端密钥 和 jwt密钥
    'rsa_pub_key' => <<<EOF
{$keys['publicKeyPem']}EOF,
    'jwt' => [
        'algorithms'         => 'HS256', /* 算法类型 HS256、HS384、HS512、RS256、RS384、RS512、ES256、ES384、ES512、PS256、PS384、PS512 */
        'access_secret_key'  => '{$keys['access_secret_key']}', /* access令牌秘钥 */
        'refresh_secret_key' => '{$keys['refresh_secret_key']}', /* refresh令牌秘钥 */
        'access_exp'         => 7200, /* access令牌过期时间，单位：秒。默认 2 小时 */
        'refresh_exp'        => 604800, /* refresh令牌过期时间，单位：秒。默认 7 天 */
        'refresh_off'        => false, /* refresh令牌是否禁用，默认不禁用 false */
        'iss'                => '{$keys['domain']}', /* 令牌签发者 */
        'nbf'                => 0, /* 某个时间点后才能访问，单位秒。（如：30 表示当前时间30秒后才能使用） */
        'leeway'             => 60, /* 时钟偏差冗余时间，单位秒。建议小于120 */
        'single_device_on'   => false, /* 是否允许单设备登录，默认不允许 false，开启需要有 Redis 支持*/
        'cache_token_ttl'    => 604800, /* 缓存令牌时间，单位：秒。默认 7 天 */
        'cache_token_a_pre'  => 'JWT:TOKEN:', /* 缓存令牌前缀，默认 JWT:TOKEN: */
        'cache_token_r_pre'  => 'JWT:REFRESH_TOKEN:', /* 缓存刷新令牌前缀，默认 JWT:REFRESH_TOKEN: */
        'get_token_on'       => false, /* 是否支持 get 请求获取令牌 */
        'get_token_key'      => 'authorization', /* GET 请求获取令牌请求key */
        //'user_model'       => function(\$userid){return [];}, /* 用户信息模型 */
    ],
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
CHARSET  = utf8mb4
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

// 引入公用函数库
include (ROOT_DIR . '/app/common.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Veitool快捷开发框架系统 安装向导</title>
<script type="text/javascript" src="<?=VT_DIR?>/static/layui/layui.js"></script>
<script type="text/javascript">var $=layui.$,jQuery=layui.jquery;</script>
<link href="<?=VT_DIR?>/static/layui/css/layui.css" type="text/css" rel="stylesheet">
<link href="tpl/style/install.css" type="text/css" rel="stylesheet"/>
</head>
<body>
<div class="header"></div>
<?php require (INSTALL_PATH . '/tpl/step_'.$s.'.php');?>
</body>
</html>
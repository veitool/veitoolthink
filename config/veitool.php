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
    'addons' => [],
    //服务端解密私钥，左边不要有空格，百度“rsa密钥在线生成”，需2048位PKCS1格式
    'rsa_pri_key' => <<<EOF
-----BEGIN PRIVATE KEY-----
...
-----END PRIVATE KEY-----
EOF,
    //加密公钥：用作前端密钥 和 jwt密钥
    'rsa_pub_key' => <<<EOF
-----BEGIN PUBLIC KEY-----
...
-----END PUBLIC KEY-----
EOF,
    'jwt' => [
        'algorithms'         => 'HS256', /* 算法类型 HS256、HS384、HS512、RS256、RS384、RS512、ES256、ES384、ES512、PS256、PS384、PS512 */
        'access_secret_key'  => '43dbd963dc420d17c59fe2642daedf1a', /* access令牌秘钥 */
        'refresh_secret_key' => 'e855cea3a1036debb12b7f7e1951938f', /* refresh令牌秘钥 */
        'access_exp'         => 7200, /* access令牌过期时间，单位：秒。默认 2 小时 */
        'refresh_exp'        => 604800, /* refresh令牌过期时间，单位：秒。默认 7 天 */
        'refresh_off'        => false, /* refresh令牌是否禁用，默认不禁用 false */
        'iss'                => 'www.veitool.com', /* 令牌签发者 */
        'nbf'                => 0, /* 某个时间点后才能访问，单位秒。（如：30 表示当前时间30秒后才能使用） */
        'leeway'             => 60, /* 时钟偏差冗余时间，单位秒。建议小于120 */
        'single_device_on'   => false, /* 是否允许单设备登录，默认不允许 false，开启需要有 Redis 支持*/
        'cache_token_ttl'    => 604800, /* 缓存令牌时间，单位：秒。默认 7 天 */
        'cache_token_a_pre'  => 'JWT:TOKEN:', /* 缓存令牌前缀，默认 JWT:TOKEN: */
        'cache_token_r_pre'  => 'JWT:REFRESH_TOKEN:', /* 缓存刷新令牌前缀，默认 JWT:REFRESH_TOKEN: */
        'get_token_on'       => false, /* 是否支持 get 请求获取令牌 */
        'get_token_key'      => 'authorization', /* GET 请求获取令牌请求key */
        //'user_model'       => function($userid){return ;}, /* 用户信息模型 */
    ],
];
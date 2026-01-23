<?php
/**
 * ===========================================================================
 * Veitool 快捷开发框架系统
 * Author: Niaho 26843818@qq.com
 * Copyright (c)2019-2026 www.veitool.com All rights reserved.
 * Licensed: 这不是一个自由软件，不允许对程序代码以任何形式任何目的的再发行
 * ---------------------------------------------------------------------------
 */
declare(strict_types=1);
namespace jwt;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\SignatureInvalidException;
use Exception;
use RuntimeException;

class JwtToken
{
    /** WEB Client */
    public const TOKEN_CLIENT_WEB = 'WEB';

    /**
     * 生成令牌
     * @param  array $extend 扩展参数
     * @return array
     * @throws RuntimeException
     */
    public static function generateToken(array $extend): array
    {
        if (!isset($extend['id'])) {
            throw new RuntimeException('扩展缺少关键参数:id');
        }
        $config = self::getConfig();
        $config['access_exp']  = $extend['access_exp'] ?? $config['access_exp'];
        $config['refresh_exp'] = $extend['refresh_exp'] ?? $config['refresh_exp'];
        // 获取加密载体
        $payload = self::getPayload($config, $extend);
        $secrets = self::getPrivateKey($config);
        // 生成 access_token
        $token = [
            'token_type'   => 'Bearer',
            'expires_in'   => $config['access_exp'],
            'access_token' => self::makeToken($payload['accessPayload'], $secrets['accessKey'], $config['algorithms'])
        ];
        // 生成 refresh_token
        if (!isset($config['refresh_off']) || ($config['refresh_off'] === false)) {
            $token['refresh_token'] = self::makeToken($payload['refreshPayload'], $secrets['refreshKey'], $config['algorithms']);
        }
        // 缓存记录：用于开启单设备登录时的参照
        if ($config['single_device_on']) {
            self::saveStoreToken($config, $extend, $token);
        }
        return $token;
    }

    /**
     * 刷新令牌
     * @param  array $_extend 现有拓展数据
     * @return array|string
     * @throws RuntimeException
     */
    public static function refreshToken(array &$_extend = []): array
    {
        $config  = self::getConfig();
        $extend  = self::verify(2, '', $config);
        $_extend = $extend['extend']; //更新外部传入扩展指针
        // 获取加密载体
        $extend['extend']['access_exp'] = $config['access_exp'];
        $payload = self::getPayload($config, $extend['extend']);
        $secrets = self::getPrivateKey($config);
        // 修正参数 计算 refresh_token 所剩有效时长
        if (($times = $extend['exp'] - time()) > 0) {
            $payload['refreshPayload']['exp'] = $extend['exp'];
            $payload['refreshPayload']['extend']['access_exp'] = $times;
            $config['refresh_exp'] = $times;
        }
        // 生成 access_token
        $newToken['access_token'] = self::makeToken($payload['accessPayload'], $secrets['accessKey'], $config['algorithms']);
        // 生成 refresh_token
        if (!isset($config['refresh_off']) || ($config['refresh_off'] === false)) {
            $newToken['refresh_token'] = self::makeToken($payload['refreshPayload'], $secrets['refreshKey'], $config['algorithms']);
        }
        // 缓存记录：用于开启单设备登录时的参照
        if ($config['single_device_on']) {
            self::saveStoreToken($config, $extend, $newToken);
        }
        return $newToken;
    }

    /**
     * 校验令牌 错误码如下：
     * 401000：请求未携带Bearer方式的authorization信息
     * 401001：非法的authorization信息
     * 401011：身份令牌未生效，请稍后再试
     * 401012：身份令牌已过期，请重新登录
     * 401013：身份令牌无效
     * 401014：身份令牌错误
     * 401015：身份令牌扩展字段不存在
     * 401016：该帐号已在其他设备登录，强制下线
     * 401017：Redis操作失败相关
     * @param  string  $token      Token
     * @param  int     $tokenType  验证类型：1.access_token, 2.refresh_token
     * @param  array   $config     配置参数
     * @return array
     */
    public static function verify(int $tokenType = 1, string $token = '', array $config = []): array
    {
        $config = $config ?: self::getConfig();
        $token  = $token ?: self::getTokenFromHeaders($config);
        $pubKey = self::getPublicKey($config, $tokenType);
        try {
            JWT::$leeway = $config['leeway'];
            $decoded = JWT::decode($token, new Key($pubKey, $config['algorithms']));
        } catch (BeforeValidException $b) {
            throw new RuntimeException('身份令牌未生效，请稍后再试', 401011);
        } catch (ExpiredException $e) {
            throw new RuntimeException('身份令牌已过期，请重新登录', 401012);
        } catch (SignatureInvalidException $e) {
            throw new RuntimeException('身份令牌无效', 401013);
        } catch (Exception $e) {
            throw new RuntimeException('令牌错误：'. $e->getMessage(), 401014);
        }
        if (!isset($decoded->extend) || empty($decoded->extend)) {
            throw new RuntimeException('身份令牌扩展字段不存在', 401015);
        }
        $decodeToken = json_decode(json_encode($decoded), true);
        if ($config['single_device_on']) {
            $cacheTokenPre = $tokenType == 1 ? $config['cache_token_a_pre'] : $config['cache_token_r_pre'];
            $client = $decodeToken['extend']['client'] ?? self::TOKEN_CLIENT_WEB;
            StoreToken::verifyToken($cacheTokenPre, $client, (string)$decodeToken['extend']['id'], $token);
        }
        return $decodeToken;
    }

    /**
     * 处理单设备登录的 Redis 操作
     * @param  array $config 配置文件
     * @param  array $extend 扩展数据
     * @param  array $tokens 令牌数组
     * @return void
     * @throws RuntimeException
     */
    private static function saveStoreToken(array $config, array $extend, array $tokens): void
    {
        $client = $extend['extend']['client'] ?? $extend['client'] ?? self::TOKEN_CLIENT_WEB;
        $uid = (string)($extend['extend']['id'] ?? $extend['id']);
        try {
            StoreToken::saveToken($config['cache_token_a_pre'], $client, $uid, $config['access_exp'], $tokens['access_token']);
            if ((!isset($config['refresh_off']) || ($config['refresh_off'] === false)) && isset($config["cache_token_r_pre"]) && isset($tokens['refresh_token'])) {
                StoreToken::saveToken($config["cache_token_r_pre"], $client, $uid, $config['refresh_exp'], $tokens['refresh_token']);
            }
        } catch (RuntimeException $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * 生成令牌
     * @param  array  $payload    载荷信息
     * @param  string $secretKey  签名key
     * @param  string $algorithms 算法
     * @return string
     */
    private static function makeToken(array $payload, string $secretKey, string $algorithms): string
    {
        return JWT::encode($payload, $secretKey, $algorithms);
    }

    /**
     * 注销令牌
     * @param  string $client 终端标识
     * @return bool
     */
    public static function clear(string $client = self::TOKEN_CLIENT_WEB): bool
    {
        $config = self::getConfig();
        if ($config['single_device_on']) {
            $currentId = (string)self::getExtend('id');
            $clearR = StoreToken::clearToken($config['cache_token_r_pre'], $client, $currentId);
            $clearA = StoreToken::clearToken($config['cache_token_a_pre'], $client, $currentId);
            return  $clearA && $clearR;
        }
        return true;
    }

    /**
     * 获取当前用户信息
     * @return array|object 用户信息数组或对象
     */
    public static function getUser(string $id = 'id')
    {
        $config = self::getConfig();
        if (isset($config['user_model']) && is_callable($config['user_model'])) {
            return $config['user_model'](self::getExtend($id));
        }
        return [];
    }

    /**
     * 获取指定令牌扩展 或 指定字段的值
     * @param  string  $key 扩展字段名
     * @return mixed  字段值
     * @throws RuntimeException
     */
    public static function getExtend(string $key = ''): mixed
    {
        return $key ? (self::getTokenExtend()[$key] ?? '') : self::getTokenExtend();
    }

    /**
     * 获取令牌有效期剩余时长
     * @param  int $tokenType Token类型：1.access_token, 2.refresh_token
     * @return int
     */
    public static function getTokenExp(int $tokenType = 1): int
    {
        return (int)self::verify($tokenType)['exp'] - time();
    }

    /**
     * 获取扩展字段
     * @return array
     * @throws RuntimeException
     */
    private static function getTokenExtend(): array
    {
        return (array)self::verify()['extend'];
    }

    /**
     * 获取 Header[authorization] / get 令牌
     * @param  array  $config  配置参数
     * @return string
     * @throws RuntimeException
     */
    private static function getTokenFromHeaders(array $config = []): string
    {
        $token = explode(' ', (string)request()->header('authorization'));
        if (!isset($token[1])) {
            if ((!$token[0] || 'undefined' == $token[0])) {
                $config = $config ?: self::getConfig();
                if (!isset($config['get_token_on']) || false === $config['get_token_on']) {
                    throw new RuntimeException('请求未携带Bearer方式的authorization信息',401000);
                }
                $token[1] = request()->get($config['get_token_key']);
                if (empty($token[1])) {
                    throw new RuntimeException('请求未携带Bearer方式的authorization信息',401000);
                }
            } else {
                throw new RuntimeException('非法的authorization信息',401001);
            }
        } elseif ($token[0] !== 'Bearer') {
            throw new RuntimeException('请求未携带Bearer方式的authorization信息',401000);
        }
        if (substr_count($token[1], '.') != 2) {
            throw new RuntimeException('非法的authorization信息',401001);
        }
        return $token[1];
    }

    /**
     * 获取加密载体
     * @param  array $config 配置参数
     * @param  array $extend 扩展加密字段
     * @return array
     */
    private static function getPayload(array $config, array $extend): array
    {
        $time = time();
        $basePayload = [
            'iss'    => $config['iss'], // 签发者
            'aud'    => $config['iss'], // 接收该JWT的一方
            'iat'    => $time,          // 签发时间
            'nbf'    => $time + ($config['nbf'] ?? 0), // 某个时间点后才能访问
            'exp'    => $time + $config['access_exp'], // 过期时间
            'extend' => $extend // 扩展信息
        ];
        $resPayLoad['accessPayload']         = $basePayload;
        $basePayload['exp']                  = $time + $config['refresh_exp'];
        $basePayload['extend']['access_exp'] = $config['refresh_exp'];
      //$basePayload['jti']                  = bin2hex(random_bytes(32));
        $resPayLoad['refreshPayload']        = $basePayload;
        return $resPayLoad;
    }

    /**
     * 按签名算法获取 [RSA]公钥
     * @param  array  $config    算法
     * @param  int    $tokenType Token类型：1.access_token, 2.refresh_token
     * @return string
     */
    private static function getPublicKey(array $config, int $tokenType = 1): string
    {
        if (in_array($config['algorithms'], ['HS512', 'HS384', 'HS256'], true)) {
            return $tokenType === 1 ? $config['access_secret_key'] : $config['refresh_secret_key']; //普通secret
        }
        return $config['rsa_pub_key']; //RSA公钥
    }

    /**
     * 按签名算法获取普通 或 RSA 私钥
     * @param  array  $config  配置文件
     * @return array  ['accessKey'=>'?',refreshKey=>'?']
     */
    private static function getPrivateKey(array $config): array
    {
        return in_array($config['algorithms'], ['HS512', 'HS384', 'HS256'], true) ?
               [
                    'accessKey'  => $config['access_secret_key'],
                    'refreshKey' => $config['refresh_secret_key'],
               ] : [
                    'accessKey'  => $config['rsa_pri_key'],
                    'refreshKey' => $config['rsa_pri_key'],
               ];
    }

    /**
     * 获取配置参数
     * @return array
     * @throws RuntimeException
     */
    private static function getConfig(): array
    {
        $config = config('veitool');
        if (empty($config)) {
            throw new RuntimeException('jwt配置文件不存在');
        }
        $config['jwt']['rsa_pri_key'] = $config['rsa_pri_key'];
        $config['jwt']['rsa_pub_key'] = $config['rsa_pub_key'];
        return $config['jwt'];
    }

}
<?php
/**
 * ===========================================================================
 * Veitool 快捷开发框架系统
 * Author: Niaho 26843818@qq.com
 * Copyright (c)2019-2025 www.veitool.com All rights reserved.
 * Licensed: 这不是一个自由软件，不允许对程序代码以任何形式任何目的的再发行
 * ---------------------------------------------------------------------------
 */
declare(strict_types=1);
namespace jwt;

use think\facade\Cache;
use RuntimeException;

class StoreToken
{
    /**
     * 创建/刷新 缓存令牌
     * @param  string $pre    缓存键前缀
     * @param  string $client 终端标识
     * @param  string $uid    UID
     * @param  int    $ttl    令牌有效期时长（秒）
     * @param  string $token  Token
     * @throws RuntimeException
     */
    public static function saveToken(string $pre, string $client, string $uid, int $ttl, string $token)
    {
        if ($ttl > 0 && $token) {
            $cacheKey = self::generateKey($pre, $client, $uid); // 获取缓存键名
            try {
                Cache::store('redis')->set($cacheKey, $token, $ttl + (int)config('veitool.jwt.leeway', 0)); // 创建新 token 缓存
            } catch (RuntimeException $e) {
                throw new RuntimeException('Redis连接失败:' . $e->getMessage(), 401017);
            }
        } else {
            throw new RuntimeException('缓存Token时参数错误', 401017);
        }
    }

    /**
     * 检查设备缓存令牌
     * @param  string $pre     缓存键前缀
     * @param  string $client  终端标识
     * @param  string $uid     UID
     * @param  string $token   Token
     * @return bool
     * @throws RuntimeException
     */
    public static function verifyToken(string $pre, string $client, string $uid, string $token): bool
    {
        if ($token) {
            $cacheKey = self::generateKey($pre, $client, $uid); // 获取缓存键名
            try {
                $ttl = Cache::store('redis')->handler()->ttl($cacheKey); // 获取底层 Redis 并拿到 $ttl
            } catch (RuntimeException $e) {
                throw new RuntimeException('Redis连接失败:'. $e->getMessage(), 401017);
            }
            if ($ttl === -2 || $ttl === 0) { // -2键不存在 或 0已经过期
                throw new RuntimeException('该帐号身份已过期，请重新登录.', 401012);
            } elseif (Cache::store('redis')->get($cacheKey) != $token) {
                throw new RuntimeException('该帐号已在其他设备登录，强制下线.', 401016);
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * 清理缓存令牌
     * @param  string $pre     缓存键前缀
     * @param  string $client  终端标识
     * @param  string $uid     UID
     * @return bool
     * @throws RuntimeException
     */
    public static function clearToken(string $pre, string $client, string $uid): bool
    {
        $cacheKey = self::generateKey($pre, $client, $uid); // 获取缓存键名
        try {
            $res = Cache::store('redis')->delete($cacheKey); // 删除 token 缓存
        } catch (RuntimeException $e) {
            throw new RuntimeException('Redis清理令牌失败:' . $e->getMessage(), 401017);
        }
        if ($res === false) {
            throw new RuntimeException('Redis清理令牌失败', 401017);
        }
        return true;
    }

    /**
     * 生成缓存键名
     * @param  string $pre 键前缀
     * @param  string $client 客户端类型
     * @param  string $uid 用户ID
     * @return string 完整的缓存键名
     */
    private static function generateKey(string $pre, string $client, string $uid): string
    {
        return sprintf('%s%s:%s', $pre, $client, $uid);
    }

}
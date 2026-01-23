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
namespace tool;

/**
 * 数据加解密相关
 * DataEncryptor::rsaDecrypt($data) rsa解密
 * DataEncryptor::aesEncrypt($data, $key, $iv) aes加密
 * DataEncryptor::aesDecrypt($data, $key, $iv) aes解密
 * */
class DataEncryptor
{
    /**
     * rsa解密
     * @param  string $data 解密的数据
     * @return string
     */
    public static function rsaDecrypt(?string $data) : string
    {
        if (! $data) {
            throw new \Exception('RSA解密数据不能为空');
        }
        $private_key = config('veitool.rsa_pri_key');
        if (! $private_key) {
            throw new \Exception('未设置解密私钥');
        }
        try {
            $private_key = openssl_pkey_get_private($private_key);
            if (! $private_key) {
                throw new \Exception('密钥错误~');
            }
            $return_de = openssl_private_decrypt(base64_decode($data), $decrypted, $private_key);
            if (! $return_de) {
                throw new \Exception('RSA解密失败，请检查密钥~');
            }
            return $decrypted;
        } catch (\Exception $e) {
            throw new \Exception("RSA解密：{$e->getMessage()}");
        }
    }

    /**
     * aes加密
     * @param  array|string|object $data 加密对象数据
     * @param  string $key 加密key
     * @param  string $iv 加密iv
     * @return string
     */
    public static function aesEncrypt($data, string $key, string $iv) : string
    {
        try {
            $data = json_encode($data);
            $data = openssl_encrypt($data, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
            return base64_encode($data);
        } catch (\Exception $e) {
            throw new \Exception("AES加密失败：{$e->getMessage()}");
        }
    }

    /**
     * aes解密
     * @param  string $data 加密数据对象
     * @param  string $key 解密key
     * @param  string $v 解密iv
     * @return array
     */
    public static function aesDecrypt(?string $data, string $key, string $iv) : array
    {
        if (! $data) {
            throw new \Exception('AES解密数据不能为空');
        }
        try {
            $data = base64_decode($data);
            $data = openssl_decrypt($data, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
            $data = json_decode($data, true);
            if (! is_array($data)) {
                throw new \Exception('解密失败');
            }
            return self::formatJsonToArray($data);
        } catch (\Exception $e) {
            throw new \Exception("AES解密失败：{$e->getMessage()}");
        }
    }

    /**
     * 含标号的同名键归到同一子数组中
     * @param  array  $arr  数据对象
     * @return array
     */
    public static function formatJsonToArray(array $arr) : array
    {
        foreach ($arr as $key => $value) {
            if (preg_match('/^(\w+)\[(\d+)\]$/', $key, $match)) {
                $arr[$match[1]][] = $value;
                unset($arr[$key]);
            }
        }
        return $arr;
    }

}
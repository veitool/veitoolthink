<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2017 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace think;

use think\cache\Driver;

/**
 * @method static get($name, $default = false)
 * @method static set($name, $value, $expire = null)
 * @method static has($name)
 * @method static inc($name, $step = 1)
 * @method static dec($name, $step = 1)
 * @method static rm($name)
 * @method static clear($tag = null)
 * @method static pull($name)
 * @method static remember($name, $value, $expire = null)
 * @method static handler()
 */
class Cache
{
    /**
     * 缓存实例
     * @var array
     */
    protected static $instance = [];

    /**
     * 操作句柄
     * @var object
     */
    protected static $handler;

    /**
     * 连接缓存
     * @access public
     * @param  array         $options  配置数组
     * @param  bool|string   $name 缓存连接标识 true 强制重新连接
     * @return Driver
     */
    public static function connect(array $options = [], $name = false)
    {
        $type = !empty($options['type']) ? $options['type'] : 'File';

        if (false === $name) {
            $name = md5(serialize($options));
        }

        if (true === $name || !isset(self::$instance[$name])) {
            $class = false !== strpos($type, '\\') ? $type : '\\think\\cache\\driver\\' . ucwords($type);

            if (true === $name) {
                $name = md5(serialize($options));
            }

            self::$instance[$name] = new $class($options);
        }

        return self::$instance[$name];
    }

    /**
     * 自动初始化缓存
     * @access public
     * @param  array         $options  配置数组
     * @return Driver
     */
    public static function init(array $options = [])
    {
        if (is_null(self::$handler)) {
            // 自动初始化缓存
            self::$handler = self::connect($options);
        }

        return self::$handler;
    }

    public static function __callStatic($method, $args)
    {
        return call_user_func_array([self::init(), $method], $args);
    }

}

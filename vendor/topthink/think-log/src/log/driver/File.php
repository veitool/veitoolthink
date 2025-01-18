<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace think\log\driver;

/**
 * 本地化调试输出到文件
 */
class File
{
    protected $config = [
        'time_format' => ' c ',
        'single'      => false,
        'file_size'   => 2097152,
        'path'        => '',
        'apart_level' => [],
    ];

    protected $writed = [];

    // 实例化并传入参数
    public function __construct($config = [])
    {
        if (is_array($config)) {
            $this->config = array_merge($this->config, $config);
        }
    }

    /**
     * 日志写入接口
     * @access public
     * @param  array $log 日志信息
     * @return bool
     */
    public function save(array $log = [])
    {
        if ($this->config['single']) {
            $destination = $this->config['path'] . 'single.log';
        } else {
            $cli         = PHP_SAPI == 'cli' ? '_cli' : '';
            $destination = $this->config['path'] . date('Ym') . '/' . date('d') . $cli . '.log';
        }

        $path = dirname($destination);
        !is_dir($path) && mkdir($path, 0755, true);

        $info = '';
        foreach ($log as $type => $val) {
            $level = '';
            foreach ($val as $msg) {
                if (!is_string($msg)) {
                    $msg = var_export($msg, true);
                }
                $level .= '[ ' . $type . ' ] ' . $msg . "\r\n";
            }

            if (in_array($type, $this->config['apart_level'])) {
                // 独立记录的日志级别
                if ($this->config['single']) {
                    $filename = $path . '/' . $type . '.log';
                } else {
                    $filename = $path . '/' . date('d') . '_' . $type . $cli . '.log';
                }

                $this->write($level, $filename, true);
            } else {
                $info .= $level;
            }
        }

        if ($info) {
            return $this->write($info, $destination);
        }

        return true;
    }

    /**
     * 日志写入
     * @access protected
     * @param  array     $message 日志信息
     * @param  string    $destination 日志文件
     * @param  bool      $apart 是否独立文件写入
     * @return bool
     */
    protected function write($message, $destination, $apart = false)
    {
        // 检测日志文件大小，超过配置大小则备份日志文件重新生成
        if (is_file($destination) && floor($this->config['file_size']) <= filesize($destination)) {
            rename($destination, dirname($destination) . '/' . time() . '-' . basename($destination));
            $this->writed[$destination] = false;
        }

        if (empty($this->writed[$destination]) && PHP_SAPI != 'cli') {
            if ($this->config['debug'] && !$apart) {
                // 获取基本信息
                $current_uri = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                $message     = '[ info ] ' . $current_uri . "\r\n" . $message;
            }

            $now     = date($this->config['time_format']);
            $ip      = $_SERVER['REMOTE_ADDR'];
            $method  = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'CLI';
            $uri     = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
            $message = "---------------------------------------------------------------\r\n[{$now}] {$ip} {$method} {$uri}\r\n" . $message;

            $this->writed[$destination] = true;
        }

        if (PHP_SAPI == 'cli') {
            $now     = date($this->config['time_format']);
            $message = "[{$now}]" . $message;
        }

        return error_log($message, 3, $destination);
    }

}

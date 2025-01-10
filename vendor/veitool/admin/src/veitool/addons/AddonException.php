<?php
/**
 * ===========================================================================
 * Veitool 快捷开发框架系统
 * Author: Niaho 26843818@qq.com
 * Copyright (c)2019-2025 www.veitool.com All rights reserved.
 * Licensed: 这不是一个自由软件，不允许对程序代码以任何形式任何目的的再发行
 * ---------------------------------------------------------------------------
 */
namespace veitool\addons;

use think\Exception;

/**
 * 插件异常处理类
 * @package think\addons
 */
class AddonException extends Exception
{

    /**
     * 构造函数
     * @param  string  $message  提示
     * @param  int     $code     编码
     * @param  string  $data     数据
     * @return
     */
    public function __construct($message, $code = 0, $data = '')
    {
        $this->message  = $message;
        $this->code     = $code;
        $this->data     = $data;
    }

}
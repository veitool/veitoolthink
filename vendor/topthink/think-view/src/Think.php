<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace think\view\driver;

use think\App;
use think\contract\TemplateHandlerInterface;
use think\helper\Str;
use think\Template;
use think\template\exception\TemplateNotFoundException;

class Think implements TemplateHandlerInterface
{
    // 模板引擎实例
    private $template;

    // 模板引擎参数
    protected $config = [
        // 默认模板渲染规则 1 解析为小写+下划线 2 全部转换小写 3 保持操作方法
        'auto_rule'     => 1,
        // 视图目录名
        'view_dir_name' => 'view',
        // 模板起始路径
        'view_path'     => '',
        // 模板文件后缀
        'view_suffix'   => 'html',
        // 模板文件名分隔符
        'view_depr'     => DIRECTORY_SEPARATOR,
        // 是否开启模板编译缓存,设为false则每次都会重新编译
        'tpl_cache'     => true,
    ];

    public function __construct(private App $app, array $config = [])
    {
        $this->config = array_merge($this->config, (array) $config);

        if (empty($this->config['cache_path'])) {
            $this->config['cache_path'] = $app->getRuntimePath() . 'temp' . DIRECTORY_SEPARATOR;
        }

        $this->template = new Template($this->config);
        $this->template->setCache($app->cache);
        $this->template->extend('$Think', function (array $vars) {
            $type  = strtoupper(trim(array_shift($vars)));
            $param = implode('.', $vars);

            return match ($type) {
                'CONST'     =>  strtoupper($param),
                'CONFIG'    =>  'config(\'' . $param . '\')',
                'LANG'      =>  'lang(\'' . $param . '\')',
                'NOW'       =>  "date('Y-m-d g:i a',time())",
                'LDELIM'    =>  '\'' . ltrim($this->getConfig('tpl_begin'), '\\') . '\'',
                'RDELIM'    =>  '\'' . ltrim($this->getConfig('tpl_end'), '\\') . '\'',
                default     =>  defined($type) ? $type : '\'\'',
            };
        });

        $this->template->extend('$Request', function (array $vars) {
            // 获取Request请求对象参数
            $method = array_shift($vars);
            if (!empty($vars)) {
                $params = implode('.', $vars);
                if ('true' != $params) {
                    $params = '\'' . $params . '\'';
                }
            } else {
                $params = '';
            }

            return 'app(\'request\')->' . $method . '(' . $params . ')';
        });
    }

    /**
     * 检测是否存在模板文件
     * @access public
     * @param  string $template 模板文件或者模板规则
     * @return bool
     */
    public function exists(string $template): bool
    {
        $template = $this->getTemplateFile($template);

        return is_file($template);
    }

    protected function getTemplateFile(string $template): string
    {
        if ('' == pathinfo($template, PATHINFO_EXTENSION)) {
            // 获取模板文件名
            $template = $this->parseTemplate($template);
        } else{
            $path = $this->config['view_path'] ?: $this->getViewPath($this->app->http->getName());
            if (!is_file($template)) {
                $template = $path . $template;
            }
            $this->template->view_path = $path;
        }

        return $template;
    }

    /**
     * 渲染模板文件
     * @access public
     * @param  string $template 模板文件
     * @param  array  $data 模板变量
     * @return void
     */
    public function fetch(string $template, array $data = []): void
    {
        $template = $this->getTemplateFile($template);

        // 模板不存在 抛出异常
        if (!is_file($template)) {
            throw new TemplateNotFoundException('template not exists:' . $template, $template);
        }

        $this->template->fetch($template, $data);
    }

    /**
     * 渲染模板内容
     * @access public
     * @param  string $template 模板内容
     * @param  array  $data 模板变量
     * @return void
     */
    public function display(string $template, array $data = []): void
    {
        $this->template->display($template, $data);
    }

    protected function getViewPath(string $app): string
    {
        $view  = $this->config['view_dir_name'] . DIRECTORY_SEPARATOR;
        $app   = $app ? str_replace('.', DIRECTORY_SEPARATOR, $app) . DIRECTORY_SEPARATOR : '';
        $paths = [
            $this->app->getBasePath() . $app . $view,
            $this->app->getBasePath() . $view . $app,
            $this->app->getRootPath() . $view . $app
        ];

        foreach ($paths as $path) {
            if (is_dir($path)) {
                return $path;
            }
        }

        return '';
    }

    /**
     * 自动定位模板文件
     * @access private
     * @param  string $template 模板文件规则
     * @return string
     */
    private function parseTemplate(string $template): string
    {
        // 分析模板文件规则
        $request = $this->app['request'];

        // 获取视图根目录
        if (strpos($template, '@')) {
            // 跨模块调用
            list($app, $template) = explode('@', $template);
        } elseif ($this->app->http->getName()) {
            $app = $this->app->http->getName();
        } elseif (method_exists($request, 'layer') && $request->layer()) {
            $app        = $request->layer();
            $controller = $request->controller(false, true);
        }

        if ($this->config['view_path']) {
            $path = $this->config['view_path'];
        } else {
            $path = $this->getViewPath($app ?? $this->app->http->getName());
            $this->template->view_path = $path;
        }

        if (isset($app)) {
            if ($this->config['view_path']) {
                $path = $this->config['view_path'];
            } else {
                $path = $this->getViewPath($app ?? $this->app->http->getName());
                $this->template->view_path = $path;
            }
        } else {
            $view = $this->config['view_dir_name'];

            if (is_dir($this->app->getAppPath() . $view)) {
                $path = $this->app->getAppPath() . $view . DIRECTORY_SEPARATOR;
            } else {
                $appName = $this->app->http->getName();
                $path    = $this->app->getRootPath() . $view . DIRECTORY_SEPARATOR . ($appName ? $appName . DIRECTORY_SEPARATOR : '');
            }

            $this->config['view_path'] = $path;
            $this->template->view_path = $path;
        }

        $depr = $this->config['view_depr'];

        if (0 !== strpos($template, '/')) {
            $template   = str_replace(['/', ':'], $depr, $template);
            $controller = $controller ?? $request->controller();

            if (strpos($controller, '.')) {
                $pos        = strrpos($controller, '.');
                $controller = substr($controller, 0, $pos) . '.' . Str::snake(substr($controller, $pos + 1));
            } else {
                $controller = Str::snake($controller);
            }

            if ($controller) {
                if ('' == $template) {
                    // 如果模板文件名为空 按照默认模板渲染规则定位
                    if (2 == $this->config['auto_rule']) {
                        $template = $request->action(true);
                    } elseif (3 == $this->config['auto_rule']) {
                        $template = $request->action();
                    } else {
                        $template = Str::snake($request->action());
                    }

                    $template = str_replace('.', DIRECTORY_SEPARATOR, $controller) . $depr . $template;
                } elseif (false === strpos($template, $depr)) {
                    $template = str_replace('.', DIRECTORY_SEPARATOR, $controller) . $depr . $template;
                }
            }
        } else {
            $template = str_replace(['/', ':'], $depr, substr($template, 1));
        }

        return $path . ltrim($template, '/') . '.' . ltrim($this->config['view_suffix'], '.');
    }

    /**
     * 配置模板引擎
     * @access private
     * @param  array  $config 参数
     * @return void
     */
    public function config(array $config): void
    {
        $this->template->config($config);
        $this->config = array_merge($this->config, $config);
    }

    /**
     * 获取模板引擎配置
     * @access public
     * @param  string  $name 参数名
     * @return void
     */
    public function getConfig(string $name)
    {
        return $this->template->getConfig($name);
    }

    public function __call($method, $params)
    {
        return call_user_func_array([$this->template, $method], $params);
    }
}

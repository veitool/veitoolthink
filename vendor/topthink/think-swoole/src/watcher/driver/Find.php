<?php

namespace think\swoole\watcher\driver;

use InvalidArgumentException;
use Swoole\Coroutine\System;
use Swoole\Timer;
use think\helper\Str;
use think\swoole\watcher\Driver;

class Find extends Driver
{
    protected $name;
    protected $directory;
    protected $exclude;
    protected $timer = null;

    public function __construct($config)
    {
        $ret = System::exec('which find');
        if (empty($ret['output'])) {
            throw new InvalidArgumentException('find not exists.');
        }
        $ret = System::exec('find --help', true);
        if (Str::contains($ret['output'] ?? '', 'BusyBox')) {
            throw new InvalidArgumentException('find version not support.');
        }

        $this->directory = $config['directory'];
        $this->exclude   = $config['exclude'];
        $this->name      = $config['name'];
    }

    public function watch(callable $callback)
    {
        $ms      = 2000;
        $seconds = ceil(($ms + 1000) / 1000);
        $minutes = sprintf('-%.2f', $seconds / 60);

        $dest = implode(' ', $this->directory);

        $name = empty($this->name) ? '' : ' \( ' . join(' -o ', array_map(fn($v) => "-name \"{$v}\"", $this->name)) . ' \)';
        $notName = '';
        $notPath = '';
        if (!empty($this->exclude)) {
            $excludeDirs = $excludeFiles = [];
            foreach ($this->exclude as $directory) {
                $directory = rtrim($directory, '/');
                if (is_dir($directory)) {
                    $excludeDirs[] = $directory;
                } else {
                    $excludeFiles[] = $directory;
                }
            }

            if (!empty($excludeFiles)) {
                $notPath = ' -not \( ' . join(' -and ', array_map(fn($v) => "-name \"{$v}\"", $excludeFiles)) . ' \)';
            }

            if (!empty($excludeDirs)) {
                $notPath = ' -not \( ' . join(' -and ', array_map(fn($v) => "-path \"{$v}/*\"", $excludeDirs)) . ' \)';
            }
        }

        $command = "find {$dest}{$name}{$notName}{$notPath} -mmin {$minutes} -type f -print";

        $this->timer = Timer::tick($ms, function () use ($callback, $command) {
            $ret = System::exec($command);
            if ($ret['code'] === 0 && strlen($ret['output'])) {
                $stdout = trim($ret['output']);
                if (!empty($stdout)) {
                    $files = array_filter(explode("\n", $stdout));
                    call_user_func($callback, $files);
                }
            }
        });
    }

    public function stop()
    {
        if ($this->timer) {
            Timer::clear($this->timer);
        }
    }

}

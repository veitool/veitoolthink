<?php
use think\facade\Route;

Route::get('static/:path', function (string $path) {
    $filename = public_path() .'static/'. $path;
    return new \think\swoole\response\File($filename);
})->pattern(['path' => '.*\.\w+$']);

Route::get('favicon.ico', function () {
    $filename = public_path() .'favicon.ico';
    return new \think\swoole\response\File($filename);
});
# think-log

写入LOG日志，支持文件及SocketLog。

安装
~~~
composer require topthink/think-log
~~~

用法：
~~~php
$log = new \think\Log;
$log->init([
	'type'=>'file',
	'path'=>'./runtime/logs/',
]);
$log->record('error info','error');
$log->error('error info');
$log->info('log info');
$log->save();
~~~


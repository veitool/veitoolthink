# think-cache

用于PHP缓存管理（PHP>5.6+）

安装
~~~
composer require topthink/think-cache
~~~

用法：
~~~php
use think\Cache;

// 缓存初始化
Cache::init([
	// 驱动方式（支持file/memcache/redis/xcache/wincache/sqlite）
	'type'   => 'File',
	// 缓存保存目录
	//'path'   => './cache/',
	// 缓存前缀
	'prefix' => '',
	// 缓存有效期 0表示永久缓存
	'expire' => 0,
]);
// 设置缓存
Cache::set('val','value',600);
// 判断缓存是否设置
Cache::has('val');
// 获取缓存
Cache::get('val');
// 删除缓存
Cache::rm('val');
// 清除缓存
Cache::clear();
// 读取并删除缓存
Cache::pull('val');
// 不存在则写入
Cache::remember('val','value');

// 对于数值类型的缓存数据可以使用
// 缓存增+1
Cache::inc('val');
// 缓存增+5
Cache::inc('val',5);
// 缓存减1
Cache::dec('val');
// 缓存减5
Cache::dec('val',5);

// 使用多种缓存类型
$redis = Cache::connect([
	// 驱动方式（支持file/memcache/redis/xcache/wincache/sqlite）
	'type'   => 'redis',
        'host'       => '127.0.0.1',
        'port'       => 6379,
	// 缓存前缀
	'prefix' => '',
	// 缓存有效期 0表示永久缓存
	'expire' => 0,
]);

$redis->set('var','value',600);
$redis->get('var');

// 或者使用
$redis->val = 'value';
echo $redis->val;
~~~

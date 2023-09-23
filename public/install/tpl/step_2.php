<div class="main">
    <div class="progress"><ul class="p-2"><li><span>许可协议</span></li><li><span>环境检测</span></li><li><span>设定配置</span></li><li><span>安装完成</span></li></ul></div>
    <div>
        <table class="layui-table" lay-even lay-skin="nob" lay-size="sm">
            <tr><th width="30%">项目</th><th width="30%">所需配置</th><th width="15%">推荐配置</th><th width="25%">当前服务器</th></tr>
            <tr><td>操作系统</td><td>不限制</td><td>Linux</td><td><?php echo PHP_OS; ?></td></tr>
            <tr><td>PHP 版本</td><td>8.1.0</td><td>8.1.0</td><td><?php echo PHP_VERSION; ?></td></tr>
            <tr><td>附件上传</td><td>2Mb</td><td>20Mb</td><td><?php echo get_cfg_var("upload_max_filesize") ? get_cfg_var("upload_max_filesize").'b' : '不允许上传附件'; ?></td></tr>
            <tr><td>GD 库</td><td>2.0</td><td>2.1</td><td>
            <?php
                $tmp = function_exists('gd_info') ? gd_info() : array();
                @$env_items[$key]['current'] = empty($tmp['GD Version']) ? 'noext' : $tmp['GD Version'];
                echo @$env_items[$key]['current'];
                unset($tmp);
            ?>
            </td>
            </tr>
            <tr><td>磁盘空间</td><td>100Mb</td><td>不限制</td><td>
            <?php
                if(function_exists('disk_free_space')){
                    @$env_items[$key]['current'] = floor(disk_free_space('../') / (1024 * 1024)) . 'Mb';
                }else{
                    $env_items[$key]['current'] = 'unknow';
                }
                echo @$env_items[$key]['current'];
            ?>
            </td>
            </tr>
        </table>
        <table class="layui-table" lay-even lay-skin="nob" lay-size="sm">
            <tr><th width="60%">扩展要求</th><th width="25%">检查结果</th><th>建议</th></tr>
            <?php foreach($extendArray as $item){?>
            <tr><td><?= $item['name'] ?></td><td><?= $item['status'] ? '支持' : '不支持' ?></td><td><?= $item['status'] ? '<b class="layui-icon green">&#xe697;</b>' : '<span>需安装</span>' ?></td></tr>
            <?php }?>
        </table>
        <table class="layui-table" lay-even lay-skin="nob" lay-size="sm">
            <tr><th width="60%">函数名称</th><th width="25%">检查结果</th><th>建议</th></tr>
            <?php foreach ($exists_array as $v){?>
            <tr><td><?php echo $v; ?>()</td><td><?= isFunExists($v) ? '支持' : '不支持' ?></td><td><?= isFunExistsTxt($v) ?></td></tr>
            <?php }?>
        </table>
        <table class="layui-table" lay-even lay-skin="nob" lay-size="sm">
            <tr><th width="60%">文件权限检测</th><th width="25%">所需状态</th><th>当前状态</th></tr>
            <?php foreach ($iswrite_array as $v){?>
            <tr><td><?php echo $v; ?></td><td>可写</td><td><?php isWrite($v); ?></td></tr>
            <?php }?>
        </table>
    </div>
</div>
<div class="footer">
    <span class="copyright"><?php echo $copyright;?></span>
    <span class="formBtn">
        <form class="iform" method="post" action="index.php?s=3">
            <a href="javascript:void(0);" onclick="history.go(-1);return false;" class="layui-btn layui-btn-primary">返 回</a>
            <a href="javascript:void(0);" class="layui-btn Btn">下一步</a>
            <input type="hidden" name="isOK" value="<?php echo $isOK;?>">
        </form>
	</span>
</div>
<script>
layui.use('layer',function(){
    var layer = layui.layer;
    var isOK = <?php echo $isOK ? 'true' : 'false';?>;
    // 表单提交
    $('.Btn').click(function(){
        if(isOK){
            $('.iform').submit();
        }else{
            layer.msg('环境检测未通过，请先修复',{icon:2,shade:[0.5,'#000'],time:2000,anim:6});
        }
    });
});
</script>
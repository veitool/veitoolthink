<form class="layui-form" action="?s=4">
    <div class="main">
        <div class="progress"><ul class="p-3"><li><span>许可协议</span></li><li><span>环境检测</span></li><li><span>设定配置</span></li><li><span>安装完成</span></li></ul></div>
        <div class="upform">
            <h2>数据库信息</h2>
            <div class="layui-form-item">
                <label class="layui-form-label"><font color="red">*</font> 数据库地址</label>
                <div class="layui-input-inline"><input type="text" class="layui-input" name="dbhost" id="dbhost" autocomplete="off" lay-verify="required" lay-reqtext="请输入数据库地址" value="127.0.0.1"/></div>
                <div class="layui-form-mid layui-word-aux">请输入数据库服务器地址，一般为：localhost</div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label"><font color="red">*</font> 数据库端口</label>
                <div class="layui-input-inline"><input type="text" class="layui-input" name="dbport" id="dbport" autocomplete="off" lay-verify="required" lay-reqtext="请输入数据库端口" value="3306"/></div>
                <div class="layui-form-mid layui-word-aux">请输入数据库端口号，一般为：3306</div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label"><font color="red">*</font> 数据库名称</label>
                <div class="layui-input-inline"><input type="text" class="layui-input" name="dbname" id="dbname" autocomplete="off" lay-verify="required" lay-reqtext="请输入数据库名称" value="veitool_db"/></div>
                <div class="layui-form-mid layui-word-aux">请输入数据库的名称，如果没有请先新增</div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label"><font color="red">*</font> 数据库账号</label>
                <div class="layui-input-inline"><input type="text" class="layui-input" name="dbuser" id="dbuser" autocomplete="off" lay-verify="required" lay-reqtext="请输入数据库账号" value="root"/></div>
                <div class="layui-form-mid layui-word-aux">请输入数据库账号，默认为：root</div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label"> 数据表前缀</label>
                <div class="layui-input-inline"><input type="text" class="layui-input" name="dbpre" id="dbpre" autocomplete="off" value="vt_"/></div>
                <div class="layui-form-mid layui-word-aux">请输入数据表前缀，默认为：vt_</div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label"><font color="red">*</font> 数据库密码</label>
                <div class="layui-input-inline"><input type="password" class="layui-input" name="dbpwd" id="dbpwd" autocomplete="off" lay-verify="required" lay-reqtext="请输入数据库密码" lay-affix="eye" value=""/></div>
                <div class="layui-form-mid layui-word-aux">请输入连接数据库账号的密码</div>
            </div>
            <h2>管理信息</h2>
            <div class="layui-form-item">
                <label class="layui-form-label"><font color="red">*</font> 后台的入口</label>
                <div class="layui-input-inline"><input type="text" class="layui-input" name="adminmap" id="adminmap" autocomplete="off" lay-verify="required" value="admin"/></div>
                <div class="layui-form-mid layui-word-aux">请输入后台管理入口地址：<?php echo $currentHost;?><span id="admin_map">admin</span></div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label"><font color="red">*</font> 管理员账号</label>
                <div class="layui-input-inline"><input type="text" class="layui-input" name="adminuser" autocomplete="off" lay-verify="required" value="admin"/></div>
                <div class="layui-form-mid layui-word-aux">请输入后台管理员的登录账号</div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label"><font color="red">*</font> 管理员密码</label>
                <div class="layui-input-inline"><input type="password" class="layui-input" name="adminpass" autocomplete="off" lay-verify="required" lay-affix="eye" value=""/></div>
                <div class="layui-form-mid layui-word-aux">请输入后台管理员的登录密码</div>
            </div>
        </div>
    </div>
    <div class="footer">
        <span class="copyright"><?php echo $copyright;?></span>
        <span class="formBtn">
            <a href="javascript:void(0);" onclick="history.go(-1);return false;" class="layui-btn layui-btn-primary">返 回</a>
            <a href="javascript:void(0);" class="layui-btn" lay-filter="install" lay-submit>开始安装</a>
        </span>
    </div>
</form>
<script>
layui.use(['form', 'layer'],function(){
    var form = layui.form, layer = layui.layer;
    /*检测数据库密码*/
    $("#dbpwd").on('blur',function(){
        var $this = $(this); if(!$this.val()) return;
        $.get("index.php",{s:6,dbhost:$("#dbhost").val(),dbport:$("#dbport").val(),dbuser:$("#dbuser").val(),dbpwd:$("#dbpwd").val()},function(data){
            if(data === 'false'){
                $this.addClass('layui-form-danger');
                layer.tips('数据库连接失败，请检查密码或其他是否正确！', $this, {tips:[1,'#ff5722'],maxWidth:'auto'});
            }else{
                layer.closeAll();
            }
        });
    })/**/
    /*后台入口变动提示*/
    $("#adminmap").bind("input propertychange",function(){
        $("#admin_map").html($(this).val());
    });/**/
    /*安装触发*/
    form.on('submit(install)',function(data){
        if($(this).hasClass('layui-btn-disabled')) return false;
        var d = data.field;
        var url = '?s=4&dbhost='+d.dbhost+'&dbname='+d.dbname+'&dbpre='+d.dbpre+'&dbuser='+d.dbuser+'&dbpwd='+d.dbpwd+'&dbport='+d.dbport+'&adminmap='+d.adminmap+'&adminuser='+d.adminuser+'&adminpass='+d.adminpass;
        layer.open({
            type: 1,
            area: ['500px', '300px'],
            title: '安装处理中，请勿关闭...',
            closeBtn: 1,
            content: '<div style="width:456px;margin:20px;color:#666;border:0;" id="install"></div>',
            success: function(){
                var xhr = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        layer.msg('安装成功',{icon:1,shade:0.2,time:3000},function(){top.location.href = '?s=5&adminmap='+ d.adminmap;});
                    }else if(xhr.readyState === 3){
                        $("#install").html(xhr.responseText);
                        var parent = $("#install").parent();
                        parent.scrollTop(parent[0].scrollHeight);
                    }
                };
                xhr.open('GET', url)
                xhr.send();
            }
        });
        return false;
    });/**/
});
</script>
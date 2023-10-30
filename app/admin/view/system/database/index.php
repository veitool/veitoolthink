<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-card-header">
            <div class="layui-btn-group">
                <a class="layui-btn" id="database-backup" v-show="@system.database/backup"><i class="layui-icon layui-icon-auz"></i> 备份数据</a>
                <a class="layui-btn" id="database-imports" v-show="@system.database/imports"><i class="layui-icon layui-icon-refresh"></i> 恢复数据</a>
                <a class="layui-btn" id="database-xiufu" v-show="@system.database/xiufu"><i class="layui-icon layui-icon-set"></i> 修复表</a>
                <a class="layui-btn" id="database-youhua" v-show="@system.database/youhua"><i class="layui-icon layui-icon-rate"></i> 优化表</a>
            </div>
            <div style="float:right;padding:10px 5px 0 0;">共<b> <?php echo $tables ?? '0';?></b> 张表 / <b><?php echo $totalsize ?? '0';?></b> Mb</div>
        </div>
        <div class="layui-card-body">
            <table lay-filter="database" id="database"></table>
        </div>
    </div>
</div>
<!--JS部分-->
<script type="text/javascript" src="{STATIC__PATH}script/md5.js"></script>
<script type="text/javascript">
layui.use(['buildItems'],function(){
    var app_root = layui.cache.maps + 'system.database/';
    var table=layui.table,admin=layui.admin,layer=layui.layer;
    /*数据表渲染*/
    table.render({
        elem: '#database',
        even: true,
        data: {$list|raw},
        cols: [[
            {type:"checkbox",fixed:"left"},
            {field:"name",title: "表名"},
            {field:"comment",edit:'text',title:"备注"},
            {field:"engine",align:'center',width:100,title:"类型"},
            {field:"data_length",align:'center',width:100,title:"数据(Mb)",sort:true},
            {field:"index_length",align:'center',width:100,title:"索引(Mb)",sort:true},
            {field:"data_total",align:'center',width:100,title:"合计(Mb)",sort:true},
            {field:"rows",align:'center',width:100,title:"记录数",sort:true},
            {fixed:'right',width:80,align:'center',toolbar:'<div><a class="layui-btn layui-btn-xs" lay-event="zidian">查看字典</a></div>',title:'操作'}
        ]]
    });/**/
    /*顶部备份数据按钮*/
    $('#database-backup').on('click',function(){
        var checkData = table.checkStatus('database').data;
        if (checkData.length === 0){return layer.msg('请选择需备份的数据表');}
        var tables = {}, sizes = {};
        for (var i=0;i<checkData.length;i++){tables[i]=checkData[i].name;sizes[checkData[i].name]=checkData[i].data_total;}
        layer.confirm('确定备份所选数据表吗？',{title:'备份数据'}, function(){
            var str = '<div style="padding:20px 10px;width:300px">' + 
                      '<div class="layui-progress layui-progress-big" lay-showpercent="true">' +
                      '<div class="layui-progress-bar layui-bg-green" style="width:0%;">' +
                      '<span class="layui-progress-text">0%</span>'+
                      '</div></div><p class="ts" style="text-align:center;padding:5px 0;"></p></div>';
            var layid = layer.open({type:1, title:'数据备份中请勿关闭...', content:str});
            var getBack = function(data){
                admin.req(app_root+"backup",data,function(res){
                    if(res.code == -1 || res.code == 401){
                        layer.msg(res.msg,{shade:[0.4,'#000'],time:1500},function(){
                            layer.close(layid);
                        });
                        return;
                    }
                    $(".layui-progress-bar").css("width",res.data.p+"%");
                    $(".layui-progress-text").html(res.data.p+"%");
                    var n = res.code==1 ? (res.data.filenum > 1 ? res.data.filenum-1 : 0) : res.data.filenum;
                    $(".ts").html("分卷 "+ n +" 备份成功，自动继续中...");
                    if(res.code==1){
                        layer.msg(res.msg,{shade:[0.4,'#000'],time:1500},function(){
                            layer.close(layid);
                        });
                    }else{
                        setTimeout(function(){getBack()},1000);
                    }
                },'post');
            }
            var data = {tables:tables,sizes:sizes}
            setTimeout(function(){getBack(data)},1000);
        });
    });/**/
    /*顶部恢复数据按钮*/
    $('#database-imports').on('click', function(){
        admin.open({
            type: 1,
            area: ['70%', '75%'],
            title: '数据恢复',
            content: [
                '<div style="padding:20px;">',
                '<div style="margin-bottom:10px;"><a class="layui-btn" id="database-open-imports-del" v-show="@system.database/del"><i class="layui-icon layui-icon-delete"></i> 删除备份</a></div>',
                '<table id="database_open_imports_table"></table></div>'
            ].join(''),
            success: function(){
                admin.vShow();/*移除无权限项*/
                /*备份列表*/
                table.render({
                    elem: '#database_open_imports_table',
                    url: app_root + 'imports',
                    even: true,
                    cols: [[
                        {type:"checkbox",fixed:"left"},
                        {field:"filename",title: "备份系列"},
                        {field:"mtime",align:'center',title:"备份时间",sort:true,templet:function(d){return layui.util.toDateString(d.mtime*1000)}},
                        {field:"filesize",align:'center',width:120,title: "数据大小(Mb)",sort:true},
                        {field:"number",align:'center',width:80,title:"卷数"},
                        {fixed:'right',width:160,align:'center',toolbar:'<div><a class="layui-btn layui-btn-xs layui-btn-danger" lay-event="rep">替换</a><a class="layui-btn layui-btn-xs" lay-event="impt">导入</a><a class="layui-btn layui-btn-xs" lay-event="down">下载</a></div>',title:'操作'}
                    ]]
                });/**/
                /*删除备份*/
                $('#database-open-imports-del').on('click', function(){
                    var checkData = table.checkStatus('database_open_imports_table').data;
                    if (checkData.length === 0){return layer.msg('请选择备份系列');}
                    var filenames = checkData.map(function(d){return d.filename;});
                    layer.confirm('确定删除所选备份吗？', function(){
                        admin.req(app_root+"del",{filenames:filenames},function(res){
                            layer.msg(res.msg,{shade:[0.4,'#000'],time:1500},function(){
                                if(res.code==1){
                                    table.reloadData('database_open_imports_table');
                                }
                            });
                        },'post');
                    });
                });/**/
                /*右侧操作工具条监听*/
                table.on('tool(database_open_imports_table)',function(ob){
                    var str = '<div style="padding:20px 10px;width:300px">' + 
                        '<div class="layui-progress layui-progress-big" lay-showpercent="true">' +
                        '<div class="layui-progress-bar layui-bg-green" style="width:0%;">' +
                        '<span class="layui-progress-text">0%</span>'+
                        '</div></div><p class="ts" style="text-align:center;padding:5px 0;"></p></div>';
                    if(ob.event === 'rep'){
                        admin.open({
                            type: 1,
                            bid: 'replace_items',
                            btn: ['执行', '取消'],
                            area: ['500px', '300px'],
                            title: ob.data.filename + ' - 备份字符替换',
                            success: function(){
                                layui.buildItems.build({
                                    bid: 'replace_items',
                                    data: [
                                        {name:"files",type:"hidden",value:ob.data.filename},
                                        {name:"old",title:"查找内容",type:"text",value:'',verify:'required',placeholder:"请输入要替换的内容",must:true},
                                        {name:"new",title:"替换为",type:"text",value:'',verify:'required',placeholder:"请输入替换后的内容",must:true},
                                        {name:"safepass",title:"安全密码",type:"password",value:'',verify:'required',placeholder:"请再输入安全密码(登录密码)",affix:'eye',must:true}
                                    ]
                                });
                                layui.form.on('submit(replace_items)',function(data){
                                    var btn = $(this);
                                    if (btn.attr('stop')){return false}else{btn.attr('stop',1)}
                                    var layid = layer.open({type:1, title:'字符替换中请勿关闭...', content:str});
                                    var Replace = function(Dt){
                                        admin.req(app_root+'replace',Dt,function(res){
                                            $(".layui-progress-bar").css("width",res.data.p+"%");
                                            $(".layui-progress-text").html(res.data.p+"%");
                                            $(".ts").html("分卷 "+ res.data.filenum +" 替换成功，自动继续中...");
                                            if(res.code > 0){
                                                 layer.msg(res.msg,{shade:[0.4,'#000'],time:1500},function(){
                                                    layer.close(layid);
                                                    btn.removeAttr('stop');
                                                });
                                            }else{
                                                setTimeout(function(){Replace(Dt)},1000);
                                            }
                                        },'post');
                                    };
                                    data.field.safepass = hex_md5(data.field.safepass);
                                    setTimeout(function(){Replace(data.field)},1000);
                                    return false;
                                });
                            }
                        });
                    }else if(ob.event === 'impt'){
                        var filename = ob.data.filename, totaltid = ob.data.number;
                        layer.confirm('即将导入：' + filename, {icon:3, title:'数据导入', maxWidth: '200px'}, function(){
                            var layid = layer.open({type:1, title:'数据导入中请勿关闭...', content:str});
                            var Imports = function(data){
                                admin.req(app_root+"import",data,function(res){
                                    $(".layui-progress-bar").css("width",res.data.p+"%");
                                    $(".layui-progress-text").html(res.data.p+"%");
                                    $(".ts").html("分卷 "+ res.data.filenum +" 导入成功，自动继续中...");
                                    if(res.code > 0){
                                         layer.msg(res.msg,{shade:[0.4,'#000'],time:1500},function(){
                                            layer.close(layid);
                                        });
                                    }else{
                                        setTimeout(function(){Imports()},1000);
                                    }
                                },'post');
                            }
                            setTimeout(function(){Imports({filename:filename})},1000);
                        });
                    }else if(ob.event === 'down'){
                        var filename = ob.data.filename, totaltid = ob.data.number;
                        var Down = function(i){
                            if(i <= totaltid){
                                location.href = app_root + "download?filename=" + filename + "&pid="+i+"";
                                i++;
                                setTimeout(function(){Down(i)},1500);
                            }
                        };
                        Down(1);
                    }
                });/**/
            }
        });
    });/**/
    /*顶部修复表按钮*/
    $('#database-xiufu').on('click', function(){
        var btn = $(this);
        if (btn.attr('stop')) return false;
        var checkRows = table.checkStatus('database').data;
        if (checkRows.length === 0){return layer.msg('请选择需修复的数据表');}
        var tables = checkRows.map(function(d){return d.name;});
        btn.attr('stop',1);
        admin.req(app_root+"xiufu",{table:tables},function(res){
            layer.msg(res.msg,{shade:[0.4,'#000'],time:1500},function(){btn.removeAttr('stop');});
        },'post');
    });/**/
    /*顶部优化表按钮*/
    $('#database-youhua').on('click', function(){
        var btn = $(this);
        if (btn.attr('stop')) return false;
        var checkRows = table.checkStatus('database').data;
        if (checkRows.length === 0){return layer.msg('请选择需优化的数据表');}
        var tables = checkRows.map(function(d){return d.name;});
        btn.attr('stop',1);
        admin.req(app_root+"youhua",{table:tables},function(res){
            layer.msg(res.msg,{shade:[0.4,'#000'],time:1500},function(){btn.removeAttr('stop');});
        },'post');
    });/**/
    /*快编监听*/
    table.on('edit(database)', function(obj){
        admin.req(app_root+"edit",{table:obj.data.name,note:obj.value},function(res){
            layer.msg(res.msg,{shade:[0.4,'#000'],time:1500});
        },'post');
    });/**/
    /*工具条监听*/
    table.on('tool(database)', function(obj){
        var data = obj.data;
        if(obj.event === 'zidian'){
            admin.open({
                type: 1,
                title: '数据表字典 - ' + data.name + ' - ' + data.comment,
                area: ['800px', '600px'],
                content: '<div class="layui-form" lay-filter="database_open_dict_info" id="database_open_dict_info" style="padding:10px 20px;"><table lay-filter="database_dict" id="database_dict"></table></div>',
                success: function(){
                    table.render({
                        elem: '#database_dict',
                        url: app_root + 'dict?table=' + data.name,
                        size: 'sm',
                        cols: [[
                            {field:"field",title:"字段名"},
                            {field:"type",title:"字段类型"},
                            {field:"default",align:'center',width:80,title:"默认值"},
                            {field:"null",align:'center',width:80,title:"允许非空"},
                            {field:"extra",align:'center',width:80,title:"自动递增",templet:function(d){return d.extra=='auto_increment'?'是':''}},
                            {field:"comment",title:"备注"}
                        ]]
                    });
                }
            });
        }
    });/**/
});
</script>
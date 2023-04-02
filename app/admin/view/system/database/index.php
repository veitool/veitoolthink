<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-card-header">
            <div class="layui-btn-group">
                <button class="layui-btn" id="top-database-backup"><i class="layui-icon layui-icon-auz"></i> 备份数据</button>
                <button class="layui-btn" id="top-database-imports"><i class="layui-icon layui-icon-refresh"></i> 恢复数据</button>
                <button class="layui-btn" id="top-database-xiufu"><i class="layui-icon layui-icon-set"></i> 修复表</button>
                <button class="layui-btn" id="top-database-youhua"><i class="layui-icon layui-icon-rate"></i> 优化表</button>
            </div>
            <div style="float:right;padding:10px 5px 0 0;">共<b> <?php echo isset($tables)?$tables:'0';?></b> 张表 / <b><?php echo isset($totalsize)?$totalsize:'0';?></b> Mb</div>
        </div>
        <div class="layui-card-body">
            <table lay-filter="database" id="database"></table>
        </div>
    </div>
</div>
<!--JS部分-->
<script type="text/javascript">
layui.use(function(){
    var app_root = layui.cache.maps + 'system.database/';
    var table=layui.table,admin=layui.admin,layer=layui.layer;
    /*数据表渲染*/
    table.render({
        elem: '#database',
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
        ]],
        data: {$list|raw},
        even: true,
        page: false,
        limit: 500
    });/**/
    /*顶部备份数据按钮*/
    $('#top-database-backup').on('click',function(){
        var checkData = table.checkStatus('database').data;
        if (checkData.length === 0){return layer.msg('请选择需备份的数据表');}
        var tables = {}, sizes = {};
        for (var i=0;i<checkData.length;i++){tables[i]=checkData[i].name;sizes[checkData[i].name]=checkData[i].data_total;}
        layer.confirm('确定备份所选数据表吗？',{title:'备份数据'}, function(index){
            layer.close(index);
            var str = '<div style="padding:20px 10px;width:300px">' + 
                      '<div class="layui-progress layui-progress-big" lay-showpercent="true">' +
                      '<div class="layui-progress-bar layui-bg-green" style="width:0%;">' +
                      '<span class="layui-progress-text">0%</span>'+
                      '</div></div><p class="ts" style="text-align:center;padding:5px 0;"></p></div>';
            var layid = layer.open({
                type: 1,
                title: '数据备份中请勿关闭...',
                content: str
            });
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
    $('#top-database-imports').on('click', function(){
        admin.open({
            type: 1,
            area: ['68%', '75%'],
            title: '数据恢复',
            content: [
                '<div style="padding:20px;">',
                '<div style="margin-bottom:10px;"><button class="layui-btn" id="database-open-imports-del"><i class="layui-icon layui-icon-delete"></i> 删除备份</button></div>',
                '<table id="database_open_imports_table" lay-filter="database_open_imports_table"></table></div>'
            ].join(''),
            success: function(){
                /*备份列表*/
                table.render({
                    elem: '#database_open_imports_table',
                    url: app_root + 'imports',
                    cols: [[
                        {type:"checkbox",fixed:"left"},
                        {field:"filename",title: "备份系列"},
                        {field:"mtime",align:'center',title:"备份时间",sort:true},
                        {field:"filesize",align:'center',width:120,title: "数据大小(Mb)",sort:true},
                        {field:"number",align:'center',width:80,title:"卷数"},
                        {fixed:'right',width:100,align:'center',toolbar:'<div><a class="layui-btn layui-btn-xs" lay-event="impt">导入</a><a class="layui-btn layui-btn-xs" lay-event="down">下载</a></div>',title:'操作'}
                    ]],
                    even: true,
                    page: false,
                    limit:0
                });/**/
                /*删除备份*/
                $('#database-open-imports-del').on('click', function(){
                    var checkData = table.checkStatus('database_open_imports_table').data;
                    if (checkData.length === 0){return layer.msg('请选择备份系列');}
                    var filenames = checkData.map(function(d){return d.filename;});
                    layer.confirm('确定删除所选备份吗？', function(delId){
                        layer.close(delId);
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
                    if(ob.event === 'impt'){
                        var filename = ob.data.filename, totaltid = ob.data.number;
                        layer.confirm('即将导入：' + filename, {icon:3, title:'数据导入', maxWidth: '200px'}, function(i){
                            layer.close(i);
                            var str = '<div style="padding:20px 10px;width:300px">' + 
                                      '<div class="layui-progress layui-progress-big" lay-showpercent="true">' +
                                      '<div class="layui-progress-bar layui-bg-green" style="width:0%;">' +
                                      '<span class="layui-progress-text">0%</span>'+
                                      '</div></div><p class="ts" style="text-align:center;padding:5px 0;"></p></div>';
                            var layid = layer.open({
                                type: 1,
                                title: '数据导入中请勿关闭...',
                                content: str
                            });
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
                            var data = {filename:filename}
                            setTimeout(function(){Imports(data)},1000);
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
    $('#top-database-xiufu').on('click', function(){
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
    $('#top-database-youhua').on('click', function(){
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
            layer.msg(res.msg,{shade:[0.4,'#000'],time:1500},function(){
                //if(res.code==1 && obj.field=='note') redata();
            });
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
                        ]],
                        page: false,
                        limit: 0
                    });
                }
            });
        }
    });/**/
});
</script>
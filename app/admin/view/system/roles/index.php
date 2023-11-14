<div class="layui-fluid">   
    <div class="layui-card">
        <div class="layui-card-header">
            <div class="layui-btn-group">
                <a class="layui-btn" id="roles-add" v-show="@system.roles/add"><i class="layui-icon layui-icon-add-circle"></i> 添加</a>
                <a class="layui-btn" id="roles-del" v-show="@system.roles/del"><i class="layui-icon layui-icon-delete"></i> 删除</a>
            </div>
        </div>
        <div class="layui-card-body">
            <table id="roles"></table>
        </div>
    </div>
</div>
<!--JS部分-->
<script type="text/javascript">
layui.use(['zTree','buildItems'], function(){
    var app_root = layui.cache.maps + 'system.roles/';
    var layer=layui.layer,table=layui.table,form=layui.form,admin=layui.admin;
    /*数据表渲染*/
    table.render({
        elem: '#roles',
        cols: [[
            {type:'checkbox',fixed:'left'},
            {field:'roleid',width:60,unresize:true,align:'center',title:'ID'},
            {field:'role_name',edit:'text',minWidth:200,title:'角色名称'},
            {field:'addtime',width:180,align:'center',title:'添加时间',templet:function(d){return layui.util.toDateString(d.addtime*1000)}},
            {field:'listorder',edit:'text',width:80,align:'center',title:'排序'},
            {field:'state',width:100,align:'center',templet:function(d){return '<input type="checkbox" name="state" lay-skin="switch" lay-text="启用|禁用" lay-filter="roles-chang" value="'+d.state+'" data-json="'+encodeURIComponent(JSON.stringify(d))+'"'+(d.state==1 ? ' checked' : '')+'>';},unresize:true,title:'启用状态'},
            {fixed:'right',width:100,align:'center',toolbar:'<div><a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a><a class="layui-btn layui-btn-xs layui-btn-danger" lay-event="del">删除</a></div>',title:'操作'}
        ]],
        url: app_root+"index?do=json",
        page: true,
        limit:{$limit}
    });/**/
    /*顶部添加按钮*/
    $('#roles-add').on('click',function(){rolesOpen();});/**/
    /*顶部删除按钮*/
    $('#roles-del').on('click', function(){
        var checkRows = table.checkStatus('roles').data;
        if(checkRows.length === 0){return layer.msg('请选择需删除的角色');}
        var ids = checkRows.map(function(d){return d.roleid;});
        del(ids);
    });/**/
    /*快编监听*/
    table.on('edit(roles)',function(obj){
        admin.req(app_root+"edit?do=up",{roleid:obj.data.roleid,av:obj.value,af:obj.field},function(res){
            layer.msg(res.msg,{shade:[0.4,'#000'],time:1500},function(){
                if(res.code==1 && obj.field=='listorder') table.reloadData('roles');
            });
        },'post',{headersToken:true});
    });/**/
    /*状态*/
    form.on('switch(roles-chang)',function(obj){
        var json = JSON.parse(decodeURIComponent($(this).data('json')));
        var av = obj.elem.checked ? 1 : 0;
        admin.req(app_root+"edit?do=up",{roleid:json.roleid,av:av,af:obj.elem.name},function(res){
            layer.tips(res.msg,obj.othis,{time:2000});
        },'post',{headersToken:true});
    });/**/
    /*工具条监听*/
    table.on('tool(roles)', function(obj){
        var data = obj.data;
        if(obj.event === 'edit'){
            rolesOpen(data);
        }else if(obj.event === 'del'){
            del(data.roleid);
        }
    });/**/
    /*删除*/
    function del(ids){
        layer.confirm('确定要删除所选角色吗？', function(){
            admin.req(app_root+"del",{roleid:ids},function(res){
                layer.msg(res.msg,{shade:[0.4,'#000'],time:1500},function(){
                    if(res.code==1) table.reloadData('roles');
                });
            },'post',{headersToken:true});
        });
    }/**/
    /*弹出窗*/
    function rolesOpen(Dt){
        admin.open({
            type: 1,
            bid: 'roles_items',
            btn: ['保存', '取消'],
            area: ['660px', '90%'],
            title: Dt ? '编辑角色' : '添加角色',
            success: function(l,index){
                var height = l.height() - 290;
                var tool = '<div style="padding:8px 0 5px 0;"><a class="layui-btn layui-btn-xs" id="zTreeAll">全选</a> <a class="layui-btn layui-btn-xs" id="zTreeNo">反选</a> <a class="layui-btn layui-btn-xs" id="zTreeE">折叠</a></div>';
                layui.buildItems.build({
                    bid: 'roles_items',
                    data: [
                        {name:"role_name",title:"角色名称",type:"text",value:'',verify:'required',placeholder:"请输入角色名称",must:true},
                        {name:"listorder",title:"排序编号",type:"html",html:'<div class="layui-input-inline"><input type="number" name="listorder" value="10" lay-verify="required" placeholder="请输入排序数字" autocomplete="off" class="layui-input"></div><div class="layui-input-block"><input type="checkbox" name="state" lay-verify="required" lay-skin="switch" lay-text="启用|禁用" value="1" checked/></div>',must:true},
                        {name:"parent_id",title:"权限列表",type:"html",html:tool+'<div style="padding-top:10px;height:'+ height +'px;overflow:auto;"><ul id="rolesTree" class="ztree"></ul></div>'}
                    ]
                });
                form.val('roles_items_form', Dt);
                var roleid = Dt ? Dt.roleid : 0;
                var loadIndex = layer.load(2);
                admin.req(app_root+"index?do=mjson",{roleid:roleid},function(res){
                    layer.close(loadIndex);
                    if(res.code === 0){
                        var treeObj = $.fn.zTree.init($('#rolesTree'),{check:{enable:true},data:{simpleData:{enable:true}}},res.data);
                        $('#zTreeAll').on('click',function(){
                            var html = $(this).html(), flag = false, tip = '全选';
                            if(html == '全选'){flag = true; tip = '清空';}
                            $(this).html(tip);
                            treeObj.checkAllNodes(flag);
                        });
                        $('#zTreeNo').on('click',function(){
                            var checked = treeObj.getCheckedNodes(true);
                            var checkeds = treeObj.transformToArray(checked);
                            var nodes = treeObj.transformToArray(treeObj.getNodes());
                            if(checked.length < nodes.length){
                                treeObj.checkAllNodes(true);
                                $.each(checkeds, function(k,node){
                                    treeObj.checkNode(node, false, false);
                                });
                            }else{
                                treeObj.checkAllNodes(false);
                            }
                        });
                        $('#zTreeE').on('click',function(){
                            var html = $(this).html(), flag = true, tip = '折叠';
                            if(html == '折叠'){flag = false; tip = '展开';}
                            $(this).html(tip);
                            treeObj.expandAll(flag);
                        });
                    }else{
                        layer.msg(res.msg,{icon:2});
                    }
                },'get');
                form.on('submit(roles_items)',function(data){
                    var btn = $(this);
                    if (btn.attr('stop')){return false}else{btn.attr('stop',1)}
                    var field = data.field; field.roleid = roleid;
                    var post_url = field.roleid ? app_root+'edit' : app_root+'add';
                        field.state = field.hasOwnProperty('state') ? field.state : '0';
                    var insTree = $.fn.zTree.getZTreeObj('rolesTree');
                    var checkedRows = insTree.getCheckedNodes(true);
                        field.role_menuid = checkedRows.map(function(d){return d.id;});
                    admin.req(post_url,field,function(res){
                        layer.msg(res.msg,{shade:[0.4,'#000'],time:1500},function(){
                            if(res.code==1){
                                layer.close(index);
                                table.reloadData('roles');
                            }
                            btn.removeAttr('stop');
                        });
                    },'post',{headersToken:true});
                    return false;
                });
            }
        });
    }/**/
});
</script>
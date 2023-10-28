<div style="padding:20px;">
    <div class="layui-btn-group" style="margin-bottom:20px;">
        <a class="layui-btn" id="menucategory-add" v-show="@system.menus/catadd"><i class="layui-icon layui-icon-add-circle"></i> 添加类别</a>
        <a class="layui-btn" id="menucategory-del" v-show="@system.menus/catdel"><i class="layui-icon layui-icon-delete"></i> 删除类别</a>
    </div>
    <table id="menucategory" lay-filter="menucategory"></table>
</div>
<!--JS部分-->
<script>
layui.use(['iconPicker','buildItems'], function(){
    var map_root = layui.cache.maps;
    var cat_root = map_root + '{$act ?? "system"}.menus/';
    var layer=layui.layer,table=layui.table,form=layui.form,admin=layui.admin;
    /*类列表*/
    table.render({
        elem: '#menucategory',
        data: <?=$list?>,
        cols: [[
            {type:"checkbox",fixed:"left"},
            {field:"catid",align:'center',width:60,title:"ID"},
            {field:"title",edit:'text',title: "类别名称"},
            {field:'icon',width:60,align:'center',templet:function(d){return d.icon ? '<i class="layui-icon '+ d.icon +'"></i>' : '';},title:'图标'},
            {field:'listorder',edit:'text',width:60,align:'center',title:'排序'},
            {field:'state',width:80,align:'center',templet:function(d){return '<input type="checkbox" name="state" lay-skin="switch" lay-text="是|否" lay-filter="menucategory-state-chang" value="'+d.state+'" data-json="'+encodeURIComponent(JSON.stringify(d))+'"'+(d.state==1 ? ' checked' : '')+'>';},unresize:true,title:'启用'},
            {fixed:'right',width:120,align:'center',toolbar:'<div><a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a><a class="layui-btn layui-btn-xs" lay-event="del">删除</a></div>',title:'操作'}
        ]]
    });/**/
    /*顶部添加按钮*/
    $('#menucategory-add').on('click', function(){menucategoryOpen();});/**/
    /*顶部删除按钮*/
    $('#menucategory-del').on('click', function(){
        var checkRows = table.checkStatus('menucategory').data;
        if(checkRows.length === 0){return layer.msg('请选择需删除的类别');}
        var ids = checkRows.map(function(d){return d.catid;});
        del(ids);
    });/**/
    /*类别显示*/
    form.on('switch(menucategory-state-chang)',function(obj){
        var json = JSON.parse(decodeURIComponent($(this).data('json')));
        var state = obj.elem.checked ? 1 : 0;
        admin.req(cat_root+"catedit?do=up",{catid:json.catid,av:state,af:'state'},function(res){
            layer.tips(res.msg,obj.othis,{time:1000,tips:[3,'#333']});
        },'post',{headersToken:true});
    });/**/
    /*快编监听*/
    table.on('edit(menucategory)',function(obj){
        admin.req(cat_root+"catedit?do=up",{catid:obj.data.catid,av:obj.value,af:obj.field},function(res){
            layer.msg(res.msg,{shade:[0.4,'#000'],time:1500},function(){
                if(res.code==1) table.reloadData('menucategory',{data:res.data});
                layui.admin.refresh();
            });
        },'post',{headersToken:true});
    });/**/
    /*右侧操作工具条监听*/
    table.on('tool(menucategory)',function(ob){
        var data = ob.data;
        if(ob.event === 'edit'){
            menucategoryOpen(data);
        }else if(ob.event === 'del'){
            del(data.catid);
        }
    });/**/
    /*添加、编辑弹出窗*/
    function menucategoryOpen(Dt){
        layui.admin.open({
            type: 1,
            bid: 'menucategory_items',
            btn: ['保存', '取消'],
            area: ['500px', '490px'],
            title: Dt ? '编辑类别' : '添加类别',
            success: function(l,index){
                layui.buildItems.build({
                    bid: 'menucategory_items',
                    data: [
                        {name:"catid",type:"hidden"},
                        {name:"title",title:"类别名称",type:"html",html:'<div class="layui-input-inline" style="width:82px;float:left;"><input type="text" name="icon" value="" id="catIconPicker" lay-filter="catIconPicker" autocomplete="off" class="layui-input"></div><div class="layui-input-block" style="margin-left:105px;margin-right:0;"><input type="text" name="title" value="" lay-verify="required" lay-reqText="请输入类别名称" placeholder="请输入类别名称" autocomplete="off" class="layui-input"></div>',must:true},
                        {name:"listorder",title:"排序编号",type:"number",value:'100',verify:'required',placeholder:"请输入排序数字",must:true}
                    ]
                });
                form.val('menucategory_items_form',Dt);
                layui.iconPicker.render({
                    elem: '#catIconPicker',
                    type: 'fontClass',
                    search: true,
                    page: false
                });
                form.on('submit(menucategory_items)',function(data){
                    var btn = $(this);
                    if (btn.attr('stop')){return false}else{btn.attr('stop',1)}
                    var field = data.field;
                    var post_url = field.catid ? cat_root+'catedit' : cat_root+'catadd';
                    admin.req(post_url, field, function(res){
                        layer.msg(res.msg,{shade:[0.4,'#000'],time:1000},function(){
                            if(res.code==1){
                                layer.close(index);
                                table.reloadData('menucategory',{data:res.data});
                                admin.refresh();
                            }
                            btn.removeAttr('stop');
                        });
                    },'post',{headersToken:true});
                    return false;
                });
            }
        });
    }/**/
    /*删除*/
    function del(ids){
        layer.confirm('确定要删除所选类别吗？', function(){
            admin.req(cat_root+"catdel",{catid:ids},function(res){
                layer.msg(res.msg,{shade:[0.4,'#000'],time:1500},function(){
                    if(res.code==1){
                        table.reloadData('menucategory',{data:res.data});
                        admin.refresh();
                    }
                });
            },'post',{headersToken:true});
        });
    }/**/
});
</script>
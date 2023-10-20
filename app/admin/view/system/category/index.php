<style>
.categorys_item{width:30px;height:30px;line-height:10px;cursor:pointer;position:relative;margin:10px 0px 0 2px;padding:0px;border:1px solid #ddd;background:#fff;display:-webkit-box;-moz-box-align:center;-webkit-box-align:center;-moz-box-pack:center;-webkit-box-pack:center;}
.categorys_item img{max-width:24px;max-height:24px;border:0}
</style>
<div style="padding:20px;">
    <div class="layui-btn-group">
        <a class="layui-btn" id="categorys-add"><i class="layui-icon layui-icon-add-circle"></i> 添加类别</a>
        <a class="layui-btn" id="categorys-del"><i class="layui-icon layui-icon-delete"></i> 删除类别</a>
    </div>
    <table id="categorys" lay-filter="categorys"></table>
</div>
<!--JS部分-->
<script>
layui.use(['buildItems'], function(){
    var map_root = layui.cache.maps;
    var cat_root = map_root + '{$act}/';
    var layer=layui.layer,table=layui.table,form=layui.form,admin=layui.admin;
    var cats = {$list|raw};
    /*类列表*/
    table.render({
        elem: '#categorys',
        data: cats,
        css: 'td .layui-table-cell{height:50px;line-height:50px;padding:0 5px;}',
        cols: [[
            {type:"checkbox",fixed:"left"},
            {field:"icon",title:"类图",align:'center',width:50,templet:function(d){return '<div class="categorys_item"><img src="'+ (d.icon ? d.icon : '') +'" lay-event="categorys-image"/></div>';}},
            {field:"catid",align:'center',width:60,title: "ID"},
            {field:"new_title",title: "类别名称"},
            {field:'sign',edit:'text',width:100,align:'center',title:'扩展标识'},
            {field:'listorder',edit:'text',width:60,align:'center',title:'排序'},
            {fixed:'right',width:100,align:'center',toolbar:'<div><a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a><a class="layui-btn layui-btn-xs" lay-event="del">删除</a></div>',title:'操作'}
        ]]
    });/**/
    /*顶部添加按钮*/
    $('#categorys-add').on('click', function(){categoryOpen();});/**/
    /*顶部删除按钮*/
    $('#categorys-del').on('click', function(){
        var checkRows = table.checkStatus('categorys').data;
        if(checkRows.length === 0){return layer.msg('请选择需删除的类别');}
        var ids = checkRows.map(function(d){return d.catid;});
        del(ids);
    });/**/
    /*快编监听*/
    table.on('edit(categorys)',function(obj){
        admin.req(cat_root+"catedit?do=up",{catid:obj.data.catid,av:obj.value,af:obj.field},function(res){
            layer.msg(res.msg,{shade:[0.4,'#000'],time:1500},function(){
                if(res.code==1) table.reloadData('categorys',{data:res.data});
            });
        },'post',{headersToken:true});
    });/**/
    /*右侧操作工具条监听*/
    table.on('tool(categorys)',function(ob){
        var data = ob.data;
        if(ob.event === 'edit'){
            categoryOpen(data);
        }else if(ob.event === 'del'){
            del(data.catid);
        }else if(ob.event === 'categorys-image'){
            var src = $(this).attr('src');
            var alt = data.title;
            layer.photos({photos:{data:[{alt:alt,src:src}],start:'0'},anim:5,shade:[0.4,'#000']});
        }
    });/**/
    /*弹出窗*/
    var categoryOpen = function(Dt){
        admin.open({
            type: 1,
            bid: 'categorys_items',
            btn: ['保存', '取消'],
            area: ['500px', '435px'],
            title: (Dt ? '编辑' : '添加') + '类别',
            success: function(l,dIndex){
                var id = Dt ? Dt.catid : '-1';
                var select = '<option value="0">顶级类别</option>';
                $.each(cats,function(k,v){if(id != v.catid && v.arrparentid.indexOf(id) == -1){select += '<option value="'+ v.catid +'">'+ v.new_title +'</option>';}});
                layui.buildItems.build({
                    bid: 'categorys_items',
                    data: [
                        {name:"catid",type:"hidden"},
                        {name:"html",title:"上级类别",type:"html",html:'<select name="parentid">'+ select +'</select>'},
                        {name:"title",title:"类别名称",type:"text",value:'',verify:'required',placeholder:"请输入类别名称",must:true},
                        {name:"icon",title:"类别图片",type:"image",value:Dt ? Dt.icon : ''},
                        {name:"listorder",title:"排序编号",type:"number",value:10,placeholder:"请输入排序编号"}
                    ],
                    map: map_root + 'system.upload/',
                    gid: 3
                });
                form.val('categorys_items_form',Dt);
                form.on('submit(categorys_items)',function(data){
                    var btn = $(this);
                    if (btn.attr('stop')){return false}else{btn.attr('stop',1)}
                    admin.req(data.field.catid ? cat_root+'catedit' : cat_root+'catadd',data.field,function(res){
                        layer.msg(res.msg,{shade:[0.4,'#000'],time:1000},function(){
                            if(res.code==1){
                                layer.close(dIndex);
                                cats = res.data;
                                table.reloadData('categorys',{data:cats});
                                admin.refresh();
                            }
                            btn.removeAttr('stop');
                        });
                    },'post',{headersToken:true});
                    return false;
                });
            }
        });
    };/**/
    /*删除*/
    var del = function(ids){
        layer.confirm('确定要删除所选类别吗？', function(){
            admin.req(cat_root+"catdel",{catid:ids},function(res){
                layer.msg(res.msg,{shade:[0.4,'#000'],time:1500},function(){
                    if(res.code==1){
                        cats = res.data;
                        table.reloadData('categorys',{data:cats});
                        admin.refresh();
                    }
                });
            },'post',{headersToken:true});
        });
    };/**/
});</script>
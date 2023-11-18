<div class="layui-fluid">   
    <div class="layui-card">
        <div class="layui-tab layui-tab-admin" lay-filter="menus_top_tab">
            <ul class="layui-tab-title" id="menus_tab"></ul>
        </div>
        <div class="layui-card-body">
            <div class="layui-card-box">
                <div class="layui-btn-group">
                    <a class="layui-btn" id="menus-add" v-show="@system.menus/add"><i class="layui-icon layui-icon-add-circle"></i> 添加</a>
                    <a class="layui-btn" id="menus-adds" v-show="@system.menus/adds"><i class="layui-icon layui-icon-add-circle"></i> 批量</a>
                    <a class="layui-btn" id="menus-del" v-show="@system.menus/del"><i class="layui-icon layui-icon-delete"></i> 删除</a>
                    <a class="layui-btn" id="menus-reset" v-show="@system.menus/reset"><i class="layui-icon layui-icon-snowflake"></i> 重构</a>
                    <a class="layui-btn" id="menus-sz" data="1"><i class="layui-icon">&#xe624;</i>展开</a>
                    <a class="layui-btn" id="menus-cat" v-show="@system.menus/category"><i class="layui-icon layui-icon-rate"></i> 分类</a>
                    <a class="layui-btn" id="menus-out" v-show="@system.menus/out"><i class="layui-icon layui-icon-download-circle"></i> 导出</a>
                    <a class="layui-btn" id="menus-up" v-show="@system.menus/up"><i class="layui-icon layui-icon-upload-drag"></i> 导入</a>
                </div>
            </div>
            <table lay-filter="menus" id="menus"></table>
        </div>
    </div>
</div>
<!--JS部分-->
<script type="text/javascript">
layui.use(['trTable','xmSelect','iconPicker','buildItems'],function(){
    var map_root = layui.cache.maps;
    var app_root = map_root + 'system.menus/';
    var layer = layui.layer,form = layui.form,admin = layui.admin,treeTable = layui.trTable;
    var category = <?=$category?>,cates = {};
    /*解析顶部分组选项*/
    var tab = $("#menus_tab");
    $.each(category,function(k,v){cates[v.catid]=v.title;tab.append('<li catid="'+ v.catid +'">'+ v.title +'</li>');});
    var catid = tab.children("li").first().addClass('layui-this').attr('catid');
    /**/
    /* 渲染表格 https://gitee.com/whvse/treetable-lay  https://gitee.com/whvse/treetable-lay/wikis/pages */
    var menusTb = treeTable.render({
        elem: '#menus',
        checkdd: false,
        where: {catid:catid},
        url: app_root+"index?do=json",
        toolbar: [
            '<p><span class="layui-inline"><input class="layui-input" id="edtSearch" placeholder="输入关键字" style="width:140px;margin-right:5px;height:30px;"></span>',
            '<button id="btnSearch" class="layui-btn layui-btn-sm layui-btn-primary layui-inline"><i class="layui-icon"></i>搜索</button>',
            '<button id="btnClearSearch" class="layui-btn layui-btn-sm layui-btn-primary layui-inline"><i class="layui-icon"></i>清除搜索</button></p>'
            ].join(''),
        tree: {
            iconIndex: 2,
            isPidData: true,
            idName: 'menuid',
            pidName: 'parent_id',
            arrowType: 'arrow2',
            getIcon: 'v-tree-icon-style2'
        },
        cols: [[
            {type:'checkbox',fixed:'left'},
            {field:'menuid',width:50,unresize:true,align:'center',title:'ID'},
            {field:'menu_name',minWidth:150,edit:'text',title:'名称'},
            {field:'menu_url',minWidth:150,edit:'text',title:'菜单标识'},
            {field:'role_url',minWidth:150,edit:'text',title:'权限路径'},
            {field:'link_url',minWidth:150,edit:'text',title:'外链路径'},
            {field:'icon',width:50,align:'center',templet:function(d){return d.icon ? '<i class="layui-icon '+ d.icon +'"></i>' : '';},title:'图标'},
            {field:'listorder',width:50,edit:'text',align:'center',title:'排序'},
            {field:'ismenu',width:60,align:'center',templet:function(d){return '<input type="checkbox" name="ismenu" lay-skin="switch" lay-text="是|否" lay-filter="menus-chang" value="'+d.ismenu+'" data-json="'+encodeURIComponent(JSON.stringify(d))+'"'+(d.ismenu==1 ? ' checked' : '')+'>';},unresize:true,title:'菜单'},
            {field:'state',width:60,align:'center',templet:function(d){return '<input type="checkbox" name="state" lay-skin="switch" lay-text="是|否" lay-filter="menus-chang" value="'+d.state+'" data-json="'+encodeURIComponent(JSON.stringify(d))+'"'+(d.state==1 ? ' checked' : '')+'>';},unresize:true,title:'显示'},
            {fixed:'right',width:150,align:'center',toolbar:'<div><a class="layui-btn layui-btn-xs" lay-event="add">添加</a><a class="layui-btn layui-btn-xs layui-btn-warm" lay-event="edit">编辑</a><a class="layui-btn layui-btn-xs layui-btn-danger" lay-event="del">删除</a></div>',title:'操作'}
        ]]
    });/**/
    /*顶部选项卡监听*/
    layui.element.on('tab(menus_top_tab)',function(){
        catid = this.getAttribute("catid");
        menusTb.reload({where:{catid:catid}});
    });/**/
    /*搜索、清除搜索*/
    $('#btnSearch').click(function(){
        var keywords = $('#edtSearch').val();
        if(keywords){
            menusTb.clearFilter();
            menusTb.filterData(keywords);
        }else{
            menusTb.clearFilter();
        }
    });
    $('#btnClearSearch').click(function(){menusTb.clearFilter();});/**/
    /*展开或折叠*/
    $('#menus-sz').click(function(){
        var ob = $(this),i,t;
        if(ob.attr('data')==1){
            menusTb.expandAll();
            i=0;t='<i class="layui-icon">&#xe67e;</i>折叠';
        }else{
            menusTb.foldAll();
            i=1;t='<i class="layui-icon">&#xe624;</i>展开';
        }
        ob.attr('data',i).html(t);
    });/**/
    /*顶部类别管理*/
    $('#menus-cat').on('click', function(){
        admin.open({
            type: 1,
            area: ['68%','75%'],
            title: '【菜单类别管理】',
            url: app_root + "category",
            success: function(){
                admin.vShow(); /*移除未有的权限按钮*/
            }
        });
    });/**/
    /*顶部重构按钮*/
    $('#menus-reset').on('click', function(){
        var btn = $(this);
        if (btn.attr('stop')) return false;
        layer.confirm('确定要重构菜单吗？重构后菜单ID将按顺序重新构建！', function(){
            btn.attr('stop',1);
            var loadIndex = layer.load(2);
            admin.req(app_root+"reset",function(res){
                layer.close(loadIndex);
                layer.msg(res.msg,{shade:[0.4,'#000'],time:1500},function(){
                    if(res.code==1) menusTb.refresh();
                    btn.removeAttr('stop');
                });
            },'post');
        });
    });/**/
    /*顶部导出管理*/
    $('#menus-out').on('click', function(){
        var btn = $(this);
        if (btn.attr('stop')) return false;
        var checkRows = menusTb.checkStatus();
        var menuid = checkRows.length > 0 ? checkRows[0].menuid : 0;
        layer.confirm('确定要导出菜单吗？<br/>将将导出在文件 /runtime/sysMenus_*.php', function(){
            btn.attr('stop',1);
            admin.req(app_root+"out",{menuid:menuid},function(res){
                layer.msg(res.msg);
                btn.removeAttr('stop');
            },'post');
        });
    });/**/
    /*顶部导入管理*/
    $('#menus-up').on('click', function(){
        var btn = $(this);
        if (btn.attr('stop')) return false;
        layer.confirm('确定要导入菜单吗？<br/>请确保存在文件 /runtime/sysMenus.php', function(){
            btn.attr('stop',1);
            admin.req(app_root+"up",function(res){
                layer.msg(res.msg,{shade:[0.4,'#000'],time:1500},function(){
                    if(res.code==1) menusTb.refresh();
                    btn.removeAttr('stop');
                });
            },'post');
        });
    });/**/
    /*是否菜单、菜单显示*/
    form.on('switch(menus-chang)',function(obj){
        var json = JSON.parse(decodeURIComponent($(this).data('json')));
        var av = obj.elem.checked ? 1 : 0;
        admin.req(app_root+"edit?do=up",{menuid:json.menuid,av:av,af:obj.elem.name},function(res){
            layer.tips(res.msg,obj.othis,{time:1000});
        },'post',{headersToken:true});
    });/**/
    /*快编监听*/
    treeTable.on('edit(menus)',function(obj){
        admin.req(app_root+"edit?do=up",{menuid:obj.data.menuid,av:obj.value,af:obj.field},function(res){
            layer.msg(res.msg,{shade:[0.4,'#000'],time:1000});
        },'post',{headersToken:true});
    });/**/
    /*顶部添加按钮*/
    $('#menus-add').on('click',function(){addOpen();});/**/
    /*顶部批量按钮*/
    $('#menus-adds').on('click',function(){addsOpen(0);});/**/
    /*顶部删除按钮*/
    $('#menus-del').on('click', function(){
        var checkRows = menusTb.checkStatus();
        if(checkRows.length == 0){return layer.msg('请选择需删除的菜单');}
        var ids = checkRows.map(function(d){return d.menuid;});
        del(ids);
    });/**/
    /*工具条监听*/
    treeTable.on('tool(menus)', function(obj){
        var data = obj.data;
        if(obj.event == 'add'){
            addsOpen(data.menuid);
        }else if(obj.event == 'edit'){
            addOpen(data);
        }else if(obj.event == 'del'){
            del(data.menuid);
        }
    });/**/
    /*删除*/
    function del(ids){
        layer.confirm('确定要删除所选菜单吗？', function(){
            admin.req(app_root+"del",{menuid:ids},function(res){
                layer.msg(res.msg,{shade:[0.4,'#000'],time:1500},function(){
                    if(res.code==1) menusTb.refresh();
                });
            },'post',{headersToken:true});
        });
    }/**/
    /*批量添加*/
    function addsOpen(id){
        admin.open({
            type: 1,
            bid: 'menuss_items',
            btn: ['确认添加', '取消'],
            area: ['460px', '350px'],
            title: '批量添加菜单',
            success: function(l,index){
                l.children('.layui-layer-content').css('overflow', 'visible');
                layui.buildItems.build({
                    bid: 'menuss_items',
                    data: [
                        {name:"pid",title:"上级ID",type:"number",value:id,verify:'required',placeholder:"请输入上级ID，0：为顶级",must:true},
                        {name:"titles",title:"菜单名称",type:"textarea",value:'',verify:'required',style:"height:160px",placeholder:"可批量添加，一行一个，点回车换行",must:true}
                    ]
                });
                form.on('submit(menuss_items)',function(data){
                    var btn = $(this);
                    if (btn.attr('stop')){return false}else{btn.attr('stop',1)}
                    data.field.catid = catid;
                    admin.req(app_root+"adds",data.field,function(res){
                        layer.msg(res.msg,{shade:[0.4,'#000'],time:1500},function(){
                            if(res.code==1){
                                layer.close(index);
                                menusTb.refresh();
                            }
                            btn.removeAttr('stop');
                        });
                    },'post',{headersToken:true});
                    return false;
                });
            }
        });
    }/**/
    /*添加、编辑弹出窗*/
    function addOpen(Dt){
        admin.open({
            type: 1,
            bid: 'menus_items',
            btn: ['保存', '取消'],
            area: ['660px', '90%'],
            title: (Dt ? '编辑' : '添加') + '菜单',
            success: function(l,index){
                layui.buildItems.build({
                    bid: 'menus_items',
                    data: [
                        {name:"menuid",type:"hidden"},
                        {name:"catid",title:"配置分组",type:"radio",options:cates,value:catid,must:true},
                        {name:"parent_id",title:"上级菜单",type:"html",html:'<div id="menus-list-tree" class="v-xmselect-tree"></div>',must:true},
                        {name:"menu_name",title:"菜单名称",type:"html",html:'<div class="layui-input-inline" style="width:82px;float:left;"><input type="text" name="icon" value="" id="menusIconPicker" lay-filter="menusIconPicker" autocomplete="off" class="layui-input"></div><div class="layui-input-block" style="margin-left:105px;margin-right:0;"><input type="text" name="menu_name" value="" lay-verify="required" lay-reqtext="请输入菜单名称" placeholder="请输入菜单名称" autocomplete="off" class="layui-input"></div>',must:true},
                        {name:"role_name",title:"权限名称",type:"text",value:'',verify:'required',placeholder:"请输入权限名称",must:true},
                        {name:"menu_url",title:"菜单标识",type:"text",value:'',placeholder:"请输入菜单标识"},
                        {name:"role_url",title:"权限路径",type:"textarea",value:'',placeholder:"多个以逗号隔开"},
                        {name:"link_url",title:"外链路径",type:"text",value:'',placeholder:"请输入外链路径(#全屏打开@全路由)"},
                        {name:"listorder",title:"排序编号",type:"number",value:'100',verify:'required',placeholder:"请输入排序数字"},
                        {name:"ismenu",title:"是否菜单",type:"switch",value:'1'},
                        {name:"state",title:"菜单显示",type:"switch",value:'1'}
                    ]
                });
                form.val('menus_items_form',Dt);
                /*图标选择*/
                layui.iconPicker.render({
                    elem: '#menusIconPicker',
                    type: 'fontClass',
                    search: true,
                    page: false
                });
                /*渲染下拉树 https://maplemei.gitee.io/xm-select/#/component/options*/
                var data = JSON.parse(JSON.stringify(menusTb.options.data));
                if(Dt) Exitem(data,Dt.menuid,true);
                layui.xmSelect.render({
                    el: '#menus-list-tree',
                    name: 'parent_id',
                    tips: '顶级菜单',
                    height: '430px',
                    data: data,
                    filterable: true,
                    radio: true,
                    clickClose: true,
                    model: {label:{type:'text'}},
                    initValue: [Dt ? Dt.parent_id : ''],
                    prop: {name:'menu_name',value:'menuid',disabled:'disabled'},
                    tree: {show:true,indent:25,strict:false,expandedKeys:true}
                });
                form.on('submit(menus_items)',function(data){
                    var btn = $(this);
                    if (btn.attr('stop')){return false}else{btn.attr('stop',1)}
                    var field = data.field;
                    var post_url = field.menuid ? app_root+'edit' : app_root+'add';
                    field.ismenu = field.hasOwnProperty('ismenu') ? field.ismenu : '0';
                    field.state  = field.hasOwnProperty('state') ? field.state : '0';
                    field.ocatid = catid;
                    admin.req(post_url,field,function(res){
                        layer.msg(res.msg,{shade:[0.4,'#000'],time:1500},function(){
                            if(res.code==1){
                                layer.close(index);
                                menusTb.refresh();
                            }
                            btn.removeAttr('stop');
                        });
                    },'post',{headersToken:true});
                    return false;
                });
            }
        });
    }/**/
    /*本身和子类不可选为上级*/
    function Exitem(data, id, flag){
        for(var a in data){
            if(flag){
                if(data[a].menuid == id){
                    data[a].disabled = true;
                    if(data[a].hasOwnProperty('children')) Exitem(data[a].children, id, false);
                }else{
                    if(data[a].hasOwnProperty('children')) Exitem(data[a].children, id, flag);
                }
            }else{
                data[a].disabled = true;
                if(data[a].hasOwnProperty('children')) Exitem(data[a].children, id, false);
            }
        }
    }/**/
});
</script>
<style>.treeTable-filter-hide{display:none !important;}</style>
<div class="layui-card-body">
    <div class="layui-card-box">
        <div class="layui-btn-group">
            <a class="layui-btn" id="dictItems-add"><i class="layui-icon layui-icon-add-circle"></i> 添加</a>
            <a class="layui-btn" id="dictItems-adds"><i class="layui-icon layui-icon-add-circle"></i> 批量</a>
            <a class="layui-btn" id="dictItems-del"><i class="layui-icon layui-icon-delete"></i> 删除</a>
            <a class="layui-btn" id="dictItems-sz" data="1"><i class="layui-icon">&#xe624;</i>展开</a>
        </div>
    </div>
    <table id="dictItems"></table>
</div>
<!--JS部分-->
<script type="text/javascript">
layui.use(['xmSelect','buildItems'],function(){
    var map_root = layui.cache.maps;
    var app_root = map_root + 'system.dict/';
    var layer = layui.layer,form = layui.form,admin = layui.admin,treeTable = layui.treeTable;
    var groupid = <?=$groupid?>;
    /*渲染表格*/
    var dictItemsTree = treeTable.render({
        elem: '#dictItems',
        url: app_root+"items?do=json",
        where:{groupid:groupid},
        page: false,
        toolbar: [
            '<p><span class="layui-inline"><input class="layui-input" id="edtSearch" placeholder="输入关键字" style="width:140px;margin-right:5px;height:30px;"></span>',
            '<button id="btnSearch" class="layui-btn layui-btn-sm layui-btn-primary layui-inline"><i class="layui-icon"></i>搜索</button>',
            '<button id="btnClearSearch" class="layui-btn layui-btn-sm layui-btn-primary layui-inline"><i class="layui-icon"></i>清除搜索</button></p>'
            ].join(''),
        cols: [[
            {type:'checkbox',fixed:'left'},
            {field:'name',edit:'text',minWidth:100,title:'项名'},
            {field:'value',edit:'text',minWidth:100,title:'项值'},
            {field:"editor",width:80,align:'center',title:"编辑"},
            {field:'listorder',width:60,edit:'text',align:'center',title:'排序'},
            {field:'state',width:60,align:'center',templet:function(d){return '<input type="checkbox" name="state" lay-skin="switch" lay-text="是|否" lay-filter="dictItems-chang" value="'+d.state+'" data-json="'+encodeURIComponent(JSON.stringify(d))+'"'+(d.state==1 ? ' checked' : '')+'>';},unresize:true,title:'显示'},
            {fixed:'right',width:140,align:'center',toolbar:'<div><a class="layui-btn layui-btn-xs" lay-event="add">添加</a><a class="layui-btn layui-btn-xs layui-btn-warm" lay-event="edit">编辑</a><a class="layui-btn layui-btn-xs layui-btn-danger" lay-event="del">删除</a></div>',title:'操作'}
        ]],
        tree: {
            data:{isSimpleData:true},
            customName:{name:'name',id:'id',pid:'parentid'},
            view:{showIcon:false},
            async:{enable:false}
        }
    });/**/
    /*搜索、清除搜索*/
    $('#btnSearch').click(function(){
        var keywords = $('#edtSearch').val(), tableId = 'dictItems';
        if(keywords){
            /* 隐藏行形式 */
            treeTable.expandAll(tableId,true);treeTable.expandAll(tableId,false);
            var $trList = dictItemsTree.config.elem.next().find('.layui-table-main table tbody').children('tr');
            $trList.addClass('treeTable-filter-hide');
            var soList = [];
            $trList.each(function(){
                var $this = $(this), index = $this.data('index');
                $this.children('td').each(function(){
                    if($(this).text().indexOf(keywords) !== -1){
                        soList.push(index);
                        return false;
                    }
                });
            });
            for (var j = 0; j < soList.length; j++) {
                var $tr = $trList.filter('[data-index="' + soList[j] + '"]');
                $tr.removeClass('treeTable-filter-hide');
                var level = parseInt($tr.data('level'));
                // 联动子级
                $tr.nextAll('tr').each(function () {
                    if (parseInt($(this).data('level')) <= level) return false;
                    $(this).removeClass('treeTable-filter-hide');
                });
                var $icon = $tr.find('.layui-table-tree-flexIcon i.layui-icon');
                if ($icon.hasClass('layui-icon-triangle-d')) treeTable.expandNode(tableId,{index:soList[j],expandFlag:false});
                // 联动父级
                $tr.prevAll('tr').each(function () {
                    var num = parseInt($(this).data('level'));
                    if (num < level) {
                        $(this).removeClass('treeTable-filter-hide');
                        var index = $(this).data('index');
                        var $icon = $(this).find('.layui-table-tree-flexIcon i.layui-icon');
                        if ($icon.hasClass('layui-icon-triangle-r')) treeTable.expandNode(tableId,{index:index,expandFlag:true});
                        level = num;
                    }
                });
            }/**/
        }else{
            treeTable.renderData(tableId);
        }
    });
    $('#btnClearSearch').click(function(){treeTable.renderData('dictItems');});/**/
    /*展开或折叠*/
    $('#dictItems-sz').click(function(){
        var ob = $(this),i,t;
        if(ob.attr('data')==1){
            treeTable.expandAll('dictItems',true);
            i=0;t='<i class="layui-icon">&#xe67e;</i>折叠';
        }else{
            treeTable.expandAll('dictItems',false);
            i=1;t='<i class="layui-icon">&#xe624;</i>展开';
        }
        ob.attr('data',i).html(t);
    });/**/
    /*状态*/
    form.on('switch(dictItems-chang)',function(obj){
        var json = JSON.parse(decodeURIComponent($(this).data('json')));
        var av = obj.elem.checked ? 1 : 0;
        admin.req(app_root+"iedit?do=up",{id:json.id,av:av,af:obj.elem.name},function(res){
            layer.tips(res.msg,obj.othis,{time:1000});
        },'post',{headersToken:true});
    });/**/
    /*快编监听*/
    treeTable.on('edit(dictItems)',function(obj){
        admin.req(app_root+"iedit?do=up",{id:obj.data.id,av:obj.value,af:obj.field},function(res){
            layer.msg(res.msg,{shade:[0.4,'#000'],time:1000});
        },'post',{headersToken:true});
    });/**/
    /*顶部添加按钮*/
    $('#dictItems-add').on('click',function(){addOpen();});/**/
    /*顶部批量按钮*/
    $('#dictItems-adds').on('click',function(){addsOpen(0);});/**/
    /*顶部删除按钮*/
    $('#dictItems-del').on('click', function(){
        var checkRows = treeTable.checkStatus('dictItems');
        if(checkRows.data.length == 0){return layer.msg('请选择需删除的字典项');}
        var ids = checkRows.data.map(function(d){return d.id;});
        del(ids);
    });/**/
    /*工具条监听*/
    treeTable.on('tool(dictItems)', function(obj){
        var data = obj.data;
        if(obj.event == 'add'){
            addsOpen(data.id);
        }else if(obj.event == 'edit'){
            addOpen(data);
        }else if(obj.event == 'del'){
            del(data.id);
        }
    });/**/
    /*删除*/
    function del(ids){
        layer.confirm('确定要删除所选字典项吗？', function(index){
            layer.close(index);
            admin.req(app_root+"idel",{id:ids},function(res){
                layer.msg(res.msg,{shade:[0.4,'#000'],time:1500},function(){
                    if(res.code==1){
                        treeTable.reload('dictItems');
                    }
                });
            },'post',{headersToken:true});
        });
    }/**/
    /*批量添加*/
    function addsOpen(id){
        admin.open({
            type: 1,
            bid: 'dictItemss_items',
            btn: ['确认添加', '取消'],
            area: ['460px', '350px'],
            title: '批量添加字典项',
            success: function(l,index){
                l.children('.layui-layer-content').css('overflow', 'visible');
                layui.buildItems.build({
                    bid: 'dictItemss_items',
                    data: [
                        {name:"groupid",type:"hidden",value:groupid},
                        {name:"pid",title:"上级ID",type:"number",value:id,verify:'required',placeholder:"请输入上级ID，0：为顶级",must:true},
                        {name:"titles",title:"字典项名",type:"textarea",value:'',verify:'required',style:"height:160px",placeholder:"可批量添加一行一个：字典项名|字典项值",must:true}
                    ]
                });
                form.on('submit(dictItemss_items)',function(data){
                    var btn = $(this);
                    if (btn.attr('stop')){return false}else{btn.attr('stop',1)}
                    admin.req(app_root+"iadds",data.field,function(res){
                        layer.msg(res.msg,{shade:[0.4,'#000'],time:1500},function(){
                            if(res.code==1){
                                layer.close(index);
                                treeTable.reload('dictItems');
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
            bid: 'dictItems_items',
            btn: ['保存', '取消'],
            area: ['660px', '600px'],
            title: (Dt ? '编辑' : '添加') + '字典项',
            success: function(l,index){
                layui.buildItems.build({
                    bid: 'dictItems_items',
                    data: [
                        {name:"id",type:"hidden"},
                        {name:"groupid",type:"hidden",value:groupid},
                        {name:"parentid",title:"上级字项",type:"html",html:'<div id="dictItems-list-tree" class="v-xmselect-tree"></div>',must:true},
                        {name:"name",title:"字典项名",type:"text",value:'',verify:'required',placeholder:"请输入字典项名称",must:true},
                        {name:"value",title:"字典项值",type:"text",value:'',verify:'required',placeholder:"请输入字典项值",must:true},
                        {name:"listorder",title:"排序编号",type:"number",value:'100',verify:'required',placeholder:"请输入排序数字"},
                        {name:"state",title:"字项状态",type:"switch",value:'1'}
                    ]
                });
                form.val('dictItems_items_form',Dt);
                /*渲染下拉树 https://maplemei.gitee.io/xm-select/#/component/options*/
                var data = JSON.parse(JSON.stringify(treeTable.getData('dictItems')));
                if(Dt) Exitem(data,Dt.id,true);
                layui.xmSelect.render({
                    el: '#dictItems-list-tree',
                    name: 'parentid',
                    tips: '顶级字典项',
                    height: '430px',
                    data: data,
                    filterable: true,
                    radio: true,
                    clickClose: true,
                    model: {label:{type:'text'}},
                    initValue: [Dt ? Dt.parentid : ''],
                    prop: {name:'name',value:'id',disabled:'disabled'},
                    tree: {show:true,indent:25,strict:false,expandedKeys:true}
                });
                form.on('submit(dictItems_items)',function(data){
                    var btn = $(this);
                    if (btn.attr('stop')){return false}else{btn.attr('stop',1)}
                    var field = data.field;
                    var post_url = field.id ? app_root+'iedit' : app_root+'iadd';
                    field.state  = field.hasOwnProperty('state') ? field.state : '0';
                    admin.req(post_url,field,function(res){
                        layer.msg(res.msg,{shade:[0.4,'#000'],time:1500},function(){
                            if(res.code==1){
                                layer.close(index);
                                Dt ? treeTable.reloadData('dictItems') : treeTable.reload('dictItems');
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
                if(data[a].id == id){
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
<style>
#dictTreeBar{padding:10px 15px;border:1px solid #e6e6e6;background-color:#f2f2f2}
#dictTree{border:1px solid #e6e6e6;border-top:none;padding:10px 5px;overflow:auto;height:-webkit-calc(100vh - 260px);height:-moz-calc(100vh - 260px);height:calc(100vh - 260px)}
.layui-tree-entry .layui-tree-txt{padding:0 5px;border:1px transparent solid;text-decoration:none!important}
.layui-tree-entry.dict-tree-click .layui-tree-txt{background-color:#fff3e0;border:1px #ffe6b0 solid}
</style>
<div class="layui-fluid">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md3">
            <div class="layui-card">
                <div class="layui-card-body" style="padding:10px;">
                    <!-- 树工具栏 -->
                    <div class="layui-form toolbar" id="dictTreeBar">
                        <div class="layui-btn-group">
                            <a id="dictType-add" class="layui-btn layui-btn-sm icon-btn" v-show="@system.dict/gadd"><i class="layui-icon">&#xe654;</i>添加</a>
                            <a id="dictType-edit" class="layui-btn layui-btn-sm icon-btn" v-show="@system.dict/gedit"><i class="layui-icon">&#xe642;</i>修改</a>
                            <a id="dictType-del" class="layui-btn layui-btn-sm icon-btn" v-show="@system.dict/gdel"><i class="layui-icon">&#xe640;</i>删除</a>
                        </div>
                    </div>
                    <!-- 左树 -->
                    <div id="dictTree"></div>
                </div>
            </div>
        </div>
        <div class="layui-col-md9">
            <div class="layui-card">
                <div class="layui-card-header">
                    <form class="layui-form render">
                        <input type="hidden" name="groupid" id="dict-groupid" value=""/>
                        <div class="layui-form-item">
                            <div class="layui-inline" style="width:72px;">
                                <select name="fields">
                                    <option value="">属性</option>
                                    <option value="0">标题</option>
                                    <option value="1">编码</option>
                                    <option value="2">查询</option>
                                    <option value="3">编辑</option>
                                    <option value="4">备注</option>
                                </select>
                            </div>
                            <div class="layui-inline" style="width:150px;"><input type="text" name="kw" placeholder="关键词" autocomplete="off" class="layui-input" lay-affix="clear"/></div>
                            <div class="layui-inline" style="width:200px;"><input type="text" name="sotime" date-render placeholder="创建时间" class="layui-input" lay-affix="clear"/></div>
                            <div class="layui-inline">
                                <div class="layui-btn-group">
                                    <button class="layui-btn" lay-submit lay-filter="search-dict"><i class="layui-icon layui-icon-search"></i> 搜索</button>
                                    <a class="layui-btn" lay-submit lay-filter="search-dict-all" onclick="$('#dict-groupid').val('')"><i class="layui-icon layui-icon-light"></i>全部</a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="layui-card-body">
                    <div class="layui-card-box">
                        <div class="layui-btn-group">
                            <a class="layui-btn" id="dict-add" v-show="@system.dict/add"><i class="layui-icon layui-icon-add-circle"></i> 添加</a>
                            <a class="layui-btn" id="dict-del" v-show="@system.dict/del"><i class="layui-icon layui-icon-delete"></i> 删除</a>
                        </div>
                    </div>
                    <table lay-filter="dict" id="dict"></table>
                </div>
            </div>
        </div>
    </div>
</div>
<!--JS部分-->
<script type="text/javascript">
layui.use(['vinfo', 'xmSelect', 'buildItems'], function(){
    var map_root = layui.cache.maps;
    var app_root = map_root + 'system.dict/';
    var layer=layui.layer,table=layui.table,form=layui.form,admin=layui.admin;
    /*==============左树结构===============*/
    var dictObj,dictData,dictKey; /*左树 选中数据 和 总树数据*/
    function renderTree(data,load){
        if(!load) dictObj = data[0];
        dictKey = {};
        $.each(data,function(k,v){
            dictKey[v.id] = v.title;
        });
        if(data){
            dictData = toTree(data);
            doTree(dictData,load);
        }else{
            admin.req(app_root + "index?do=dict",function(res){
                dictData = toTree(res);
                doTree(dictData,load);
            });
        }
    }
    function doTree(data,load){
        layui.tree.render({
            elem: '#dictTree',
            data: data,
            onlyIconControl: true,
            click: function(obj){
                $('#dictTree').find('.dict-tree-click').removeClass('dict-tree-click');
                $(obj.elem).children('.layui-tree-entry').addClass('dict-tree-click');
                dictObj = obj.data;
                $('#dict-groupid').val(obj.data.id);
                table.reloadData('dict',{where:{groupid:obj.data.id},page:{curr:1}});
            }
        });
        var item = $('#dictTree .layui-tree-entry:first');
        load ? item.find('.layui-tree-main>.layui-tree-txt').trigger('click') : item.addClass('dict-tree-click');
    }
    /*初始渲染*/
    renderTree(<?=$Types?>);
    /*左树添加按钮*/
    $('#dictType-add').on('click',function(){organOpen();});/**/
    /*左树编辑按钮*/
    $('#dictType-edit').on('click',function(){organOpen(dictObj);});/**/
    /*左树删除按钮*/
    $('#dictType-del').on('click', function(){
        if(!dictObj) return layer.msg('未选择类型');
        layer.confirm('确定要删除所选类型吗？子类型以及所属字典均会被删除！',function(){
            admin.req(app_root+"gdel",{id:dictObj.id},function(res){
                layer.msg(res.msg,{shade:[0.4,'#000'],time:1500},function(){
                    if(res.code==1) renderTree(res.data,true);
                });
            },'post',{headersToken:true});
        });
    });/**/
    /*树形编辑弹窗*/
    function organOpen(Dt){
        admin.open({
            type: 1,
            bid: 'organ_items',
            btn: ['保存', '取消'],
            area: ['500px', '500px'],
            title: (Dt ? '修改' : '添加') + '类型',
            success: function(l,index){
                layui.buildItems.build({
                    bid: 'organ_items',
                    data: [
                        {name:"id",type:"hidden"},
                        {name:"parentid",title:"上级类型",type:"html",html:'<div id="organ-list-tree" class="v-xmselect-tree"></div>',must:true},
                        {name:"title",title:"类型名称",type:"text",value:'',verify:'required',placeholder:"请输入类型名称",must:true},
                        {name:"note",title:"备注说明",type:"textarea",value:'',placeholder:"请输入备注说明(选填)"}
                    ]
                });
                form.val('organ_items_form',Dt);
                /*渲染下拉树 https://maplemei.gitee.io/xm-select/#/component/options*/
                var data = JSON.parse(JSON.stringify(dictData));
                if(Dt) Exitem(data,Dt.id,true);
                layui.xmSelect.render({
                    el: '#organ-list-tree',
                    name: 'parentid',
                    tips: '顶级类型',
                    height: '240px',
                    data: data,
                    filterable: true,
                    radio: true,
                    clickClose: true,
                    model: {label:{type:'text'}},
                    initValue: [Dt ? Dt.parentid : (dictObj ? dictObj.id : 0)],
                    prop: {name:'title',value:'id',disabled:'disabled'},
                    tree: {show:true,indent:25,strict:false,expandedKeys:true}
                });
                form.on('submit(organ_items)',function(data){
                    var btn = $(this);
                    if (btn.attr('stop')){return false}else{btn.attr('stop',1)}
                    var post_url = data.field.id ? app_root+'gedit' : app_root+'gadd';
                    admin.req(post_url,data.field,function(res){
                        layer.msg(res.msg,{shade:[0.4,'#000'],time:1500},function(){
                            if(res.code==1){
                                layer.close(index);
                                renderTree(res.data,true);
                            }
                            btn.removeAttr('stop');
                        });
                    },'post',{headersToken:true});
                    return false;
                });
            }
        });
    }/**/
    /*二维数组转为树形结构*/
    function toTree(data){
        let result = []
        let map = {};
        if(!Array.isArray(data)){return result;}
        data.forEach(item =>{
            delete item.children;
            item.spread = true;
            map[item.id] = item;
        });
        data.forEach(item =>{
            let parent = map[item.parentid];
            if(parent){
                (parent.children || (parent.children = [])).push(item);
            } else {
                result.push(item);
            }
        });
        return result;
    }/**/
    /*类型本身和子类不可选为上级*/
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
    }/*==============左树结构END==============*/
    /*渲染数据*/
    table.render({
        elem: '#dict',
        css: '.layui-table[lay-size=sm] td .layui-table-cell{height:38px;line-height:28px;}',
        url: app_root+"index?do=json",
        height: 'full-313',
        cols: [[
            {type:'checkbox',fixed:'left'},
            {field:"title",edit:'text',title:"名称"},
            {field:"code",edit:'text',title:"编码"},
            {field:"note",edit:'text',title:"备注"},
            {field:"groupid",width:100,align:'center',title:"类型",templet:function(d){return dictKey[d.groupid]}},
            {field:"addtime",width:100,align:'center',title:"创建",sort:!0,templet:function(d){return layui.util.toDateString(d.addtime*1000,'yyyy-MM-dd')}},
            {field:"editor",width:80,align:'center',title:"编辑"},
            {fixed:'right',width:170,align:'center',toolbar:'<div><a class="layui-btn layui-btn-xs layui-bg-blue" lay-event="list">字典项管理</a><a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a><a class="layui-btn layui-btn-xs layui-btn-danger" lay-event="del">删除</a></div>',title:'操作'}
        ]],
        page: true,
        limit:{$limit}
    });
    /*顶部添加按钮*/
    $('#dict-add').on('click',function(){dictOpen();});/**/
    /*顶部删除按钮*/
    $('#dict-del').on('click', function(){
        var checkRows = table.checkStatus('dict').data;
        if(checkRows.length === 0){return layer.msg('请选择需删除的字典');}
        var ids = checkRows.map(function(d){return d.id;});
        del(ids);
    });/**/
    /*快编监听*/
    table.on('edit(dict)',function(obj){
        admin.req(app_root+"edit?do=up",{id:obj.data.id,av:obj.value,af:obj.field},function(res){
            layer.msg(res.msg,{shade:[0.4,'#000'],time:500});
        },'post',{headersToken:true});
    });/**/
    /*工具条监听*/
    table.on('tool(dict)', function(obj){
        var data = obj.data;
        if(obj.event === 'edit'){
            dictOpen(data);
        }else if(obj.event === 'del'){
            del(data.id);
        }else if(obj.event === 'list'){
            admin.open({
                type: 2,
                offset: 'rb',
                title: data.title +' - 字典项管理',
                area: ["70%", "100%"],
                skin: 'layui-anim layui-anim-rl layui-layer-Right',
                url: app_root + 'items?groupid=' + data.id,
                success: function(){}
            });
        }
    });/**/
    /*删除*/
    function del(ids){
        layer.confirm('确定要删除所选字典吗？', function(){
            admin.req(app_root+"del",{id:ids},function(res){
                layer.msg(res.msg,{shade:[0.4,'#000'],time:1500},function(){
                    if(res.code==1) table.reloadData('dict');
                });
            },'post',{headersToken:true});
        });
    }/**/
    /*弹出窗*/
    function dictOpen(Dt){
        admin.open({
            type: 1,
            bid: 'dict_items',
            btn: ['保存', '取消'],
            area: ['800px','550px'],
            title: (Dt ? '编辑' : '添加') + '字典',
            success: function(l,index){
                layui.buildItems.build({
                    bid: 'dict_items',
                    data: [
                        {name:"id",type:"hidden"},
                        {name:"groupid",title:"所属类型",type:"html",html:'<div id="organ-list-tree" class="v-xmselect-tree"></div>',must:true},
                        {name:"title",title:"字典名称",type:"text",value:'',verify:'required',vertype:'tips',placeholder:"请输入字典名称",must:true},
                        {name:"code",title:"字典编码",type:"text",value:'',verify:'required',vertype:'tips',placeholder:"请输入字典编码",must:true},
                        {name:"sql",title:"查询语句",type:"textarea",id:'sql_str',value:'',placeholder:"若设置了查询语句，请确保原生SQL语法正确"},
                        {name:"note",title:"备注信息",type:"textarea",value:''},
                    ]
                });
                form.val('dict_items_form',Dt);
                /*判断帐号是否已被占用*/
                $("input").blur(function(){
                    var o = $(this);
                    var obj = o.attr('name');
                    var val = o.val();
                    if(obj=='code' && val.length>2){
                        admin.req(app_root+"index?do=check",{code:val,id:(Dt ? Dt.id : 0)},function(res){
                            if(res.code==1) layer.msg(res.msg,{shade:[0.4,'#000'],time:1500},function(){o.focus();});
                        },'post');
                        return ;
                    }
                });
                /*渲染下拉树 https://maplemei.gitee.io/xm-select/#/component/options*/
                var data = JSON.parse(JSON.stringify(dictData));
                layui.xmSelect.render({
                    el: '#organ-list-tree',
                    name: 'groupid',
                    tips: '所属类型',
                    height: '300px',
                    data: data,
                    filterable: true,
                    radio: true,
                    clickClose: true,
                    layVerify: 'required',
                    layVerType: 'tips',
                    layReqText: '请选择所属类型',
                    model: {label:{type:'text'}},
                    initValue: [Dt ? Dt.groupid : 0],
                    prop: {name:'title',value:'id',disabled:'disabled'},
                    tree: {show:true,indent:25,strict:false,expandedKeys:true}
                });
                layui.dropdown.render({
                    elem: '#sql_str',
                    data: [{id:'1',title:'SELECT id,title as name,id as value,parentid as pid,arrparentid as pids FROM vt_organ WHERE id > 1'}],
                    click: function(d){$('#sql_str').val(d.title);},
                    style: 'width:660px;height:100px;overflow-y:auto;overflow-x:hidden;'
                });
                form.on('submit(dict_items)',function(data){
                    var btn = $(this);
                    if (btn.attr('stop')){return false}else{btn.attr('stop',1)}
                    var post_url = data.field.id ? app_root+'edit' : app_root+'add';
                    admin.req(post_url,data.field,function(res){
                        layer.msg(res.msg,{shade:[0.4,'#000'],time:1500},function(){
                            if(res.code==1){
                                layer.close(index);
                                table.reloadData('dict');
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
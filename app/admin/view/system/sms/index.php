<div class="layui-fluid">   
    <div class="layui-card">
        <div class="layui-card-header">
            <form class="layui-form" lay-filter="sms-form-search">
                <div class="layui-form-item">
                    <div class="layui-inline" style="width:80px;">
                        <select name="fields">
                            <option value="">属性</option>
                            <option value="0">手机</option>
                            <option value="1">内容</option>
                            <option value="2">发送人</option>
                            <option value="3">结果</option>
                        </select>
                    </div>
                    <div class="layui-inline" style="width:150px;"><input type="text" name="kw" placeholder="关键词" autocomplete="off" class="layui-input" lay-affix="clear"></div>
                    <div class="layui-inline" style="margin-right:0">
                        <div class="layui-input-inline" style="width:192px;"><input type="text" name="sotime" id="sms-search-time" placeholder="时间" autocomplete="off" class="layui-input" lay-affix="clear"></div>
                    </div>
                    <div class="layui-inline">
                        <div class="layui-btn-group">
                            <button class="layui-btn" lay-submit lay-filter="top-sms-search"><i class="layui-icon layui-icon-search layuiadmin-button-btn"></i> 搜索</button>
                            <button class="layui-btn" lay-submit lay-filter="top-sms-all"><i class="layui-icon layui-icon-light"></i>全部</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="layui-card-body">
            <div class="layui-card-box">
                <div class="layui-btn-group">
                    <button class="layui-btn" id="top-sms-send"><i class="layui-icon layui-icon-add-circle"></i> 发送短信</button>
                    <button class="layui-btn" id="top-sms-del"><i class="layui-icon layui-icon-delete"></i> 删除记录</button>
                </div>
            </div>
            <table lay-filter="sms" id="sms"></table>
        </div>
    </div>
</div>
<!--JS部分-->
<script type="text/javascript">
layui.use(['buildItems'],function(){
    var app_root = layui.cache.maps + 'system.sms/';
    var table=layui.table,form=layui.form,admin=layui.admin;
    //渲染搜索元素
    form.render(null, 'sms-form-search');
    layui.laydate.render({elem:'#sms-search-time',range:true,format:'yyyy/MM/dd',done:function(){$('#sms-search-time').trigger('input')}});
    /*渲染数据*/
    table.render({
        elem: '#sms',
        url: app_root+"index?do=json",
        cols: [[
            {type:'checkbox',fixed:'left'},
            {field:"itemid",fixed:"left",width:60,align:'center',title:"ID",sort:!0},
            {field:"mobile",width:120,title:"手机号"},
            {field:"message",title:"内容"},
            {field:"word",width:80,align:'center',title:"字数"},
            {field:"sendtime",align:'center',width:150,title:"发送时间",sort:!0,templet:function(d){return layui.util.toDateString(d.sendtime*1000)}},
            {field:"editor",width:100,align:'center',title:"发送人"},
            {field:"code",width:80,align:'center',title:"发送结果"},
            {fixed:'right',width:68,align:'center',toolbar:'<div><a class="layui-btn layui-btn-xs" lay-event="del">删除</a></div>',title:'操作'}
        ]],
        page: true,
        limit:{$limit}
    });/**/
    /*监听搜索*/
    form.on('submit(top-sms-search)', function(data){
        var field = data.field;
        table.reloadData('sms',{where:field,page:{curr:1}});
        return false;
    });/**/
    /*监听全部按钮*/
    form.on('submit(top-sms-all)', function(){
        table.reloadData('sms',{where:'',page:{curr:1}});
        return false;
    });/**/
    /*顶部发送短信*/
    $('#top-sms-send').on('click', function(){
        admin.open({
            type: 1,
            bid: 'sms_items',
            btn: ['发送', '取消'],
            area: ['500px', '350px'],
            title: '发送短信',
            success: function(l,index){
                layui.buildItems.build({
                    bid: 'sms_items',
                    data: [
                        {name:"mobile",title:"接收号码",type:"text",value:'',verify:'phone',placeholder:"请输入接收手机号",must:true},
                        {name:"message",title:"短信内容",type:"textarea",value:'',verify:'required',placeholder:"请输入短信内容",must:true},
                        {name:"sign",title:"短信签名",type:"text",value:'【微特】'}
                    ]
                });
                form.on('submit(sms_items)',function(data){
                    var btn = $(this);
                    if (btn.attr('stop')){return false}else{btn.attr('stop',1)}
                    admin.req(app_root+"send",data.field,function(res){
                        layer.msg(res.msg,{shade:[0.4,'#000'],time:1500},function(){
                            layer.close(index);
                            if(res.code==1){
                                table.reloadData('sms');
                            }
                            btn.removeAttr('stop');
                        });
                    },'post');
                    return false;
                });
            }
        });
    });/**/
    /*顶部删除短信按钮*/
    $('#top-sms-del').on('click', function(){
        var checkData = table.checkStatus('sms').data;
        if (checkData.length === 0){return layer.msg('请选择短信记录');}
        var ids = checkData.map(function(d){return d.itemid;});
        del(ids);
    });/**/
    /*工具条监听*/
    table.on('tool(sms)', function(obj){
        var data = obj.data;
        if(obj.event === 'del'){
            del(data.itemid);
        }
    });/**/
    /*删除*/
    function del(ids){
        layer.confirm('确定要删除所选短信记录吗？', function(index){
            layer.close(index);
            admin.req(app_root+"del",{itemid:ids},function(res){
                layer.msg(res.msg,{shade:[0.4,'#000'],time:1500},function(){
                    if(res.code==1) table.reloadData('sms');
                });
            },'post');
        });
    }/**/
});
</script>
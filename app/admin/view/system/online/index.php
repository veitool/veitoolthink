<div class="layui-fluid">   
    <div class="layui-card">
        <div class="layui-card-header">
            <form class="layui-form" lay-filter="online-form-search">
                <div class="layui-form-item">
                    <div class="layui-inline" style="width:80px;">
                        <select name="fields">
                            <option value="">属性</option>
                            <option value="0">用户</option>
                            <option value="1">路径</option>
                            <option value="2">IP</option>
                        </select>
                    </div>
                    <div class="layui-inline" style="width:120px;"><input type="text" name="kw" placeholder="关键词" autocomplete="off" class="layui-input"></div>
                    <div class="layui-inline" style="margin-right:0">
                        <div class="layui-input-inline" style="width:172px;"><input type="text" name="sotime" id="online-search-time" placeholder="访问时间" autocomplete="off" class="layui-input"></div>
                    </div>
                    <div class="layui-inline" style="width:72px;">
                        <select name="type">
                            <option value="" selected="">类型</option>
                            <option value="0">后台</option>
                            <option value="1">会员</option>
                        </select>
                    </div>
                    <div class="layui-inline">
                        <div class="layui-btn-group">
                            <button class="layui-btn" lay-submit lay-filter="top-online-search"><i class="layui-icon layui-icon-search layuiadmin-button-btn"></i> 搜索</button>
                            <button class="layui-btn" lay-submit lay-filter="top-online-all"><i class="layui-icon layui-icon-light"></i>全部</button>
                        </div>
                    </div>
                    <div class="layui-btn-group" style="float:right;">
                        <a class="layui-btn layui-btn-primary layui-border-blue">当前在线：<span id="online_count"></span></a>
                    </div>
                </div>
            </form>
        </div>
        <div class="layui-card-body">
            <table lay-filter="online" id="online"></table>
        </div>
    </div>
</div>
<!--JS部分-->
<script type="text/javascript">
layui.use(['vinfo'],function(){
    var map_root = layui.cache.maps;
    var app_root = map_root + 'system.online/';
    var table=layui.table,form=layui.form;
    var ip = '{:VT_IP}';
    //渲染搜索元素
    form.render(null, 'online-form-search');
    layui.laydate.render({elem:'#online-search-time',range:true,format:'yyyy/MM/dd'});
    /*渲染数据*/
    table.render({
        elem: '#online',
        url: app_root+"index?do=json",
        cols: [[
            {type:'checkbox',fixed:'left'},
			{field:"username",width:120,title:"用户",templet:function(d){return '<a style="cursor:pointer;" lay-event="userinfo"><font'+ (!d.type && d.ip==ip ? ' color=blue' : '') +'>'+ d.username +'</font></a>'}},
			{field:"url",title:"路径"},
            {field:"type",width:80,align:'center',title:"类型",templet:function(d){return d.type==1 ? '会员': '后台'}},
            {field:"ip",width:150,align:'center',title:"IP",toolbar:'<div><a style="cursor:pointer;" lay-event="showip">{{d.ip}}</a></div>'},
            {field:"etime",align:'center',width:150,title:"时间",sort:!0,templet:function(d){return layui.util.toDateString(d.etime*1000)}}
        ]],
        done: function(res){
            if(res.msg){
                $('#online_count').html(res.msg + ' 人');
            }
        },
        page: true,
        limit:{$limit}
    });/**/
    /*监听搜索*/
	form.on('submit(top-online-search)', function(data){
        table.reloadData('online',{where:data.field,page:{curr:1}});
        return false;
	});/**/
    /*监听全部按钮*/
	form.on('submit(top-online-all)', function(){
		table.reloadData('online',{where:'',page:{curr:1}});
        return false;
	});/**/
    /*工具条监听*/
    table.on('tool(online)', function(obj){
        var data = obj.data;
        if(obj.event === 'userinfo'){
            if(data.type>0){
                var type = 'user';
                var title = '会员详细';
                var url = map_root + 'member.index/index?do=info&username=' + data.username;
            }else{
                var type = 'muser';
                var title = '用户详细';
                var url = map_root + 'system.manager/index?do=info&username=' + data.username
            }
            layui.vinfo.open({type:type,title:title,url:url});
        }else if(obj.event === 'showip'){
            layui.admin.util.ip(data.ip);
        }
    });/**/
});
</script>
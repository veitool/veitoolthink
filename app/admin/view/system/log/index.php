<div class="layui-fluid">   
    <div class="layui-card">
        <div class="layui-tab layui-tab-admin" lay-filter="log_top_tab">
            <ul class="layui-tab-title">
                <li lay-id="login">登录日志</li>
                <li lay-id="manager">后台日志</li>
                <li lay-id="web">访问日志</li>
            </ul>
        </div>
        <div class="layui-card-header">
            <form class="logTab_login layui-form render">
                <div class="layui-form-item">
                    <div class="layui-inline" style="width:72px;">
                        <select name="fields">
                            <option value="">属性</option>
                            <option value="0">用户</option>
                            <option value="1">IP</option>
                            <option value="2">密码</option>
                            <option value="3">终端</option>
                        </select>
                    </div>
                    <div class="layui-inline" style="width:150px;"><input type="text" name="kw" placeholder="关键词" autocomplete="off" class="layui-input" lay-affix="clear"/></div>
                    <div class="layui-inline" style="width:80px;"><input type="text" name="message" placeholder="结果" autocomplete="off" class="layui-input" lay-affix="clear"/></div>
                    <div class="layui-inline" style="width:200px;"><input type="text" name="sotime" date-render placeholder="登录时间" class="layui-input" lay-affix="clear"/></div>
                    <div class="layui-inline" style="width:72px;"><select name="admin" id="search_log_select"></select></div>
                    <div class="layui-inline">
                        <div class="layui-btn-group">
                            <button class="layui-btn" lay-submit lay-filter="search-loginlog"><i class="layui-icon layui-icon-search"></i> 搜索</button>
                            <a class="layui-btn" lay-submit lay-filter="search-loginlog-all"><i class="layui-icon layui-icon-light"></i> 全部</a>
                            <a class="layui-btn" id="loginlog-clear" v-show="@system.log/ldel"><i class="layui-icon layui-icon-delete"></i> 清理</a>
                        </div>
                    </div>
                </div>
            </form>
            <form class="logTab_manager layui-form render">
                <div class="layui-form-item">
                    <div class="layui-inline" style="width:72px;">
                        <select name="fields">
                            <option value="">属性</option>
                            <option value="0">用户</option>
                            <option value="1">IP</option>
                            <option value="2">路径</option>
                        </select>
                    </div>
                    <div class="layui-inline" style="width:150px;"><input type="text" name="kw" placeholder="关键词" autocomplete="off" class="layui-input" lay-affix="clear"/></div>
                    <div class="layui-inline" style="width:200px;"><input type="text" name="sotime" date-render placeholder="操作时间" class="layui-input" lay-affix="clear"/></div>
                    <div class="layui-inline">
                        <div class="layui-btn-group">
                            <button class="layui-btn" lay-submit lay-filter="search-managerlog"><i class="layui-icon layui-icon-search"></i> 搜索</button>
                            <a class="layui-btn" lay-submit lay-filter="search-managerlog-all"><i class="layui-icon layui-icon-light"></i>全部</a>
                            <a class="layui-btn" id="managerlog-clear" v-show="@system.log/mdel"><i class="layui-icon layui-icon-delete"></i> 清理</a>
                        </div>
                    </div>
                </div>
            </form>
            <form class="logTab_web layui-form render">
                <div class="layui-form-item">
                    <div class="layui-inline" style="width:72px;">
                        <select name="fields">
                            <option value="">属性</option>
                            <option value="0">用户</option>
                            <option value="1">IP</option>
                            <option value="2">路径</option>
                            <option value="3">终端</option>
                        </select>
                    </div>
                    <div class="layui-inline" style="width:150px;"><input type="text" name="kw" placeholder="关键词" autocomplete="off" class="layui-input" lay-affix="clear"/></div>
                    <div class="layui-inline" style="width:200px;"><input type="text" name="sotime" date-render placeholder="操作时间" class="layui-input" lay-affix="clear"/></div>
                    <div class="layui-inline">
                        <div class="layui-btn-group">
                            <button class="layui-btn" lay-submit lay-filter="search-weblog"><i class="layui-icon layui-icon-search"></i> 搜索</button>
                            <a class="layui-btn" lay-submit lay-filter="search-weblog-all"><i class="layui-icon layui-icon-light"></i>全部</a>
                            <a class="layui-btn" id="weblog-clear" v-show="@system.log/wdel"><i class="layui-icon layui-icon-delete"></i> 清理</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="layui-card-body">
            <span class="logTab_login"><table lay-filter="loginlog" id="loginlog"></table></span>
            <span class="logTab_manager"><table lay-filter="managerlog" id="managerlog"></table></span>
            <span class="logTab_web"><table lay-filter="weblog" id="weblog"></table></span>
        </div>
    </div>
</div>
<!--JS部分-->
<script type="text/javascript">
layui.use(['vinfo'],function(){
    var mtab = layui.router().search.mtab; mtab = mtab ? mtab : 'login';
    var map_root = layui.cache.maps;
    var app_root = map_root + 'system.log/';
    var table=layui.table,admin=layui.admin;
    var limit = {$limit}, PT = {$PT|raw};
    var log_select = '<option value="">位置</option>';$.each(PT,function(k,v){log_select += '<option value="'+ k +'">'+ v +'</option>';});
    /*位置选择*/
    $('#search_log_select').html(log_select);
    /*初始选中选卡*/
    $('*[lay-id="'+ mtab +'"]').addClass('layui-this');
    /*定义开关防重复请求*/
    var do_login = true, do_manager = true, do_web = true;
    /*顶部初始面板*/
    changBox(mtab);
    /*顶部选项卡监听*/
    layui.element.on('tab(log_top_tab)', function(){
        mtab = this.getAttribute("lay-id");
        changBox(mtab);
    });
    /*面板切换*/
    function changBox(tab){
        $("[class^='logTab_']").hide();
        $('.logTab_' + tab).show();
        /*登录日志*/
        if(tab == 'login' && do_login){
            do_login = false;
            table.render({
                elem: '#loginlog',
                cellExpandedMode:'tips',
                url: app_root+"login",
                cols: [[
                    {field:"logid",fixed:"left",width:80,align:'center',title:"ID",sort:!0},
                    {field:"username",width:120,title:"帐号",toolbar:'<div><a style="cursor:pointer;" lay-event="userinfo">{{d.username}}</a></div>'},
                    {field:"password",width:200,align:'center',title:"密码"},
                    {field:"logintime",width:150,align:'center',title:"时间",sort:!0,templet:function(d){return layui.util.toDateString(d.logintime*1000)}},
                    {field:"loginip",align:'center',width:150,title:"IP",toolbar:'<div><a style="cursor:pointer;" lay-event="showip">{{d.loginip}}</a></div>'},
                    {field:"agent",align:'center',expandedMode:'tips',title:"终端"},
                    {field:"admin",width:60,align:'center',title:"位置",templet:function(d){return PT[d.admin]}},
                    {field:"message",width:80,align:'center',title:"结果"},
                    {fixed:'right',width:60,align:'center',toolbar:'<div><a class="layui-btn layui-btn-xs" lay-event="check">校验</a></div>',title:'操作'}
                ]],
                page: true,
                limit:limit
            });/**/
            /*日志清理*/
            $('#loginlog-clear').on('click',function(){
                layer.confirm('为系统安全，系统仅能删除30天之前的日志！', function(){
                    admin.req(app_root+"ldel",function(res){
                        layer.msg(res.msg,{shade:[0.4,'#000'],time:1500},function(){
                            if(res.code==1) table.reloadData('loginlog');
                        });
                    },'post');
                });
            });/**/
            /*工具条监听*/
            table.on('tool(loginlog)', function(obj){
                var data = obj.data;
                if(obj.event === 'check'){
                    layer.prompt({
                        formType: 3,
                        title: '密码校验，请输入密码(明文)'
                    },function(value, index){
                        layer.close(index);
                        admin.req(app_root+"login?do=check",{logid:data.logid,password:value},function(res){
                            layer.msg(res.msg,{shade:[0.4,'#000'],time:1500});
                        },'post');
                    });
                }else if(obj.event === 'userinfo'){
                    if(data.admin>0){
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
                    admin.util.ip(data.loginip);
                }
            });/**/
        }/**/
        /*管理日志*/
        if(tab == 'manager' && do_manager){
            do_manager = false;
            table.render({
                elem: '#managerlog',
                cellExpandedMode:'tips',
                url: app_root + "manager",
                cols: [[
                    {field:"logid",fixed:"left",width:80,align:'center',title:"ID",sort:!0},
                    {field:"url",edit:'text',title:"路径"},
                    {field:"username",width:120,align:'center',title: "用户"},
                    {field:"ip",align:'center',width:150,title:"IP",toolbar:'<div><a style="cursor:pointer;" lay-event="showip">{{d.ip}}</a></div>'},
                    {field:"logtime",width:150,align:'center',title:"时间",sort:!0,templet:function(d){return layui.util.toDateString(d.logtime*1000)}}
                ]],
                page: true,
                limit: limit
            });/**/
            /*日志清理*/
            $('#managerlog-clear').on('click',function(){
                layer.confirm('为系统安全，系统仅能删除7天之前的日志！', function(){
                    admin.req(app_root+"mdel",function(res){
                        layer.msg(res.msg,{shade:[0.4,'#000'],time:1500},function(){
                            if(res.code==1) table.reloadData('managerlog');
                        });
                    },'post');
                });
            });/**/
            /*工具条监听*/
            table.on('tool(managerlog)', function(obj){
                var data = obj.data;
                if(obj.event === 'showip'){
                    admin.util.ip(data.ip);
                }
            });/**/
        }/**/
        /*网站日志*/
        if(tab == 'web' && do_web){
            do_web = false;
            table.render({
                elem: '#weblog',
                cellExpandedMode:'tips',
                url: app_root + "web",
                cols: [[
                    {field:"logid",fixed:"left",width:80,align:'center',title:"ID",sort:!0},
                    {field:"url",edit:'text',title:"路径"},
                    {field:"logtime",width:150,align:'center',title:"时间",sort:!0,templet:function(d){return layui.util.toDateString(d.logtime*1000)}},
                    {field:"ip",align:'center',width:150,title:"IP",toolbar:'<div><a style="cursor:pointer;" lay-event="showip">{{d.ip}}</a></div>'},
                    {field:"username",width:120,align:'center',title: "用户",toolbar:'<div><a style="cursor:pointer;" lay-event="userinfo">{{d.username}}</a></div>'},
                    {field:"agent",align:'center',expandedMode:'tips',title:"终端"}
                ]],
                page: true,
                limit: limit
            });/**/
            /*日志清理*/
            $('#weblog-clear').on('click',function(){
                layer.confirm('确定要清理前台访问日志吗？', function(){
                    admin.req(app_root+"wdel",function(res){
                        layer.msg(res.msg,{shade:[0.4,'#000'],time:1500},function(){
                            if(res.code==1) table.reloadData('weblog');
                        });
                    },'post');
                });
            });/**/
            /*工具条监听*/
            table.on('tool(weblog)', function(obj){
                var data = obj.data;
                if(obj.event === 'showip'){
                    admin.util.ip(data.ip);
                }else if(obj.event === 'userinfo'){
                    layui.vinfo.open({type:'user',title:'会员详细',url:map_root + 'member.index/index?do=info&username=' + data.username});
                }
            });/**/
        }/**/
    }/**/
});
</script>
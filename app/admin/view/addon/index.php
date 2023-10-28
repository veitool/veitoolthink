<style>
#addon + .layui-table-view .layui-table-body tbody > tr > td{padding:0;}
#addon + .layui-table-view .layui-table-body tbody > tr > td > .layui-table-cell{height:50px;line-height:50px;padding:0 5px;}
.addon_item{width:30px;height:30px;line-height:10px;cursor:pointer;position:relative;margin:10px 0px 0 2px;padding:0px;border:1px solid #ddd;background:#fff;display:-webkit-box;-moz-box-align:center;-webkit-box-align:center;-moz-box-pack:center;-webkit-box-pack:center;}
.addon_item img{max-width:24px;max-height:24px;border:0}
</style>
<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-tab layui-tab-admin" lay-filter="addon_top_tab">
            <ul class="layui-tab-title" id="addon_category">
                <li lay-id="-1" class="layui-this">全部</li>
                <li lay-id="0">其他</li>
            </ul>
        </div>
        <div class="layui-card-header">
            <form class="layui-form render">
                <div class="layui-form-item">
                    <div class="layui-inline" style="width:160px;">
                        <input type="text" name="kw" id="addon-kw" placeholder="搜索" autocomplete="off" class="layui-input" lay-affix="clear"/>
                    </div>
                    <div class="layui-inline">
                        <button lay-submit lay-filter="addon-search" style="display:none">搜索</button>
                        <div class="layui-btn-group">
                            <a class="layui-btn layui-btn-danger" id="Taddon-0"><i class="layui-icon">&#xe748;</i> 全部</a>
                            <a class="layui-btn" id="Taddon-1"><i class="layui-icon">&#xe627;</i> 免费</a>
                            <a class="layui-btn" id="Taddon-2"><i class="layui-icon">&#xe65e;</i> 付费</a>
                            <a class="layui-btn" id="Taddon-3"><i class="layui-icon">&#x1005;</i> 本地已装</a>
                            <a class="layui-btn" id="top-addon-install"><i class="layui-icon layui-icon-upload-drag"></i> 离线安装</a>
                            <a class="layui-btn" id="top-addon-user"><i class="layui-icon layui-icon-username"></i> 会员信息</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="layui-card-body addontable">
            <table lay-filter="addon" id="addon"></table>
        </div>
    </div>
</div>
<!--状态-->
<script type="text/html" id="addon-state-tpl">
{{# if(typeof addons[d.name] != 'undefined'){ }}
<input type="checkbox" name="state" lay-skin="switch" lay-text="是|否" lay-filter="addon-state-chang" value="{{d.state}}" data-json="{{encodeURIComponent(JSON.stringify(d))}}" {{addons[d.name].state==1 ? 'checked' : ''}}>
{{# }else{ return '-' } }}
</script>
<!--操作-->
<script type="text/html" id="addon-tool-tpl">
{{# if(d.link){ }}
<a href="{{ d.link }}" target="_blank" class="layui-btn layui-btn-xs">了解详细</a>
{{# }else if(typeof addons[d.name] != 'undefined'){ }}
    {{# if(addons[d.name].version !== d.version){ }}
    <div class="layui-btn-group">
    <a class="layui-btn layui-btn-xs layui-btn-normal" lay-event="upgrade" style="width:72px;"><i class="layui-icon">&#xe681;</i> 升级</a>
    <a class="layui-btn layui-btn-xs layui-btn-normal" lay-event="versionlists"><i class="layui-icon">&#xe625;</i></a>
    </div><br/>
    {{# } }}
    {{# if(addons[d.name].config){ }}
    <div class="layui-btn-group">
    <a class="layui-btn layui-btn-xs" lay-event="setting"><i class="layui-icon" style="padding-right:1px;">&#xe714;</i>配置</a>
    <a class="layui-btn layui-btn-xs layui-btn-danger" lay-event="uninstall"><i class="layui-icon" style="padding-right:1px;">&#xe640;</i>卸载</a>
    </div>
    {{# }else{ }}
    <a class="layui-btn layui-btn-xs layui-btn-danger" lay-event="uninstall" style="width:100px;"><i class="layui-icon">&#xe640;</i> 卸载删除</a>
    {{# } }}
{{# }else{ }}
<div class="layui-btn-group">
<a class="layui-btn layui-btn-xs" lay-event="install" style="width:72px;"><i class="layui-icon">&#xe601;</i> 安装</a>
<a class="layui-btn layui-btn-xs" lay-event="versionlist"><i class="layui-icon">&#xe625;</i></a>
</div>
{{# } }}
</script>
<!--JS部分-->
<script>
var addons = {$addons|raw};
layui.use(function(){
    var type  = 0; // 0全部 1免费 2付费
    var table = layui.table,admin=layui.admin,form=layui.form;
    var catid = layui.router().search.catid; catid = catid ? catid : '-1'; // 分类
    var version  = layui.cache.version;
    var map_root = layui.cache.maps;
    var app_root = map_root + 'addon/';
    var app_api  = "{:config('veitool.api_url')}/";
    /*本地上传安装*/
    var uIndex;
    layui.upload.render({
        elem: '#top-addon-install',
        url: app_root + 'local',
        accept: "file",
        exts: 'zip',
        before: function(){uIndex = layer.load(2);this.data = {uid:getUser('uid'),token:getUser('token')};},
        error: function(){layer.close(uIndex);},
        done: function(res){
            layer.close(uIndex);
            return layer.msg(res.msg,{shade:[0.4,'#000'],time:3000},function(){
                if(res.code==1){
                    addons = res.data.addons;
                    table.reloadData('addon');
                }else if(res.code==2){
                    $("#top-addon-user").trigger("click");
                }
            });
        }
    });/**/
    /*会员信息*/
    $('#top-addon-user').on("click",function(){
        var userInfo = getUser();
        if(userInfo){
            layer.load();
            $.getJSON(app_api + 'api/addon/user', userInfo, function(res){
                layer.close(layer.index);
                if(res.code==1){
                    admin.open({
                        type: 1,
                        tpl: true,
                        data: res.data,
                        btn: ['退出登录', '取消'],
                        yes: function(id,lay){lay.find("[lay-submit]").trigger('click');},
                        area: ['450px', '300px'],
                        title: '会员信息',
                        content: [
                            '<div style="padding:20px 20px 0 20px;">',
                            '<p style="background:#009688;padding:15px 10px;color:#fff;margin-bottom:20px;border-radius:3px;line-height:28px;position:relative;"><b style="font-size:16px;">您好，{{ d.nickname }}</b><br/>您的帐号：{{ d.username }} 当前已经登录<br/>Veitool 平台将同步记录您所购买的插件</p>',
                            '<p style="margin-bottom:20px;"><i class="layui-icon" style="color:#009688">&#xe67b;</i> <a href="#" id="my-addon-url" target="_blank" style="color:#009688">我购买的插件</a></p>',
                            '<input type="submit" style="display:none;" id="addon_logout_submit" lay-submit></div>'
                        ].join(''),
                        success: function(l,index){
                            $('#my-addon-url').on('click',function(){
                                return $(this).attr('href',app_api + 'member.index#/member.addon/order');
                            });
                            $('#addon_logout_submit').on("click",function(){
                                $.getJSON(app_api + 'api/addon/logout',userInfo,function(res){
                                    layer.msg(res.msg,{shade:[0.4,'#000'],time:1000},function(){
                                        layer.close(index);
                                        if(res.code==1){
                                            admin.removeToken();
                                            table.reloadData('addon',{'url':app_api + "api/addon/index" + getUser('uids')});
                                        }
                                    });
                                });
                            });
                        }
                    });
                }else{
                    layer.msg(res.msg,{shade:[0.4,'#000'],time:1000},function(){
                        admin.removeToken();
                        $("#top-addon-user").trigger("click");
                    });
                }
            });
        }else{
            admin.open({
                type: 1,
                area: ['450px', '300px'],
                btn: ['立即登录', '注册帐号'],
                yes: function(id,lay){lay.find("[lay-submit]").trigger('click');},
                btn2: function(){window.open(app_api + 'member.register/index', "_blank");return false;},
                title: '会员登录',
                content: [
                    '<form class="layui-form model-form" lay-filter="addon_userlogin_form">',
                    '<h2 style="margin:10px 0 30px 30px;color:#009688;text-align:center;"><fieldset class="layui-elem-field layui-field-title"><legend style="font-size:16px;margin-left:0px;">温馨提示：此处登录帐号为 <a href="'+ app_api +'" style="color:#dd4b39" target="_blank">Veitool官网帐号</a></legend></fieldset></h2>',
                    '<div class="layui-form-item"><label class="layui-form-label layui-form-required">登录帐号:</label><div class="layui-input-block"><input type="text" name="username" id="username" class="layui-input" lay-verify="required|user" lay-verType="tips" lay-reqText="请输入登录帐号" placeholder="您的用户名、手机号或邮箱" autocomplete="off"/></div></div>',
                    '<div class="layui-form-item"><label class="layui-form-label layui-form-required">登录密码:</label><div class="layui-input-block"><input type="password" name="password" id="password" class="layui-input" lay-verify="required|pass" lay-verType="tips" lay-reqText="请输入登录密码" placeholder="请输入登录密码" autocomplete="off"/></div></div>',
                    '<input type="submit" style="display:none;" lay-filter="addon_userlogin_submit" lay-submit></form>'
                ].join(''),
                success: function(l,index){
                    form.verify({
                        user: function(v){if(!/^[\S]{4,20}$/.test(v)){return '请输入4-20位登录帐号';}},
                        pass: function(v){if(!/^[\S]{6,20}$/.test(v)){return '请输入6-20位登录密码';}}
                    });
                    form.on('submit(addon_userlogin_submit)',function(data){
                        var btn = $(this);
                        if (btn.attr('stop')){return false;}else{btn.attr('stop',1);}
                        admin.req(app_api + 'api/addon/login',data.field,function(res){
                            layer.msg(res.msg,{shade:[0.4,'#000'],time:1500},function(){
                                if(res.code==1){
                                    layer.close(index);
                                    admin.putToken(res.data);
                                    table.reloadData('addon',{'url':app_api + "api/addon/index" + getUser('uids')});
                                }
                                btn.removeAttr('stop');
                            });
                        },'post');
                        return false;
                    });
                }
            });
        }
    });/**/
    /*监听搜索*/
    form.on('submit(addon-search)', function(data){
        var field = data.field;field.catid = catid;field.version = version;
        table.reloadData('addon',{where:field,page:{curr:1}});
        return false;
    });/**/
    /*监听快捷按钮*/
    $("[id^='Taddon-']").on('click',function(){
        var $this = $(this),id = $this.attr('id').split('-')[1];
        $("[id^='Taddon-']").removeClass('layui-btn-danger');
        $this.addClass('layui-btn-danger');
        if(id=='3'){
            table.reloadData('addon',{page:false,url:app_root+'exist'+getUser('uids')});
        }else{
            type = id;
            var field = [];field.catid = catid;field.type = type;field.version = version;
            table.reloadData('addon',{where:field,url:app_api+"api/addon/index"+getUser('uids'),page:{curr:1}});
        }
    });/**/
    /*渲染数据*/
    table.render({
        elem: '#addon',
        size: 'sm',
        page: true,
        limit:{$limit},
        url: app_api + "api/addon/index" + getUser('uids'),
        where:{catid:catid,type:type,version:version,first:1},
        css: '.layui-table[lay-size=sm] td .layui-table-cell{height:auto;line-height:28px;}',
        cols: [[
            {field:"img",title:"配图",align:'center',width:60,templet:function(d){return '<div class="addon_item"><img src="'+ (d.img ? app_api + d.img : '') +'" lay-event="addon-event-image"/></div>';}},
            {field:'title',title:'名称',templet:function(d){return '<a href="'+ app_api + 'addon/'+ d.name +'" target="_blank">'+ d.title +'</a>';}},
            {field:'intro',title:'介绍',templet:function(d){return d.intro;}},
            {field:'author',width:100,align:'center',title:'作者',templet:function(d){return '<a href="'+ app_api +'" target="_blank">'+ d.author +'</a>';}},
            {field:'price',width:100,align:'center',title:'价格',templet:function(d){return d.price>0 ? '<font color=red>￥'+d.price+'</font>' : '<font color=green>免费</font>'}},
            {field:"down",width:100,align:'center',title:"下载",sort:!0},
            {field:"itemid",width:50,align:'center',title:"已买",templet:function(d){return d.isbuy ? '<i class="layui-icon" style="color:#009688">&#xe679;</i>' : '-';}},
            {field:'version',width:100,align:'center',title:'版本',templet:function(d){return (typeof addons[d.name] != 'undefined' && addons[d.name].version !== d.version) ? '<a href="'+ app_api + 'addon/'+ d.name +'?version='+d.version+'" target="_blank" lay-tips ="发现新版本:'+d.version+' 点击查看更新日志">'+addons[d.name].version + ' <i class="layui-badge-dot"></i></a>' : d.version}},
            {field:'homeweb',width:60,align:'center',title:'前台',templet:function(d){return (typeof addons[d.name] != 'undefined' && addons[d.name].home) ? '<a href="'+ addons[d.name].home + '" target="_blank"><span class="layui-badge layui-bg-green">查看</span></a>' : '-';}},
            {field:'demo',width:60,align:'center',title:'演示',templet:function(d){return d.demo ? '<a href="'+ d.demo + '" target="_blank"><span class="layui-badge">演示</span></a>' : '-';}},
            {field:'state',width:68,align:'center',templet:'#addon-state-tpl',unresize:true,title:'启用'},
            {fixed:'right',width:120,align:'center',toolbar:'#addon-tool-tpl',title:'操作'}
        ]],
        done: function(res){
            if(typeof res.msg != 'undefined' && $("#addon_category li").size() == 2){
                $.each(res.msg, function(k,v){
                    $('<li lay-id="' + v.catid + '">' + v.title + '</li>').insertBefore($("#addon_category li:last"));
                });
            }
        }
    });/**/
    /*顶部选项卡监听*/
    layui.element.on('tab(addon_top_tab)', function(){
        catid = this.getAttribute("lay-id");
        var field = [];field.catid = catid;field.type = type;field.version = version;
        table.reloadData('addon',{where:field,page:{curr:1}});
    });/**/
    /*工具条监听*/
    table.on('tool(addon)', function(obj){
        var data = obj.data;
        if(obj.event === 'setting'){
            admin.open({
                type: 1,
                area: ['60%','80%'],
                title: '【'+ data.title +' - 配置管理】',
                url: app_root + "setting?addon=" + data.name,
                btn: ['确认保存', '取消'],
                yes: function(id,lay){lay.find("[lay-submit]").trigger('click');}
            });
        }else if(obj.event === 'install' || obj.event === 'upgrade'){
            doReq(data.name,data.version,obj.event);
        }else if(obj.event === 'uninstall'){
            if(addons[data.name].state==1){
                return layer.msg('请先禁用插件再进行卸载',{shade:[0.4,'#000'],time:2000});
            }
            layer.confirm('确定要卸载该插件吗？',function(){
                var loadIndex = layer.load(2);
                admin.req(app_root + "uninstall",{name:data.name},function(res){
                    layer.close(loadIndex);
                    layer.msg(res.msg,{shade:[0.4,'#000'],time:1000},function(){
                        if(res.code==1){
                            addons = res.data.addons;
                            table.reloadData('addon');
                        }
                    });
                },'post',{headersToken:true});
            });
        }else if(obj.event === 'versionlist' || obj.event === 'versionlists'){
            var way = obj.event == 'versionlist' ? 'install' : 'upgrade';
            layui.dropdown.render({
                elem: this,
                show: true,
                data: data.releaselist,
                click: function(obj){
                    doReq(data.name,obj.title,way);
                },
                style: 'width:40px;margin-left:-35px;text-align:center;'
            });
        }else if(obj.event === 'addon-event-image'){
            var src = $(this).attr('src');
            layer.photos({photos:{data:[{alt:data.title,src:src}],start:'0'},anim:5,shade:[0.4,'#000']});
        }
    });/**/
    /*启用状态*/
    form.on('switch(addon-state-chang)',function(obj){
        var json = JSON.parse(decodeURIComponent($(this).data('json')));
        var state = obj.elem.checked ? 1 : 0;
        admin.req(app_root + "state",{name:json.name,state:state},function(res){
            addons = res.data.addons;
            layer.tips(res.msg,obj.othis,{time:2000});
            if(res.code == 1) loadLeftMenu();
        },'post');
    });/**/
    /*请求处理*/
    function doReq(name,version,way){
        if(way === 'upgrade'){
            if(addons[name].state==1) return layer.msg('请先禁用插件再进行升级',{shade:[0.4,'#000'],time:2000});
            layer.confirm('确认执行《<b>在线升级</b>》？<font color=red><br/>1、请务必做好代码和数据备份！<br/>2、升级后如出现冗余数据，请根据需要移除即可！<br/>3、不建议帐生产环境升级，请在本地完成升级测试！</font><br/>如有重要数据请备份后再操作！',function(){
                sendReq(name,version,'upgrade');
            });
        }else{
            sendReq(name,version,'install');
        }
    }/**/
    /**
     * 安装插件
     * @param {string}  name     插件名
     * @param {string}  version  插件版本
     * @param {string}  way      操作类型
     */
    function sendReq(name,version,way){
        var userInfo = getUser();
        if(!userInfo){
            layer.msg('请先登录Veitool会员后再进行操作！',{shade:[0.4,'#000'],time:2000},function(){
                $("#top-addon-user").trigger("click");
            });
            return false;
        }
        var index = layer.load(2);
        admin.req(app_root + way,{name:name,version:version,uid:userInfo.uid,token:userInfo.token},function(res){
            layer.close(index);
            if(res.code==2){
                var Iframe = 0;
                admin.open({
                    type: 2,
                    area: ['690px', '738px'],
                    title: '插件支付',
                    content: res.data.payurl + '&t=' + Math.random(),
                    success: function(lay,index){
                        Iframe = index;
                        return ;
                    }
                });
                //监听子页传递的关闭指令
                window.addEventListener('message',function(e){
                    if(e.data == 1){
                        table.reloadData('addon');
                        layer.close(Iframe);
                    }else if(e.data == 5 && Iframe){ //Token错误或已过期 执行重新登录弹窗
                        layer.close(Iframe);Iframe = false;
                        admin.removeToken();
                        $("#top-addon-user").trigger("click");
                    }
                },false);
                return false;
            }
            //正常安装
            layer.msg(res.msg,{shade:[0.4,'#000'],time:3000},function(){
                if(res.code==1){
                    addons = res.data.addons;
                    table.reloadData('addon');
                    if(way=='install') loadLeftMenu();
                }else if(res.code==5){
                    admin.removeToken();
                    $("#top-addon-user").trigger("click");
                }
            });
        },'post');
    }/**/
    /*获取token*/
    function getUser(t){
        var UT = admin.getToken();
        if(t=='uid'){
            return UT ? UT.uid : '';
        }else if(t=='uids'){
            return UT ? '?uid='+UT.uid : '';
        }else if(t=='token'){
            return UT ? UT.token : '';
        }
        return UT ? UT : '';
    }/**/
    /*重载左侧主菜单*/
    var loadLeftMenu = function(){
        admin.req(layui.cache.maps + 'index/json',function(res){
            admin.putUser(res.user);
            layui.index.buildLeftMenus(res.menus);
        });
    }/**/
});
</script>
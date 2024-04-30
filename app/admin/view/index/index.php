<!DOCTYPE html>
<html>
<head>
<title>{:vconfig('sys_title','后台管理')} - {:vconfig('site_title','Veitool快捷开发框架系统')}</title>
<meta charset="utf-8"/>
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<link rel="icon" href="{PUBLIC__PATH}/favicon.ico"/>
<link rel="stylesheet" href="{STATIC__PATH}layui/css/layui.css"/>
<link rel="stylesheet" href="{STATIC__PATH}admin/admin.css"/>
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->
</head>
<body class="layui-layout-body">
<div class="layui-layout layui-layout-admin">
    <div class="layui-header">
        <div class="layui-logo"><img src="{STATIC__PATH}admin/img/logo.png"/><cite>&nbsp;{:vconfig('sys_title','后台管理')}</cite></div>
        <ul class="layui-nav layui-layout-left">
            <li class="layui-nav-item" lay-unselect><a v-event="flexible" title="侧边伸缩"><i class="layui-icon layui-icon-shrink-right"></i></a></li>
            <li class="layui-nav-item" lay-unselect><a v-event="refresh" title="刷新"><i class="layui-icon layui-icon-refresh-3"></i></a></li>
        </ul>
        <ul class="layui-nav layui-layout-right">
            <li class="layui-nav-item" lay-unselect><a v-event="clearCache" title="缓存" data-url="{:APP_MAP}/index/clear"><i class="layui-icon layui-icon-clear"></i></a></li>
            <li class="layui-nav-item" lay-unselect><a href="{PUBLIC__PATH}/" target="_blank" title="前台"><i class="layui-icon layui-icon-website"></i></a></li>
            <li class="layui-nav-item" lay-unselect><a v-event="lockScreen" data-url="{PUBLIC__PATH}/static/admin/page/tpl/lock.html" title="锁屏"><i class="layui-icon layui-icon-password"></i></a></li>
            <li class="layui-nav-item" lay-unselect><a v-event="fullScreen" title="全屏"><i class="layui-icon layui-icon-screen-full"></i></a></li>
            <li class="layui-nav-item" lay-unselect>
                <a><img src="" id="vFace" class="layui-nav-img"><cite id="vUser"></cite></a>
                <dl class="layui-nav-child">
                    <dd><a id="vName"></a></dd><hr>
                    <dd><a href="#/system.manager/index/action=info">个人中心</a></dd><hr><span id="vRole"></span>
                    <dd><a v-event="logout" data-url="{:APP_MAP}/login/logout">退出</a></dd>
                </dl>
            </li>
            <li class="layui-nav-item" lay-unselect><a v-event="popupRight" data-url="{PUBLIC__PATH}/static/admin/page/tpl/theme.html" title="主题"><i class="layui-icon layui-icon-more-vertical"></i></a></li>
        </ul>
    </div>
    <!-- 侧边栏 -->
    <div class="layui-side"><div class="layui-side-scroll"><ul class="layui-nav layui-nav-tree" lay-filter="admin-side-nav" lay-shrink="all"></ul></div></div>
    <!-- 主体部分 -->
    <div class="layui-body"></div>
    <!-- 底部 -->
    <div class="layui-footer layui-text">copyright © 2024 <a href="https://www.veitool.com" target="_blank">veitool.com</a> all rights reserved.<span class="pull-right">Version {:VT_VERSION}</span></div>
</div>
<!-- 加载动画 -->
<div class="page-loading"><div class="v-loader"><div></div></div></div>
<!-- js部分 -->
<script type="text/javascript" src="{STATIC__PATH}layui/layui.js"></script>
<script>
var $ = jQuery = layui.$;
layui.config({
    base: "{STATIC__PATH}admin/module/",
    maps: ("{:APP_MAP}" || "/admin") + "/", // 映射后的后台根路径
    static: "{STATIC__PATH}",  // 静态资源根路径 buildItems.js中有用
    version: "{:VT_VERSION}",  // 框架版本
    bins:{
        baseServer: '',    // 接口地址，实际项目请换成http形式的地址
        pageTabs: true,    // 是否开启多标签
        cacheTab: false,   // 是否记忆Tab
        defaultTheme: '',  // 默认主题
        openTabCtxMenu: true,  // 是否开启Tab右键菜单
        maxTabNum: 12,         // 最多打开多少个tab
        viewPath: '',          // 视图位置
        viewSuffix: '',        // 视图后缀
        reqPutToPost: true,    // req请求put方法变成post
        apiNoCache: true,      // ajax请求json数据不带版本号
        tabAutoRefresh: false, // 是否每点击菜单都刷新
        tableName: 'vadmin',   // 存储表名
        token: '{:token($tokenName)}',   // CSRF-TOKEN
    }
}).extend({
    Cropper: "Cropper/Cropper", //图片裁剪
    tagsInput: "tagsInput/tagsInput", //标签
    fileLibrary: "fileLibrary/fileLibrary", //资源库管理
    buildItems: "buildItems/buildItems", //构建项
    cascader: "cascader/cascader", //无限级联 地区
    orgCharts:'orgCharts/orgCharts', //组织结构图
    zTree:'zTree/zTree' //树形结构
}).use(["index", "admin"], function(){
    layui.admin.req(layui.cache.maps + 'index/json',function(res){
        layui.admin.putUser(res.user);
        $('#vUser').text(res.user.username);
        $('#vName').html(res.user.truename);
        $('#vFace').attr('src',res.user.face ? res.user.face : '{PUBLIC__PATH}/static/admin/img/head.jpg');
        layui.index.buildLeftMenus(res.menus); // 构建左侧菜单
        // 构造角色选项
        var r_str = '';
        $.each(res.user.rolem,function(k,val){
            r_str += (res.user.roleid == val.id ? '<dd style="background-color:#e9cccc;"><a>' : '<dd><a href="{:APP_MAP}/system.manager/index?action=role&roleid='+ val.id +'">') + val.name +'</a></dd>';
        });
        $('#vRole').html(r_str + '<hr>');
    });
});
</script>
</body>
</html>
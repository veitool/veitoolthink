<style>
#seting_box{padding-right:80px;}
#seting_box .layui-form-label{width:130px}
#seting_box .layui-input-block{margin-left:160px}
#seting_box .layui-word-aux{color:#ccc!important;}
</style>
<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-tab layui-tab-admin" lay-filter="setting_top_tab">
            <ul class="layui-tab-title" id="setting_tab"></ul>
        </div>
        <div class="layui-card-body">
            <form class="layui-form" lay-filter="seting_box_form" style="padding-top:30px;">
                <div id="seting_box"></div>
                <div class="layui-form-item text-center">
                    <button class="layui-btn" lay-submit lay-filter="setting-edit-submit">确认保存</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
layui.use(['buildItems'], function(){
    var app_root = layui.cache.maps + 'system.setting/';
    var groups = <?=$groups?>;
    /*解析顶部分组选项*/
    var tab = $("#setting_tab");
    layui.each(groups,function(k,v){tab.append('<li group="'+ k +'">'+ v +'</li>');});
    var group = tab.children("li").first().addClass('layui-this').attr('group');
    /**/
    /*构建配置项*/
    build(group);
    /*顶部选项卡监听*/
    layui.element.on('tab(setting_top_tab)',function(){
        group = this.getAttribute("group");
        build(group);
    });/**/
    /*构建配置项 group 分组名*/
    function build(group){
        layui.buildItems.build({
            bid: 'seting_box',
            url: app_root + 'index?do=json&group='+ group,
            map: layui.cache.maps + 'system.upload/',
            gid: 1
        });
    };/**/
    /*监听提交*/
    layui.form.on('submit(setting-edit-submit)', function(data){
        var btn = $(this);data.field.__g = group;
        if (btn.attr('stop')){return false;}else{btn.attr('stop',1);}
        layui.admin.req(app_root+"edit",data.field,function(res){
            layer.msg(res.msg,{shade:[0.4,'#000'],time:1500});
            btn.removeAttr('stop');
        },'post',{headersToken:true});
        return false;
    });/**/
});
</script>
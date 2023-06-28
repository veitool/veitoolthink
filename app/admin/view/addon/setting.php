<style>
#seting_addon{padding-right:80px;line-height:24px;}
#seting_addon .layui-form-label{width:130px}
#seting_addon .layui-input-block{margin-left:160px}
#seting_addon .layui-word-aux{color:#ccc!important;}
</style>
<div>
    <div class="layui-tab layui-tab-admin" lay-filter="setting_addon_tab" style="display:none"><ul class="layui-tab-title" id="setting_addon_ul"></ul></div>
    <form class="layui-form" lay-filter="seting_addon_form" style="padding-top:30px;">
        <div id="seting_addon"></div>
        <input type="submit" style="display:none;" lay-filter="seting_addon_submit" lay-submit/>
    </form>
</div>
<script>
layui.use(['buildItems'], function(){
    var app_root = layui.cache.maps + 'addon/';
    var addon = '<?=$addon?>',groups = <?=$groups?>, group = '';
    /*如果有分组*/
    if(groups.constructor === Object){
        var tab = $("#setting_addon_ul");
        tab.parent().show();
        layui.each(groups,function(k,v){tab.append('<li group="'+ k +'">'+ v +'</li>');});
        group = tab.children("li").first().addClass('layui-this').attr('group');
        layui.element.on('tab(setting_addon_tab)',function(){
            group = this.getAttribute("group");
            build(group);
        });
    }/**/
    /*构建配置项*/
    build(group);
    /*构建配置项 group 分组名*/
    function build(group){
        layui.buildItems.build({
            gid: 1,
            bid: 'seting_addon',
            url: app_root + 'setting?do=json&addon='+ addon +'&group='+ group,
            map: layui.cache.maps + 'system.upload/'
        });
    };/**/
    /*监听提交*/
    layui.form.on('submit(seting_addon_submit)', function(data){
        var btn = $(this),field = data.field;
        if (btn.attr('stop')){return false;}else{btn.attr('stop',1);}
        field.__g = group;field.__a = addon;
        layui.admin.req(app_root + "setup",field,function(res){
            layui.layer.msg(res.msg,{shade:[0.4,'#000'],time:1500});
            btn.removeAttr('stop');
        },'post',{headersToken:true});
        return false;
    });/**/
});
</script>
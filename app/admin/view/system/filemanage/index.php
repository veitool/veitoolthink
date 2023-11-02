<style>
.files_item{width:60px;height:60px;line-height:40px;cursor:pointer;margin:10px auto 0 auto;padding:4px;border:1px solid #ddd;background:#fff;display:-webkit-box;-moz-box-align:center;-webkit-box-align:center;-moz-box-pack:center;-webkit-box-pack:center;}
.files_item img{max-width:50px;max-height:50px;border:0}
</style>
<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-card-header">
            <form class="layui-form render">
                <div class="layui-form-item" style="margin-bottom:5px;">
                    <div class="layui-inline" style="width:80px;">
                        <select name="fields">
                            <option value="">属性</option>
                            <option value="0">文件名</option>
                            <option value="1">用户名</option>
                        </select>
                    </div>
                    <div class="layui-inline" style="width:150px;"><input type="text" name="kw" placeholder="关键词" autocomplete="off" class="layui-input" lay-affix="clear"/></div>
                    <div class="layui-inline" style="width:200px;"><input type="text" name="sotime" date-render placeholder="上传时间" class="layui-input" lay-affix="clear"/></div>
                    <div class="layui-inline" style="width:110px;"><select name="groupid" id="search_filemanage_select"></select></div>
                    <div class="layui-inline" style="width:72px;">
                        <select name="isdel">
                            <option value="">软删</option>
                            <option value="0">未删</option>
                            <option value="1">已删</option>
                        </select>
                    </div>
                    <div class="layui-inline">
                        <div class="layui-btn-group">
                            <button class="layui-btn" lay-submit lay-filter="search-filemanage"><i class="layui-icon layui-icon-search"></i> 搜索</button>
                            <a class="layui-btn" lay-submit lay-filter="search-filemanage-all"><i class="layui-icon layui-icon-light"></i>全部</a>
                            <a class="layui-btn" id="top-filemanage-del" v-show="@system.filemanage/del"><i class="layui-icon layui-icon-delete"></i> 删除</a>
                            <a class="layui-btn" id="top-filemanage-reset" v-show="@system.filemanage/reset"><i class="layui-icon layui-icon-vercode"></i> 恢复</a>
                            <a class="layui-btn" id="top-filemanage-clear" v-show="@system.filemanage/clear"><i class="layui-icon layui-icon-close"></i> 清理</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="layui-card-body">
            <table lay-filter="filemanage" id="filemanage"></table>
        </div>
    </div>
</div>
<!--JS部分-->
<script type="text/javascript">
layui.use(function(){
    var app_root = layui.cache.maps + 'system.filemanage/';
    var table=layui.table,admin=layui.admin;
    //分组渲染
    var Group = {$group|raw};
    var filemanage_select = '<option value="">文件分组</option><option value="0">尚未分组</option>'; $.each(Group,function(k,v){filemanage_select += '<option value="'+ k +'">'+ v +'</option>';});
    $('#search_filemanage_select').html(filemanage_select);
    /*渲染数据*/
    table.render({
        elem: '#filemanage',
        url: app_root+"index?do=json",
        cellExpandedMode:'tips',
        css: 'td .layui-table-cell{height:80px;line-height:80px;padding:0 5px;}',
        cols: [[
            {type:'checkbox',fixed:'left'},
            {field:"fileurl",align:'center',title:"预览图",width:80,templet:function(d){return '<div class="files_item"><img src="'+ get_icon(d.fileurl,d.filetype,d.fileext) +'" alt="'+d.filename+'" lay-event="file-event-image"/></div>';}},
            {field:"filename",edit:'text',title:"文件名"},
            {field:"fileurl",title:"路径"},
            {field:"username",align:'center',width:100,title:"用户",templet:function(d){return d.admin==1 ? '<font color=red>'+d.username+'</font>' : d.username;}},
            {field:"groupname",align:'center',width:80,title:"分组",templet:function(d){return d.groupname ? d.groupname : '尚未分组';}},
            {field:"filesize",align:'center',width:80,title:"大小(Kb)"},
            {field:"filetype",align:'center',width:80,title:"类型"},
            {field:"isdel",align:'center',width:60,title:"软删",templet:function(d){return d.isdel==1 ? '<font color=red>已删</font>' : '-';}},
            {field:"addtime",align:'center',width:150,title:"上传时间",sort:!0,templet:function(d){return layui.util.toDateString(d.addtime*1000)}},
            {fixed:'right',width:100,align:'center',toolbar:'<div><a class="layui-btn layui-btn-xs" lay-event="reset">恢复</a><a class="layui-btn layui-btn-xs layui-btn-danger" lay-event="del">删除</a></div>',title:'操作'}
        ]],
        page: true,
        limit:{$limit}
    });/**/
    /*顶部删除按钮*/
    $('#top-filemanage-del').on('click', function(){
        var checkRows = table.checkStatus('filemanage').data;
        if(checkRows.length === 0){return layer.msg('请选择需删除的文件');}
        var ids = checkRows.map(function(d){return d.fileid;});
        doup(ids,'del');
    });/**/
    /*顶部恢复按钮*/
    $('#top-filemanage-reset').on('click', function(){
        var checkRows = table.checkStatus('filemanage').data;
        if(checkRows.length === 0){return layer.msg('请选择需恢复的文件');}
        var ids = checkRows.map(function(d){return d.fileid;});
        doup(ids,'reset');
    });/**/
    /*顶部清理按钮*/
    $('#top-filemanage-clear').on('click', function(){
        layer.confirm('确定要清理软删除的文件吗，清理后将不可恢复！', function(){
            admin.req(app_root+"clear",function(res){
                layer.msg(res.msg,{shade:[0.4,'#000'],time:1500},function(){
                    if(res.code==1) table.reloadData('filemanage');
                });
            },'post',{headersToken:true});
        });
    });/**/
    /*快编监听*/
    table.on('edit(filemanage)',function(obj){
        admin.req(app_root+"edit",{fileid:obj.data.fileid,av:obj.value,af:obj.field},function(res){
            layer.msg(res.msg,{shade:[0.4,'#000'],time:1500});
        },'post',{headersToken:true});
    });/**/
    /*工具条监听*/
    table.on('tool(filemanage)', function(obj){
        var data = obj.data;
        if(obj.event === 'del'){
            doup(data.fileid,'del');
        }else if(obj.event === 'reset'){
            doup(data.fileid,'reset');
        }else if(obj.event === 'file-event-image'){
            var src = $(this).attr('src');
            var alt = $(this).attr('alt');
            layer.photos({photos:{data:[{alt:alt,src:src}],start:'0'},anim:5,shade:[0.4,'#000']});
        }
    });/**/
    /*删除 恢复*/
    function doup(ids,type){
        var tip = type==='del' ? '删除' : '恢复';
        layer.confirm('确定要'+ tip +'所选文件吗？', function(){
            admin.req(app_root + type,{fileid:ids},function(res){
                layer.msg(res.msg,{shade:[0.4,'#000'],time:1500},function(){
                    if(res.code==1) table.reloadData('filemanage');
                });
            },'post',{headersToken:true});
        });
    }/**/
    /*按类型获取图标*/
    function get_icon(url,type,ext){
        if(type==='video'){
            return '/static/fileicon/video.png';
        }else if(type==='audio'){
            return '/static/fileicon/audio.png';
        }else if(type==='file'){
            return (ext==='jpg' || ext==='jpeg' || ext==='png' || ext==='gif' || ext==='bmp') ? '/static/fileicon/pic.png' : '/static/fileicon/'+ext+'.png';
        }else{
            return url;
        }
    }/**/
});
</script>
<div class="layui-fluid">   
    <div class="layui-card">
        <div class="layui-card-header">
            <div class="layui-btn-group">
                <a class="layui-btn" id="areas-add" v-show="@system.area/add"><i class="layui-icon layui-icon-add-circle"></i> 添加</a>
                <a class="layui-btn" id="areas-del" v-show="@system.area/del"><i class="layui-icon layui-icon-delete"></i> 删除</a>
                <a class="layui-btn" id="areas-import" v-show="@system.area/import"><i class="layui-icon layui-icon-set"></i> 导入</a>
            </div>
            <div id="area_guide" style="float:right"></div>
        </div>
        <div class="layui-card-body">
            <table lay-filter="areas" id="areas"></table>
        </div>
    </div>
</div>
<!--JS部分-->
<script type="text/javascript">
layui.use(['buildItems'], function(){
    var app_root = layui.cache.maps + 'system.area/';
    var table=layui.table,form=layui.form,admin=layui.admin;
    var pid = 0;    //初始上级ID
    var area = '';
    var pname = ''; //临时 地区ID|地区名 用于无子地区时调用
    var pstr = '';  //上次的导航串
    /*渲染数据*/
    var box = {
        elem: '#areas',
        data: {$list|raw},
        even: true,
        limit:100,
        cols: [[
            {type:'checkbox',fixed:'left'},
            {field:'areaid',width:60,unresize:true,align:'center',title:'ID'},
            {field:'areaname',minWidth:200,edit:'text',title:'区名'},
            {field:'listorder',width:100,align:'center',edit:'text',title:'排序'},
            {field:'parentid',width:100,align:'center',title:'上级ID'},
            {field:'childs',width:100, align:'center',title:'子地区'},
            {fixed:'right',width:140,align:'center',toolbar:'<div><a class="layui-btn layui-btn-xs" lay-event="info">子地区</a><a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a></div>',title:'操作'}
        ]],
        done: function(res,p,c){
            if(pid > 0){
                var str = '';
                if(res.data.length>0){
                    pstr = res.data[0].arrparentid;
                }else if(pname){
                    pstr = pstr ? pstr + ',' + pname : ('0|顶级,' + pname);
                }
                var arr = pstr.split(',');
                for(var j=0;j<arr.length;j++){
                    if(j==(arr.length-1)){
                        area = arr[j].split('|')[1];
                        str = str + ' <button class="layui-btn top-area-btn layui-btn-primary">'+area+'('+ c +')</button>';
                    }else{
                        str = str + ' <button class="layui-btn top-area-btn" id="g_'+arr[j].split('|')[0]+'">'+arr[j].split('|')[1]+'</button>';
                    }
                }
                //构建逐级导航
                $("#area_guide").html(str);
                $("[id^='g_']").unbind().bind("click",function(){
                    pid = $(this).attr('id').split('_')[1];
                    redata();
                });
            }else{
                pstr = '';
                area = '顶级地区';
                $("#area_guide").html('<button class="layui-btn top-area-btn layui-btn-primary">顶级('+ c +')</button>');
            }
        }
    };
    var redata = function(){
        box.data = '';
        box.url = app_root + 'index?pid=' + pid;
        table.render(box);
    };/**/
    /*初始顶级数据*/
    table.render(box);
    /*顶部添加按钮*/
    $('#areas-add').on('click',function(){areasOpen();});/**/
    /*顶部删除按钮*/
    $('#areas-del').on('click', function(){
        var checkRows = table.checkStatus('areas').data;
        if(checkRows.length === 0){return layer.msg('请选择需删除的地区');}
        var ids = checkRows.map(function(d){return d.areaid;});
        del(ids);
    });/**/
    /*顶部导入按钮*/
    $('#areas-import').on('click', function(){
        layer.confirm('确定要导入内置地区数据吗？？', function(index){
            $("#layui-layer"+index+" .layui-layer-content").html('数据导入中，请勿关闭...');
            admin.req(app_root+"import",function(res){
                layer.msg(res.msg,{shade:[0.4,'#000'],time:1500},function(){
                    if(res.code==1){pname = ''; redata();}
                });
            },'post');
        });
    });/**/
    /*快编监听*/
    table.on('edit(areas)', function(obj){
        admin.req(app_root+"edit",{areaid:obj.data.areaid,av:obj.value,af:obj.field},function(res){
            layer.msg(res.msg,{shade:[0.4,'#000'],time:1000},function(){
                if(res.code==1 && obj.field!='areaname') redata();
            });
        },'post',{headersToken:true});
    });/**/
    /*工具条监听*/
    table.on('tool(areas)', function(obj){
        var data = obj.data;
        if(obj.event === 'info'){
            pid = data.areaid;
            pname = pid + '|' + data.areaname;
            redata();
        }else if(obj.event === 'del'){
            del(data.areaid);
        }
    });/**/
    /*删除*/
    function del(ids){
        layer.confirm('确定删除所选地区吗？？', function(){
            admin.req(app_root+"del",{areaid:ids},function(res){
                layer.msg(res.msg,{shade:[0.4,'#000'],time:1500},function(){
                    if(res.code==1){pname = ''; redata();}
                });
            },'post',{headersToken:true});
        });
    }/**/
    /*弹出窗*/
    function areasOpen(){
        admin.open({
            type: 1,
            bid: 'areas_items',
            btn: ['保存', '取消'],
            area: ['460px', '350px'],
            title: '添加地区',
            success: function(l,index){
                layui.buildItems.build({
                    bid: 'areas_items',
                    data: [
                        {name:"pname",title:"上级地区",type:"html",html:'<div class="layui-form-mid tipx" style="font-size:14px;">'+area+'</div><input type="hidden" name="parentid" value="'+pid+'"/>'},
                        {name:"areaname",title:"地区名称",type:"textarea",value:'',verify:'required',placeholder:"允许批量添加，一行一个，点回车换行",must:true},
                        {name:"listorder",title:"排序编号",type:"number",value:100,verify:'required',placeholder:"请输入排序数字",must:true}
                    ]
                });
                form.on('submit(areas_items)',function(data){
                    var btn = $(this);
                    if (btn.attr('stop')){return false}else{btn.attr('stop',1)}
                    admin.req(app_root+"add",data.field,function(res){
                        layer.msg(res.msg,{shade:[0.4,'#000'],time:1500},function(){
                            if(res.code==1){
                                layer.close(index); redata();
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
/*
 * Veitool 2.0 文件库管理组件 2022-10-18
 * Website: www.veitool.com
 * Author：niaho (QQ:26843818)
 */
layui.define(function(g){
    layui.link(layui.cache.base+"fileLibrary/fileLibrary.css");
    var $ = layui.$,layer = layui.layer;
    var c = []; //配置组
    var $h; //构建的容器
    var Up; //上传实例
    var GP; //分组数据
    c.group_html = '{{# layui.each(d.group_list, function(k,v){ }}'+
                   '<li class="ng-scope" data-group-id="{{ v.groupid }}" title="{{ v.groupname }}">'+
                   '<a class="group-edit" href="javascript:void(0);" title="编辑分组"><i class="layui-icon layui-icon-edit"></i></a>'+
                   '<a class="group-name" href="javascript:void(0);">{{ v.groupname }}</a>'+
                   '<a class="group-delete" href="javascript:void(0);" title="删除分组"><i class="layui-icon layui-icon-delete"></i></a></li>'+
                   '{{#  }); }}';
    c.item_html =  '{{# layui.each(d.file_list.data, function(k,v){ }}'+
                   '<li class="ng-scope" title="{{ v.filename }}" data-file-id="{{ v.fileid }}" data-file-path="{{ v.fileurl }}">'+
                   '<div class="img-cover" style="background-image:url({{ v.fileurl }})"></div>'+
                   '<p class="file-name">{{ v.filename }}</p>'+
                   '<div class="select-mask"><i class="layui-icon">&#xe605;</i></div></li>'+
                   '{{#  }); }}';
    c.list_html = '<ul class="file-list-item">'+ c.item_html +'</ul>'+
                  '{{# if (d.file_list.last_page > 1){ }}'+
                  '<div class="file-page-box"><ul class="pagination">'+
                  '{{# if (d.file_list.current_page > 1){ }}<li><a class="switch-page" href="javascript:void(0);" title="上一页" data-page="{{ d.file_list.current_page - 1 }}"> << </a></li>{{# } }}'+
                  '{{# if (d.file_list.current_page < d.file_list.last_page){ }}<li><a class="switch-page" href="javascript:void(0);" title="下一页" data-page="{{ d.file_list.current_page + 1 }}"> >> </a></li>{{# } }}'+
                  '</ul></div>'+
                  '{{# } }}';
    c.main_html = '<div class="file-group"><ul class="nav-new">'+
                  '<li class="ng-scope" data-group-id="-1"><a class="group-name" href="javascript:void(0);" title="全部">全部</a></li>'+
                  '<li class="ng-scope" data-group-id="0"><a class="group-name" href="javascript:void(0);" title="尚未分组">尚未分组</a></li>'+ c.group_html +
                  '</ul><a class="group-add" href="javascript:void(0);">新增分组</a></div>'+
                  '<div class="file-list">'+
                  '<div class="file-list-header"><div class="layui-btn-group">'+
                  '<button class="layui-btn layui-btn-sm" id="top-file-move">移动至 <i class="layui-icon">&#xe625;</i></button>'+
                  '<button class="layui-btn layui-btn-sm filelibrary-del"><i class="layui-icon layui-icon-delete"></i> 删除</button>'+
                  '<button class="layui-btn layui-btn-sm filelibrary-up"><i class="layui-icon">&#xe681;</i> 上传图片</button>'+
                  '</div><input type="text" class="layui-inline layui-input" style="display:inline;width:370px;margin-left:15px;line-height:20px;height:30px" placeholder="外部URL" id="fileOtherUrl"/></div>'+
                  '<div id="file-list-body">'+ c.list_html + '</div>'+
                  '<div class="cf"></div>'+
                  '</div>';
    var f = {
        open: function(d,success){
            c.title = d.title === undefined ? '文件管理' : d.title; //弹出窗标题
            c.url = d.url === undefined ? '/admin/system.upload/' : d.url; //文件操作接口控制器基类路径
            c.type  = d.type  === undefined ? 'image' : d.type;   //文件类型
            c.limit = d.limit === undefined ? 16 : d.limit;   //文件每页数
            c.thum = d.thum === undefined ? 0 : d.thum;   //是否生成缩略图  单个非零数表示按默认尺寸生成 150|80 则表示宽150 高80
            c.groupid = d.groupid === undefined ? -1 : d.groupid; //文件分组ID
            c.params = $.extend({limit:c.limit,groupid:c.groupid}, d.params || {}); // params自定义参数
            f.getJsonData(c.params,function(data){
                layer.open({
                    type: 1,
                    title: c.title,
                    skin: 'file-library',
                    area: '840px',
                    anim: 1,
                    btn: ['确定','取消'],
                    content: f.tpl(c.main_html,data),
                    success: function(res){
                        GP = data.group_list;
                        for(var i=0;i<GP.length;i++){
                            GP[i].id = GP[i]['groupid'];
                            GP[i].title = GP[i]['groupname'];
                        }
                        f.init(res); //初始化
                    },
                    yes: function(index){
                        //确认回调,返回二维数组{[file_id, file_path]}
                        typeof success === 'function' && success(f.getSelectedFiles());
                        layer.close(index);
                    }
                });
            });
        },
        /**
         * 初始化文件库弹窗
         * @param e
         */
        init: function(e){
           $h = e;
           // 初始选中左侧分组项
           $h.find('.file-group').find('[data-group-id="' + c.groupid + '"]').addClass('active');
           // 注册分类切换事件
           f.switchClassEvent();
           // 注册文件点击选中事件
           f.selectFilesEvent();
           // 新增分组事件
           f.addGroupEvent();
           // 编辑分组事件
           f.editGroupEvent();
           // 删除分组事件
           f.deleteGroupEvent();
           // 注册文件上传事件
           f.uploadImagesEvent();
           // 注册文件删除事件
           f.deleteFilesEvent();
           // 注册文件移动事件
           f.moveFilesEvent();
           // 注册文件列表分页事件
           f.fileListPage();
        },
        /**
         * 获取文件库列表数据
         * @param params
         * @param success
         */
        getJsonData: function(params, success){
            var loadId = layer.load(2);
            typeof params === 'function' && (success = params);
            params.limit = params.limit || c.limit;
            // 获取文件库列表
            $.getJSON(c.url + 'files', params, function(res){
                layer.close(loadId);
                if(res.code === 1){
                    typeof success === 'function' && success(res.data);
                }else{
                    layer.msg(res.msg,{anim:6});
                }
            });
        },
        /**
         * 分类切换事件
         */
        switchClassEvent: function(){
            // 注册分类切换事件
            $h.find('.file-group').on('click', 'li', function(){
                var $this = $(this);
                // 切换选中状态
                $this.addClass('active').siblings('.active').removeClass('active');
                // 重新渲染文件列表
                f.renderFileList();
                // 重新注册文件上传
                Up.reload({url: c.url + 'upfile?action=image&groupid=' + f.getCurrentGroupId() + '&thum=' + c.thum});
            });
        },
        /**
         * 注册文件选中事件
         */
        selectFilesEvent: function(){
            // 绑定文件选中事件
            $h.find('#file-list-body').on('click','.file-list-item li',function(){
                $(this).toggleClass('active');
            });
        },
        /**
         * 新增分组事件
         */
        addGroupEvent: function(){
            var $groupList = $h.find('.file-group > ul');
            $h.on('click', '.group-add', function (){
                layer.prompt({title: '请输入新分组名称'},function(value, index){
                    var load = layer.load();
                    $.post(c.url + 'group?action=add',{groupname:value,grouptype:c.type},function(res){
                        layer.msg(res.msg);
                        if (res.code === 1) {
                            $groupList.append(f.tpl(c.group_html,{group_list:[res.data]}));
                            GP.push({id:res.data.groupid,title:res.data.groupname});
                            f.moveFilesEvent();
                        }
                        layer.close(load);
                    },"json");
                    layer.close(index);
                });
            });
        },
        /**
         * 编辑分组事件
         */
        editGroupEvent: function(){
           $h.find('.file-group').on('click', '.group-edit', function(){
                var $li = $(this).parent(),groupid = $li.data('group-id');
                layer.prompt({title:'修改分组名称',value: $li.attr('title')},function(value,index){
                    var load = layer.load();
                    $.post(c.url + 'group?action=edit',{groupid:groupid,groupname:value},function(res){
                       layer.msg(res.msg);
                       if (res.code === 1) {
                            $li.attr('title', value).find('.group-name').text(value);
                            $.each(GP,function(k,v){if(groupid===v.id) GP[k].title=value;});
                            f.moveFilesEvent();
                       }
                       layer.close(load);
                    },"json");
                    layer.close(index);
                });
                return false;
           });
        },
        /**
         * 删除分组事件
         */
        deleteGroupEvent: function(){
            $h.find('.file-group').on('click','.group-delete',function(){
                var $li = $(this).parent(),groupid = $li.data('group-id');
                layer.confirm('确定删除该分组吗？',{title:'系统提示'},function(index){
                    var load = layer.load();
                    $.post(c.url + 'group?action=del',{groupid:groupid},function(res){
                        layer.msg(res.msg);
                        if(res.code === 1){
                            $li.remove();
                            $.each(GP,function(k,v){if(groupid===v.id) GP.splice(k,1);});
                            f.moveFilesEvent();
                        }
                        layer.close(load);
                    },"json");
                    layer.close(index);
                });
                return false;
            });
        },
        /**
         * 文件上传 (多文件)
         */
        uploadImagesEvent: function(){
            Up = layui.upload.render({
                elem: '.filelibrary-up',
                url: c.url + 'upfile?action=image&groupid=' + c.groupid + '&thum=' + c.thum,
                multiple: true,
                done: function(res){
                    if(res.code===1){
                        var $list = $h.find('ul.file-list-item');
                        $list.prepend(f.tpl(c.item_html,{file_list:{data:[res.data]}}));
                    }else{
                        layer.msg(res.msg ? res.msg : '上传失败！',{shade:[0.4,'#000'],time:1500});
                    }
                }
           });
        },
        /**
         * 删除选中的文件
         */
        deleteFilesEvent: function(){
            $h.on('click', '.filelibrary-del', function(){
                var fileids = f.getSelectedFileIds();
                if (fileids.length === 0){
                    layer.msg('您还没有选择任何文件~',{offset:'t',anim:6});
                    return;
                }
                layer.confirm('确定删除选中的文件吗？',{title:'系统提示'},function(index){
                    var load = layer.load();
                    $.post(c.url + 'files?action=del',{fileids:fileids},function(result){
                        layer.close(load);
                        if (result.code === 1){
                            f.renderFileList();
                        }else{
                            layer.msg(result.msg);
                        }
                    },"json");
                    layer.close(index);
                });
            });
        },
        /**
         * 文件移动事件
         */
        moveFilesEvent: function(){
            layui.dropdown.render({
                elem: '#top-file-move',
                data: GP,
                click: function(obj){
                    var fileids = f.getSelectedFileIds();
                    if (fileids.length === 0){
                        layer.msg('您还没有选择任何文件~',{offset:'t',anim:6});
                        return false;
                    }
                    layer.confirm('确定移动选中的文件吗？',{title:'系统提示'},function(index){
                        var load = layer.load();
                        $.post(c.url + 'files?action=move',{groupid:obj.id,fileids:fileids},function(result){
                            layer.msg(result.msg);
                            if (result.code === 1){
                                f.renderFileList();
                            }
                            layer.close(load);
                        },"json");
                        layer.close(index);
                    });
                }
            });
        },
        /**
         * 注册文件列表分页事件
         */
        fileListPage: function(){
            $h.find('#file-list-body').on('click','.switch-page',function(){
                var page = $(this).data('page');
                f.renderFileList(page);
            });
        },
        /**
         * 重新渲染文件列表
         * @param page
         */
        renderFileList: function(page){
            var groupid = f.getCurrentGroupId();
            // 重新渲染文件列表
            f.getJsonData({groupid:groupid,page:page || 1},function(data){
                $h.find('#file-list-body').html(f.tpl(c.list_html, data));
            });
        },
        /**
         * 获取选中的文件列表
         * @returns {Array}
         */
        getSelectedFiles: function(){
            var selectedList = [];
            if($('#fileOtherUrl').val()){
                selectedList[0] = {file_id:0,file_path:$('#fileOtherUrl').val()};
            }else{
                $h.find('.file-list-item > li.active').each(function(index){
                    var $this = $(this);
                    selectedList[index] = {file_id:$this.data('file-id'),file_path:$this.data('file-path')};
                });
            }
            return selectedList;
        },
        /**
         * 获取选中的文件的ID集
         * @returns {Array}
         */
        getSelectedFileIds: function(){
            var fileList = f.getSelectedFiles();
            var data = [];
            fileList.forEach(function(item){
                data.push(item.file_id);
            });
            return data;
        },
        /**
         * 获取当前分组id
         * @returns {*}
         */
        getCurrentGroupId: function(){
            return $h.find('.file-group > ul > li.active').data('group-id');
        },
        /**
         * 模板解析
         * @param t
         * @param d
         */
        tpl: function(t,d){var h='';layui.laytpl(t).render(d,function(r){h=r;});return h;}
    };
    g("fileLibrary",f);
});
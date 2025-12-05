/*
 * Veitool 1.0.3 构建表单项 2023-08-12
 * Website:www.veitool.com 
 * Author：niaho (QQ:26843818)
 */
layui.define(['tagsInput','fileLibrary','cascader'], function(e){
    layui.link(layui.cache.base+"buildItems/buildItems.css");
    var $ = layui.$,layer=layui.layer,form=layui.form,tagsInput=layui.tagsInput,fileLibrary=layui.fileLibrary;
    var static = layui.cache.static;
    var f = []; //方法组
    var c = []; //配置组
    var $h; //构建的容器
    //基础模板
    c.label_html  = '{{# if(d.title){ }}<label class="layui-form-label{{# if(d.must){ }} layui-form-required{{# } }}">{{- d.title }}</label>{{# } }}';
    c.block_html  = '<div class="{{# if(d.title){ }}layui-input-block{{# }else{ }}layui-input-wrap{{# } }}"';
    c.inline_html = '<div class="layui-input-inline"';
    c.item_html = '<div class="layui-form-item" id="item-{{ d.relation ? d.relation + "-" : "" }}{{ d.name }}" style="{{# if(d.itemStyle){ }}{{ d.itemStyle }}{{# } }}{{# if(d.hide || (d.relation && d.relation.indexOf("_")!=-1)){ }}display:none;{{# } }}">' + c.label_html;
    c.tips_html = '{{# if(d.tips){ }}<div class="layui-form-mid{{# if(d.type ==\'switch\' || d.type ==\'captcha\' || d.type ==\'keyval\' || d.type ==\'colorpicker\'){ }} tipx{{# } }}"><i class="layui-icon">&#xe748;</i> {{ d.tips }}</div>{{# } }}';
    c.vers_html = '{{# if(d.verify){ }}lay-verify="{{ d.verify }}" lay-vertype="{{ d.vertype || \'\' }}" lay-reqtext="{{ d.reqtext || d.placeholder || \'\' }}" {{# } }}';
    c.plac_html = '{{# if(d.maxlength){ }}maxlength="{{ d.maxlength }}" {{# } }}{{# if(d.placeholder){ }}placeholder="{{ d.placeholder }}" {{# } }}{{# if(d.readonly){ }}readonly {{# } }}';
    c.affix_html = '{{# if(d.affix){ }}lay-affix="{{ d.affix }}" {{# } }}';
    c.item_style = '{{# if(d.style){ }}style="{{ d.style }}" {{# } }}';
    //表单元素
    c.text_html = '<input type="text" name="{{ d.name }}" lay-filter="{{ d.name }}" value="{{ d.value }}" ' + c.item_style + c.vers_html + c.plac_html + c.affix_html;
    c.number_html = '<input type="number" name="{{ d.name }}" lay-filter="{{ d.name }}" value="{{ d.value }}" ' + c.item_style + c.vers_html + c.plac_html + '{{# if(d.id){ }}id="{{ d.id }}"{{# } }} class="layui-input" lay-affix="number">';
    c.switch_html = '{{# layui.buildItems.on(d.name,d.relation || "","switch"); }}<input type="checkbox" name="{{ d.name }}" lay-skin="switch" lay-text="ON|OFF" lay-filter="{{ d.name }}" value="1" {{ d.value ==1 ? "checked" : "" }}/>';
    c.radio_html = '{{# layui.buildItems.on(d.name,d.relation || "","radio"); layui.each(d.options, function(key, txt){ }}<input type="radio" name="{{ d.name }}" lay-filter="{{ d.name }}" value="{{ key }}" title="{{ txt }}" {{ d.value == key ? "checked" : "" }} />{{# }); }}';
    c.checkbox_html = '{{# layui.buildItems.on(d.name,"","checkbox"); layui.each(d.options, function(key, txt){ }}<input type="checkbox" name="{{ d.name }}[]" lay-filter="{{ d.name }}" lay-skin="{{ d.skin }}" value="{{ key }}" title="{{ txt }}" {{ (d.value).split(",").indexOf(String(key))>-1 ? "checked" : "" }}/>{{# }); }}';
    c.password_html = '<input type="password" name="{{ d.name }}" value="{{ d.value }}" ' + c.item_style + c.vers_html + 'autocomplete="off" ' + c.plac_html + c.affix_html;
    c.textarea_html = '<textarea name="{{ d.name }}" ' + c.item_style;
    c.select_html = '<select name="{{ d.name }}" lay-filter="{{ d.name }}" ' + c.item_style + c.vers_html + '{{# layui.buildItems.on(d.name,"","select"); if(d.search){ }}lay-search{{# } }}>{{# if(d.optiontip || d.reqtext){ }}<option value="">{{ d.optiontip || d.reqtext }}</option>{{# } }}{{# layui.each(d.options, function(key, txt){ }}<option value="{{ key }}" {{ d.value == key ? "selected" : "" }}>{{ txt }}</option>{{# }); }}</select>';
    //隐藏域
    c.hidden = '<input type="hidden" name="{{ d.name }}" value="{{ d.value || \'\' }}"/>';
    //clear
    c.clear = '<div style="clear:both"></div>';
    //静态代码
    c.html = c.item_html + c.block_html + '>' + '{{- d.html }}' + c.tips_html + '</div></div>';
    //单行文本、数组
    c.text = c.item_html + c.block_html + '>' + c.text_html + '{{# if(d.id){ }}id="{{ d.id }}"{{# } }} class="layui-input"/>' + c.tips_html + '</div></div>';
    //多行文本
    c.textarea = c.array = c.item_html + c.block_html + '>' + c.textarea_html + 'class="layui-textarea" {{# if(d.id){ }}id="{{ d.id }}" {{# } }}' + c.vers_html + c.plac_html + '>{{ d.value }}</textarea>' + c.tips_html + '</div></div>';
    //键值对组
    c.keyval_html = '{{# d.value = d.value || "{}"; layui.each(typeof d.value === "string" ? JSON.parse(d.value) : d.value, function(k, v){ }}<div class="keyval_item"><div class="layui-input-inline"><input type="text" value="{{ k }}" class="layui-input" placeholder="key"></div>'+
            '<div class="layui-form-mid">:</div><div class="layui-input-inline"><input type="text" value="{{ v }}" class="layui-input" placeholder="value"></div>'+
            '<a class="layui-btn layui-bg-red del"><i class="layui-icon layui-icon-delete"></i></a></div>{{# }); }}';
    c.keyval = c.item_html + c.block_html + '><div id="keyval-show-{{ d.name }}" data-name="{{ d.name }}"><input type="hidden" name="{{ d.name }}" id="keyval-input-{{ d.name }}" value="" ' +
            'lay-verify="{{ d.verify || \'\' }}" lay-reqtext="{{ d.reqtext || \'\' }}"/>'+ c.keyval_html + '</div><a class="layui-btn keyval-add" id="keyval-add-{{ d.name }}"><i class="layui-icon layui-icon-add-circle"></i> 追加</a>' + c.tips_html + '</div></div>';
    //静态文本
    c.static = c.item_html + c.block_html + '>' + '<div class="layui-form-mid">{{ d.value }}</div></div></div>';
    //密码
    c.password = c.item_html + c.block_html + '>' + c.password_html + '{{# if(d.id){ }}id="{{ d.id }}"{{# } }} class="layui-input" lay-affix="eye"/>' + c.tips_html + '</div></div>';
    //复选
    c.checkbox = c.item_html + c.block_html + '>' + c.checkbox_html + '</div>' + c.tips_html + '</div>';
    //单选
    c.radio = c.item_html + c.block_html + '>' + c.radio_html + '</div>' + c.tips_html + '</div>';
    //日期、时间、日期时间
    c.year = c.month = c.date = c.time = c.datetime = c.item_html + c.block_html + '>' + c.text_html + 'class="layui-input" placeholder="yyyy-MM-dd" id="show-date-{{ d.name }}" data-type="{{ d.type}}" data-range="{{ d.range ? true : false }}"/>' + c.tips_html + '</div></div>';
    //开关
    c.switch = c.item_html + c.block_html + '>' + c.inline_html + ' style="width:62px;">' + c.switch_html + '</div>' + c.tips_html + '</div></div>';
    //下拉
    c.select = c.item_html + c.block_html + '>' + c.select_html + c.tips_html + '</div></div>';
    //标签
    c.tags = c.item_html + c.block_html + '>' + c.text_html + 'class="layui-hide" id="show-tags-{{ d.name }}"/>' + c.tips_html + '</div></div>';
    //数字
    c.number = c.item_html + c.block_html + '>' + c.number_html.replace("maxlength=", "max=") + c.tips_html + '</div></div>';
    //级联地区
    c.areas = c.item_html + c.block_html + '>' + c.text_html + 'id="areas-{{ d.name }}" class="layui-input"/>' + c.tips_html + '</div></div>';
    //取色器
    c.colorpicker = c.item_html + c.block_html + '>' + c.inline_html + ' style="width:120px;">' + c.text_html + 'id="show-colorpicker-{{ d.name }}" placeholder="请选择颜色" class="layui-input"/></div>' + c.inline_html + ' style="left:-11px;width:40px;"><div id="colorpicker-{{ d.name }}"></div></div>' + c.tips_html + '</div></div>';
    //百度编辑器
    c.ueditor = c.item_html + c.block_html + '>' + c.textarea_html + ' id="ueditor-{{ d.id ? d.id : d.name }}" style="border:0;padding:0;">{{- d.value }}</textarea>' + c.tips_html + '</div></div>';
    //Md编辑器
    c.cherrymd = c.item_html + c.block_html + '><div '+ c.item_style +'id="cherrymd-{{ d.id ? d.id : d.name }}"></div><textarea id="temp-{{ d.id ? d.id : d.name }}" style="display:none;">{{- d.value }}</textarea>' + c.tips_html + '</div></div>';
    //Md编辑器
    c.editormd = c.item_html + c.block_html + '><style>.editormd-preview li{list-style:inherit!important}.editormd-code-toolbar>select{display:initial}</style><div id="editormd-{{ d.id ? d.id : d.name }}" style="z-index:1000;">' + c.textarea_html + ' style="display:none;">{{- d.value }}</textarea></div>' + c.tips_html + '</div></div>';
    //TinyMCE编辑器
    c.tinymce = c.item_html + c.block_html + '>' + c.textarea_html + ' id="tinymce-{{ d.id ? d.id : d.name }}">{{- d.value }}</textarea>' + c.tips_html + '</div></div>';
    //上传单图
    c.image = c.item_html + c.block_html + '><div id="image-show-{{ d.name }}" data-type="image" data-verify="{{ d.verify || \'\' }}" data-reqtext="{{ d.reqtext || \'\' }}">{{# if (d.value) { }}' +
              '<div class="image_item"><img src="{{ d.value }}"/><input type="hidden" name="{{ d.name }}" value="{{ d.value }}"/>'+
              '<i class="layui-icon layui-icon-edit item-edit"></i><i class="layui-icon layui-icon-close item-delete"></i></div>{{# }else{ }}<input type="hidden" name="{{ d.name }}" lay-verify="{{ d.verify || \'\' }}" lay-reqtext="{{ d.reqtext || \'\' }}"/>{{# } }}</div>'+
              '<div class="image_item layui-upload-drag" style="background:#efefef;" id="up-image-btn-{{ d.name }}" data-type="image" data-name="{{ d.name }}" data-thum="{{ d.thum || 0 }}"><i class="layui-icon">&#xe67c;</i><br/>上传图片</div>'+
              '<div style="clear:both"></div>' + c.tips_html + '</div></div>';
    //上传多图
    c.images = c.item_html + c.block_html + '><div id="image-show-{{ d.name }}" data-type="images" data-verify="{{ d.verify || \'\' }}" data-reqtext="{{ d.reqtext || \'\' }}">{{# var i = 0;layui.each(d.value, function(key, url){ i=1; }}'+
              '<div class="image_item"><img src="{{ url }}"/><input type="hidden" name="{{ d.name }}[]" value="{{ url }}"/>'+
              '<i class="layui-icon layui-icon-edit item-edit"></i><i class="layui-icon layui-icon-close item-delete"></i></div>{{# }); }}{{# if (i == 0) { }}<input type="hidden" name="{{ d.name }}" lay-verify="{{ d.verify || \'\' }}" lay-reqtext="{{ d.reqtext || \'\' }}"/>{{# } }}</div>'+
              '<div class="image_item layui-upload-drag" style="background:#efefef;" id="up-image-btn-{{ d.name }}" data-type="images" data-name="{{ d.name }}" data-thum="{{ d.thum || 0 }}"><i class="layui-icon">&#xe67c;</i><br/>上传图片</div>'+
              '<div style="clear:both"></div>' + c.tips_html + '</div></div>';
    //上传文件
    c.upfile = c.item_html + c.block_html + '>' + c.inline_html + ' style="width:85px;float:right;"><button type="button" class="layui-btn" data-type="{{ d.filetype }}" id="{{ d.id ? d.id : \'upfile-btn-\' + d.name }}">上传文件</button></div>'+
               c.block_html + ' style="margin-right:105px;margin-left:0;">'+ c.text_html + 'id="upfile-{{ d.name }}" class="layui-input"/></div>'+
               c.tips_html + '</div></div>';
    //验证码
    c.captcha = c.item_html + c.block_html + ' >' + c.text_html + 'id ="{{ d.name }}" class="layui-input" style="width:150px;float:left;margin-right:10px;"/><div class="box-{{ d.name }}" style="float:left;margin-right:10px;"><a class="layui-btn layui-btn-primary">点击获取验证码</a></div>' + c.tips_html + '</div></div>';
    //关联项
    c.relation = [];
    var b = {
        on: function(o,m,t){
            // 记录关联项，用于渲染完成后显示选中的关联项
            if(m && (t=='switch' || t=='radio')) c.relation.push({name:o,obj:m});
            // 监听变化并回调
            form.on(t +'('+ o +')',function(data){
                if(t=='switch' && m){
                    var obj = $("[id^='item-"+ m + '_' + data.value +"']");
                    data.elem.checked ? obj.show() : obj.hide();
                }else if(t=='radio' && m){
                    $("[id^='item-"+ m +"_']").hide();
                    $("[id^='item-"+ m + '_' + data.value +"']").show();
                }
                typeof c.success === 'function' && c.success(data, o);
            });
        },
        render: function(d){
            $h = $("#"+ d.bid); //绑定ID
            c.gid = d.gid || -1; //上传资源分组ID
            c.map = d.map || 'admin/system.upload/'; //上传接口
            b.init();
        },
        build: function(d){
            c.relation = []; //关联项清空
            c.bid  = d.bid || ''; //绑定ID
            c.gid  = d.gid || -1;  //上传资源分组ID
            c.map  = d.map || 'admin/system.upload/'; //上传接口
            c.url  = d.url || ''; //构建项json数据接口
            c.data = d.data || []; //构建项json数据
            c.space = d.space ? ' '+d.space : ''; //栅格间隙 layui-col-space[n]
            c.style = d.style ? '<style>'+ d.style + '</style>' : ''; //追加自定义样式
            c.success = d.success || '';
            if(c.data.length > 0 || c.data.constructor === Object){
                b.sett(c.data);
            }else{
                var load = layer.load(2);
                $.getJSON(c.url, function(res){
                    layer.close(load);
                    if(res.code === 1){
                        b.sett(res.data);
                    }else{
                        layer.msg(res.msg,{anim:6});
                    }
                });
            }
        },
        sett: function(data){ //data: 二维数组[{name:标识,title:标题,group:分组,type:类型,value:值,options:选项},{}]
            var html = '', str = '', tab_t = '', tab_c = '';
            $h = $("#"+ c.bid); f = []; //清空方法组
            for(var i in data){
                var d = data[i];
                if(d.type=='layui_tab'){
                    tab_t += '<li'+ (d.showTab ? ' class="layui-this"' : '') +'>'+ d.title +'</li>';
                    tab_c += '<div class="layui-tab-item'+(d.showTab ? ' layui-show' : '')+ c.space +'">';
                    for(var j in d.data){
                        var dd = d.data[j];
                        if(c[dd.type]){
                            str = b.tpl(c[dd.type],dd);
                            if(dd.callBack && typeof dd.callBack === 'function') f[dd.name] = dd.callBack;
                            tab_c += (dd.itemCol ? '<div class="'+ dd.itemCol +'">' + str + '</div>' : str);
                        }
                    }
                    tab_c += '</div>';
                }else if(c[d.type]){
                    str = b.tpl(c[d.type],d);
                    if(d.callBack && typeof d.callBack === 'function') f[d.name] = d.callBack;
                    html += (d.itemCol ? '<div class="'+ d.itemCol +'">' + str + '</div>' : str);
                }
            }
            html += tab_t ? '<div class="layui-tab layui-tab-brief"><ul class="layui-tab-title">'+tab_t+'</ul><div class="layui-tab-content">'+tab_c+'</div></div>' : !$h.addClass(c.space) || '';
            $h.html(c.style + html);
            form.render(null,c.bid+'_form');
            //显示选中的关联项
            $.each(c.relation,function(i,v){
                let ra = $h.find('input[name="'+v.name+'"]:checked').val();
                $("[id^='item-"+v.obj+"_"+ra+"']").show();
            });
            b.init();
             //回调并监听输入框变化
            if(typeof c.success === 'function'){
                c.success(null, '_init_');
                $h.find('input, textarea').on('input', function() { c.success({value: this.value}, this.name); });
            }
        },
        init: function(){
            //渲染时间
            b.rendTime();
            //渲染标签
            b.rendTags();
            //渲染地区
            b.rendAreas();
            //渲染取色器
            b.rendColorpicker();
            //注册相册
            b.Photos();
            //注册拖动
            b.ddSort();
            //注册编图
            b.regAction();
            //注册图库
            b.regUpload();
            //注册上传文件
            b.regUpFile();
            //百度编辑器
            b.uEditor();
            //MD编辑器
            b.cherryMd();
            //MD编辑器
            b.editorMd();
            //TinyMCE编辑器
            b.TinyMCE();
        },
        rendTime: function(){
            $h.find("[id^='show-date-']").each(function(){
                layui.laydate.render({elem:'#'+ $(this).attr('id'),type:$(this).data('type'),range:$(this).data('range')});
            });
        },
        rendTags: function(){
            $h.find("[id^='show-tags-']").each(function(){
                $('#'+ $(this).attr('id')).tagsInput();
            });
        },
        rendAreas:function(){
            if($h.find("[id^='areas-']").length > 0){
                b.getCT(function(){
                    $h.find("[id^='areas-']").each(function(){
                        var id = $(this).attr('id'), name = $(this).attr('name');
                        layui.cascader.render({
                            elem: "#"+ id,
                            data: cityData,
                            itemHeight: '260px',
                            filterable: true, //开启搜索
                            changeOnSelect: true, //选择即改变
                            onChange: f[name]
                        });
                    });
                });
            }
        },
        rendColorpicker: function(){
            $h.find("[id^='colorpicker-']").each(function(){
                var id = $(this).attr('id');
                layui.colorpicker.render({elem:'#'+ id,color:$('#show-' + id).val(),done:function(color){$('#show-' + id).val(color);}});
            });
        },
        Photos: function(){
            $h.find("[id^='image-show-']").each(function(){
                var id  = '#'+ $(this).attr('id');
                layer.photos({photos:id,anim:1,shade:[0.4,'#000']});
            });
        },
        regUpFile: function(){
            $h.find("[id^='upfile-btn-']").each(function(){
                var id  = '#'+ $(this).attr('id'),type = $(this).data('type') || 'file';
                var name = id.split('-')[2];
                layui.upload.render({
                    elem: id,
                    url: c.map + "upfile?action=" + type,
                    accept: "file",
                    done: function(res){
                        layer.msg(res.msg,{shade:[0.4,'#000'],time:2000},function(){
                            if(res.code==1){
                                $("#upfile-" + name).val(res.data.fileurl);
                                $("#size-" + name).val(res.data.filesize+'KB');
                            }
                        });
                    }
                });
                layer.photos({photos:id,anim:1,shade:[0.4,'#000']});
            });
        },
        ddSort: function(){
            var ids = [];
            $h.find("[id^='image-show-']").each(function(){
                if($(this).data('type')=='images') ids.push($(this).attr('id'));
            });
            $h.find("[id^='keyval-show-']").each(function(){
                ids.push($(this).attr('id'));
                b.setArr($(this).data('name')); // 初始赋值到隐藏域
            });
            if(ids.length > 0){
                layui.define(function(e){
                    $ = layui.$;window.jQuery = layui.$;
                    jQuery.getScript(static + "script/ddsort/ddsort.js").done(function(){
                        e('DDSort',jQuery);
                        if(ids.length > 0){
                            for(var i in ids){
                                let rs = ids[i].split("-");
                                $("#"+ids[i]).DDSort({
                                    target: '.'+ rs[0] +'_item',
                                    delay: 100, // 延时处理，默认为 50 ms，防止手抖点击 A 链接无效
                                    floatStyle:{
                                        'border': '1px solid #ccc',
                                        'background-color': '#fff'
                                    },
                                    up:function(){if(rs[0]=='keyval') b.setArr(rs[2]);}
                                });
                            }
                        }
                    }).fail(function(){
                        layui.hint().error('加载DDSort.js失败');
                    });
                });
            }
        },
        regAction: function(){
            $h.find("[id^='image-show-']").on('click','.image_item .item-edit', function(){
                var $this = $(this);
                var $img   = $this.parent().children('img');
                var $input = $this.parent().children('input');
                layui.admin.cropImg({
                    title: '编辑图像',
                    imgSrc: $img.attr('src'),
                    aspectRatio: 0,
                    acceptMime: 'image/*',
                    onCrop: function (base64){
                        var formData = new FormData();
                        var timestamp = Date.parse(new Date());
                        formData.append('file', layui.admin.util.toBlob(base64), timestamp + '.jpg');
                        var loadIndex = layer.load(2);
                        $.ajax({
                            method: "post",
                            url: c.map + "upfile?action=image&groupid=" + c.gid,
                            data: formData,
                            processData: false,
                            contentType: false,
                            dataType: "json",
                            success: function (res){
                                if(res.code==1) {
                                    $img.attr('src',base64);
                                    $input.val(res.data.fileurl);
                                }else{
                                    layer.msg(res.msg,{icon:2,shade:[0.4,'#000'],time:1500});
                                }
                                layer.close(loadIndex);
                            }
                        });
                    }
                });
            });
            $h.find("[id^='image-show-']").on('click','.image_item .item-delete', function(){
                var $this = $(this), noClick = $this.data('noClick'), name = $this.data('name');
                if (noClick) {return false;}
                layer.confirm('您确定要删除该' + (name ? name : '图片') + '吗？', {
                    title: '友情提示'
                }, function (index){
                    var pparent = $this.parent().parent();
                    $this.parent().remove();
                    if(pparent.children().length==0){
                        let name = pparent.attr('id').replace("image-show-","");
                        pparent.html('<input type="hidden" name="'+ name +'" lay-verify="'+ pparent.data('verify') +'" lay-reqtext="'+ pparent.data('reqtext') +'"/>');
                    }
                    layer.close(index);
                });
            });
            $h.find("[id^='keyval-add-']").on('click',function(){
                var id  = $(this).attr('id');
                var name = id.split('-')[2];
                var str = b.tpl(c.keyval_html,{value:{'':''}});
                $("#keyval-show-"+name).append(str);
            });
            $h.find("[id^='keyval-show-']").on('click','.keyval_item .del',function(){
                var name = $(this).parent().parent().data('name');
                $(this).parent().remove();
                b.setArr(name);
            });
            $h.find("[id^='keyval-show-']").on('blur','.keyval_item input',function(){
                b.setArr($(this).parent().parent().parent().data('name'));
            });
        },
        regUpload: function(){
            $h.find("[id^='up-image-btn-']").on('click', function(){
                var $this = $(this);
                var type = $this.data('type');
                var name = $this.data('name');
                var thum = $this.data('thum');
                fileLibrary.open({title:'图片管理',thum:thum,groupid:c.gid,url:c.map},function(res){
                    if(res.length == 0) return;
                    var $pbox = $h.find("#image-show-" + name);
                    if(type=='image'){
                        $pbox.html('<div class="image_item"><img src="'+ res[0].file_path +'" /><input type="hidden" name="'+ name +'" value="'+ res[0].file_path +'"><i class="layui-icon layui-icon-edit item-edit"></i><i class="layui-icon layui-icon-close item-delete"></i></div>');
                    }else{
                        if($pbox.children('input').length>0) $pbox.html('');
                        for(var i in res){
                            var v = res[i];
                            $pbox.append('<div class="image_item"><img src="'+ v.file_path +'" /><input type="hidden" name="'+ name +'[]" value="'+ v.file_path +'"><i class="layui-icon layui-icon-edit item-edit"></i><i class="layui-icon layui-icon-close item-delete"></i></div>');
                        }
                    }
                    b.Photos();
                });
            });
        },
        uEditor: function(){
            var ids = [];
            $h.find("[id^='ueditor-']").each(function(){
                ids.push($(this).attr('id'));
            });
            if(ids.length > 0){
                b.getUE(function(){
                    for(var i in ids){
                        let eid = ids[i], isOpen = $("#"+eid).parents('.layui-layer-content').length, height = $("#"+eid).parent().parent().height() - 110; height = height > 200 ? height : 200;
                        UE.delEditor(eid); //先销毁 实现每次重新加载
                        UE.getEditor(eid,{
                            initialFrameHeight: height,
                            toolbars: [[
                                'fullscreen','source','|','undo','redo','|','bold','italic','underline','fontborder','strikethrough','superscript','subscript','removeformat','pasteplain','|',
                                'forecolor','backcolor','insertorderedlist','insertunorderedlist','selectall','|','justifyleft','justifycenter','justifyright','justifyjustify','|',
                                'rowspacingtop','rowspacingbottom','lineheight','|','fontfamily','fontsize','insertcode','|','link','unlink','|','imagenone','imageleft','imageright','imagecenter','|',
                                'insertvideo','attachment','map','|','inserttable','deletetable','insertparagraphbeforetable','insertrow','deleterow','insertcol','deletecol','mergecells','mergeright','mergedown',
                                'splittocells','splittorows','splittocols','charts','|','horizontal','emotion','print','preview'
                            ]],
                            wordCount: false,
                            autoFloatEnabled: false,
                            autosave: false, //saveInterval: 5000,
                            zIndex: isOpen ? 19991000 : 999,
                            cpos: isOpen ? 'fixed' : '', //isOpen来判断是否为弹窗 弹出窗口中编辑器兼容可全屏
                            UEDITOR_HOME_URL: static + 'ueditor/',
                            serverUrl: c.map + 'ueditor?groupid=' + c.gid
                        });
                        UE.registerUI('插入图片', function(editor, uiName){
                            var btn = new UE.ui.Button({
                                name: uiName,
                                title: uiName,
                                cssRules: 'background-position:-380px 0;',
                                onclick: function(){
                                    fileLibrary.open({title:'图片管理',groupid:c.gid,url:c.map},function(res){
                                        if(res.length == 0) return;
                                        var html = '';
                                        for(var i in res){
                                            var v = res[i];
                                            html = html + '<p><img src="'+ v.file_path +'" /></p>';
                                        }
                                        editor.execCommand('insertHtml', html);
                                    });
                                }
                            });
                            editor.addListener('selectionchange', function(){ //源码模式时按钮变灰切换
                                var state = editor.queryCommandState(uiName);
                                if(state == -1){btn.setDisabled(true);btn.setChecked(false);}else{btn.setDisabled(false);btn.setChecked(state);}
                            });
                            return btn;
                        },43);
                    };
                });
            }
        },
        cherryMd: function(){
            var ids = [],obj = [];
            $h.find("[id^='cherrymd-']").each(function(){
                ids.push($(this).attr('id'));
            });
            if(ids.length > 0){
                b.getCM(function(){
                    for(var i in ids){
                        let eid = ids[i], heg = $("#"+eid).parent().parent().height() - 10; heg = heg>400 ? heg : 400;
                        let name = eid.split("-")[1],k = i;
                        obj[i] = new Cherry({
                            id:eid,
                            engine:{syntax:{codeBlock:{wrap:false}}},
                            editor:{height:heg+'px',id:name,name:name,value:$('#temp-'+name).val(),autoSave2Textarea:true,codemirror:{autofocus:false}},
                            callback:{afterInit:function(){$("textarea[name='"+name+"']")[0].value=$('#temp-'+name).val();}},
                            toolbars:{
                                toolbar: ['bold','italic','strikethrough','justify','|','header','list','panel','graph','|','vimg',{insert:['vado','vido','br','code','table','line-table','bar-table','link','linkOut','hr','detail']},'export','settings'],
                                toolbarRight: ['fullScreen'],
                                sidebar: ['mobilePreview', 'copy', 'theme'],
                                customMenu:{
                                    vimg:Cherry.createMenuHook('图库',{onClick:function(){
                                        fileLibrary.open({title:'图片管理',groupid:c.gid,url:c.map},function(res){
                                            if(res.length == 0) return;
                                            var html = '',v = '';
                                            for(var n in res){
                                                v = res[n];
                                                html = html + '![说明#100%]('+ v.file_path +')\n';
                                            }
                                            obj[k].insert(html);
                                        });
                                    }}),
                                    vido:Cherry.createMenuHook('视频',{iconName:'video',onClick:function(){return '!video[描述](url){poster=封面}';}}),
                                    vado:Cherry.createMenuHook('音频',{iconName:'video',onClick:function(){return '!audio[描述](url)';}}),
                                    linkOut:Cherry.createMenuHook('外链接',{iconName:'link',onClick:function(){return '[https://www.veitool.com](https://www.veitool.com){target=_blank}';}})
                                }
                            }
                        });
                    }
                });
            }
        },
        editorMd: function(){
            var ids = [];
            $h.find("[id^='editormd-']").each(function(){
                ids.push($(this).attr('id'));
            });
            if(ids.length > 0){
                b.getEM(function(){
                    for(var i in ids){
                        let heg = $("#"+ids[i]).parent().parent().height() - 10; heg = heg>400 ? heg : 400;
                        editormd(ids[i],{
                            width: "100%",
                            height: heg,
                            emoji: true,
                            tex: true,
                            codeFold : true,
                            flowChart: true,
                            htmlDecode: true,
                            sequenceDiagram: true,
                            //saveHTMLToTextarea : true,
                            path: static + "editormd/lib/",
                            toolbarIcons: function(){
                                return ["undo","redo","|","bold","del","italic","quote","ucwords","uppercase","lowercase","|","h1","h2","h3","h4","h5","h6","|","list-ul","list-ol","hr","link","reference-link","file","|","code","preformatted-text","code-block","table","html-entities","watch","preview","clear","fullscreen","help"]
                            },
                            toolbarIconsClass:{file:"fa-picture-o"},
                            toolbarHandlers:{
                                file:function(cm){
                                    fileLibrary.open({title:'图片管理',groupid:c.gid,url:c.map},function(res){
                                        if(res.length == 0) return;
                                        var html ='',v = '';
                                        for(var i in res){
                                            v = res[i];
                                            html = html + '<p><img src="'+ v.file_path +'" /></p>';
                                        }
                                        cm.replaceSelection("[![说明]("+ v.file_path +" \"说明\")](https://www.veitool.com \"说明\")");
                                    });
                                }
                            }
                        });
                    }
                });
            }
        },
        TinyMCE: function(){
            var ids = [];
            $h.find("[id^='tinymce-']").each(function(){
                ids.push($(this).attr('id'));
            });
            if(ids.length > 0){
                b.getTE(function(){
                    tinymce.remove();
                    for(var i in ids){
                        let heg = $("#"+ ids[i]).height(); heg = heg>400 ? heg : 400;
                        tinymce.init({
                            selector: '#'+ ids[i],
                            min_height: heg,
                            suffix: '.min',
                            branding: false,
                            language:'zh_CN',
                            base_url: static + 'tinymce',
                            relative_urls : false,
                            plugins: 'preview searchreplace autolink directionality visualblocks visualchars fullscreen image link media template code codesample charmap pagebreak nonbreaking anchor insertdatetime table advlist lists wordcount help emoticons autosave autoresize vimgs',
                            toolbar: 'code undo redo forecolor backcolor bold italic underline strikethrough removeformat image vimgs media link | alignleft aligncenter alignright alignjustify lineheight fullscreen | \ styles blocks fontfamily fontsize | outdent indent bullist numlist blockquote',
                            font_size_formats: '12px 14px 16px 18px 24px 36px 48px 56px 72px',
                            font_family_formats: '微软雅黑=Microsoft YaHei,Helvetica Neue,PingFang SC,sans-serif;苹果苹方=PingFang SC,Microsoft YaHei,sans-serif;宋体=simsun,serif;仿宋体=FangSong,serif;黑体=SimHei,sans-serif;Arial=arial,helvetica,sans-serif;Arial Black=arial black,avant garde;Book Antiqua=book antiqua,palatino;',
                            file_picker_callback: function(callback,value,meta){
                                var upurl = c.map + "upfile?action=image&groupid=" + c.gid;
                                var filetype = '.jpg, .jpeg, .png, .gif';
                                if(meta.filetype === 'file'){
                                     upurl = c.map + "upfile?action=file&groupid=" + c.gid;
                                     filetype = '.pdf, .txt, .zip, .rar, .7z, .doc, .docx, .xls, .xlsx, .ppt, .pptx';
                                }else if(meta.filetype === 'media'){
                                     upurl = c.map + "upfile?action=video&groupid=" + c.gid;
                                     filetype = '.mp3, .mp4';
                                }
                                var input = document.createElement('input');input.setAttribute('type', 'file');input.setAttribute('accept', filetype);input.click();/*模拟出一个input用于添加本地文件*/
                                input.onchange = function(){
                                    var xhr = new XMLHttpRequest(), formData = new FormData(), file = this.files[0];
                                    xhr.withCredentials = false;
                                    xhr.open('POST',upurl);
                                    xhr.onload = function(){
                                        if(xhr.status != 200){
                                            alert('HTTP Error: ' + xhr.status);return;
                                        }
                                        var res = JSON.parse(xhr.responseText);
                                        if(!res || typeof res.data.fileurl != 'string'){
                                            alert('Invalid JSON: ' + xhr.responseText);return;
                                        }
                                        callback(res.data.fileurl,{title:file.name});
                                    };
                                    formData.append('file', file, file.name);
                                    xhr.send(formData);
                                };
                            },
                            vimgs_upload_hander: function(editor){
                                fileLibrary.open({title:'图片管理',groupid:c.gid,url:c.map},function(res){
                                    if(res.length == 0) return;
                                    var html = '';
                                    for(var i in res){
                                        var v = res[i];
                                        html = html + '<p><img src="'+ v.file_path +'" /></p>';
                                    }
                                    editor.insertContent(html);
                                });
                            },
                            setup: function(editor){editor.on('change',function(){editor.save();})}
                        });
                    }
                });
            }
        },
        getCT: function(success){
            if(window.cityData){
                typeof success === 'function' && success();
            }else{
                $.getScript(static + "script/cityData.js", function(){
                    window.cityData = cityData;
                    typeof success === 'function' && success();
                });
            }
        },
        getUE: function(success){
            if(window.UE){
                typeof success === 'function' && setTimeout(function(){success()},100);
            }else{
                $.getScript(static + "ueditor/ueditor.all.min.js", function(){
                    window.UE = UE;
                    typeof success === 'function' && success();
                });
            }
        },
        getCM: function(success){
            if(window.Cherry){
                typeof success === 'function' && setTimeout(function(){success()},500);/*延迟：解决存在于OPEN窗口中时编辑区的渲染尺寸问题*/
            }else{
                layui.link(static + "cherrymd/cherry-markdown.min.css");
                $.getScript(static + "cherrymd/cherry-markdown.min.js", function(){
                    window.Cherry = Cherry;
                    typeof success === 'function' && success();
                });
            }
        },
        getEM: function(success){
            if(window.editormd){
                typeof success === 'function' && setTimeout(function(){success()},100);
            }else{
                layui.link(static + "editormd/css/editormd.min.css");
                $.getScript(static + "editormd/editormd.min.js", function(){
                    window.editormd = editormd;
                    typeof success === 'function' && success();
                });
            }
        },
        getTE: function(success){
            if(window.tinymce){
                typeof success === 'function' && success();
            }else{
                $.getScript(static + "tinymce/tinymce.min.js", function(){
                    window.tinymce = tinymce;
                    typeof success === 'function' && success();
                });
            }
        },
        setArr: function(name){
            var obj = {}, val;
            $h.find("#keyval-show-"+ name +" .keyval_item").each(function(){
                let key = $(this).find("input:eq(0)").val();
                if(key) obj[key] = $(this).find("input:eq(1)").val();
            });
            val = JSON.stringify(obj);
            $("#keyval-input-"+name).val(val==='{}' ? '' : val);
        },
        tpl: function(t,d){var h='';layui.laytpl(t).render(d,function(r){h=r;});return h;}
    };
    e("buildItems",b);
});
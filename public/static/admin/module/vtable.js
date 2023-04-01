/*
 * 简单数据表渲染
 * Website:www.veitool.com 
 * Author：niaho (QQ:26843818)
 */
layui.define(function(e){
    var $ = layui.$,layer=layui.layer,laytpl=layui.laytpl,laypage=layui.laypage,form=layui.form,Class=function(){};
    Class.prototype.render = function(d,b){
        let c = []; //配置组
        let $h;    //构建的容器
        c.elem = d.elem || '';
        $h = $('#'+c.elem); //构建的容器
        c.url  = d.url || ''; //构建项json数据接口
        c.data = d.data || []; //构建项json数据
        c.field = d.field || {}; //构建项json数据请求参数
        c.field.limit = d.limit || 10;
        c.field.page = d.page || 1;
        c.html = d.html;
        if(c.data.length > 0 || c.data.constructor === Object){
            tpl(c.data);
        }else{
            var load = layer.load(2);
            $.getJSON(c.url, c.field, function(res){
                layer.close(load);
                if(res.code === 0){
                    tpl(res);
                    typeof b === 'function' && b(res);
                }else{
                    layer.msg(res.msg,{anim:6});
                }
            });
        }
        let tpl = function(res){
            laytpl(c.html).render(res.data,function(html){
                $h.html(html);
                form.render('checkbox');
                if(res.count>0){
                    laypage.render({
                        elem: c.elem + '_pages',
                        prev: '<i class="layui-icon"></i>',
                        next: '<i class="layui-icon"></i>',
                        count: res.count,
                        layout: ['prev', 'page', 'next', 'skip', 'count', 'limit'],
                        curr: c.field.page,
                        limit: c.field.limit,
                        jump: function(obj,first){
                            if(!first){
                                c.page = obj.curr;
                                c.limit = obj.limit;
                                v.render(c);
                            }
                        }
                    });
                }
            });
        };
    }
    let v = new Class();
    e("vtable",v);
});
/*
 * 公用详细弹层
 * Website:www.veitool.com 
 * Author：niaho (QQ:26843818)
 */
layui.define(["admin","printer"], function(e){
    var $ = layui.$,admin=layui.admin,layer=layui.layer;
    var t = [], p = [], tpl = '';
    //用户详细信息模板
    p.muser = '<style>.muser_info td{color:#009688;}</style><div style="padding:10px 20px;">'+
        '<table class="layui-table muser_info" lay-skin="line"><thead><tr><th colspan="4">※ 用户信息</th></tr></thead><tbody>'+
        '<tr><th width="100">用户帐号</th><td width="150">{{ d.username }}</td><th>用户角色</th><td>{{ d.role_name }}</td></tr>'+
        '<tr><th>真实姓名</th><td>{{ d.truename }} （{{ d.gender==1 ? \'男\' : \'女\'}}）</td><th>联系电话</th><td>{{ d.mobile ? d.mobile : \'-\' }}</td></tr>'+
        '<tr><th>用户昵称</th><td>{{ d.nickname ? d.nickname : \'-\' }}</td><th>职位</th><td>{{ d.career ? d.career + \'（\'+ d.department +\'）\' : \'-\' }}</td></tr>'+
        '<tr><th>现金余额</th><td>{{ d.money ? d.money : 0 }}</td><th>积分余额</th><td>{{ d.credit ? d.credit : 0 }}</td></tr>'+
        '<tr><th>所属地区</th><td colspan="3">{{ d.areaname }}</td></tr>'+
        '<tr><th>详细地址</th><td colspan="3">{{ d.address ? d.address : \'-\' }}</td></tr>'+
        '<tr><th>微信</th><td>{{ d.wx ? d.wx : \'-\' }}</td><th>QQ</th><td>{{ d.qq ? d.qq : \'-\' }}</td></tr>'+
        '<tr><th>支付宝</th><td>{{ d.ali ? d.ali : \'-\' }}</td><th>邮箱</th><td>{{ d.email ? d.email : \'-\' }}</td></tr>'+
        '<tr><th>添加时间</th><td>{{ layui.util.toDateString(d.add_time*1000) }}</td><th>最近编辑</th><td>{{ d.upd_time ? layui.util.toDateString(d.upd_time*1000) : \'-\' }}</td></tr>'+
        '<tr><th>最近登录</th><td>{{ d.logintime ? layui.util.toDateString(d.logintime*1000) : \'-\' }}</td><th>登录 IP</th><td>{{ d.loginip }}</td></tr>'+
        '<tr><th>登录次数</th><td>{{ d.logins }} 次</td><th width="100">用户状态</th><td>{{# if (d.state==1){ }}正常{{# }else{ }}正常{{# } }}</td></tr>'+
        '</tbody></table>'+
        '<div class="layui-form-item text-right"><button type="reset" class="layui-btn layui-btn-sm layui-btn-primary" id="muser_info_print">'+
        '<i class="layui-icon layui-icon-print"></i> 打印</button></div>'+
        '</div>';
    //会员详细信息模板
    p.user = '<style>.user_info td{color:#009688;}</style><div style="padding:10px 20px 0 20px;">'+
        '<table class="layui-table user_info" lay-skin="line"><thead><tr><th colspan="4">※ 会员信息</th></tr></thead><tbody>'+
        '<tr><th width="100">会员帐号</th><td width="150">{{ d.username }}</td><th>会员ID号</th><td>{{ d.userid }}</td></tr>'+
        '<tr><th>真实姓名</th><td>{{ d.truename }} （{{ d.gender==1 ? \'男\' : \'女\'}}）</td><th>会员级别</th><td>{{# var Level = ["粉丝","会员","VIP"] }}{{ Level[d.level] }} - {{ d.group_name }}</td></tr>'+
        '<tr><th>会员昵称</th><td>{{ d.nickname ? d.nickname : \'-\' }}</td><th>联系电话</th><td>{{ d.mobile ? d.mobile : \'-\' }}</td></tr>'+
        '{{# layui.each(d.fmoney,function(k,v){ }}{{# if(k%2==0){ }}<tr>{{# } }}<th>{{ v.title }}</th><td{{ d.fmoney.length%2==1 && d.fmoney.length==(k+1) ? \' colspan="3"\' : \'\' }}>{{ d[v.key] }}</td>{{# if(k%2==1){ }}<tr>{{# } }}{{# });}}'+
        '<tr><th>推荐领导</th><td colspan="3">{{ d.inviter ? d.inviter : \'-\' }}</td></tr>'+
        '<tr><th>邮箱地址</th><td colspan="3">{{ d.email ? d.email : \'-\' }}</td></tr>'+
        '<tr><th>所属地区</th><td colspan="3">{{ d.areaname }}</td></tr>'+
        '<tr><th>详细地址</th><td colspan="3">{{ d.address ? d.address : \'-\' }}</td></tr>'+
        '<tr><th>注册时间</th><td>{{ layui.util.toDateString(d.add_time*1000) }}</td><th>最近编辑</th><td>{{ d.upd_time ? layui.util.toDateString(d.upd_time*1000) : \'-\' }}</td></tr>'+
        '<tr><th>上次登录</th><td>{{ d.lasttime ? layui.util.toDateString(d.lasttime*1000) : \'-\' }}</td><th>上次 IP</th><td>{{ d.lastip ? d.lastip : \'-\' }}</td></tr>'+
        '<tr><th>最近登录</th><td>{{ d.logintime ? layui.util.toDateString(d.logintime*1000) : \'-\' }}</td><th>最近 IP</th><td>{{ d.loginip ? d.loginip : \'-\' }}</td></tr>'+
        '<tr><th>登录次数</th><td>{{ d.logins }} 次</td><th width="100">会员状态</th><td>{{# if (d.state==1){ }}正常{{# }else{ }}锁定{{# } }}</td></tr>'+
        '</tbody></table>'+
        '<div class="layui-form-item text-right"><button type="reset" class="layui-btn layui-btn-sm layui-btn-primary" id="user_info_print">'+
        '<i class="layui-icon layui-icon-print"></i> 打印</button></div>'+
        '</div>';
    var v = {
        open: function(d){
            t.type = d.type || 'user';
            t.url  = d.url || '';
            t.para = d.para || {};
            t.title = d.title || '提示';
            t.area  = d.area || ['680px','95%'];
            t.data  = d.data || [];
            tpl = d.content || p[t.type];
            if(t.data.length > 0 || t.data.constructor === Object){
                v.tpl(t.data);
            }else{
                var load = layer.load(2);
                $.getJSON(t.url, t.para, function(res){
                    layer.close(load);
                    if(res.code === 1){
                        v.tpl(res.data);
                    }else{
                        layer.msg(res.msg,{anim:6});
                    }
                });
            }
        },
        tpl: function(data){
            admin.open({
                type: 1,
                tpl:true,
                area: t.area,
                title: t.title,
                data:data,
                content: tpl,
                success: function(){
                    $('#'+ t.type +'_info_print').click(function(){
                        layui.printer.printHtml({
                            html: '<table class="print-table">' + $('.'+ t.type +'_info').html() + '</table>'
                        });
                    });
                }
            });
        }
    };
    e("vinfo",v);
});
<tpl>
<style>
/* 用户信息 */
.user-info-head{width:110px;height:110px;line-height:110px;position:relative;display:inline-block;border:0 solid #eee;cursor:pointer;margin:0 auto}
.user-info-head img{width:110px;height:110px;border-radius:50%;}
.user-info-head i{position:absolute;height:25px;width:25px;line-height:25px;cursor:pointer;background:rgba(153,153,153,.7);border-radius:50%;text-align:center;color:#fff!important;display:none;}
.user-info-head .item-edit{top:-3px;left:-8px;}
.user-info-head:hover .item-edit{display:block}
.user-info-head .item-edit:hover{background:#000}
.user-info-head .item-chang{top:-3px;right:-8px;}
.user-info-head:hover .item-chang{display:block}
.user-info-head .item-chang:hover{background:#000}
.user-info-left p{border-bottom:1px solid #eee;padding:15px 0 5px 0;}
.user-info-left p span{color:#aaa;margin-right:5px;}
.user-info-list-item{position:relative;padding-bottom:8px}
.user-info-list-item>.layui-icon{position:absolute}
.user-info-list-item>p{padding-left:30px}
.layui-line-dash{border-bottom:1px dashed #ccc;margin:15px 0}
/*会员表单*/
.userInfoForm{max-width:600px;padding:25px 10px 0 0;}
.userInfoForm .layui-form-item{margin-bottom:25px;}
.userInfoForm .layui-form-radio{margin:0;}
.userInfoForm .layui-form-item .layui-form-label{width:130px;padding:9px 10px;}
.userInfoForm .layui-form-item .layui-input-block{margin-left:150px;}
/*账号绑定*/
.user-info-item{padding:15px 60px 10px 5px;border-bottom:1px solid #e8e8e8;position:relative}
.user-info-item .user-txt span{color:#aaa;margin-right:8px;}
.user-info-item .user-bd-list-lable{color:#333;margin-bottom:4px}
.user-info-item .user-info-oper{position:absolute;top:50%;right:10px;margin-top:-8px;cursor:pointer}
.user-info-item .user-bd-list-img{width:48px;height:48px;line-height:48px;position:absolute;top:50%;left:10px;margin-top:-24px}
.user-info-item .user-bd-list-img + .user-bd-list-content{margin-left:68px;}
</style>
<div class="layui-fluid">   
    <div class="layui-row layui-col-space15">
        <!-- left -->
        <div class="layui-col-sm12 layui-col-md3">
            <div class="layui-card">
                <div class="layui-card-body" style="padding:25px;min-height:-webkit-calc(100vh - 215px);min-height:-moz-calc(100vh - 215px);min-height:calc(100vh - 215px)">
                    <div class="text-center layui-text">
                        <div class="user-info-head">
                            <div id="userInfoHead"><img src="{$User.face ? $User.face : '/static/admin/img/head.jpg'}" alt=""/></div>
                            <i class="layui-icon layui-icon-edit item-edit" id="face-edit"></i>
                            <i class="layui-icon layui-icon-addition item-chang" id="face-chang"></i>
                        </div>
                        <h2 style="padding-top:20px;">{$User.username}</h2>
                        <p style="padding-top:8px;">{$User.truename}</p>
                    </div>
                    <div class="layui-text" style="padding-top:30px;">
                        <div class="user-info-list-item"><i class="layui-icon layui-icon-username"></i><p>{$User.role_name}</p></div>
                        <div class="user-info-list-item"><i class="layui-icon layui-icon-location"></i><p>{$User.areaname}</p></div>
                    </div>
                    <div class="layui-line-dash"></div>
                    <div class="user-info-left">
                        <p><span>登录次数：</span>{$User.logins} 次</p>
                        <p><span style="letter-spacing:2px;">登录 IP：</span>{$User.loginip}</p>
                        <p><span>添加时间：</span>{{ layui.util.toDateString({$User.addtime*1000}) }}</p>
                        <p><span>最近修改：</span>{{ layui.util.toDateString({$User.edittime*1000}) }}</p>
                        <p><span>最近登录：</span>{{ layui.util.toDateString({$User.logintime*1000}) }}</p>
                    </div>
                </div>
            </div>
        </div>
        <!-- right -->
        <div class="layui-col-sm12 layui-col-md9">
            <div class="layui-card">
                <!-- 选项卡开始 -->
                <div class="layui-tab layui-tab-brief" lay-filter="userInfoTab">
                    <ul class="layui-tab-title">
                        <li class="layui-this" id="infoShow">基本信息</li>
                        <li id="infoEdit">修改资料</li>
                        <li id="infoEdit">修改密码</li>
                    </ul>
                    <div class="layui-tab-content" style="min-height:-webkit-calc(100vh - 228px);min-height:-moz-calc(100vh - 228px);min-height:calc(100vh - 228px)">
                        <!-- tab1 -->
                        <div class="layui-tab-item layui-show">
                            <div class="user-bd-list layui-text">
                                <div class="user-info-item"><div class="user-txt"><span>用户帐号：</span>{$User.username} （{$User.role_name}）</div></div>
                                <div class="user-info-item"><div class="user-txt"><span>用户昵称：</span>{$User.nickname}</div></div>
                                <div class="user-info-item"><div class="user-txt"><span>真实姓名：</span>{$User.truename}（{$User.gender==1 ? '男' : '女'}）</div></div>
                                <div class="user-info-item"><div class="user-txt"><span>邮箱地址：</span>{$User.email}</div></div>
                                <div class="user-info-item"><div class="user-txt"><span>用户手机：</span>{$User.mobile}</div></div>
                                <div class="user-info-item"><div class="user-txt"><span>联系地址：</span>{$User.address}</div></div>
                                <div class="user-info-item">
                                    <div class="user-bd-list-img"><i class="layui-icon layui-icon-login-qq" style="color:#3492ED;font-size:48px;"></i></div>
                                    <div class="user-bd-list-content"><div class="user-bd-list-lable">绑定QQ</div><div class="user-bd-list-text">当前未绑定QQ账号</div></div>
                                    <a class="user-info-oper">绑定</a>
                                </div>
                                <div class="user-info-item">
                                    <div class="user-bd-list-img"><i class="layui-icon layui-icon-login-wechat" style="color:#4DAF29;font-size:48px;"></i></div>
                                    <div class="user-bd-list-content"><div class="user-bd-list-lable">绑定微信</div><div class="user-bd-list-text">当前未绑定绑定微信账号</div></div>
                                    <a class="user-info-oper">绑定</a>
                                </div>
                                <div style="margin-top:20px;text-align:right;"><a class="layui-btn layui-btn-primary" href="#/system.manager/index">返回用户列表</a></div>
                            </div>
                        </div>
                        <!-- tab2 -->
                        <div class="layui-tab-item">
                            <form class="layui-form userInfoForm render">
                                <div class="layui-form-item">
                                    <label class="layui-form-label">用户帐号:</label>
                                    <div class="layui-input-block"><input type="text" value="{$User.username}" class="layui-input"  readonly/></div>
                                </div>
                                <div class="layui-form-item">
                                    <label class="layui-form-label layui-form-required">用户昵称:</label>
                                    <div class="layui-input-block"><input type="text" name="nickname" id="nickname" value="{$User.nickname}" class="layui-input" lay-verify="nickname" lay-verType="tips" placeholder="请输入用户昵称"/></div>
                                </div>
                                <div class="layui-form-item">
                                    <label class="layui-form-label layui-form-required">真实姓名:</label>
                                    <div class="layui-input-block">
                                        <div class="layui-inline"><input type="text" name="truename" id="truename" value="{$User.truename}" class="layui-input" lay-verify="truename" lay-verType="tips" placeholder="请输入真实姓名" style="width:260px;"/></div>
                                        <div class="layui-inline">
                                            <input type="radio" name="gender" value="1" title="男"{$User.gender==1 ? " checked" : ""} >
                                            <input type="radio" name="gender" value="2" title="女"{$User.gender==2 ? " checked" : ""} >
                                        </div>
                                    </div>
                                </div>
                                <div class="layui-form-item">
                                    <label class="layui-form-label">邮箱地址:</label>
                                    <div class="layui-input-block"><input type="text" name="email" id="email" value="{$User.email}" class="layui-input" lay-verType="tips" lay-verify="email" placeholder="请输入邮箱地址"/></div>
                                </div>
                                <div class="layui-form-item">
                                    <label class="layui-form-label">用户手机:</label>
                                    <div class="layui-input-block"><input type="text" name="mobile" id="mobile" value="{$User.mobile}" class="layui-input" lay-verType="tips" lay-verify="phone" placeholder="请输入用户手机"/></div>
                                </div>
                                <div class="layui-form-item">
                                    <label class="layui-form-label">所属地区:</label>
                                    <div class="layui-input-block"><input type="text" name="areaid" id="user_show_areas" value="{$User.areaid ? $User.areaid : 20}" class="layui-input" placeholder="请选择地区"/></div>
                                </div>
                                <div class="layui-form-item">
                                    <label class="layui-form-label">详细地址:</label>
                                    <div class="layui-input-block"><input type="text" name="address" id="address" value="{$User.address}" class="layui-input" placeholder="请输入详细地址"/></div>
                                </div>
                                <div class="layui-form-item">
                                    <div class="layui-input-block"><button class="layui-btn" lay-filter="userInfoSubmit" data-url="edits" data-token="true" lay-submit>确认修改</button></div>
                                </div>
                            </form>
                        </div>
                        <!-- tab3 -->
                        <div class="layui-tab-item">
                            <!-- 修改登录密码 -->
                            <form class="layui-form userInfoForm render">
                                <div class="layui-form-item">
                                    <label class="layui-form-label layui-form-required">原登录密码：</label>
                                    <div class="layui-input-block"><input type="password" name="oldPassword" id="oldpassword" class="layui-input" lay-verify="password" lay-verType="tips" data-tip="原登录" lay-affix="eye" autocomplete='off' placeholder="请输入原登录密码"/></div>
                                </div>
                                <div class="layui-form-item">
                                    <label class="layui-form-label layui-form-required">新登录密码：</label>
                                    <div class="layui-input-block"><input type="password" name="newPassword" id="password" class="layui-input" lay-verify="password" lay-verType="tips" data-tip="新登录" lay-affix="eye" autocomplete='off' placeholder="请输入新登录设密码"/></div>
                                </div>
                                <div class="layui-form-item">
                                    <label class="layui-form-label layui-form-required">重复新密码：</label>
                                    <div class="layui-input-block"><input type="password" name="rePassword" id="passwords" class="layui-input" lay-verify="password" lay-verType="tips" data-tip="重复登录" lay-affix="eye" data-pass="password" autocomplete='off' placeholder="请再次输入新登录密码"/></div>
                                </div>
                                <div class="layui-form-item">
                                    <div class="layui-input-block"><button class="layui-btn" lay-filter="userInfoSubmit" data-url="changpwd" data-token="false" lay-submit>确认修改</button></div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- //选项卡结束 -->
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="{STATIC__PATH}script/cityData.js"></script>
<script>
layui.use(['fileLibrary', 'cascader'], function () {
    var gid = 1;
    var userid = {$User.userid};
    var map_root = layui.cache.maps;
    var app_root = map_root + 'system.manager/';
    var layer=layui.layer,form=layui.form,admin=layui.admin;
    /*自定义验证*/
    form.verify({
        nickname: [/^[\w\u4e00-\u9fa5]{2,10}$/, '昵称必须为2-10位数字、字母、汉字或下划线组成'],
        truename: [/^[\w\u4e00-\u9fa5]{2,30}$/, '姓名必须为2-30位数字、字母、汉字或下划线组成'],
        password: function(v,i){if(!/^[\S]{6,16}$/.test(v)){return $(i).data('tip')+'密码必须为6-16位非空字符组成';} if($(i).data('pass')){if(v!=$('#'+$(i).data('pass')).val())return '两次密码输入不一致';}}
    });
    /*顶部选项卡监听*/
    layui.element.on('tab(userInfoTab)', function(){
        if(this.getAttribute("id") == 'infoEdit'){
            layui.cascader.render({ /*渲染所属地区*/
                elem: "#user_show_areas",
                data: cityData,
                itemHeight: '260px',
                filterable: true,
                changeOnSelect: true
            });
        }
    });/**/
    /*点击头像*/
    $('#userInfoHead img').click(function(){
        var src = $(this).attr('src');
        layer.photos({photos:{data:[{alt:'',src:src}],start:'0'},anim:5,shade:[0.4,'#000']});
    });/**/
    /*编辑头像*/
    $('#face-edit').click(function (){
        var $img = $('#userInfoHead>img');
        if (!$img.attr('src')) return;
        admin.cropImg({
            title: '头像编辑',
            imgSrc: $img.attr('src'),
            aspectRatio: 1, /*裁剪比例*/
            acceptMime: 'image/*',
            onCrop: function (base64){
                var formData = new FormData();
                var timestamp = Date.parse(new Date());
                formData.append('file', admin.util.toBlob(base64),timestamp + '.jpg');
                var loadIndex = layer.load(2);
                admin.req(map_root + "system.upload/upfile?action=image&groupid=" + gid, formData, function(res){
                    if(res.code==1){
                        admin.req(app_root + "edit?do=up",{userid:userid,av:res.data.fileurl,af:'face'}, function(re){
                            layer.close(loadIndex);
                            if(re.code===1){
                                layer.msg(re.msg,{icon:1,time:1000},function(){
                                    $img.attr('src',res.data.fileurl);
                                });
                            }else{
                                layer.msg(re.msg,{icon:2,anim:6});
                            }
                        },'post',{headersToken:true});
                    }else{
                        layer.close(loadIndex);
                        layer.msg(res.msg,{icon:2,shade:[0.4,'#000'],time:1000});
                    }
                },'post',{processData:false,contentType:false});
            }
        });
    });/**/
    /*选择头像*/
    $('#face-chang').click(function (){
        var $img = $('#userInfoHead>img');
        layui.fileLibrary.open({
            title: '图片管理',
            groupid: gid,
            url: map_root + "system.upload/"
        },function(res){
            if(res.length == 0) return;
            var src = res[0].file_path;
            admin.req(app_root+"edit?do=up",{userid:userid,av:src,af:'face'},function(res){
                layer.msg(res.msg,{shade:[0.4,'#000'],time:1000},function(){if(res.code==1) $img.attr('src',src);});
            },'post',{headersToken:true});
        });
    });/**/
    /*会员修改表单提交*/
    form.on('submit(userInfoSubmit)',function(data){
        var btn = $(this);
        if(btn.attr('stop')){return false;}else{btn.attr('stop',1);}
        admin.req(app_root + btn.data('url'),data.field,function(res){
            layer.msg(res.msg,{shade:[0.4,'#000'],time:1500},function(){
                if(res.code==1) admin.refresh();
                btn.removeAttr('stop');
            });
        },'post',{headersToken:btn.data('token')});
        return false;
    });/**/
});
</script>
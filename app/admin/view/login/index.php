<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>管理员登录 - {:vconfig('site_title','微特管理系统')}</title>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=no"/>
<link rel="shortcut icon" href="{PUBLIC__PATH}/favicon.ico"/>
<link href="{STATIC__PATH}login/login.css" rel="stylesheet" type="text/css"/>
<link href="{STATIC__PATH}layui/css/layui.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src="{STATIC__PATH}layui/layui.js"></script>
<script type="text/javascript">var $=layui.$,jQuery=layui.jquery;</script>
<script type="text/javascript" src="{STATIC__PATH}script/jquery.SuperSlide.2.1.1.js"></script>
<script type="text/javascript" src="{STATIC__PATH}script/md5.js"></script>
</head>
<body>
    <div class="login-layout">
        <div class="logo"></div>
        <div class="login-form" style="position:relative">
            <form class="formContent">
                <div class="title">管理员登录</div>
                <div class="formInfo">
                    <div class="formText">
                        <i class="icon icon-user"></i><input type="text" name="username" id="username" value="" autocomplete="off" class="input-text" placeholder="用户名" />
                    </div>
                    <div class="formText">
                        <i class="icon icon-pwd"></i><input type="password" name="password" id="password" value="" autocomplete="off" class="input-text" placeholder="密  码" />
                    </div>
                    <div class="formText submitDiv">
                        {if vconfig('admin_captcha',1)}
                        <div class="code"><div class="arrow"></div><div class="code-img"><img src=""/></div></div>
                        <span class="text_span"><input type="text" name="captcha" id="captcha" value="" maxlength="5" placeholder="验证码"/></span>
                        <span class="submit_span"><button class="sub" id="login_btn">登 录</button></span>
                        {else}
                        <span class="submit_span"><button class="sub" id="login_btn" style="border-radius:50px;width:100%;display:block;">登 录</button></span>
                        {/if}
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div id="bannerBox">
        <ul id="slideBanner" class="slideBanner">
            <li><img src="{STATIC__PATH}login/banner_1.jpg"></li>
            <li><img src="{STATIC__PATH}login/banner_2.jpg"></li>
        </ul>
    </div>
<script type="text/javascript">
    layui.use(function(){
        var layer = layui.layer;
        //背景切换
        $("#bannerBox").slide({mainCell:".slideBanner",effect:"fold",interTime:3500,delayTime:500,autoPlay:true,autoPage:true,endFun:function(i,c,s){
            $(window).resize(function(){
                var width = $(window).width();
                var height = $(window).height();
                s.find(".slideBanner,.slideBanner li").css({"width":width,"height":height});
            });
        }});
        //获取焦点
        $(".formText .input-text").focus(function(){
            $(this).parent().addClass("focus");
        });
        //失去焦点
        $(".formText .input-text").blur(function(){
            $(this).parent().removeClass("focus");
        });
        //提示验证码
        $("#captcha").focus(function(){
            $('.code').show();
            if($('.code-img img').attr('src')=='') getCaptcha();
        });
        //刷新验证码
        $('.code-img img').on('click',function(){getCaptcha();});
        //隐藏验证码提示层
        $(document).click(function(e){
            if(e.target.name != 'captcha' && !$(e.target).parents("div").is(".submitDiv")){
                $('.code').hide();
            }
        });
        //默认焦点
        $('#username').focus();
        //回车触发
        $(document).keypress(function(e){if(e.which == 13){login();}});
        //点击触发
        $('#login_btn').on('click',function(){login();return false;});
        //登录处理
        var login = function(){
            var username = $('#username').val();
            var password = $('#password').val();
            var captcha  = $('#captcha');
            var _this;
            if(username == ''){
                _this = $('#username');
                return tip('请输入登录用户名',_this,1);
            }
            if(password == ''){
                _this = $('#password');
                return tip('请输入登录密码',_this,1);
            }
            if(captcha.length > 0){
                _this = captcha;
                captcha = _this.val();
                if(captcha.length != 5)
                return tip('请输入5位验证码',_this,3);
                if($('.code-img img').attr('src')==''){
                    $('.code').show();
                    getCaptcha();
                }
            }else{
                captcha = '';
            }
            var btn = $('#login_btn');
            if (btn.attr('stop')){return false;}else{btn.attr('stop',1);}
            //提交数据
            $.ajax({
                type: "POST",
                url: "{:url('admin/login/check')}",
                data: {username:username,password:hex_md5(password),captcha:captcha},
                dataType: "json",
                success:function(res){
                    var icon = res.code == '1' ? 1 : 2;
                    var anim = res.code == '1' ? 0 : 6;
                    var time = res.code == '1' ? 1500 : 2000;
                    layer.msg(res.msg,{icon:icon,shade:[0.5,'#000'],time:time,anim:anim},function(){
                        if(res.code == '1'){
                            location.href = res.data.url;
                        }else{
                            if(captcha) getCaptcha();
                        }
                        btn.removeAttr('stop');
                    });
                }
            });
        };
        var tip = function(t,o,f){layer.tips(t,o,{tips:[f,'#ff7c3a']});o.focus();return false;};
        //获取验证码
        var getCaptcha = function(){$('.code-img img').attr("src","{:url('api/captcha/admin')}?t="+Math.random());};
    });
</script>
</body>
</html>
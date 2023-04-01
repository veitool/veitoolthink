<tpl>
<style>
.layui-card-header.m{font-weight:bold;color:#aaa;}
/*应用快捷块样式*/
.console-app-group{display:block;padding:16px;border-radius:4px;text-align:center;background-color:#fff;cursor:pointer}
.console-app-group .console-app-icon{width:32px;height:32px;line-height:32px;margin-bottom:6px;display:inline-block;-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;font-size:32px;color:#69c0ff}
/*最新动态时间线*/
.layui-timeline-dynamic .layui-timeline-item{padding-bottom:0}
.layui-timeline-dynamic .layui-timeline-item:before{top:16px}
.layui-timeline-dynamic .layui-timeline-axis{width:9px;height:9px;left:1px;top:7px;background-color:#cbd0db}
.layui-timeline-dynamic .layui-timeline-axis.active{background-color:#0c64eb;box-shadow:0 0 0 2px rgba(12,100,235,.3)}
.dynamic-card-body{box-sizing:border-box;overflow:hidden}
.dynamic-card-body:hover{overflow-y:auto;padding-right:9px}
</style>
<!-- 正文开始 -->
<div class="layui-fluid">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-xs12">
            <div class="layui-card">
                <div class="layui-card-header m">快捷方式</div>
                <div class="layui-card-body">
                    <div class="layui-row layui-col-space15">
                        <div class="layui-col-sm6" style="padding-bottom:0;">
                            <div class="layui-row layui-col-space15">
                                <div class="layui-col-xs6 layui-col-sm3">
                                    <a class="console-app-group" href="#/addon/index">
                                        <i class="console-app-icon layui-icon layui-icon-app" style="font-size:26px;padding-top:3px;margin-right:6px;"></i>
                                        <div class="console-app-name">插件管理</div>
                                    </a>
                                </div>
                                <div class="layui-col-xs6 layui-col-sm3">
                                    <a class="console-app-group" href="#/system.setting/build">
                                        <i class="console-app-icon layui-icon layui-icon-set" style="color:#95de64;"></i>
                                        <div class="console-app-name">设配置项</div>
                                    </a>
                                </div>
                                <div class="layui-col-xs6 layui-col-sm3">
                                    <a class="console-app-group" href="#/system.setting/index">
                                        <i class="console-app-icon layui-icon layui-icon-component" style="color:#ff9c6e;"></i>
                                        <div class="console-app-name">管理配置</div>
                                    </a>
                                </div>
                                <div class="layui-col-xs6 layui-col-sm3">
                                    <a class="console-app-group" href="#/system.menus/index">
                                        <i class="console-app-icon layui-icon layui-icon-list" style="color:#b37feb;font-size:36px;"></i>
                                        <div class="console-app-name">后台菜单</div>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="layui-col-sm6" style="padding-bottom:0;">
                            <div class="layui-row layui-col-space15">
                                <div class="layui-col-xs6 layui-col-sm3">
                                    <a class="console-app-group" href="#/system.roles/index">
                                        <i class="console-app-icon layui-icon layui-icon-group" style="color:#ffd666;font-size:30px;"></i>
                                        <div class="console-app-name">角色管理</div>
                                    </a>
                                </div>
                                <div class="layui-col-xs6 layui-col-sm3">
                                    <a class="console-app-group" href="#/system.manager/index">
                                        <i class="console-app-icon layui-icon layui-icon-user" style="color:#5cdbd3;font-size:30px;"></i>
                                        <div class="console-app-name">用户管理</div>
                                    </a>
                                </div>
                                <div class="layui-col-xs6 layui-col-sm3">
                                    <a class="console-app-group" href="#/system.database/index">
                                        <i class="console-app-icon layui-icon layui-icon-table" style="color:#ff85c0;font-size:36px;"></i>
                                        <div class="console-app-name">数据维护</div>
                                    </a>
                                </div>
                                <div class="layui-col-xs6 layui-col-sm3">
                                    <a class="console-app-group" href="#/system.filemanage/index">
                                        <i class="console-app-icon layui-icon layui-icon-picture" style="color:#ffc069;"></i>
                                        <div class="console-app-name">文件管理</div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="layui-row layui-col-space15">
        <div class="layui-col-xs12 layui-col-sm6 layui-col-md3">
            <div class="layui-card">
                <div class="layui-card-header m">访问量<span class="layui-badge layui-badge-green pull-right">日</span></div>
                <div class="layui-card-body"><p class="lay-big-font">1028</p><p>总访问量<span class="pull-right">12008</span></p></div>
            </div>
        </div>
        <div class="layui-col-xs12 layui-col-sm6 layui-col-md3">
            <div class="layui-card">
                <div class="layui-card-header m">销售额<span class="layui-badge layui-badge-blue pull-right">月</span></div>
                <div class="layui-card-body">
                    <p class="lay-big-font"><span style="font-size:26px;line-height:1;"></span>1000</p><p>总销售额<span class="pull-right">60080</span></p>
                </div>
            </div>
        </div>
        <div class="layui-col-xs12 layui-col-sm6 layui-col-md3">
            <div class="layui-card">
                <div class="layui-card-header m">订单量<span class="layui-badge layui-badge-red pull-right">周</span></div>
                <div class="layui-card-body">
                    <p class="lay-big-font">180</p><p>转化率<span class="pull-right">70%</span></p>
                </div>
            </div>
        </div>
        <div class="layui-col-xs12 layui-col-sm6 layui-col-md3">
            <div class="layui-card">
                <div class="layui-card-header m">新增用户<span class="icon-text pull-right" lay-tips="指标说明" lay-direction="4" lay-offset="5px,5px"><i class="layui-icon layui-icon-tips"></i></span>
                </div>
                <div class="layui-card-body">
                    <p class="lay-big-font">1 <span style="font-size:24px;line-height:1;"></span></p>
                    <p>总用户<span class="pull-right">2人</span></p>
                </div>
            </div>
        </div>
    </div>
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md6 layui-col-sm6">
            <div class="layui-card">
                <div class="layui-card-header m">版本信息</div>
                <div class="layui-card-body">
                    <table class="layui-table layui-text">
                        <colgroup><col width="90"><col></colgroup>
                        <tbody>
                        <tr><td>软件版本</td><td>Veitool-v{{ layui.cache.version }}&emsp;<a href="https://www.veitool.com" target="_black">更新日志</a></td></tr>
                        <tr><td>基础框架</td><td>ThinkPHP-v<?php echo \think\App::VERSION;?> + Layui-v{{ layui.v }}</td></tr>
                        <tr><td>开发官方</td><td><a href="https://www.veitool.com" target="_black">https://www.veitool.com</a></td></tr>
                        <tr><td>服务系统</td><td>{:PHP_OS}</td></tr>
                        <tr><td>服务环境</td><td>{:PHP_SAPI}</td></tr>
                        <tr><td>PHP版本</td><td>{:PHP_VERSION}</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="layui-col-md6 layui-col-sm6">
            <div class="layui-card">
                <div class="layui-card-header m">最近登录</div>
                <div class="layui-card-body dynamic-card-body mini-bar" style="height:265px;">
                    <ul class="layui-timeline layui-timeline-dynamic">
                        <?php
                            $str = '';
                            $rs = \app\model\system\LoginLog::order("logid desc")->field("username,admin,loginip,logintime,message")->limit(10)->select()->toArray();
                            foreach($rs as $v){
                                $active = $v['admin'] ? '' :' active';
                                $str .= '<li class="layui-timeline-item">';
                                $str .= '<i class="layui-icon layui-timeline-axis'.$active.'"></i><div class="layui-timeline-content layui-text"><div class="layui-timeline-title">'.$v['username'].' / '.$v['loginip'].' / '.$v['message'].'<span class="pull-right">'. date('Y-m-d H:i:s',$v['logintime']).' </span></div></div>';
                                $str .= '</li>';
                            }
                            echo $str;
                        ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
layui.define(function(e){var t=layui.jquery,a=layui.layer,i=layui.cache.bins,n=".layui-layout-admin>.layui-body",o=n+">.layui-tab",l=".layui-layout-admin>.layui-side>.layui-side-scroll",r=".layui-layout-admin>.layui-header",s={layerData:{}};s.router={index:"/",lash:null,routers:{},init:function(e){return s.router.index=s.router.routerInfo(e.index).path.join("/"),e.pop&&"function"==typeof e.pop&&(s.router.pop=e.pop),e.notFound&&"function"==typeof e.notFound&&(s.router.notFound=e.notFound),s.router.onhashchange(),window.onhashchange=function(){s.router.onhashchange()},this},reg:function(e,t){if(e)if(t||(t=function(){}),e instanceof Array)for(var a in e)this.reg.apply(this,[e[a],t]);else"string"==typeof e&&(e=s.router.routerInfo(e).path.join("/"),"function"==typeof t?s.router.routers[e]=t:"string"==typeof t&&s.router[t]&&(s.router.routers[e]=s.router.routers[t]));return this},routerInfo:function(e){e||(e=location.hash);var t=e.replace(/^#+/g,"").replace(/\/+/g,"/");return 0!==t.indexOf("/")&&(t="/"+t),layui.router("#"+t)},refresh:function(e){s.router.onhashchange(e,!0)},go:function(e){location.hash="#"+s.router.routerInfo(e).href},onhashchange:function(e,t){var a=s.router.routerInfo(e);s.router.lash=a.href;var i=a.path.join("/");i&&"/"!==i||(i=s.router.index,a=s.router.routerInfo(s.router.index)),s.router.pop&&s.router.pop.call(this,a),s.router.routers[i]?(a.refresh=t,s.router.routers[i].call(this,a)):s.router.notFound&&s.router.notFound.call(this,a)}},s.getToken=function(){var e=layui.data(i.tableName);if(e)return e.token},s.removeToken=function(){layui.data(i.tableName,{key:"token",remove:!0})},s.putToken=function(e){layui.data(i.tableName,{key:"token",value:e})},s.putUser=function(e){layui.data(i.tableName,{key:"loginUser",value:e})},s.loadLeft=function(e){s.req(layui.cache.maps+"index/json?do=1",function(t){(!e||1==e)&&s.putUser(t.user),(!e||2==e)&&layui.index.buildLeftMenus(t.menus)})},s.getUser=function(){var e=layui.data(i.tableName);if(e)return e.loginUser},s.getAjaxHeaders=function(e){var t=[],a=s.getToken();return a&&t.push({name:"Authorization",value:"Bearer "+a.access_token}),t},s.ajaxSuccessBefore=function(e,t,i){return 401!==e.code||(s.removeToken(),layui.layer.msg(e.msg,{icon:2,anim:6,time:1500},function(){a.closeAll(),e.data.url&&location.replace(e.data.url)}),!1)},s.routerNotFound=function(e){layui.layer.alert('路由<span class="text-danger">'+e.path.join("/")+"</span>不存在",{title:"提示",offset:"30px",skin:"layui-layer-admin",btn:[],anim:6,shadeClose:!0})},s.flexible=function(e){var a=t(".layui-layout-admin"),i=a.hasClass("admin-nav-mini");void 0===e&&(e=i),i===e&&(e?(s.hideTableScrollBar(),a.removeClass("admin-nav-mini")):a.addClass("admin-nav-mini"),layui.event.call(this,"admin","flexible({*})",{expand:e}),s.resizeTable(600))},s.activeNav=function(e){if(e||(e=location.hash),!e)return console.warn("active url is null");e.indexOf("=")>0&&(e=(e=e.split("="))[0].substring(0,e[0].lastIndexOf("/"))),t(l+">.layui-nav .layui-nav-item .layui-nav-child dd.layui-this").removeClass("layui-this"),t(l+">.layui-nav .layui-nav-item.layui-this").removeClass("layui-this");var a=t(l+'>.layui-nav a[href="#'+e+'"]');if(0===a.length)return console.warn(e+" not found");var i=t(".layui-layout-admin").hasClass("admin-nav-mini");if("all"===t(l+">.layui-nav").attr("lay-shrink")){var n=a.parent("dd").parents(".layui-nav-child");i||t(l+">.layui-nav .layui-nav-itemed>.layui-nav-child").not(n).css("display","block").slideUp("fast",function(){t(this).css("display","")}),t(l+">.layui-nav .layui-nav-itemed").not(n.parent()).removeClass("layui-nav-itemed")}a.parent().addClass("layui-this");var o=a.parent("dd").parents(".layui-nav-child").parent();if(!i){var u=o.not(".layui-nav-itemed").children(".layui-nav-child");u.slideDown("fast",function(){if(t(this).is(u.last())){u.css("display","");var e=a.offset().top+a.outerHeight()+30-s.getPageHeight(),i=115-a.offset().top;e>0?t(l).animate({scrollTop:t(l).scrollTop()+e},300):i>0&&t(l).animate({scrollTop:t(l).scrollTop()-i},300)}})}o.addClass("layui-nav-itemed"),t('ul[lay-filter="admin-side-nav"]').addClass("layui-hide");var d=a.parents(".layui-nav");d.removeClass("layui-hide"),t(r+">.layui-nav>.layui-nav-item").removeClass("layui-this"),t(r+'>.layui-nav>.layui-nav-item>a[nav-bind="'+d.attr("nav-id")+'"]').parent().addClass("layui-this")},s.popupRight=function(e){return e.anim=-1,e.offset="r",e.move=!1,e.fixed=!0,void 0===e.area&&(e.area="336px"),void 0===e.title&&(e.title=!1),void 0===e.closeBtn&&(e.closeBtn=!1),void 0===e.shadeClose&&(e.shadeClose=!0),void 0===e.skin&&(e.skin="layui-anim layui-anim-rl layui-layer-adminRight"),s.open(e)},s.open=function(e){e.content&&2===e.type&&(e.url=void 0),!e.url||2!==e.type&&void 0!==e.type||(e.type=1),void 0===e.shadeClose&&(e.shadeClose=!0),void 0===e.skin&&(e.skin="layui-layer-admin");var i=e.end;if(e.end=function(){a.closeAll("tips"),i&&i()},e.url){var n=e.success;e.success=function(a,i){t(a).data("tpl",e.tpl||""),s.reloadLayer(i,e.url,n)}}else if(e.tpl&&e.content)e.content=layui.laytpl(e.content).render(e.data);else if(e.bid){var o=e.bid.split("@"),l=o[0],r=o[1]?" "+o[1]:"";e.btn=e.btn||["确定","取消"],e.yes=e.yes||function(e,t){t.find("[lay-submit]").trigger("click")},e.content=e.content||'<form class="layui-form model-form'+r+'" lay-filter="'+l+'_form"><div id="'+l+'"></div><input type="submit" style="display:none;" lay-filter="'+l+'" lay-submit/></form>'}var u=a.open(e);return e.data&&(s.layerData["d"+u]=e.data),u},s.getLayerData=function(e,t){if(void 0===e)return void 0===(e=parent.layer.getFrameIndex(window.name))?null:parent.layui.admin.getLayerData(parseInt(e),t);if(isNaN(e)&&(e=s.getLayerIndex(e)),void 0!==e){var a=s.layerData["d"+e];return t&&a?a[t]:a}},s.putLayerData=function(e,t,a){if(void 0===a)return void 0===(a=parent.layer.getFrameIndex(window.name))?void 0:parent.layui.admin.putLayerData(e,t,parseInt(a));if(isNaN(a)&&(a=s.getLayerIndex(a)),void 0!==a){var i=s.getLayerData(a);i||(i={}),i[e]=t,s.layerData["d"+a]=i}},s.reloadLayer=function(e,a,i){if("function"==typeof a&&(i=a,a=void 0),isNaN(e)&&(e=s.getLayerIndex(e)),void 0!==e){var n=t("#layui-layer"+e);void 0===a&&(a=n.data("url")),a&&(n.data("url",a),s.showLoading(n),s.ajax({url:a,dataType:"html",success:function(a){s.removeLoading(n,!1),"string"!=typeof a&&(a=JSON.stringify(a));var o=n.data("tpl");if(!0===o||"true"===o){var l=s.getLayerData(e)||{};l.layerIndex=e;var r=t("<div>"+a+"</div>"),u={};for(var d in r.find("script,[tpl-ignore]").each(function(e){var a=t(this);u["temp_"+e]=a[0].outerHTML,a.after("${temp_"+e+"}").remove()}),a=layui.laytpl(r.html()).render(l),u)a=a.replace("${"+d+"}",u[d])}n.children(".layui-layer-content").html(a),s.renderTpl("#layui-layer"+e+" [v-tpl]"),i&&i(n[0],e)}}))}},s.alert=function(e,t,i){return"function"==typeof t&&(i=t,t={}),void 0===t.skin&&(t.skin="layui-layer-admin"),void 0===t.shade&&(t.shade=.1),a.alert(e,t,i)},s.confirm=function(e,t,i,n){return"function"==typeof t&&(n=i,i=t,t={}),void 0===t.skin&&(t.skin="layui-layer-admin"),void 0===t.shade&&(t.shade=.1),a.confirm(e,t,i,n)},s.prompt=function(e,t){return"function"==typeof e&&(t=e,e={}),void 0===e.skin&&(e.skin="layui-layer-admin layui-layer-prompt"),void 0===e.shade&&(e.shade=.1),a.prompt(e,t)},s.req=function(e,a,n,o,l){return"function"==typeof a&&(l=o,o=n,n=a,a={}),void 0!==o&&"string"!=typeof o&&(l=o,o=void 0),o||(o="GET"),"string"==typeof a?(l||(l={}),l.contentType||(l.contentType="application/json;charset=UTF-8")):i.reqPutToPost&&("put"===o.toLowerCase()?(o="POST",a._method="PUT"):"delete"===o.toLowerCase()&&(o="GET",a._method="DELETE")),s.ajax(t.extend({url:(i.baseServer||"")+e,data:a,type:o,dataType:"json",success:n},l))},s.ajax=function(e){var a=s.util.deepClone(e);e.dataType||(e.dataType="json"),e.headers||(e.headers={});var n=s.getAjaxHeaders(e.url);if(n)for(var o=0;o<n.length;o++)void 0===e.headers[n[o].name]&&(e.headers[n[o].name]=n[o].value);e.headersToken&&(e.headers["X-CSRF-TOKEN"]=i.token);var l=e.success;return e.success=function(n,o,r){!1!==s.ajaxSuccessBefore(s.parseJSON(n),e.url,{param:a,reload:function(e){s.ajax(t.extend(!0,a,e))},update:function(e){n=e},xhr:r})?(e.headersToken&&n.token&&(i.token=n.token),l&&l(n,o,r)):e.cancel&&e.cancel()},e.error=function(t,a){e.success({code:t.status,msg:t.statusText},a,t)},!i.version||i.apiNoCache&&"json"===e.dataType.toLowerCase()||(-1===e.url.indexOf("?")?e.url+="?v=":e.url+="&v=",!0===i.version?e.url+=(new Date).getTime():e.url+=i.version),t.ajax(e)},s.parseJSON=function(e){if("string"==typeof e)try{return JSON.parse(e)}catch(e){}return e},s.showLoading=function(e,a,n,o){void 0===e||"string"==typeof e||e instanceof t||(a=e.type,n=e.opacity,o=e.size,e=e.elem),void 0===a&&(a=i.defaultLoading||1),void 0===o&&(o="sm"),void 0===e&&(e="body");var l=['<div class="v-loader '+o+'"><div></div></div>','<div class="ball-loader '+o+'"><span></span><span></span><span></span><span></span></div>'];t(e).addClass("page-no-scroll"),t(e).scrollTop(0);var r=t(e).children(".page-loading");r.length<=0&&(t(e).append('<div class="page-loading">'+l[a-1]+"</div>"),r=t(e).children(".page-loading")),void 0!==n&&r.css("background-color","rgba(255,255,255,"+n+")"),r.show()},s.removeLoading=function(e,a,i){void 0===e&&(e="body"),void 0===a&&(a=!0);var n=t(e).children(".page-loading");i?n.remove():a?n.fadeOut("fast"):n.hide(),t(e).removeClass("page-no-scroll")},s.putTempData=function(e,t,a){var n=a?i.tableName:i.tableName+"_tempData";null==t?a?layui.data(n,{key:e,remove:!0}):layui.sessionData(n,{key:e,remove:!0}):a?layui.data(n,{key:e,value:t}):layui.sessionData(n,{key:e,value:t})},s.getTempData=function(e,t){"boolean"==typeof e&&(t=e,e=void 0);var a=t?i.tableName:i.tableName+"_tempData",n=t?layui.data(a):layui.sessionData(a);return e?n?n[e]:void 0:n},s.rollPage=function(e){var a=t(o+">.layui-tab-title"),i=a.scrollLeft();if("left"===e)a.animate({scrollLeft:i-120},100);else if("auto"===e){var n=0;a.children("li").each(function(){if(t(this).hasClass("layui-this"))return!1;n+=t(this).outerWidth()}),a.animate({scrollLeft:n-120},100)}else a.animate({scrollLeft:i+120},100)},s.refresh=function(e){s.router.refresh(e)},s.closeThisTabs=function(e){var i=t(o+">.layui-tab-title");if(e){if(e===i.find("li").first().attr("lay-id"))return a.msg("主页不能关闭",{icon:2});i.find('li[lay-id="'+e+'"]').find(".layui-tab-close").trigger("click")}else{if(i.find("li").first().hasClass("layui-this"))return a.msg("主页不能关闭",{icon:2});i.find("li.layui-this").find(".layui-tab-close").trigger("click")}},s.closeOtherTabs=function(e){e?t(o+">.layui-tab-title li:gt(0)").each(function(){e!==t(this).attr("lay-id")&&t(this).find(".layui-tab-close").trigger("click")}):t(o+">.layui-tab-title li:gt(0):not(.layui-this)").find(".layui-tab-close").trigger("click")},s.closeAllTabs=function(){t(o+">.layui-tab-title li:gt(0)").find(".layui-tab-close").trigger("click"),t(o+">.layui-tab-title li:eq(0)").trigger("click")},s.changeTheme=function(e,t,a,i){if(a||s.putSetting("defaultTheme",e),t||(t=top),s.removeTheme(t),e)try{var n=t.layui.jquery("body");n.addClass(e),n.data("theme",e)}catch(e){}if(!i)for(var o=t.frames,l=0;l<o.length;l++)s.changeTheme(e,o[l],!0,!1)},s.removeTheme=function(e){e||(e=window);try{var t=e.layui.jquery("body"),a=t.data("theme");a&&t.removeClass(a),t.removeData("theme")}catch(e){}},s.closeThisDialog=function(){return s.closeDialog()},s.closeDialog=function(e){e?a.close(s.getLayerIndex(e)):parent.layer.close(parent.layer.getFrameIndex(window.name))},s.getLayerIndex=function(e){if(!e)return parent.layer.getFrameIndex(window.name);var a=t(e).parents(".layui-layer").first().attr("id");return a&&a.length>=11?a.substring(11):void 0},s.iframeAuto=function(){return parent.layer.iframeAuto(parent.layer.getFrameIndex(window.name))},s.getPageHeight=function(){return document.documentElement.clientHeight||document.body.clientHeight},s.getPageWidth=function(){return document.documentElement.clientWidth||document.body.clientWidth},s.modelForm=function(e,a,i){var n=t(e);n.addClass("layui-form"),i&&n.attr("lay-filter",i);var o=n.find(".layui-layer-btn .layui-layer-btn0");o.attr("lay-submit",""),o.attr("lay-filter",a)},s.btnLoading=function(e,a,i){void 0!==a&&"boolean"==typeof a&&(i=a,a=void 0),void 0===a&&(a="&nbsp;加载中"),void 0===i&&(i=!0);var n=t(e);i?(n.addClass("v-btn-loading"),n.prepend('<span class="v-btn-loading-text"><i class="layui-icon layui-icon-loading layui-anim layui-anim-rotate layui-anim-loop"></i>'+a+"</span>"),n.attr("disabled","disabled").prop("disabled",!0)):(n.removeClass("v-btn-loading"),n.children(".v-btn-loading-text").remove(),n.removeProp("disabled").removeAttr("disabled"))},s.openSideAutoExpand=function(){var e=t(".layui-layout-admin>.layui-side");e.off("mouseenter.openSideAutoExpand").on("mouseenter.openSideAutoExpand",function(){t(this).parent().hasClass("admin-nav-mini")&&(s.flexible(!0),t(this).addClass("side-mini-hover"))}),e.off("mouseleave.openSideAutoExpand").on("mouseleave.openSideAutoExpand",function(){t(this).hasClass("side-mini-hover")&&(s.flexible(!1),t(this).removeClass("side-mini-hover"))})},s.openCellAutoExpand=function(){var e=t("body");e.off("mouseenter.openCellAutoExpand").on("mouseenter.openCellAutoExpand",".layui-table-view td",function(){t(this).find(".layui-table-grid-down").trigger("click")}),e.off("mouseleave.openCellAutoExpand").on("mouseleave.openCellAutoExpand",".layui-table-tips>.layui-layer-content",function(){t(".layui-table-tips-c").trigger("click")})},s.parseLayerOption=function(e){for(var a in e)e.hasOwnProperty(a)&&e[a]&&-1!==e[a].toString().indexOf(",")&&(e[a]=e[a].toString().split(","));var i={success:"layero,index",cancel:"index,layero",end:"",full:"",min:"",restore:""};for(var n in i)if(i.hasOwnProperty(n)&&e[n])try{/^[a-zA-Z_]+[a-zA-Z0-9_]+$/.test(e[n])&&(e[n]+="()"),e[n]=new Function(i[n],e[n])}catch(t){e[n]=void 0}return e.content&&"string"==typeof e.content&&0===e.content.indexOf("#")&&(t(e.content).is("script")?e.content=t(e.content).html():e.content=t(e.content)),void 0===e.type&&void 0===e.url&&(e.type=2),e},s.strToWin=function(e){var t=window;if(!e)return t;for(var a=e.split("."),i=0;i<a.length;i++)t=t[a[i]];return t},s.hideTableScrollBar=function(){if(!(s.getPageWidth()<=768)){var e=i.pageTabs?t(o+">.layui-tab-content>.layui-tab-item.layui-show"):t(n);window.hsbTimer&&clearTimeout(window.hsbTimer),e.find(".layui-table-body.layui-table-main").addClass("no-scrollbar"),window.hsbTimer=setTimeout(function(){e.find(".layui-table-body.layui-table-main").removeClass("no-scrollbar")},600)}},s.resizeTable=function(e){setTimeout(function(){(i.pageTabs?t(o+">.layui-tab-content>.layui-tab-item.layui-show"):t(n)).find(".layui-table-view").each(function(){var e=t(this).attr("lay-table-id");layui.table&&layui.table.resize(e)})},void 0===e?0:e)},s.vForm=function(e){e.find("input[date-render]").map(function(){let e=t.extend({type:"date",range:!0,format:"yyyy/MM/dd"},lay.options(t(this),"date-render"));this.setAttribute("autocomplete","off"),layui.laydate.render({elem:this,type:e.type,range:e.range,format:e.format,done:function(){t(this.elem).trigger("input")}})}),e.find(".layui-form [lay-submit][lay-filter^='search-']").map(function(){let e=t(this).attr("lay-filter"),a=e.split("-"),i=a[3]?a[3]:"";layui.form.on("submit("+e+")",function(e){let n=e.field;if(a[2]&&"all"==a[2]&&(n={},i)){let a=i.split("|");t.each(a,function(t,a){n[a]=e.field[a]?e.field[a]:""})}let o=layui.table.getOptions(a[1]);return layui.table.reloadData(a[1],{where:n,page:!!o.page&&{curr:1}}),!1})}),e.find(".render").map(function(){layui.form.render(t(this))})},s.getDict=function(e){var t=s.getUser().dict;return e&&t[e]?t[e]:t},s.vDict=function(e,a){var i=e.find("[v-dict]"),n=s.getUser().dict;i.length>0&&layui.use(["xmSelect"],function(){var e=s.util.deepClone(a),o=e?t.extend({},n,e):n;i.map(function(){let e=t(this),a=e.attr("v-dict"),i=n[a]?0:1,l=e.prop("tagName"),r=0===a.indexOf("{")||0===a.indexOf("[")?lay.options(e,"v-dict"):o[a];if(a&&r)if("SELECT"==l){let a=e.html();t.each(r,function(e,t){a+='<option value="'+(t.value||e)+'">'+(t.name||t)+"</option>"}),e.html(a),layui.form.render(e)}else{if(!Array.isArray(r)){let e=[];t.each(r,function(t,a){e.push({name:t,value:a})}),r=e}layui.xmSelect.render(t.extend({el:e.get(0),radio:!0,clickClose:!0,tips:"请选择",height:"300px",autoRow:!0,filterable:!0,searchTips:"搜索词",model:{label:{type:"text"}},tree:{show:!0,indent:25,expandedKeys:!0,strict:!1},data:i?r:s.util.toTree(r,"pid")},lay.options(e,"options")))}})})},s.vShow=function(e){var a=e.find("[v-show]");if(a.length>0){var i=s.getUser();a.map(function(){let e=t(this).attr("v-show");e&&i.roles.indexOf(","+e.replace("@","")+",")<0&&(0===e.indexOf("@")?t(this).addClass("layui-btn-disabled").removeAttr("id").off("click"):t(this).remove())})}},s.events={flexible:function(){s.strToWin(t(this).data("window")).layui.admin.flexible()},refresh:function(){s.strToWin(t(this).data("window")).layui.admin.refresh()},back:function(){s.strToWin(t(this).data("window")).history.back()},clearCache:function(){var e=t(this).data("url");a.confirm("确定要清空缓存吗？",{title:"系统提示",skin:"layui-layer-admin",shade:.1},function(){a.load(2),s.req(e,function(e){return a.closeAll(),a.msg(e.msg)})})},logout:function(){var e=s.util.deepClone(t(this).data());function i(){if(e.ajax){var t=a.load(2);s.req(e.ajax,function(i){if(a.close(t),e.parseData)try{i=new Function("res",e.parseData)(i)}catch(e){console.error(e)}i.code==(e.code||0)?(s.removeToken(),location.replace(e.url||"/")):a.msg(i.msg,{icon:2})},e.method||"delete")}else s.removeToken(),location.replace(e.url||"/")}if(s.unlockScreen(),!1===e.confirm||"false"===e.confirm)return i();s.strToWin(e.window).layui.layer.confirm(e.content||"确定要退出登录吗？",t.extend({title:"温馨提示",skin:"layui-layer-admin",shade:.1},s.parseLayerOption(e)),function(){i()})},open:function(){var e=s.util.deepClone(t(this).data());s.strToWin(e.window).layui.admin.open(s.parseLayerOption(e))},popupRight:function(){var e=s.util.deepClone(t(this).data());s.strToWin(e.window).layui.admin.popupRight(s.parseLayerOption(e))},fullScreen:function(){var e="layui-icon-screen-full",a="layui-icon-screen-restore",i=t(this).find("i");if(document.fullscreenElement||document.msFullscreenElement||document.mozFullScreenElement||document.webkitFullscreenElement||!1){var n=document.exitFullscreen||document.webkitExitFullscreen||document.mozCancelFullScreen||document.msExitFullscreen;if(n)n.call(document);else if(window.ActiveXObject){var o=new ActiveXObject("WScript.Shell");o&&o.SendKeys("{F11}")}i.addClass(e).removeClass(a)}else{var l=document.documentElement,r=l.requestFullscreen||l.webkitRequestFullscreen||l.mozRequestFullScreen||l.msRequestFullscreen;if(r)r.call(l);else if(window.ActiveXObject){var s=new ActiveXObject("WScript.Shell");s&&s.SendKeys("{F11}")}i.addClass(a).removeClass(e)}},leftPage:function(){s.strToWin(t(this).data("window")).layui.admin.rollPage("left")},rightPage:function(){s.strToWin(t(this).data("window")).layui.admin.rollPage()},closeThisTabs:function(){var e=t(this).data("url");s.strToWin(t(this).data("window")).layui.admin.closeThisTabs(e)},closeOtherTabs:function(){s.strToWin(t(this).data("window")).layui.admin.closeOtherTabs()},closeAllTabs:function(){s.strToWin(t(this).data("window")).layui.admin.closeAllTabs()},closeDialog:function(){t(this).parents(".layui-layer").length>0?s.closeDialog(this):s.closeDialog()},closeIframeDialog:function(){s.closeDialog()},closePageDialog:function(){s.closeDialog(this)},lockScreen:function(){s.strToWin(t(this).data("window")).layui.admin.lockScreen(t(this).data("url"))}},s.cropImg=function(e){var i="image/jpeg",n=e.aspectRatio,o=e.imgSrc,l=e.imgType,r=e.onCrop,u=e.limitSize,d=e.acceptMime,c=e.exts,p=e.title;void 0===n&&(n=1),void 0===p&&(p="裁剪图片"),l&&(i=l),layui.use(["Cropper"],function(){var e=layui.Cropper;var l=['<div class="layui-row">','<div class="layui-col-sm8" style="min-height: 9rem;">','<img id="v-crop-img" src="',o||"",'" style="max-width:100%;" alt=""/>',"</div>",'<div class="layui-col-sm4 layui-hide-xs" style="padding: 15px;text-align: center;">','<div id="v-crop-img-preview" style="width: 100%;height: 9rem;overflow: hidden;display: inline-block;border: 1px solid #dddddd;"></div>',"</div>","</div>",'<div class="text-center v-crop-tool" style="padding: 15px 10px 5px 0;">','<div class="layui-btn-group" style="margin-bottom: 10px;margin-left: 10px;">','<button title="放大" data-method="zoom" data-option="0.1" class="layui-btn icon-btn" type="button"><i class="layui-icon layui-icon-add-1"></i></button>','<button title="缩小" data-method="zoom" data-option="-0.1" class="layui-btn icon-btn" type="button"><span style="display: inline-block;width: 12px;height: 2.5px;background: rgba(255, 255, 255, 0.9);vertical-align: middle;margin: 0 4px;"></span></button>',"</div>",'<div class="layui-btn-group layui-hide-xs" style="margin-bottom: 10px;">','<button title="向左旋转" data-method="rotate" data-option="-45" class="layui-btn icon-btn" type="button"><i class="layui-icon layui-icon-refresh-1" style="transform: rotateY(180deg) rotate(40deg);display: inline-block;"></i></button>','<button title="向右旋转" data-method="rotate" data-option="45" class="layui-btn icon-btn" type="button"><i class="layui-icon layui-icon-refresh-1" style="transform: rotate(30deg);display: inline-block;"></i></button>',"</div>",'<div class="layui-btn-group" style="margin-bottom: 10px;">','<button title="左移" data-method="move" data-option="-10" data-second-option="0" class="layui-btn icon-btn" type="button"><i class="layui-icon layui-icon-left"></i></button>','<button title="右移" data-method="move" data-option="10" data-second-option="0" class="layui-btn icon-btn" type="button"><i class="layui-icon layui-icon-right"></i></button>','<button title="上移" data-method="move" data-option="0" data-second-option="-10" class="layui-btn icon-btn" type="button"><i class="layui-icon layui-icon-up"></i></button>','<button title="下移" data-method="move" data-option="0" data-second-option="10" class="layui-btn icon-btn" type="button"><i class="layui-icon layui-icon-down"></i></button>',"</div>",'<div class="layui-btn-group" style="margin-bottom: 10px;">','<button title="左右翻转" data-method="scaleX" data-option="-1" class="layui-btn icon-btn" type="button" style="position: relative;width: 41px;"><i class="layui-icon layui-icon-triangle-r" style="position: absolute;left: 9px;top: 0;transform: rotateY(180deg);font-size: 16px;"></i><i class="layui-icon layui-icon-triangle-r" style="position: absolute; right: 3px; top: 0;font-size: 16px;"></i></button>','<button title="上下翻转" data-method="scaleY" data-option="-1" class="layui-btn icon-btn" type="button" style="position: relative;width: 41px;"><i class="layui-icon layui-icon-triangle-d" style="position: absolute;left: 11px;top: 6px;transform: rotateX(180deg);line-height: normal;font-size: 16px;"></i><i class="layui-icon layui-icon-triangle-d" style="position: absolute; left: 11px; top: 14px;line-height: normal;font-size: 16px;"></i></button>',"</div>",'<div class="layui-btn-group" style="margin-bottom: 10px;">','<button title="重新开始" data-method="reset" class="layui-btn icon-btn" type="button"><i class="layui-icon layui-icon-refresh"></i></button>','<button title="选择图片" id="v-crop-img-upload" class="layui-btn icon-btn" type="button" style="border-radius: 0 2px 2px 0;"><i class="layui-icon layui-icon-upload-drag"></i></button>',"</div>",'<button data-method="getCroppedCanvas" data-option="{ &quot;maxWidth&quot;: 4096, &quot;maxHeight&quot;: 4096 }" class="layui-btn icon-btn" type="button" style="margin-left: 10px;margin-bottom: 10px;"><i class="layui-icon">&#xe605;</i>完成</button>',"</div>"].join("");s.open({title:p,area:"665px",type:1,content:l,success:function(l,p){t(l).children(".layui-layer-content").css("overflow","visible"),function l(){var p,f=t("#v-crop-img"),y={elem:"#v-crop-img-upload",auto:!1,drag:!1,choose:function(t){t.preview(function(t,a,n){i=a.type,f.attr("src",n),o&&p?(p.destroy(),p=new e(f[0],m)):(o=n,l())})}};if(void 0!==u&&(y.size=u),void 0!==d&&(y.acceptMime=d),void 0!==c&&(y.exts=c),layui.upload.render(y),!o)return t("#v-crop-img-upload").trigger("click");var m={aspectRatio:n,preview:"#v-crop-img-preview"};p=new e(f[0],m),t(".v-crop-tool").on("click","[data-method]",function(){var e,n,o=t(this).data();if(p&&o.method){switch(o=t.extend({},o),e=p.cropped,o.method){case"rotate":e&&m.viewMode>0&&p.clear();break;case"getCroppedCanvas":"image/jpeg"===i&&(o.option||(o.option={}),o.option.fillColor="#fff")}switch(n=p[o.method](o.option,o.secondOption),o.method){case"rotate":e&&m.viewMode>0&&p.crop();break;case"scaleX":case"scaleY":t(this).data("option",-o.option);break;case"getCroppedCanvas":n?(r&&r(n.toDataURL(i)),s.closeDialog("#v-crop-img")):a.msg("裁剪失败",{icon:2,anim:6})}}})}()}})})},s.util={Convert_BD09_To_GCJ02:function(e){var t=52.35987755982988,a=e.lng-.0065,i=e.lat-.006,n=Math.sqrt(a*a+i*i)-2e-5*Math.sin(i*t),o=Math.atan2(i,a)-3e-6*Math.cos(a*t);return{lng:n*Math.cos(o),lat:n*Math.sin(o)}},Convert_GCJ02_To_BD09:function(e){var t=52.35987755982988,a=e.lng,i=e.lat,n=Math.sqrt(a*a+i*i)+2e-5*Math.sin(i*t),o=Math.atan2(i,a)+3e-6*Math.cos(a*t);return{lng:n*Math.cos(o)+.0065,lat:n*Math.sin(o)+.006}},animateNum:function(e,a,i,n){a=null==a||!0===a||"true"===a,i=isNaN(i)?500:i,n=isNaN(n)?100:n;var o=function(e,t){return t&&/^[0-9]+.?[0-9]*$/.test(e)?(e=e.toString()).replace(e.indexOf(".")>0?/(\d)(?=(\d{3})+(?:\.))/g:/(\d)(?=(\d{3})+(?:$))/g,"$1,"):e};t(e).each(function(){var e=t(this),l=e.data("num");l||(l=e.text().replace(/,/g,""),e.data("num",l));var r="INPUT,TEXTAREA".indexOf(e.get(0).tagName)>=0,s=function(e){for(var t="",a=0;a<e.length;a++){if(!isNaN(e.charAt(a)))return t;t+=e.charAt(a)}}(l.toString()),u=function(e){for(var t="",a=e.length-1;a>=0;a--){if(!isNaN(e.charAt(a)))return t;t=e.charAt(a)+t}}(l.toString()),d=l.toString().replace(s,"").replace(u,"");if(isNaN(1*d)||"0"===d)return r?e.val(l):e.html(l),console.error("not a number");var c=d.split("."),p=c[1]?c[1].length:0,f=0,y=d;Math.abs(1*y)>10&&(f=parseFloat(c[0].substring(0,c[0].length-1)+(c[1]?".0"+c[1]:"")));var m=(y-f)/n,h=0,v=setInterval(function(){var t=s+o(f.toFixed(p),a)+u;r?e.val(t):e.html(t),f+=m,h++,(Math.abs(f)>=Math.abs(1*y)||h>5e3)&&(t=s+o(y,a)+u,r?e.val(t):e.html(t),clearInterval(v))},i/n)})},deepClone:function(e){var t,a=s.util.isClass(e);if("Object"===a)t={};else{if("Array"!==a)return e;t=[]}for(var i in e)if(e.hasOwnProperty(i)){var n=e[i],o=s.util.isClass(n);t[i]="Object"===o?arguments.callee(n):"Array"===o?arguments.callee(n):e[i]}return t},isClass:function(e){return null===e?"Null":void 0===e?"Undefined":Object.prototype.toString.call(e).slice(8,-1)},fullTextIsEmpty:function(e){if(!e)return!0;for(var t=["img","audio","video","iframe","object"],a=0;a<t.length;a++)if(e.indexOf("<"+t[a])>-1)return!1;var i=e.replace(/\s*/g,"");return!i||(!(i=i.replace(/&nbsp;/gi,""))||!(i=i.replace(/<[^>]+>/g,"")))},removeStyle:function(e,a){"string"==typeof a&&(a=[a]);for(var i=0;i<a.length;i++)t(e).css(a[i],"")},scrollTop:function(e){if(e)e=t(e);else{var a=t(".layui-layout-admin>.layui-body");0===(e=a.children(".layui-tab").children(".layui-tab-content").children(".layui-tab-item.layui-show")).length&&0===(e=a.children(".layui-body-header.show+div")).length&&(e=a)}e.animate({scrollTop:0},300)},render:function(e){if("string"==typeof e.url)return e.success=function(a){s.util.render(t.extend({},e,{url:a}))},void("ajax"===e.ajax?s.ajax(e):s.req(e.url,e.where,e.success,e.method,e));var a=layui.laytpl(e.tpl).render(e.url);t(e.elem).next("[v-tpl-rs]").remove(),t(e.elem).after(a),t(e.elem).next().attr("v-tpl-rs",""),e.done&&e.done(e.url)},toBlob:function(e){for(var t=e.split(","),a=t[0].match(/:(.*?);/)[1],i=atob(t[1]),n=i.length,o=new Uint8Array(n);n--;)o[n]=i.charCodeAt(n);return new Blob([o],{type:a})},ip:function(e){window.showAdd=function(e){a.msg(e.addr,{shade:[.4,"#000"],time:2e3})},t.ajax({type:"GET",dataType:"jsonp",jsonpCallback:"showAdd",url:layui.cache.maps+"index/ip?ip="+e})},toTree:function(e,t){let a=[],i={};return Array.isArray(e)?(t=t||"parentid",e.forEach(e=>{delete e.children,e.spread=!0,i[e.id]=e}),e.forEach(e=>{let n=i[e[t]];n?(n.children||(n.children=[])).push(e):a.push(e)}),a):a},buildOption:function(e,a,i,n){let o=i?'<option value="">'+i+"</option>":"";return Array.isArray(a)&&"object"==typeof a[0]?(n=t.extend({val:"id",name:"name"},n||{}),a.map(e=>{o+='<option value="'+e[n.val]+'">'+e[n.name]+"</option>"})):t.each(a,function(e,t){o+='<option value="'+e+'">'+t+"</option>"}),t(e).append(o),layui.form.render(t(e)),o}},s.lockScreen=function(e){if(e){var i=t("#v-lock-screen-group");if(i.length>0)i.fadeIn("fast"),s.isLockScreen=!0,s.putTempData("isLockScreen",s.isLockScreen,!0);else{var n=a.load(2);s.ajax({url:e,dataType:"html",success:function(i){a.close(n),"string"==typeof i?(t("body").append('<div id="v-lock-screen-group">'+i+"</div>"),s.isLockScreen=!0,s.putTempData("isLockScreen",s.isLockScreen,!0),s.putTempData("lockScreenUrl",e,!0)):(console.error(i),a.msg(JSON.stringify(i),{icon:2,anim:6}))}})}}},s.unlockScreen=function(e){var a=t("#v-lock-screen-group");e?a.remove():a.fadeOut("fast"),s.isLockScreen=!1,s.putTempData("isLockScreen",null,!0)},s.tips=function(e){return a.tips(e.text,e.elem,{tips:[e.direction||1,e.bg||"#191a23"],tipsMore:e.tipsMore,time:e.time||-1,success:function(a){var i=t(a).children(".layui-layer-content");if((e.padding||0===e.padding)&&i.css("padding",e.padding),e.color&&i.css("color",e.color),e.bgImg&&i.css("background-image",e.bgImg).children(".layui-layer-TipsG").css("z-index","-1"),e.fontSize&&i.css("font-size",e.fontSize),e.offset){var n=e.offset.split(","),o=n[0],l=n.length>1?n[1]:void 0;o&&t(a).css("margin-top",o),l&&t(a).css("margin-left",l)}}})},s.renderTpl=function(e){function a(e){if(e)try{return new Function("return "+e+";")()}catch(t){console.error(t+"\nlay-data: "+e)}}layui.admin||(layui.admin=s),t(e||"[v-tpl]").each(function(){var e=t(this),i=s.util.deepClone(t(this).data());if(i.elem=e,i.tpl=e.html(),i.url=a(e.attr("v-tpl")),i.headers=a(i.headers),i.where=a(i.where),i.done)try{i.done=new Function("res",i.done)}catch(e){console.error(e+"\nlay-data:"+i.done),i.done=void 0}s.util.render(i)})},s.putSetting=function(e,t){i[e]=t,s.putTempData(e,t,!0)},s.recoverState=function(){if(s.getTempData("isLockScreen",!0)&&s.lockScreen(s.getTempData("lockScreenUrl",!0)),i.defaultTheme&&s.changeTheme(i.defaultTheme,window,!0,!0),i.closeFooter&&t("body").addClass("close-footer"),void 0!==i.navArrow){var e=t(l+">.layui-nav-tree");i.navArrow&&e.addClass(i.navArrow)}i.pageTabs&&"true"==i.tabAutoRefresh&&t(o).attr("lay-autoRefresh","true")},s.on=function(e,t){return layui.onevent.call(this,"admin",e,t)};var u=".layui-layout-admin.admin-nav-mini>.layui-side .layui-nav .layui-nav-item";t(document).on("mouseenter",u+","+u+" .layui-nav-child>dd",function(){if(s.getPageWidth()>768){var e=t(this),a=e.find(">.layui-nav-child");if(a.length>0){e.addClass("admin-nav-hover"),a.css("left",e.offset().left+e.outerWidth());var i=e.offset().top;i+a.outerHeight()>s.getPageHeight()&&((i=i-a.outerHeight()+e.outerHeight())<60&&(i=60),a.addClass("show-top")),a.css("top",i),a.addClass("v-anim-drop-in")}else e.hasClass("layui-nav-item")&&s.tips({elem:e,text:e.find("cite").text(),direction:2,offset:"12px"})}}).on("mouseleave",u+","+u+" .layui-nav-child>dd",function(){a.closeAll("tips");var e=t(this);e.removeClass("admin-nav-hover");var i=e.find(">.layui-nav-child");i.removeClass("show-top v-anim-drop-in"),i.css({left:"auto",top:"auto"})}),t(document).on("click","*[v-event]",function(){var e=s.events[t(this).attr("v-event")];e&&e.call(this)}),t(document).on("mouseenter","*[lay-tips]",function(){var e=t(this);s.tips({elem:e,text:e.attr("lay-tips"),direction:e.attr("lay-direction"),bg:e.attr("lay-bg"),offset:e.attr("lay-offset"),padding:e.attr("lay-padding"),color:e.attr("lay-color"),bgImg:e.attr("lay-bgImg"),fontSize:e.attr("lay-fontSize")})}).on("mouseleave","*[lay-tips]",function(){a.closeAll("tips")}),t(document).on("click",".form-search-expand,[search-expand]",function(){var e=t(this),a=e.parents(".layui-form").first(),i=e.data("expand"),n=e.attr("search-expand");if(void 0===i||!0===i){i=!0,e.data("expand",!1),e.html('收起 <i class="layui-icon layui-icon-up"></i>');var o=a.find(".form-search-show-expand");o.attr("expand-show",""),o.removeClass("form-search-show-expand")}else i=!1,e.data("expand",!0),e.html('展开 <i class="layui-icon layui-icon-down"></i>'),a.find("[expand-show]").addClass("form-search-show-expand");n&&new Function("d",n)({expand:i,elem:e})}),t(document).on("click.v-sel-fixed",".v-select-fixed .layui-form-select .layui-select-title",function(){var e=t(this),a=e.parent().children("dl"),i=e.offset().top,n=e.outerWidth(),o=e.outerHeight(),l=t(document).scrollTop(),r=a.outerWidth(),u=a.outerHeight(),d=i+o+5-l,c=e.offset().left;d+u>s.getPageHeight()&&(d=d-u-o-10),c+r>s.getPageWidth()&&(c=c-r+n),a.css({left:c,top:d,"min-width":n})}),s.hideFixedEl=function(){t(".v-select-fixed .layui-form-select").removeClass("layui-form-selected layui-form-selectup"),t("body>.layui-laydate").remove()},a.oldTips=a.tips,a.tips=function(e,i,n){var o;if(t(i).length>0&&t(i).parents(".layui-form").length>0&&(t(i).is("input")||t(i).is("textarea")?o=t(i):(t(i).hasClass("layui-form-select")||t(i).hasClass("layui-form-radio")||t(i).hasClass("layui-form-checkbox")||t(i).hasClass("layui-form-switch"))&&(o=t(i).prev())),!o)return a.oldTips(e,i,n);n.tips=n.tips||[o.attr("lay-direction")||3,o.attr("lay-bg")||"#333"],setTimeout(function(){n.success=function(e){t(e).children(".layui-layer-content").css("padding","6px 12px")},a.oldTips(e,i,n)},100)};var d=s.getTempData(!0);if(d)for(var c=["pageTabs","cacheTab","defaultTheme","navArrow","closeFooter","tabAutoRefresh"],p=0;p<c.length;p++)void 0!==d[c[p]]&&(i[c[p]]=d[c[p]]);s.recoverState(),s.renderTpl(),e("admin",s)});
/*打印组件*/
layui.define(function (exports) {
    var $ = layui.jquery;
    var hideClass = 'hide-print';  // 打印时隐藏
    var printingClass = 'printing';  // 正在打印
    var ieWebBrowser = '<object id="WebBrowser" classid="clsid:8856F961-340A-11D0-A96B-00C04FD705A2" width="0" height="0"></object>';
    var printer = {
        // 判断是否是ie
        isIE: function () {
            return (!!window.ActiveXObject || 'ActiveXObject' in window);
        },
        // 判断是否是Edge
        isEdge: function () {
            return navigator.userAgent.indexOf('Edge') !== -1;
        },
        // 判断是否是Firefox
        isFirefox: function () {
            return navigator.userAgent.indexOf('Firefox') !== -1;
        }
    };

    /** 打印当前页面 */
    printer.print = function (param) {
        window.focus();  // 让当前窗口获取焦点
        param || (param = {});
        var hide = param.hide;  // 需要隐藏的元素
        var horizontal = param.horizontal;  // 纸张是否是横向
        var iePreview = param.iePreview;  // 兼容ie打印预览
        var blank = param.blank;  // 是否打开新窗口
        var close = param.close;  // 如果是打开新窗口，打印完是否关闭
        // 设置参数默认值
        (iePreview === undefined) && (iePreview = true);
        (blank === undefined && window !== top && iePreview && printer.isIE()) && (blank = true);
        (close === undefined && blank && !printer.isIE()) && (close = true);
        // 打印方向控制
        $('#page-print-set').remove();
        if (horizontal !== undefined) {
            var printSet = '<style type="text/css" media="print" id="page-print-set">';
            printSet += (' @page {size:' + (horizontal ? 'landscape' : 'portrait') + ';}');
            printSet += '</style>';
            $('body').append(printSet);
        }
        // 隐藏打印时需要隐藏内容
        printer.hideElem(hide);
        // 打印
        var pWindow;
        if (blank) {
            // 创建打印窗口
            pWindow = window.open('', '_blank');
            pWindow.focus();  // 让打印窗口获取焦点
            // 写入内容到打印窗口
            var pDocument = pWindow.document;
            pDocument.open();
            var blankHtml = '<!DOCTYPE html>' + document.getElementsByTagName('html')[0].innerHTML;
            if (iePreview && printer.isIE()) {
                blankHtml += ieWebBrowser;
                blankHtml += ('<script>window.onload = function(){ WebBrowser.ExecWB(7, 1); ' + (close ? 'window.close();' : '') + ' }</script>');
            } else {
                blankHtml += ('<script>window.onload = function(){ window.print(); ' + (close ? 'window.close();' : '') + ' }</script>');
            }
            pDocument.write(blankHtml);
            pDocument.close();
        } else {
            pWindow = window;
            if (iePreview && printer.isIE()) {
                ($('#WebBrowser').length === 0) && ($('body').append(ieWebBrowser));
                WebBrowser.ExecWB(7, 1);
            } else {
                pWindow.print();
            }
        }
        printer.showElem(hide);
        return pWindow;
    };

    /** 打印html字符串 */
    printer.printHtml = function (param) {
        param || (param = {});
        var html = param.html;  // 打印的html内容
        var blank = param.blank;  // 是否打开新窗口
        var close = param.close;  // 打印完是否关闭打印窗口
        var print = param.print;  // 是否自动调用打印
        var horizontal = param.horizontal;  // 纸张是否是横向
        var iePreview = param.iePreview;  // 兼容ie打印预览
        // 设置参数默认值
        (print === undefined) && (print = true);
        (iePreview === undefined) && (iePreview = true);
        (blank === undefined && printer.isIE()) && (blank = true);
        (close === undefined && blank && !printer.isIE()) && (close = true);
        // 创建打印窗口
        var pWindow, pDocument;
        if (blank) {
            pWindow = window.open('', '_blank');
            pDocument = pWindow.document;
        } else {
            var printFrame = document.getElementById('printFrame');
            if (!printFrame) {
                $('body').append('<iframe id="printFrame" style="display: none;"></iframe>');
                printFrame = document.getElementById('printFrame');
            }
            pWindow = printFrame.contentWindow;
            pDocument = printFrame.contentDocument || printFrame.contentWindow.document;
        }
        pWindow.focus();  // 让打印窗口获取焦点
        // 写入内容到打印窗口
        if (html) {
            // 加入公共css
            html += ('<style>' + printer.getCommonCss(true) + '</style>');
            // 打印方向控制
            if (horizontal !== undefined) {
                html += '<style type="text/css" media="print">';
                html += ('  @page {size:' + (horizontal ? 'landscape' : 'portrait') + ';}');
                html += '</style>';
            }
            // 打印预览兼容ie
            if (iePreview && printer.isIE()) {
                html += ieWebBrowser;
                if (print) {
                    html += ('<script>window.onload = function(){ WebBrowser.ExecWB(7, 1); ' + (close ? 'window.close();' : '') + ' }</script>');
                }
            } else if (print) {
                html += ('<script>window.onload = function(){ window.print(); ' + (close ? 'window.close();' : '') + ' }</script>');
            }
            // 写入html
            pDocument.open();
            pDocument.write(html);
            pDocument.close();
        }
        return pWindow;
    };

    /** 分页打印 */
    printer.printPage = function (param) {
        param || (param = {});
        var htmls = param.htmls;  // 打印的内容
        var horizontal = param.horizontal;  // 纸张是否是横向
        var style = param.style;  // 打印的样式
        var padding = param.padding;  // 页边距
        var blank = param.blank;  // 是否打开新窗口
        var close = param.close;  // 打印完是否关闭打印窗口
        var print = param.print;  // 是否自动调用打印
        var width = param.width;  // 页面宽度
        var height = param.height;  // 页面高度
        var iePreview = param.iePreview;  // 兼容ie打印预览
        var isDebug = param.debug;  // 调试模式
        // 设置参数默认值
        (print === undefined) && (print = true);
        (iePreview === undefined) && (iePreview = true);
        (blank === undefined && printer.isIE()) && (blank = true);
        (close === undefined && blank && !printer.isIE()) && (close = true);
        // 创建打印窗口
        var pWindow, pDocument;
        if (blank) {
            pWindow = window.open('', '_blank');
            pDocument = pWindow.document;
        } else {
            var printFrame = document.getElementById('printFrame');
            if (!printFrame) {
                $('body').append('<iframe id="printFrame" style="display:none;"></iframe>');
                printFrame = document.getElementById('printFrame');
            }
            pWindow = printFrame.contentWindow;
            pDocument = printFrame.contentDocument || printFrame.contentWindow.document;
        }
        pWindow.focus();  // 让打印窗口获取焦点
        // 拼接打印内容
        var htmlStr = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>打印窗口</title>';
        style && (htmlStr += style);  // 写入自定义css
        // 控制分页的css
        htmlStr += printer.getPageCss(padding, width, height);
        // 控制打印方向
        if (horizontal !== undefined) {
            htmlStr += '<style type="text/css" media="print">';
            htmlStr += ('  @page {size:' + (horizontal ? 'landscape' : 'portrait') + ';}');
            htmlStr += '</style>';
        }
        htmlStr += '</head><body>';
        // 拼接分页内容
        if (htmls) {
            var pageClass = isDebug ? ' page-debug' : '';  // 调试模式
            htmlStr += '<div class="print-page' + pageClass + '">';
            for (var i = 0; i < htmls.length; i++) {
                htmlStr += '<div class="print-page-item">';
                htmlStr += htmls[i];
                htmlStr += '</div>';
            }
            htmlStr += '</div>';
        }
        // 兼容ie打印预览
        if (iePreview && printer.isIE()) {
            htmlStr += ieWebBrowser;
            if (print) {
                htmlStr += ('<script>window.onload = function(){ WebBrowser.ExecWB(7, 1); ' + (close ? 'window.close();' : '') + ' }</script>');
            }
        } else if (print) {
            htmlStr += ('<script>window.onload = function(){ window.print(); ' + (close ? 'window.close();' : '') + ' }</script>');
        }
        htmlStr += '</body></html>';
        // 写入打印内容
        pDocument.open();
        pDocument.write(htmlStr);
        pDocument.close();
        return pWindow;
    };

    /** 分页打印的css */
    printer.getPageCss = function (padding, width, height) {
        var pageCss = '<style>';
        pageCss += 'body{margin:0 !important;}';
        // 自定义边距竖屏样式
        pageCss += '.print-page .print-page-item{page-break-after:always !important;box-sizing:border-box !important;border:none !important;';
        padding && (pageCss += ('padding:' + padding + ';'));
        width && (pageCss  += (' width:' + width + ';'));
        height && (pageCss += (' height:' + height + ';'));
        pageCss += '} ';
        // 调试模式样式
        pageCss += '.print-page.page-debug .print-page-item{border:1px solid red !important;}';
        pageCss += printer.getCommonCss(true);  // 加入公共css
        pageCss += '</style>';
        return pageCss;
    };

    /** 隐藏元素 */
    printer.hideElem = function (elems) {
        $('.' + hideClass).addClass(printingClass);
        if (!elems) {
            return;
        }
        if (elems instanceof Array) {
            for (var i = 0; i < elems.length; i++) {
                $(elems[i]).addClass(hideClass + ' ' + printingClass);
            }
        } else {
            $(elems).addClass(printingClass);
        }
    };

    /** 取消隐藏 */
    printer.showElem = function (elems) {
        $('.' + hideClass).removeClass(printingClass);
        if (!elems) {
            return;
        }
        if (elems instanceof Array) {
            for (var i = 0; i < elems.length; i++) {
                $(elems[i]).removeClass(hideClass + ' ' + printingClass);
            }
        } else {
            $(elems).removeClass(printingClass);
        }
    };

    /** 打印公共样式 */
    printer.getCommonCss = function (isPrinting) {
        var cssStr = ('.' + hideClass + '.' + printingClass + '{visibility:hidden !important;}');
        cssStr += '.print-table{border:none;border-collapse:collapse;width:100%;}';
        cssStr += '.print-table td,.print-table th{color:#333;padding:9px 15px;word-break:break-all;border:1px solid #333;}';
        if (isPrinting) { cssStr += ('.' + hideClass + ' {visibility:hidden !important;}');}
        return cssStr;
    };

    /** 拼接html */
    printer.makeHtml = function (param) {
        var title = param.title;
        var style = param.style;
        var body = param.body;
        title == undefined && (title = '打印窗口');
        var htmlStr = '<!DOCTYPE html><html lang="en">';
        htmlStr += '<head><meta charset="UTF-8">';
        htmlStr += ('<title>' + title + '</title>');
        style && (htmlStr += style);
        htmlStr += '</head>';
        htmlStr += '<body>';
        body && (htmlStr += body);
        htmlStr += '</body>';
        htmlStr += '</html>';
        return htmlStr;
    };

    $('head').append('<style>' + printer.getCommonCss() + '</style>');
    exports("printer", printer);
});
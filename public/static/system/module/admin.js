// +----------------------------------------------------------------------
// | swiftadmin极速开发框架后台模板 [基于layui开发]
// +----------------------------------------------------------------------
// | Copyright (c) 2020-2030 http://www.swiftadmin.net
// +----------------------------------------------------------------------
// | git://github.com/meystack/swiftadmin.git 616550110
// +----------------------------------------------------------------------
// | Author: meystack <coolsec@foxmail.com> Apache 2.0 License Code
// +----------------------------------------------------------------------

layui.define(['jquery', 'i18n', 'element', 'layer', 'form', 'rate', 'table', 'slider', 'upload', 'laydate', 'dropdown', 'colorpicker', 'cascader', 'content', 'tags'], function (exports) {

    "use strict";
    var $ = layui.jquery;
    var i18n = layui.i18n;
    var layer = layui.layer;
    var form = layui.form;
    var rate = layui.rate;
    var table = layui.table;
    var slider = layui.slider;
    var element = layui.element;
    var laydate = layui.laydate;
    var cascader = layui.cascader;
    var upload = layui.upload;
    var content = layui.content;
    var colorpicker = layui.colorpicker;

    // 系统常量
    var TABFILTER = 'swiftadmin-tabs', BODY = '.layui-body', LAYOUTBODY = ".layui-layout-body",
        LAYOUTADMIN = ".layui-layout-admin"
        , TABS = BODY + ">.layui-tab", FLEXIBLEID = "flexible", MENUFILTER = "lay-side-menu",
        LAYTOPMENU = "lay-top-menu", LAYSIDESHRINK = "layadmin-side-shrink"
        , LAYSIDESPREAD = "layadmin-side-spread-sm", ICONSHRINKRIGHT = "layui-icon-shrink-right",
        ICONSPREADLEFT = "layui-icon-spread-left", STR_EMPTY = ''
        , BODYSHADE = "<div class=\"layadmin-body-shade\" sa-event=\"shade\"><\/div>",
        BODYSHADECLASS = ".layadmin-body-shade", LAYERCONTENT = ".layui-layer-content";

    // 对象初始化
    var admin = {
        options: {
            tplName: 'swiftadmin',      // 数据标识
            version: '1.2.0',           // 版本
            moreLabel: true,            // 是否开启多标签
            cacheTab: true,             // 缓存多标签
            maxTabNum: 20,              // 最大打开标签
            TabLists: [],               // 标签缓存
            style: 'dark',              // 样式
            theme: 'blue',              // 皮肤
            layout: 'left',             // 布局操作
            fluid: true,                // 是否内容铺满
            openHeader: true,
            openFooter: true,
            refreshClearTab: true,
        },
        getSpinningHtml: function () {
            return ['<div id="loading">',
                '<div class="loader">',
                '<div class="ant-spin ant-spin-spinning" >',
                '<span class="ant-spin-dot ant-spin-dot-spin">',
                '<i class="ant-spin-dot-item"></i>',
                '<i class="ant-spin-dot-item"></i>',
                '<i class="ant-spin-dot-item"></i>',
                '<i class="ant-spin-dot-item"></i>',
                ' </span></div></div></div>'].join('');
        },
        // 展现动画
        showLoading: function (obj) {
            var html = admin.getSpinningHtml();
            var exist = $(obj).children('#loading');
            if (exist.length <= 0) {
                $(obj).append(html);
            } else {
                exist.show();
            }

        },
        removeLoading: function (obj) {
            (typeof (obj) === undefined) && (obj = "body");
            if (obj === 'master') {
                var master = $(LAYOUTBODY).children("#loading");
                master && master.hide();

                // 兼容IE
                master && master.remove();
                $(LAYOUTADMIN).show();

            } else {
                $(obj).next("#loading").hide();
            }
        },
        setConfig: function (key, value) {
            var tplName = admin.options.tplName + '_session';
            if (value != null && value !== "undefined") {
                layui.sessionData(tplName, {
                    key: key,
                    value: value
                })
            } else {
                layui.sessionData(tplName, {
                    key: key,
                    remove: true
                })
            }
        },
        getConfig: function (key) {
            var tplName = admin.options.tplName + '_session';
            var array = layui.sessionData(tplName);
            if (array) {
                return array[key]
            } else {
                return false
            }
        }
        , setStorage: function (key, value) {
            var tplName = admin.options.tplName + '_system';
            if (value != null && value !== "undefined") {
                layui.data(tplName, {
                    key: key,
                    value: value
                })
            } else {
                layui.data(tplName, {
                    key: key,
                    remove: true
                })
            }
        },
        getStorage: function (key) {
            var tplName = admin.options.tplName + '_system';
            var array = layui.data(tplName);
            if (array) {
                return array[key]
            } else {
                return false
            }
        }
        , setBreadHtml: function () {

            var b = '<div class="layui-breadcrumb-header layui-breadcrumb" lay-separator="/">';
            b += '      <a lay-href="#">' + i18n.prop('主页') + '</a>';
            b += '      <span class="breadcrumb">';
            b += '          <a lay-href="#">Dashboard</a>';
            b += '      </span>';
            b += "   </div>";
            if ($('.layui-breadcrumb-header').length <= 0) {
                $('.layui-nav-head').before(b);
                $('.layui-nav-head').hide();
            }

            if (admin.screen() < 2) {
                $('.layui-breadcrumb-header').hide();
            }
        }
        , setBreadcrumb: function (urls, title) {

            var text = STR_EMPTY,
                current = $('.layui-nav-tree li [lay-href="' + urls + '"]');
            var bread = $(current).parents().find('.layui-nav-item');
            for (var i = 0; i < bread.length; i++) {

                var elem = $(bread[i]).find('[lay-href="' + urls + '"]');
                if (elem.length) {
                    var name = $(bread[i]).find('a:first').text();
                    text += '<a lay-href="#" >' + name + '</a><span lay-separator="">/</span>';
                }
            }

            text += '<a lay-href="' + urls + '">' + title + '</a>';
            $('.breadcrumb').html(text);

        },
        fullScreen: function () { //全屏
            var ele = document.documentElement
                ,
                reqFullScreen = ele.requestFullScreen || ele.webkitRequestFullScreen || ele.mozRequestFullScreen || ele.msRequestFullscreen;
            if (typeof reqFullScreen !== 'undefined' && reqFullScreen) {
                reqFullScreen.call(ele);
            }
        },
        exitScreen: function () { //退出全屏
            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if (document.mozCancelFullScreen) {
                document.mozCancelFullScreen();
            } else if (document.webkitCancelFullScreen) {
                document.webkitCancelFullScreen();
            } else if (document.msExitFullscreen) {
                document.msExitFullscreen();
            }
        }
        , rollPage: function (type, index) {
            var o = $(TABS + ">.layui-tab-title");
            var p = o.scrollLeft();
            if ("left" === type) {
                o.animate({
                    "scrollLeft": p - 120
                }, 100)
            } else {
                if ("auto" === type) {
                    var n = 0;
                    o.children("li").each(function () {
                        if ($(this).hasClass("layui-this")) {
                            return false
                        } else {
                            n += $(this).outerWidth()
                        }
                    });
                    o.animate({
                        "scrollLeft": n - 120
                    }, 100)
                } else {
                    o.animate({
                        "scrollLeft": p + 120
                    }, 100)
                }
            }
        }
        , refresh: function (id) {
            if (id == null) {
                return false
            }

            var iframe = $('iframe[lay-id="' + id + '"]');
            if (!admin.options.moreLabel) {
                $('#loading').show();
                return iframe[0].contentWindow.location.reload(true);
            }

            iframe = $(TABS + " .layui-tab-item").find("iframe");
            for (let i = 0; i < iframe.length; i++) {
                var layid = $(iframe[i]).attr('lay-id');
                if (layid == id) {
                    iframe[i].contentWindow.location.reload(true);
                    $(iframe[i]).next("#loading").css({'overflow': 'hidden', 'display': "block"});
                }
            }

        }
        , globalStyleCss: function () {
            var css = '<style id="style-light">';
            css += '.layadmin-setTheme-side, .layui-side-menu,.layui-layout-admin .layui-logo,.layui-nav-itemed>.layui-nav-child{';
            css += 'background-color: #fff!important;';
            css += 'color: #000;}';
            css += '.layui-side-menu .layui-nav .layui-nav-item>a,';
            css += '.layui-nav-tree .layui-nav-child,';
            css += '.layui-nav-itemed>a, .layui-nav-tree .layui-nav-title a, .layui-nav-tree .layui-nav-title a:hover,';
            css += '.layui-side .layui-logo h1,';
            css += '.layui-side-menu .layui-nav .layui-nav-item>a, .layui-nav-tree .layui-nav-child a,';
            css += '.layui-layout-admin .layui-side .layui-nav .layui-other-set {';
            css += 'color: #000!important;}';
            css += '.layui-nav-tree .layui-nav-child, .layui-nav-tree .layui-nav-child a:hover {color: #1890ff!important;}';
            css += '.layui-nav-tree .layui-nav-child .layui-this a { color: #fff!important;}';
            css += '.layui-nav .layui-nav-more { border-top-color: rgba(0,0,0,.7);}';
            css += '.layui-nav .layui-nav-mored, .layui-nav-itemed>a .layui-nav-more {border-color: transparent transparent #000;}';
            css += '.layadmin-side-shrink .layui-side .layui-nav .layadmin-nav-hover>.layui-nav-child:before{background-color: #fff; }';
            css += '.layadmin-side-shrink .layui-side-menu .layui-nav>.layui-nav-itemed>a{background: #f2f2f2;}';
            css += '.layui-nav-tree .layui-nav-child dd.layui-this, .layui-nav-tree .layui-nav-child dd.layui-this a, .layui-nav-tree .layui-this,';
            css += '.layui-nav-tree .layui-this>a {background-color: #e6f7ff!important;border-right: 1px solid #1890ff!important;color: #1890ff!important;}';


            css += '</style>';
            return css;
        }
        , setTheme: function () {
            var theme = admin.getStorage('theme') || admin.options.theme;
            if (admin.getStorage('moreLabel')) {
                var iframe = top.layui.$(TABS + " .layui-tab-item").find("iframe");
                for (let i = 0; i < iframe.length; i++) {
                    $(iframe[i]).contents().find('body').attr('id', theme);
                }
            } else {
                top.layui.$('iframe').contents().find('body').attr('id', theme);
            }

            top.layui.$('body').attr('id', theme);
        }
        , setlayFluid: function () {

            var fluid = admin.getStorage('fluid'),
                fixedStyle = '<style id="lay-fluid">.layui-fluid{max-width:1200px;}</style>';

            if (typeof fluid == 'undefined') {
                fluid = admin.options.fluid;
            }

            if (admin.options.moreLabel) {
                var iframe = top.layui.$(TABS + " .layui-tab-item").find("iframe");
                for (let i = 0; i < iframe.length; i++) {

                    if (fluid !== false) {
                        $(iframe[i]).contents().find('#lay-fluid').remove();
                    } else {
                        if ($(iframe[i]).contents().find('head').find('#lay-fluid').length <= 0) {
                            $(iframe[i]).contents().find('head').append(fixedStyle);
                        }
                    }
                }
            } else {

                if (fluid !== false) {
                    top.layui.$('iframe').contents().find('#lay-fluid').remove();
                } else {
                    if ($(iframe[i]).contents().find('head').find('#lay-fluid').length <= 0) {
                        top.layui.$('iframe').contents().find('head').append(fixedStyle);
                    }
                }
            }

        }
        , setDropStyle: function () {
            var dropstyle = admin.getStorage('dropstyle');
            if (typeof dropstyle !== "undefined") {
                top.layui.$(".layui-nav-tree").removeClass('arrow1 arrow2 arrow3');
                top.layui.$(".layui-nav-tree").addClass(dropstyle);
            }
        }
        , setPageHeaderFooter: function (type = 'header') {

            if (type === 'header') {
                var openHeader = admin.getStorage('openHeader');
                if (!openHeader) {
                    console.log('移除');
                    top.layui.$('.layui-header,.layui-logo').hide();
                    top.layui.$('.layui-nav-tree,.layui-body').addClass('lay-fix-top');
                } else {
                    top.layui.$('.layui-header,.layui-logo').show();
                    top.layui.$('.layui-nav-tree,.layui-body').removeClass('lay-fix-top');
                }

            } else {
                var openFooter = admin.getStorage('openFooter');
                if (!openFooter) {
                    top.layui.$('.layui-footer').addClass('layui-hide');
                    top.layui.$('.layui-layout-admin>.layui-body').addClass('lay-fix-bottom');
                } else { // 显示
                    top.layui.$('.layui-footer').removeClass('layui-hide');
                    top.layui.$('.layui-layout-admin>.layui-body').removeClass('lay-fix-bottom');
                }
            }
        }
        , changeI18n: function (type) {
            i18n.render(type);
        }
        , screen: function () {
            var width = $(window).width()
            if (width > 1200) {
                return 3; //大屏幕
            } else if (width > 992) {
                return 2; //中屏幕
            } else if (width > 768) {
                return 1; //小屏幕
            } else {
                return 0; //超小屏幕
            }
        },
        flexible: function (status) {
            var app = $(LAYOUTBODY),
                iconElem = $('#' + FLEXIBLEID);
            var screen = admin.screen();
            if (status) {
                iconElem.removeClass(ICONSPREADLEFT).addClass(ICONSHRINKRIGHT);
                if (screen < 2) {
                    app.addClass(LAYSIDESPREAD);
                } else {
                    app.removeClass(LAYSIDESPREAD);
                }
                app.removeClass(LAYSIDESHRINK);
                if ($(BODYSHADECLASS).length <= 0) {
                    $(LAYOUTADMIN).append(BODYSHADE);
                }
            } else {
                iconElem.removeClass(ICONSHRINKRIGHT).addClass(ICONSPREADLEFT);
                if (screen < 2) {
                    app.removeClass(LAYSIDESHRINK);
                } else {
                    app.addClass(LAYSIDESHRINK);
                }
                // layadmin-side-shrink layadmin-side-spread-sm
                app.removeClass(LAYSIDESPREAD)
                $(LAYOUTADMIN).removeClass(BODYSHADE);
            }
        }
    };

    // 定义全局事件
    admin.event = {
        flexible: function () {
            var iconElem = $('#' + FLEXIBLEID);
            var spread = iconElem.hasClass(ICONSPREADLEFT);
            admin.flexible(spread ? 'open' : null);
        },
        shade: function () {
            admin.flexible();
        },
        refresh: function () {
            admin.refresh(admin.getConfig("activeTab"));
        },
        back: function () {

        },
        theme: function () {
            var n = $(this).data("url");
            admin.event.popupRight({
                id: "layer-theme",
                type: 2,
                content: n
            })
        },
        message: function () {
            var that = $(this),
                n = that.data("url");
            admin.event.flowOpen({
                id: "layer-msg",
                type: 2,
                // title: "🔔  消息提醒",
                area: ["336px", "390px"],
                content: n
            }, that)
        },
        pwd: function () {
            var n = $(this).data("url");
            admin.event.pupupOpen({
                id: "layer-pwd",
                type: 2,
                shade: 0,
                title: "🔑  修改密码",
                area: ["385px", "295px"],
                content: n
            })
        },
        clear: function () {
        }
        , logout: function (res) {
            var href = $(this).data("url");
            layui.layer.confirm("确定要退出登录吗？", {
                title: '提示',
            }, function () {
                location.replace(href)
            })
        }
        , flowOpen: function (n, that) {

            if (typeof that === "undefined") {
                return layer.msg(i18n.prop('未定义'), 'info');
            }

            var elemWidth = 0, client = that[0].getBoundingClientRect();

            if (n.title == undefined) {
                n.title = false;
                n.closeBtn = false
            }

            n.shadeClose = true;
            n.area || (n.area = "336px");

            if (n.area instanceof Array) {
                elemWidth = n.area[0]
            } else {
                elemWidth = n.area;
            }
            elemWidth = parseInt(elemWidth);
            n.anim || (n.anim = 5);
            n.resize = n.resize != undefined ? n.resize : false;
            n.shade = n.shade != undefined ? n.shade : 0.1;
            var top = client.height,
                padding = (that.innerWidth() - that.width()) / 2, // 不用计算 padding
                left = client.left + (client.width / 2) - (elemWidth / 2);
            if (!n.offset) {
                n.offset = [top, left];
            }

            return layer.open(n);
        }
        , pupupOpen: function (n) {

            if (n.title == undefined) {
                n.title = false;
                n.closeBtn = false
            }
            if (!n.offset) {
                if ($(window).width() < 768) {
                    n.offset = "15px"
                } else {
                    if (window == top) {
                        n.offset = "25%"
                    } else {
                        n.offset = "20%"
                    }
                }
            }
            n.resize = n.resize != undefined ? n.resize : false;
            n.shade = n.shade != undefined ? n.shade : 0.1;
            return layer.open(n)
        }
        , pupupTop: function (n) {
            n = admin.event.popupAnim(n);
            n.offset = n.offset || (n.offset = "t");
            return admin.event.pupupOpen(n)
        }
        , pupupDown: function (n) {
            n = admin.event.popupAnim(n);
            n.offset = n.offset || (n.offset = "d");
            return admin.event.pupupOpen(n)
        }
        , pupupLeft: function (n) {
            n = admin.event.popupAnim(n);
            n.offset = n.offset || (n.offset = "l");
            return admin.event.pupupOpen(n)
        }
        , popupRight: function (n) {
            n = admin.event.popupAnim(n);
            n.offset = n.offset || (n.offset = "r");
            return admin.event.pupupOpen(n)
        }
        , popupAnim: function (n) {
            n.anim = -1;
            n.shadeClose = true;
            n.area || (n.area = "336px");
            n.skin || (n.skin = "layui-anim layui-anim-rl layui-layer-adminRight");
            n.move = false;
            if (n.fixed == undefined) {
                n.fixed = true
            }

            return n;
        }
        , fullscreen: function (res) {
            var SCREEN_FULL = 'layui-icon-screen-full'
                , SCREEN_REST = 'layui-icon-screen-restore'
                , iconElem = res.children("i");
            if (iconElem.hasClass(SCREEN_FULL)) {
                iconElem.addClass(SCREEN_REST).removeClass(SCREEN_FULL);
                admin.fullScreen();
            } else {
                admin.exitScreen();
                iconElem.addClass(SCREEN_FULL).removeClass(SCREEN_REST);
            }
        }
        , leftPage: function () {
            admin.rollPage('left');
        }
        , rightPage: function () {
            admin.rollPage();
        }
        , tabs: function () {
            var url = $(this).data("url");
            var title = $(this).data("title");
            title || (title = $(this).text());
            if (top.layui && top.layui.admin) {
                top.layui.admin.createElementTabs({
                    id: url,
                    title: title ? title : "",
                    url: url
                })
            } else {
                location.href = url
            }
        }
        , closeThisTabs: function (id) {
            if (id != null && typeof (id) == 'object') {
                id = admin.getConfig('activeTab');
            }
            let TabLists = admin.getConfig("TabLists");
            for (const tabListsKey in TabLists) {
                let el = TabLists[tabListsKey];
                console.log(el)
                if (el.home && id === el.id) {
                    layer.info(i18n.prop('请不要关闭主页'));
                    return false;
                }
            }
            element.tabDelete(TABFILTER, id);
        },
        closeOtherTabs: function (id) {
            if (id != null && typeof (id) == 'object') {
                id = admin.getConfig('activeTab');
            }

            var TabLists = admin.getConfig("TabLists");
            var length = TabLists.length - 1;
            for (; length >= 1; length--) {
                if (TabLists[length].id !== id) {
                    element.tabDelete(TABFILTER, TabLists[length].id);
                }
            }

            admin.setConfig("activeTab", id);
        },
        closeAllTabs: function () {

            let TabLists = admin.getConfig("TabLists");
            for (const tabListsKey in TabLists) {
                let el = TabLists[tabListsKey];
                if (!el.home) {
                    element.tabDelete(TABFILTER,el.id);
                }
            }
        },
        closeDialog: function (that) {
            let _type = $(that).parents(".layui-layer").attr("type");

            if (typeof _type === "undefined") {
                parent.layer.close(parent.layer.getFrameIndex(window.name));
            } else {
                var o = $(that).parents(".layui-layer").attr("id").substring(11);
                layer.close(o);
            }
        }
        , closePageDialog: function () {
            admin.event.closeDialog(this);
        }
        , ajax: function (url, data, async = false) {
            var result = [];
            $.ajax({
                url: url,
                type: "post",
                data: data,
                async: async,
                success: function (res) {
                    result = res;
                }
            });

            try {
                return typeof (result) !== "object" ? JSON.parse(result) : result;
            } catch (error) {
                console.error('result not JSON');
            }
        }

        , open: function (clickObject, tableThis, mergeOptions) {

            var options = {
                url: admin._validauthURL(clickObject),
                type: clickObject.data('type') || 2,
                area: clickObject.data('area') || "auto",
                title: clickObject.data('title') || false,
                maxmin: clickObject.data('maxmin') || false,
                auto: clickObject.data('auto') || undefined,
                shadeClose: clickObject.data('shadeclose') || false,
                scrollbar: clickObject.data('scrollbar') || undefined,
                disableform: clickObject.data('disable') || false,
                callback: clickObject.attr('callback') || undefined,
                iframeAuto: false
            };

            options.id = options.url.replace(/[^0-9|a-z|A-Z]/i, '');
            options.url = options.url.replace(/\s+/g, '');
            let firstURL = options.url.substring(0, 1);
            if (firstURL && firstURL === '#') {
                options.type = 1;
                options.url = $(options.url).html();
                if (typeof tableThis !== 'undefined') {
                    let htmls = $(options.url);
                    $(htmls).find('*[data-disabled]').addClass('layui-disabled').attr('disabled', '');
                    options.url = htmls.prop("outerHTML");
                }
            }

            if (options.area !== "auto") {
                options.area = options.area.split(',');
                if (options.area.length === 2 && options.area[1] === '100%') {
                    options.offset = 0;
                    if (typeof options.url == 'object') {
                        options.url = options.url[0];
                    }
                } else if (options.area.length === 1) {
                    options.iframeAuto = true;
                }
            }

            (typeof (mergeOptions) !== "undefined")
            && (options = $.extend(options, mergeOptions));

            // 小窗口重载按比例大小
            if ($(window).width() < 768) {
                options.area = ['90%', '90%'];
            }

            layer.open({
                type: options.type,
                area: options.area,
                title: options.title,
                maxmin: options.maxmin,
                shadeClose: options.shadeClose,
                scrollbar: false,
                content: options.url,
                success: function (layero, index) {

                    options.iframeAuto && layer.iframeAuto(index);
                    if (options.type <= 1) {
                        // 禁止滚动条
                        $(layero).children('.layui-layer-content').css('overflow', 'visible');
                        if (typeof tableThis !== 'undefined' && !options.disableform) {
                            form.val(options.id, tableThis.data);
                        }

                        const components = admin.components;
                        for (const key in components) {
                            if (options.url.match('lay-' + key)) {
                                eval(components[key])();
                            }
                        }

                        form.render();

                        // 是否存在回调函数
                        if (typeof options.callback != 'undefined') {
                            return admin.callbackfunc(clickObject, {
                                tableThis: tableThis,
                                layero: layero,
                                index: index
                            });
                        }

                        form.on("submit(submitPage)", function (post) {

                            var othat = $(this),
                                reload = $(othat).data('reload') || false,
                                postUrl = $(othat).parents('form').attr('action');

                            var action = !tableThis ? 'add' : 'edit';
                            if (!postUrl) {
                                postUrl = othat.attr('lay-' + action);
                            }

                            if (typeof postUrl == 'undefined') {
                                postUrl = _global_.app + '/' + _global_.controller + '/' + action;
                            }

                            $.ajax({
                                url: postUrl,
                                type: 'post',
                                dataType: 'json',
                                data: post.field,
                                success: function (res) {
                                    for (var elem in post.field) {
                                        var lay = $(clickObject).parents("tr").find('*[data-field=' + elem + ']').find('*[lay-skin]');
                                        if (lay.length !== 0) {
                                            delete post.field[elem];
                                        }
                                    }

                                    if (res.code === 200) {

                                        switch (reload) {
                                            case 'top':
                                                top.location.reload();
                                                break;
                                            case 'parent':
                                                parent.location.reload();
                                                break;
                                            case 'self':
                                                location.reload();
                                                break;
                                            default:
                                        }

                                        tableThis ?
                                            tableThis.update(JSON.parse(JSON.stringify(post.field))) :
                                            table.reloadData("lay-tableList");
                                        layer.msg(res.msg);
                                        admin.event.closeDialog(othat);
                                    } else {
                                        layer.error(res.msg)
                                    }
                                },
                                error: function (res) {
                                    layer.msg(i18n.prop('访问方法失败'), 'error');
                                }
                            })

                            return false;
                        })

                    }

                }
            })
        }
        , request: function (clickObject, reqData, callback, merge) {

            let options = {
                url: admin._validauthURL(clickObject),
                type: clickObject.data('type') || 'post',
                dataType: clickObject.data('dataType') || 'json',
                timeout: clickObject.data('timeout') || '6000',
                confirm: clickObject.data('confirm'),
                reload: clickObject.data('reload'),
                close: clickObject.data('close') || false,
                tableId: clickObject.data('table') || clickObject.attr('batch'),
                jump: clickObject.data('jump') || false, // 是否跳转
                async: clickObject.data('async') || true // 默认异步调用
            }

            let reqSend = function (options) {
                if (typeof merge !== 'undefined') {
                    options = $.extend({}, options, merge);
                }

                $.ajax({
                    url: options.url,
                    type: options.type,
                    dataType: options.dataType,
                    timeout: options.timeout,
                    data: reqData,
                    async: options.async,
                    success: function (res) {

                        if (res.code === 200) {
                            if (typeof callback !== 'undefined'
                                && typeof callback.success === 'function') {
                                return callback.success(res);
                            }

                            // 是否重载当前页
                            switch (options.reload) {
                                case 'top':
                                    top.location.reload();
                                    break;
                                case 'parent':
                                    parent.location.reload();
                                    break;
                                case 'self':
                                    location.reload();
                                    break;
                                default:
                            }

                            if (typeof (options.tableId) !== "undefined") {
                                if (typeof table.cache[options.tableId] !== "undefined") {
                                    table.reloadData(options.tableId);
                                } else {
                                    try {
                                        parent.layui.table.reloadData(options.tableId);
                                    } catch (e) {
                                        console.warn(e);
                                    }
                                }
                            }

                            /**
                             * 存在并且设置了跳转
                             * 适合下载文件操作
                             */
                            if (res.url && options.jump !== false) {
                                location.href = res.url;
                            }

                            if (typeof (options.close) !== false) {
                                admin.event.closeDialog(clickObject);
                            }

                            top.layer.msg(res.msg);
                        } else {
                            if (typeof callback !== 'undefined'
                                && typeof callback.error === 'function') {
                                return callback.error(res);
                            }

                            top.layer.error(res.msg);
                        }
                    },
                    error: function (res) {
                        layer.msg(i18n.prop('访问方法失败'), 'error');
                    }
                })

                return options;
            }

            if (options.confirm !== undefined) {

                options.confirm = options.confirm || '确定执行此操作吗?';
                layer.confirm(options.confirm, function (index) {

                    layer.close(index);
                    return reqSend(options);

                }, function (index) {
                    layer.close(index);
                    return false;
                })

            } else {
                return reqSend(options);
            }
        }
    }

    /**
     * 全局回调函数
     * @param {*} clickThis
     * @param {*} collection
     * @param {*} before
     * @returns
     */
    admin.callback = {};
    admin.callbackfunc = function (clickThis, collection, before) {
        let funcObj = before ? clickThis.attr('callbefore') : clickThis.attr('callback');
        if (typeof funcObj != 'undefined') {
            funcObj = 'admin.callback.' + funcObj;
            if (typeof eval(funcObj) === "function") {
                return eval(funcObj)(clickThis, collection);
            } else {
                layer.msg(i18n.prop('回调函数错误'), 'error');
                return false;
            }
        }
    }

    /**
     * 鉴权函数
     * @param {*} othis
     */
    admin._validauthURL = function (othis) {

        var curl = $(othis).attr('data-url');
        if (typeof curl == 'undefined') {
            layer.msg(i18n.prop('URL未定义'), 'error');
            throw 'lay-ajax url undefined';
        }

        if (curl.indexOf(_global_.app) !== -1) {
            var route = curl.replace('.html', '');
            route = route.substring(1);
            var main = route.substring(0, route.indexOf('/'));
            if (route.indexOf('.php') !== -1) {
                main = route.replace(main, '').substring(1);
                main = main.substring(0, main.indexOf('/'));
            }

            // 查找方法
            var action = route.substring(route.indexOf(main) + main.length + 1);
            if (action.indexOf('/') !== -1) {
                action = action.substring(0, action.indexOf('/'))
            } else if (action.indexOf('?') !== -1) {
                action = action.substring(0, action.indexOf('?'))
            }

            route = main + ':' + action;
            let status, recursive = function (elem) {
                for (let i in elem) {
                    var n = elem[i];

                    if (route === n.alias) {
                        status = true;
                    }

                    if (typeof n.children !== undefined) {
                        recursive(n.children);
                    }
                }

                return status ? status : false;
            }

            let router = admin.getConfig('router');
            try {
                if (typeof router.supersAdmin !== undefined) {
                    if (router.supersAdmin === false
                        && curl.indexOf('://') === -1 && !recursive(router.authorities)) {
                        layer.msg(i18n.prop('无权操作'), 'error');
                        throw '没有权限';
                    }
                }
            } catch (e) {

            }
        }

        return curl;
    }

    /**
     * 全局渲染组件
     */
    admin.components = {
        datetime: function (params) {
            /**
             * 时间控件
             * 1、lay-datetime 参数 默认留空即可，layui自动绑定到了元素 自动赋值
             */
            var datetime = $('*[lay-datetime]');
            datetime.each(function (key, obj) {
                var t = $(obj).data('datetype') || 'datetime',
                    f = $(obj).data('dateformat') || 'yyyy-MM-dd HH:mm:ss',
                    val = $(obj).val() || '',//获取value值
                    r = $(obj).data('range') || false,
                    max = $(obj).data('maxvalue') || '2222-12-31',
                    min = $(obj).data('minvalue') || '1930-01-01';

                laydate.render({
                    elem: this
                    , type: t
                    , range: r
                    , max: max
                    , min: min
                    , value: val
                    , format: f
                    , done: function (value, date, end_date) {
                        // console.log(value, date, end_date);
                    }
                });
            })
        },
        slider: function (params) {
            /**
             * 滑块组件
             */
            layui.each($("*[lay-slider]"), function (key, elem) {

                var that = $(this),
                    type = that.data('type') || 'default',
                    min = that.data('min') || 0,
                    max = that.data('max') || 100,
                    theme = that.data('theme') || '#1890ff',
                    step = that.data('step') || 1,
                    input = that.data('input') || false,
                    showstep = that.data('showstep') || false;
                var name = $(elem).attr("lay-slider");
                var value = $('input[name=' + name + ']').val() || that.data('default');

                slider.render({
                    elem: elem
                    , type: type
                    , min: min
                    , max: max
                    , step: step
                    , showstep: showstep
                    , theme: theme
                    , input: input
                    , value: value
                    , change: function (value) {

                        if (value <= min || isNaN(value)) {
                            value = min;
                        }

                        $('input[name=' + name + ']').val(value);
                    }
                })
            })
        },
        rate: function (params) {
            /**
             * 星星组件 / 默认访问参数 为GET
             * 1、lay-rate 必填参数 list|ones 列表还是单个
             * 2、data-url  必填参数 点击进行GET的地址
             * 3、data-object  必填参数 进行修改的对象ID
             * 4、data-value 必填参数 渲染时使用的原始星星
             * 5、data-theme  颜色
             * 6、data-readonly 是否只是展示，
             */
            layui.each($("*[lay-rate]"), function (index, elem) {
                var that = $(this),
                    url = that.data("url") || undefined,
                    ids = that.data('ids') || undefined,
                    theme = that.data('theme') || '#1890ff',
                    length = that.data('length') || 5,
                    half = that.data('half') || false,
                    readonly = that.data('readonly') || false;

                var name = $(elem).attr("lay-rate");
                var el = $('input[name="' + name + '"]');
                var value = el.val() || that.data('value');

                rate.render({
                    elem: elem
                    , half: half
                    , length: length
                    , theme: theme
                    , readonly: readonly
                    , value: value
                    , choose: function (value) {

                        // 如果当前存在URL则进行AJAX处理
                        if (typeof url != 'undefined') {

                        } else {
                            el.val(value);
                        }
                    }
                })
            })
        },
        tips: function (params) {
            /**
             * 监听消息提示事件
             */
            $(document).on("mouseenter", "*[lay-tips]", function () {
                var remind = $(this).attr("lay-tips");
                var tips = $(this).data("offset") || 1;
                var color = $(this).data("color") || '#000';
                layer.tips(remind, this, {
                    time: -1,
                    tips: [tips, color],
                    area: ['auto', 'auto'],
                });
            }).on("mouseleave", "*[lay-tips]", function () {
                layer.closeAll("tips");
            });
        },
        colorpicker: function (params) {
            /**
             * 颜色选择器控件
             * lay-colorpicker 填写的数据为是哪一个类
             * data-value 初始化的颜色，自己从数据库获取，必填参数
             */
            var picker = $('*[lay-colorpicker]');
            picker.each(function (index, elem) {
                var name = $(elem).attr("lay-colorpicker");
                var color = $('.' + name).val() || $(name).data('value');
                colorpicker.render({
                    elem: this
                    , color: color
                    , predefine: true
                    , alpha: true
                    , done: function (e) {
                        $('.' + name).val(e);
                    }
                });
            })
        },
        upload: function (params) {

            layui.each($('*[lay-upload]'), function (index, elem) {

                var that = $(this),
                    name = $(elem).attr('lay-upload') || undefined,
                    url = $(elem).data('url') || _global_.app + '/Ajax/upload',
                    type = $(elem).data('type') || 'normal',
                    size = $(elem).data('size') || 102400,
                    accept = $(elem).data('accept') || 'images',
                    multiple = $(elem).data('multiple') || false,
                    chunkSize = typeof _upload_chunkSize != 'undefined' ? _upload_chunkSize : '2097152',
                    callback = $(elem).attr('callback') || undefined,
                    blobSlice = File.prototype.slice || File.prototype.mozSlice || File.prototype.webkitSlice;

                var uploadFiles = {
                    normal: function (res, name) {
                        $('input.' + name).prop('value', res.url);
                        $('img.' + name).prop('src', res.url);
                    },
                    images: function (res, name) {
                        let o = $('img.' + name);
                        o.prop('src', res.url);
                        o.parent('div').removeClass('layui-hide');
                        $('input.' + name).val(res.url);
                        $(elem).find('p,i,hr').addClass('layui-hide');
                    },
                    multiple: function (res, name) {

                        let boxList = $(that).parents('.layui-imagesbox').find('.layui-input-inline');
                        let length = boxList.length;
                        $(this).parents('form').find('input#' + name + '_clear').remove();
                        $(boxList).each(function (i, item) {
                            $(item).find('input.layui-hide').prop('name', name + '[' + i + '][src]');
                            $(item).find('input.layui-input').prop('name', name + '[' + i + '][title]');
                        })
                        let html = '<div class="layui-input-inline layui-uplpad-image">';
                        html += '<img src="' + res.url + '" >';
                        html += '<input type="text" name="' + name + '[' + (length - 1) + '][src]" class="layui-hide" value="' + res.url + '">';
                        html += '<input type="text" name="' + name + '[' + (length - 1) + '][title]" class="layui-input" placeholder="图片简介">';
                        html += '<span class="layui-badge layui-badge-red" onclick="layui.$(this).parent().remove();">删除</span></div>';
                        $(elem).parent().before(html);

                    }
                }

                layui.upload.render({
                    elem: elem
                    , url: url
                    , method: 'post'
                    , size: size
                    , auto: false
                    , accept: 'file'
                    , multiple: multiple
                    , choose: function (obj) {

                        var that = this, files = this.files = obj.pushFile();
                        for (var index in files) {

                            let file = files[index];
                            if ((file.size / 1024) > size) {
                                delete files[index];
                                layer.error('文件大小超过限制，最大不超过' + size + 'KB');
                                return false;
                            }

                            if (file.size <= chunkSize) {
                                obj.upload(index, file)
                                delete files[index];
                            } else {

                                var chunkId = file.lastModified + '-' + file.size,
                                    chunkLength = Math.ceil(file.size / chunkSize),
                                    fileExt = /\.([0-9A-z]+)$/.exec(file.name)[1];

                                admin.options[chunkId] = {
                                    obj: obj,
                                    file: file,
                                    othis: that,
                                    files: files,
                                    index: index
                                };

                                var chunkList = layui.data(chunkId).chunkList;
                                if (typeof chunkList == 'undefined') {
                                    chunkList = [];
                                    for (var i = 0; i < chunkLength; i++) {
                                        var item = {
                                            state: false,
                                            data: {
                                                index: i,
                                                chunkCount: chunkLength,
                                                source: file.name,
                                                fileExt: fileExt,
                                                fileSize: file.size,
                                                mimeType: file.type,
                                                chunkId: chunkId,
                                                chunkName: chunkId + '-' + i + '.' + fileExt
                                            }
                                        };

                                        chunkList.push(item);
                                    }
                                    layui.data(chunkId, {key: 'chunkList', value: chunkList});
                                }

                                var progress = 'undefined';
                                for (var key in chunkList) {
                                    if (chunkList[key].state === false) {
                                        progress = key;
                                        break;
                                    }
                                }

                                if (progress === 'undefined') {
                                    layui.data(chunkId, null);
                                    return layer.error('数据读取异常');
                                }
                                that.data = chunkList[progress].data;
                                var start = progress * chunkSize;
                                var end = Math.min(file.size, start + chunkSize);
                                obj.upload(index, blobSlice.call(file, start, end), that.data.source);
                            }
                        }

                    }
                    , before: function (res) {
                        that.prop("disabled", true);
                    }, done: function (res, indexObj, fileObj) {

                        that.prop("disabled", false);
                        if (typeof callback != 'undefined') {
                            return admin.callbackfunc(this.item, {res: res, index: indexObj, file: fileObj});
                        }

                        /**
                         * 处理分片回调
                         */
                        if (typeof res.index != 'undefined'
                            && typeof res.chunkId != 'undefined') {

                            if (res.code !== 200) {
                                layer.closeAll();
                                return layer.error(res.msg);
                            }

                            var index = res.index,
                                chunkId = res.chunkId;
                            var chunkList = layui.data(chunkId).chunkList;
                            if (typeof chunkList == 'undefined') {
                                layui.data(chunkId, null);
                                return false;
                            }

                            if (!$('#' + chunkId).length) {
                                window[chunkId] = layer.open({
                                    type: 1,
                                    title: false,
                                    skin: 'chunkProgress',
                                    closeBtn: 0,
                                    area: ['420px', 'auto'],
                                    content: [
                                        '<div id="' + chunkId + '" class="layui-progress layui-progress-big" lay-showPercent="yes" lay-filter="uploadProgress">',
                                        '<div class="layui-progress-bar layui-bg-blue" lay-percent="' + Math.round(index / chunkList.length * 10000) / 100 + '%" ></div>',
                                        '</div>',
                                    ].join(''),
                                    success: function (layerObj, index) {
                                        layer.setTop(layerObj);
                                    }
                                })
                                element.render();
                            } else {
                                element.progress('uploadProgress', Math.round((index + 1) / chunkList.length * 10000) / 100 + '%');
                            }

                            var obj = admin.options[chunkId].obj,
                                file = admin.options[chunkId].file,
                                othis = admin.options[chunkId].othis;
                            chunkList[index].state = true;
                            layui.data(chunkId, {key: 'chunkList', value: chunkList});
                            if ((index + 1) === chunkList.length) {

                                othis.data = chunkList[index].data;
                                othis.data.action = 'marge';
                                obj.upload(index, blobSlice.call(file, 0, 10), othis.data.source);
                                layui.data(chunkId, null);
                                delete admin.options[chunkId].file;
                                delete admin.options[chunkId].files[admin.options[chunkId].index];
                            } else {
                                othis.data = chunkList[index + 1].data;
                                var start = (index + 1) * chunkSize;
                                var end = Math.min(start + chunkSize, file.size);
                                obj.upload(index, blobSlice.call(file, start, end), othis.data.source);
                            }

                            return false;
                        }

                        if (res.code === 200 && res.url) {
                            if (typeof res.chunkId !== 'undefined') {
                                layer.close(window[res.chunkId]);
                            }

                            layer.msg(res.msg);
                            uploadFiles[type](res, name);
                        } else {
                            layer.error(res.msg);
                            that.prop("disabled", false);
                        }
                    }
                    , error: function (index, upload) {
                        console.log(index, upload)
                    }
                })
            })
        },
        cascader: function (el) {
            var elObj = [];
            el = el || "input[lay-cascader]";
            layui.each($(el), function (index, elem) {

                var value = $(elem).val();
                var name = $(elem).attr('name');
                var propsValue = $(elem).data('value') || 'value';
                var parents = $(elem).data('parents') || false;

                if (typeof value != 'undefined' && value) {
                    value = value.split('/');
                    value = value[value.length - 1];
                    value = $.trim(value);
                }

                elObj[index] = cascader({
                    elem: elem,
                    value: value,
                    clearable: true,
                    filterable: true,
                    showAllLevels: parents,
                    props: {
                        value: propsValue
                    },
                    options: cascader_data
                });

                elObj[index].changeEvent(function (value, node) {

                    if (node != null) {
                        if (parents) {
                            var arrpath = [];

                            for (const key in node.path) {
                                var path = node.path[key].data;
                                arrpath.push($.trim(path[propsValue]));
                            }
                            $('#' + name).val(arrpath.join('/'));
                        } else {

                            $(elem).val(node.data[propsValue]);

                        }
                    } else {
                        $(elem).val('');
                    }
                });
            })
        },
        content: function (params) {
            layui.each($("textarea[lay-editor]"), function (index, elem) {

                let id = $(elem).attr('id');
                if (typeof id == 'undefined' || !id) {
                    id = 'Matheditor_' + Math.round(Math.random() * 36);
                    $(elem).prop('id', id);
                }

                content.tinymce(id);
            })
        },
        markdown: function (params) {
            layui.each($("textarea[lay-markdown]"), function (index, elem) {
                $(elem).hide();
                let _id = 'markdown_' + Math.round(Math.random() * 36);
                $(elem).after('<div id="' + _id + '"></div>');
                content.markdown(_id, elem);
            })
        },
        tags: function (params) {
            layui.each($('.layui-tags'), function (i, e) {
                $(e).remove();
            })
            layui.each($('*[lay-tags]'), function (index, elem) {
                let url = $(elem).data('url') || '';
                let isTags = layui.tags.render({
                    elem: elem,
                    url: url,
                    done: function (key, all) {
                    }
                });
            })
        },
    }

    // 国际化
    i18n.render(admin.getStorage('language') || 'zh-CN');
    for (const key in admin.components) {
        eval(admin.components[key])();
    }

    // 初始化皮肤
    admin.setTheme();
    admin.getStorage('style') === 'light' && $('head').append(admin.globalStyleCss());
    $('body').on('click', 'span.layui-upload-clear', function (e) {
        e.stopPropagation();
        e.preventDefault();
        $(this).parents('.layui-upload-drag').find('i,p,hr').removeClass('layui-hide');
        $(this).parents('.layui-upload-drag').find('div').addClass('layui-hide');
        $(this).parents('.layui-upload-drag').find('input').prop('value', '');
    })

    $('body').on('click', '.layui-jsonvar-add', function (e) {
        e.stopPropagation();
        e.preventDefault();
        var html = '<tr>';
        html += '<td><input type="text" class="layui-input" name="' + $(this).data('name') + '[key][]"></td>';
        html += '<td><input type="text" class="layui-input" name="' + $(this).data('name') + '[value][]"></td>';
        html += '<td><i class="layui-icon fa-times" onclick="layui.admin.removeJson(this);" ></i></td>';
        html += '</tr>';
        $(this).parents('form').find('input#' + $(this).data('name') + '_clear').remove();
        $(this).prev('table').find('tbody').append(html);
    })

    /**
     * 重置表单值
     * @param that
     * @param type
     */
    admin.resetInput = function (that, type = 'json') {
        let name = $(that).data('name'),
            form = $(that).parents('form');
        let length = 0;
        if (type === 'json') {
            length = $(that).parents('tbody').find('tr').length;
            $(that).parents('tr').remove();
            if (length <= 1) {
                $(form).append('<input id="' + name + '_clear" name="' + name + '" value="" hidden>');
            }
        } else if (type === 'images') {
            length = $(that).parents('.layui-imagesbox').find('.layui-uplpad-image').length;
            $(that).parent('div').remove();
            if (length <= 2) {
                $(form).append('<input id="' + name + '_clear" name="' + name + '" value="" hidden >');
            }
        }
    }

    /*
     * 监听全局radio选择器
     */
    form.on('radio(radioStatus)', function (data) {
        var display = $(this).data('display');
        if (display != null && display !== 'undefined') {
            (data.value === 1) ? $('.' + display).show() : $('.' + display).hide();
        }
    })

    /**
     * 监听select过滤器
     */
    form.on('select(selectStatus)', function (data) {

        var select = $(this).parents().prev('select'),
            display = select.data('display'),
            disable = select.data('disable'),
            selector = select.data('selector');

        if (typeof (selector) == 'undefined' || selector == null) {
            if (typeof (display) != 'undefined' && typeof (disable) == 'undefined') {
                (data.value == 1) ? $('.' + display).show() : $('.' + display).hide();
            }

            if (typeof (display) != 'undefined' && typeof (disable) != 'undefined') {
                (data.value == disable) ? $('.' + display).hide() : $('.' + display).show();
            }
        } else {
            selector = selector.replace('，', ',').split(',');
            for (let i in selector) {
                (data.value !== selector[i]) ? $('.' + selector[i]).hide() : $('.' + selector[i]).show();
            }
        }
    })

    /**
     * 监听switch点击状态
     */
    form.on('switch(switchStatus)', function (obj) {

        let that = $(this)
            , callback = {
            error: function (res) {
                $(obj.elem).prop('checked', !obj.elem.checked);
                layer.error(res.msg);
                form.render('checkbox');
            }
        }
            , data = {
            id: $(this).attr('value'),
            status: obj.elem.checked ? 1 : 0
        };

        if ($('.bubble').length) {
            $('.bubble').removeClass('bubble');
            return false;
        }

        admin.event.request(that, data, callback);
    });

    /**
     * 监听form表单提交
     */
    form.on('submit(submitIframe)', function (data) {

        var that = $(this), _form = that.parents('form'),
            _close = that.data("close") || undefined,
            _url = _form.attr("action") || false;

        if (_url === false || _url === '') {
            try {
                var app = _global_.app;
                var action = _global_.action;
                var controller = _global_.controller;
                _url = app + '/' + controller + '/' + action;
            } catch (error) {
                console.warn(error);
            }
        }

        if (typeof _url === 'undefined') {
            layer.msg(i18n.prop('远程URL未定义'), 'error');
            return false;
        }

        var _parent = that.data('reload') || false;
        that.attr("disabled", true);
        $.post(_url, data.field, function (res) {

            if (res.code === 200) {
                top.layer.msg(res.msg);
                if (_close === undefined) {
                    admin.event.closeDialog(that);
                }
                if (_parent !== false && parent.window !== top) {
                    parent.location.reload();
                } else {
                    if (parent.layui.table.cache['lay-tableList']) {
                        parent.layui.table.reloadData('lay-tableList');
                    }
                }
            } else {
                top.layer.error(res.msg);
            }

            try {
                if (typeof res.data.__token__ !== 'undefined') {
                    $('input#__token__').val(res.data.__token__);
                }
            } catch (e) {
                // 默认不处理异常
            }

        }, 'json');

        setTimeout(function (e) {
            that.attr("disabled", false);
        }, 2000);

        return false;
    });

    /**
     * 监听form表单搜索
     */
    form.on('submit(formSearch)', function (data) {

        var field = data.field;
        for (const key in field) {
            if (!field[key]) {
                delete field[key];
            }
        }
        table.reloadData('lay-tableList', {
            page: {curr: 1},
            where: field
        });
    })

    /**
     * 监听表格事件
     */
    table.on("tool(lay-tableList)", function (obj) {

        var data = obj.data
            , reqData = {}
            , selector = $(this).parents('table').find('tbody tr')
            , callback = {
            success: function (res) {
                obj.del();
                if ((selector.length - 1) === 0 ||
                    typeof selector.length === 'undefined') {
                    table.reloadData("lay-tableList");
                }

                layer.msg(res.msg);
            },
            error: function (res) {
                layer.error(res.msg);
            }
        }
            , othis = $(this)
            , title = othis.data("title") || undefined
            , field = $(this).data('field');
        if (typeof (field) === "undefined") {
            reqData = {
                id: data.id
            }
        } else {
            var array = field.split(",");
            for (let d in array) {
                reqData[d] = data[d];
            }
        }

        if (obj.event === "edit") {
            admin.event.open(othis, obj);
        } else if (obj.event === "del") {
            var tips = i18n.prop('确定要删除吗');
            if (typeof title !== "undefined") {
                tips += ' ' + title + ' ';
            }
            layer.confirm(tips, function (index) {
                admin.event.request(othis, reqData, callback);
                layer.close(index);
            })

        } else if (obj.event === "ajax") {
            admin.event.request(othis, reqData, callback);
        } else {
            admin.event.open(othis, obj);
        }
    })

    /**
     * 鼠标click状态下，OPEN图片
     */
    $(document).on("click", "*[lay-image-click]", function () {

        var that = $(this)
            , images = that.attr("lay-image-click")
            , size = that.data("size") || undefined;
        if (images.length === 0 && that[0].localName === "img") {
            images = that.prop("src");
        }

        let width, height;
        if (typeof (size) !== 'undefined') {
            size = size.split(",");
            if (size.length === 2) {
                width = size[0];
                height = size[1];
            }
        } else {
            width = "25%";
            height = "35%";
        }

        layer.open({
            type: 1,
            title: '图片预览',
            offset: '15%',
            area: [width, height],
            shadeClose: true,
            content: '<img class="lay-images-address" src="' + images + '" width="100%" height="100%" >'
        })

    });

    /**
     * 鼠标hover状态下，显示图片
     */
    $(document).on("mouseenter", "*[lay-image-hover]", function () {
        var that = $(this)
            , images = that.attr("lay-image-hover")
            , size = that.data("size") || undefined;
        if (images.length === 0 && that[0].localName === "img") {
            images = that.prop("src");
        } else if (that[0].localName === "input") {
            images = that.prop("value");
        }

        if (!images) return;
        var event = window.event || event;
        var width;
        var height;
        var left = event.clientX + document.body.scrollLeft + 20;
        var top = event.clientY + document.body.scrollTop + 20;

        if (typeof (size) !== 'undefined') {
            size = size.split(",");
            if (size.length === 2) {
                width = size[0];
                height = size[1];
            }
        } else {
            width = "25%";
            height = "35%";
        }

        height = Number(height);
        var sightHeight = $(window).height();

        if (height > sightHeight) {
            height = sightHeight;
        }

        if (sightHeight <= (top + height)) {
            top = sightHeight - height - 50;
        }

        var showbox = '<div class="lay-images-show" style="display:none;">';
        showbox += '<img class="lay-images-address" src="' + images + '" width="' + width + '" height="' + height + '" ></div>';
        $('body').append(showbox);
        $(".lay-images-show").css({left: left, top: top, display: "inline-block"});

    }).on("mouseleave", "*[lay-image-hover]", function () {
        $(".lay-images-show").remove();
    });

    /**
     * 列表全部删除操作
     * 默认传入表的参数，进行POST投递
     */
    $(document).on("click", "*[lay-batch]", function (obj) {

        var othis = $(this)
            , tableId = othis.data("table") || null
            , data = othis.data("field") || undefined
            , selector = $(this).parents("body").find('.layui-table-main tbody *[data-field=id]')
            , list = table.checkStatus(tableId)
            , tips = '确定要批量操作';

        if (tableId === null || tableId === undefined) {
            layer.msg(i18n.prop('表格ID未定义'), 'error');
            return false;
        }

        var field = ['id'];
        if (typeof data !== 'undefined') {
            field = field.concat(data.split(','));
        }

        if (list.data.length === 0) {
            layer.msg(i18n.prop('请勾选数据'), 'error');
            return false;
        }

        var data = {};
        for (var n in field) {
            var e = field[n];
            field[e] = [];
            for (var i in list.data) {
                field[e].push(list.data[i][e]);
            }
            data[e] = field[e];
        }

        layer.confirm(i18n.prop(tips), function (index) {
            admin.event.request(othis, data);
            layer.close(index);
        })
    })

    /**
     * 监听全局layui.open事件并解决鉴权问题
     */
    $(document).on('click', "*[lay-open]", function () {
        admin.event.open($(this), undefined, {});
    })

    /**
     * 监听全局属性鉴权
     */
    $(document).on('click', "*[lay-noauth]", function () {

        var event = $(this).attr('lay-event');
        var router = admin.getConfig('router');
        try {
            if (typeof router.supersAdmin !== undefined) {
                if (router.supersAdmin === false && event === undefined) {
                    layer.msg(i18n.prop('无权操作'), 'error');
                    throw '没有权限';
                }
            }
        } catch (e) {
        }
    })

    /**
     * 监听ajax属性操作
     */
    $(document).on("click", "*[lay-ajax]", function (obj) {

        var data = {}
            , callback = $(this).data("callback") || undefined
            , packet = $(this).attr("data-packet") || null
            , object = $(this).attr("data-object") || undefined;
        if (typeof object !== "undefined") {
            object = object.split(',');
            for (var i = 0; i < object.length; i++) {
                let ele = object[i].split(":");
                var val = $('.' + ele[1]).val();
                data[ele[0]] = val;
            }
        }

        if (packet !== 'null') {
            packet = new Function("return " + packet)();
            data = $.extend({}, data, packet);
        }

        admin.event.request($(this), data, admin.callback[callback]);
    })

    /**
     * 监听模板打开函数 SHIFT + P
     */
    $(document).keyup(function (event) {
        if (event.shiftKey && event.keyCode === 80) {
            $('[sa-event="theme"]').click();
        }
    });

    /**
     * 监听文件选择
     */
    $(document).on("click", "*[lay-choose]", function (obj) {

        let field = $(this).attr('lay-choose') || undefined,
            type = $(this).data('type') || 'file';
        layer.open({
            type: 2,
            title: i18n.prop('文件选择'),
            area: ['62%', '65%'],
            content: _global_.app + '/system/Attachment/index?choose=true',
            success: function (layero, index) {
                let body = layer.getChildFrame('body', index),
                    html = '<input hidden id="_fileFiled" value="' + field + '" >';
                html += '<input hidden id="_fileType" value="' + type + '" >';
                html += '<input hidden id="_fileChoose" data-index="' + index + '" >';
                body.append(html);
            }
        })
    })

    // 清理系统缓存
    layui.dropdown.render({
        elem: '#clearCache'
        , data: [{
            title: '一键清理缓存'
            , event: 'all'
        }, {
            title: '清理内容缓存'
            , event: 'content'
        }, {
            title: '清理模板缓存'
            , event: 'template'
        }, {
            title: '清理插件缓存'
            , event: 'plugin'
        }], click: function (data, othis) {

            var index = layui.layer.confirm("确定要清理缓存吗？", {
                title: '提示',
            }, function () {
                admin.event.request($('#clearCache'), {type: data.event});
                layui.layer.close(index);
            })

        }
    });

    /**
     * 监听小屏幕后hover菜单栏事件
     */
    var layuiside = "." + LAYSIDESHRINK + " .layui-side .layui-nav .layui-nav-item";
    $(document).on("mouseenter", layuiside + "," + layuiside + " .layui-nav-child>dd", function () {
        if (admin.screen() >= 2) {
            var self = $(this),
                child = self.find(">.layui-nav-child");
            if (child.length > 0) {
                self.addClass("layadmin-nav-hover");
                child.css({"width": "50px", "top": self.offset().top, "left": self.offset().left + self.outerWidth()});
            } else {
                if (admin.getStorage('nav') === 'left') {
                    if (self.hasClass("layui-nav-item")) {
                        var title = self.find("cite").text(),
                            href = self.find("a").attr("lay-href");

                        var html = '<dl id="layui-nav-child" class="layui-nav-child" >';
                        html += '<dd class="';
                        if (self.hasClass("layui-this")) {
                            html += 'layui-this';
                        }
                        html += '"><a lay-href="' + href + '">' + title + '</a></dd>';
                        html += '</dl>';
                        $(self).append(html);
                        element.render("nav");
                        self.addClass("layadmin-nav-hover");
                        child = self.find(">.layui-nav-child");
                        child.css({
                            "width": "50px",
                            "top": self.offset().top,
                            "left": self.offset().left + self.outerWidth()
                        });
                    }
                } else {
                    var n = self.text();
                    layer.tips(n, self, {
                        tips: [2, "#303133"],
                        time: -1,
                        success: function (r, s) {
                            $(r).css("margin-top", "12px")
                        }
                    })
                }
            }
        }

    }).on("mouseleave", layuiside + "," + layuiside + " .layui-nav-child>dd", function () {
        layer.closeAll("tips");
        var self = $(this);
        self.removeClass("layadmin-nav-hover");
        $("#layui-nav-child").remove();
        var child = self.find("dl");
        // ie下bug不能为unset
        child.css({
            "left": 0,
            "top": 0,
        });
    });

    let checkChildren = function (object) {
        let isChildren = false;
        layui.each(object.children, function (index, element) {
            if (element.type === 0) {
                return isChildren = true;
            }
        })
        return isChildren;
    }

    /**
     * 获取左侧布局
     * @param {*} object
     * @param {*} child
     * @returns
     */
    admin.getNavHtml = function (object, child = false, icon = true) {

        let navHtml = STR_EMPTY;

        layui.each(object, function (index, element) {

            if (checkChildren(element)) {
                if ((element.pid === 0 && !element.type) || !child) {
                    navHtml += '<li class="layui-nav-item">';
                } else {
                    navHtml += '<dd class="layui-nav-item hybrid-item">';
                }
                navHtml += '<a href="javascript:;">';
                if (element.icon && icon) {
                    navHtml += '<i class="layui-icon ' + element.icon + '"></i>';
                }

                navHtml += '<cite>' + element.title + '</cite>';
                navHtml += '<i class="layui-icon layui-icon-down layui-nav-more"></i>';
                navHtml += '</a>';
                navHtml += '<dl class="layui-nav-child">';
                navHtml += admin.getNavHtml(element.children, true);
                navHtml += '</dl>';

                if ((element.pid === 0 && !element.type) || !child) {
                    navHtml += '</li>';
                } else {
                    navHtml += '</dd>';
                }

            } else {

                if ((element.pid === 0 && !element.type) || !child) {
                    navHtml += '<li class="layui-nav-item">';
                    navHtml += '<a lay-href="' + element.router + '" class="layui-nav-noChild" >';
                    if (element.icon && icon) {
                        navHtml += '<i class="layui-icon ' + element.icon + '"></i>';
                    }
                    navHtml += '<cite>' + element.title + '</cite>';
                    navHtml += '</a></li>';
                } else {
                    if (!element.type) {
                        navHtml += '<dd class="layui-nav-noChild">';
                        navHtml += '<a lay-href="' + element.router + '">';
                        if (element.icon) {
                            navHtml += '<i class="layui-icon ' + element.icon + '"></i>';
                        }

                        navHtml += '<cite>' + element.title + '</cite>' + '</a></dd>';
                    }
                }
            }
        })

        return navHtml;
    }

    admin.getTopHtml = function (object, child = false, icon = true) {

        let navHtml = STR_EMPTY;
        layui.each(object, function (index, element) {

            if (checkChildren(element)) {
                if (element.pid === 0 && !element.type) {
                    navHtml += '<li class="layui-nav-item">';
                } else {
                    navHtml += '<dd>';
                }
                navHtml += '<a href="javascript:;">';
                if (element.pid !== 0 && element.icon) {
                    navHtml += '<i class="layui-icon ' + element.icon + '" style="margin-right: 10px" ></i>';
                }

                navHtml += element.title;
                if (element.pid !== 0) {
                    navHtml += '<i class="layui-icon layui-icon-right"></i>';
                }

                navHtml += '</a>';
                if (element.pid === 0) {
                    navHtml += '<dl class="layui-nav-child">';
                    navHtml += admin.getTopHtml(element.children, true);
                    navHtml += '</dl>';
                } else {
                    navHtml += '<dl class="layui-nav-third-child">';
                    navHtml += admin.getTopHtml(element.children, true);
                    navHtml += '</dl>';
                }

                if (element.pid === 0 && !element.type) {
                    navHtml += '</li>';
                } else {
                    navHtml += '</dd>';
                }
            } else {
                if (element.pid === 0 && !element.type) {
                    navHtml += '<li class="layui-nav-item">';
                    navHtml += '<a lay-href="' + element.router + '" >';
                    navHtml += element.title;
                    navHtml += '</a></li>';
                } else {
                    if (!element.type) {
                        navHtml += '<dd class="layui-nav-noChild">';
                        navHtml += '<a lay-href="' + element.router + '">';
                        if (element.icon) {
                            navHtml += '<i class="layui-icon ' + element.icon + '" style="margin-right: 10px" ></i>';
                        }
                        navHtml += element.title + '</a></dd>';
                    }
                }
            }
        })

        return navHtml;
    }

    /**
     * 处理顶级菜单下拉
     */
    $('body').on('mouseenter', 'dl.layui-nav-child dd', function (e) {
        let self = $(this), child = self.find(">dl.layui-nav-third-child");
        child.css({"top": 0, "left": $(self).parents('dl').width(), 'display': 'block'});
    }).on('mouseleave', 'dl.layui-nav-child dd', function (e) {
        let self = $(this), child = self.find(">dl.layui-nav-third-child");
        child.hide();
    })

    $('body').on('click', 'dl.layui-nav-child dd', function (e) {

        e.stopPropagation();
        e.preventDefault();
        layui.each($('.layui-nav-top .layui-this'), function (i, n) {
            $(n).removeClass('layui-this');
        })

        if ($(this).hasClass('layui-nav-noChild')) {
            $(this).addClass('layui-this');
        }
    })

    /**
     * 混合菜单布局
     * @param {*} route
     */
    admin.getNavhybrid = function (route) {

        var header = STR_EMPTY, navHtml = STR_EMPTY;
        for (let index = 0; index < route.length; index++) {
            const element = route[index], nav = 'swift-admin-' + (index + 1);
            if (element.pid === 0) {
                header += '<li class="layui-nav-item layui-hide-xs ';
                if (index === 0) {
                    header += 'layui-this';
                }
                header += '">';
                if (!element.children) {
                    header += '<a href="javascript:;" data-bind="' + nav + '" sa-event="tabs" data-url="' + element.router;
                    header += '" lay-title="' + element.title + '" >' + element.title + '</a>';
                } else {
                    header += '<a href="javascript:;" class="lay-nav-bind" lay-nav-bind="' + nav + '" >' + element.title + '</a>';
                }
                header += '</li>';
                route[index]['nav'] = nav;
            }

            if (checkChildren(element)) {
                navHtml += '<div class="' + element.nav + '"';
                if (index === 0) {
                    navHtml += 'style="display:block;"';
                }

                navHtml += '>';
                navHtml += admin.getNavHtml(element.children, false);
                navHtml += '</div>';
            }
        }

        return {header: header, navHtml: navHtml};

    }


    /**
     * 创建多标签
     * @param {*} res
     * @returns
     */
    admin.createElementTabs = function (res, bool = false) {

        var options = this.options;

        if (!res.url) {
            layer.msg(i18n.prop('菜单的地址不能为空'));
            return;
        }

        var id = res.id || res.url;
        var url = res.url;
        var title = res.title;

        if (options.moreLabel) {
            if ((options.TabLists.length + 1) >= options.maxTabNum) {
                layer.msg(i18n.prop('最大打开20个标签页'));
                return false
            }

            if ($(TABS + '>.layui-tab-title [lay-id="' + id + '"]').length >= 1) {
                element.tabChange(TABFILTER, id);
                return false;
            }

            title = '<em class="circle"></em><span class="title">' + (title ? title : "") + "</span>";
            element.tabAdd(TABFILTER, {
                id: id,
                title: title,
                content: '<iframe lay-id="' + url + '" src="' + url + '" frameborder="0" onload="layui.admin.removeLoading(this)" class="swiftadmin-iframe"></iframe>'
            });

            var layout = admin.getStorage('layout') || options.layout;
            if (layout === 'left') {
                admin.setBreadHtml();
            }

            if (options.TabLists.length >= 2 && layout === "left") {
                admin.setBreadcrumb(url, title);
            }

            admin.showLoading($('iframe[lay-id="' + url + '"]').parent());
            element.render("breadcrumb");
            element.tabChange(TABFILTER, id);

            let exist = false;
            for (const key in options.TabLists) {
                let el = options.TabLists[key];
                if (el.url === res.url) {
                    exist = true;
                }
            }
            if (!exist) {
                options.TabLists.push(res);
            }

        } else {

            var iframe = $('.swiftadmin-iframe');
            $('*[lay-filter="swiftadmin-tabs"] .layui-tab-item').each(function (i, n) {
                if (!$(n).hasClass('layui-show')) {
                    $(n).remove();
                }
            })

            if (typeof (iframe) === "undefined" || iframe.length <= 0) {
                var html = ['<div id="swiftadmin-iframe">',];
                html += ' <iframe lay-id="' + url + '" src="' + url + '"';
                html += 'frameborder="0" onload="layui.admin.removeLoading(this)" class="swiftadmin-iframe"></iframe></div>';

                $(LAYOUTADMIN + '>' + BODY).html(html);
            } else {

                iframe.attr("lay-id", url);
                iframe.attr("src", url);
                admin.setBreadcrumb(url, title);
            }

            admin.setBreadHtml();
            element.render("breadcrumb");
            admin.showLoading($('#swiftadmin-iframe'));
            admin.activityTabElem(id);
        }

        if (options.cacheTab && options.moreLabel) {
            admin.setConfig('TabLists', options.TabLists);
        }

        admin.setConfig('activeTab', url);
    }

    element.on("nav(" + MENUFILTER + ")", function (res) {

        var othis = $(this), id = othis.attr("lay-id");
        var href = othis.attr("lay-href");

        if (!id) {
            id = href;
        }

        if (href && href !== "javascript:;") {
            var title = othis.attr("sa-title");
            title || (title = othis.text().replace(/(^\s*)|(\s*$)/g, ""));

            admin.createElementTabs({
                id: id,
                url: href,
                title: title,
                home: false
            }, true);
        }

        if (admin.screen() < 2
            && othis.children('.layui-nav-more').length === 0) {
            admin.flexible();
        }

    })

    /**
     * 活动的菜单样式
     * @param {} Id
     */
    admin.activityTabElem = function (Id) {

        var layout = admin.getStorage('layout') || admin.options.layout;
        $(".layui-nav li").removeClass("layui-this").removeClass("layui-nav-itemed");
        $(".layui-nav li dd").removeClass("layui-this").removeClass("layui-nav-itemed");
        switch (layout) {
            case 'top':
                // $('.layui-nav-top dd.layui-this').removeClass('layui-this');
                $('.layui-nav-top [lay-href="' + Id + '"]').parent('dd').addClass('layui-this');
                break;
            case 'hybrid':
                var othis = $('.layui-nav li [lay-href="' + Id + '"]');
                var navBind = $('.layui-nav-tree [lay-href="' + Id + '"]').parents('div').attr('class');
                if (typeof (navBind) !== "undefined") {
                    $(othis).parent('li').addClass('layui-this');
                    $(othis).parents('li').addClass('layui-nav-itemed');
                    $(othis).parents('dd.hybrid-item').addClass('layui-nav-itemed');
                }
                $("div[class^='swift-admin']").hide();
                $("." + navBind).show();
                othis.parent("dd,li").addClass('layui-this');
                break;
            default:
                var othis = $('.layui-nav li [lay-href="' + Id + '"]');
                othis.parent("dd,li").addClass('layui-this');
                othis.parent("dd").parents("dd,li").addClass("layui-nav-itemed");
                break;
        }
    }

    /**
     * 监听TAB点击
     * 需要切换状态
     */
    element.on("tab(" + TABFILTER + ")", function (v) {

        var id = $(this).attr("lay-id");
        admin.activityTabElem(id);
        var layout = admin.getStorage('layout') || "left";
        if (layout === "left" || typeof (layout) === "undefined") {
            var title = $(this).children('.title').text();
            if (admin.options.TabLists.length >= 2 && layout === "left") {
                admin.setBreadcrumb(id, title);
            }
        }

        admin.rollPage("auto");
        admin.setConfig("activeTab", id);
    });

    element.on("tabDelete(" + TABFILTER + ")", function (res) {
        var id = admin.options.TabLists[res.index];
        if (id && typeof id === 'object') {
            admin.options.TabLists.splice(res.index, 1);
            if (admin.options.cacheTab) {
                admin.setConfig("TabLists", admin.options.TabLists);
            }
            id = admin.options.TabLists[res.index - 1];
            if (id && typeof id === 'object') {
                admin.setConfig("activeTab", id.id || id.url);
            }
        }
    })

    $('body').off("contextmenu").on("contextmenu", TABS + " li", function (v) {

        let that = $(this);
        let id = $(this).attr("lay-id");

        layui.dropdown.render({
            elem: that
            , trigger: 'contextmenu'
            , style: 'width: 110px;text-align:center;'
            , id: id
            , show: true
            , data: [
                {
                    title: '刷新当前'
                    , id: 'refresh'
                }
                , {
                    title: '关闭当前'
                    , id: 'closeThisTabs'
                }
                , {
                    title: '关闭其他'
                    , id: 'closeOtherTabs'
                }
                , {
                    title: '关闭全部'
                    , id: 'closeAllTabs'
                }]
            , click: function (obj, othis) {
                if (obj.id === 'refresh') {
                    admin.refresh(id);
                    element.tabChange(TABFILTER, id);
                } else if (obj.id === 'closeThisTabs') {
                    admin.event.closeThisTabs(id)
                } else if (obj.id === 'closeOtherTabs') {
                    admin.event.closeOtherTabs(id)
                } else if (obj.id === 'closeAllTabs') {
                    admin.event.closeAllTabs(id)
                }
            }
        });
        return false;
    })


    $(window).on('resize', function () {
        var layout = admin.getStorage('layout') || "left";
        var width = $(window).width() - 550;

        if (admin.screen() < 2) {
            admin.flexible();
            $('.layui-breadcrumb-header').hide();
            if ($(BODYSHADECLASS).length <= 0) {
                $(LAYOUTADMIN).append(BODYSHADE);
            }
        } else {

            admin.flexible('open');
            $('.layui-breadcrumb-header').show();
            $(BODYSHADECLASS).remove();
        }

        if (layout === "top" || layout === "hybrid") {
            $('.layui-nav-head').css({
                "overflow": "hidden"
                , "width": width
            })
            if (width >= 900) {
                $('.layui-nav-head').css({"overflow": "unset"})
            }
        }
    });

    // 监听全局sa-event事件
    $('body').on("click", "*[sa-event]", function () {
        let name = $(this).attr("sa-event");
        let obj = admin.event[name];
        obj && obj.call(this, $(this));
    });

    /**
     * 基础布局函数
     * @param {*} res
     * @param {*} router
     * @param {*} reloadTabs
     */
    admin.BasicLayout = function (res, router = null, reloadTabs = true) {

        var othis = this,
            options = othis.options;

        if (top !== window) {
            return false;
        }

        router = router || othis.getConfig('router');
        options.layout = othis.getStorage('layout') || options.layout;
        options.moreLabel = othis.getStorage('moreLabel') !== false;

        $('.layui-nav-head').hide();
        $(".layui-side-menu,.layui-breadcrumb").show();
        $('.layui-nav-top,.layui-nav-tree').html(STR_EMPTY);

        var BodyLayout = {
            left: function (route) {
                $('.layui-nav-tree').html(othis.getNavHtml(route, true));
                $('.layui-layout-left,.layui-footer,' + LAYOUTADMIN + '>' + BODY).removeAttr('style');
            },

            top: function (route) {
                $(".layui-side-menu").hide();
                $('.layui-breadcrumb').hide();
                $('.layui-nav-head').show();
                $('.layui-nav-top').html(othis.getTopHtml(route, true, false));
                $('.layui-layout-left,.layui-footer,' + LAYOUTADMIN + '>' + BODY).css('left', '0');

            },
            hybrid: function (route) {
                $('.layui-breadcrumb').hide();
                $('.layui-nav-head').show();
                var obj = admin.getNavhybrid(route);
                $('.layui-nav-top').html(obj.header);
                $('.layui-nav-tree').html(obj.navHtml);
                $('.layui-layout-left,.layui-footer,' + LAYOUTADMIN + '>' + BODY).removeAttr('style');
                $('a.lay-nav-bind').on("click", function (res) {
                    var that = $(this),
                        navBind = that.attr('lay-nav-bind');
                    if (typeof (navBind) !== "undefined") {
                        $("div[class^='swift-admin']").hide();
                        $("." + navBind).show();
                    }
                })
            },
        };

        BodyLayout[options.layout](router.authorities);
        var allowclose = '<div class="layui-tab" lay-allowClose="true" lay-filter="swiftadmin-tabs">';
        allowclose += '       <ul class="layui-tab-title"></ul>';
        allowclose += '          <div class="layui-tab-content"></div>';
        allowclose += "   </div>";
        allowclose += ' <div id="tabs-control">';
        allowclose += '   <div class="layui-icon swiftadmin-tabs-control layui-icon-left" sa-event="leftPage"></div>';
        allowclose += '   <div class="layui-icon swiftadmin-tabs-control layui-icon-right" sa-event="rightPage"></div>';
        allowclose += '   <div class="layui-icon swiftadmin-tabs-control layui-icon-down">';
        allowclose += '      <ul class="layui-nav swiftadmin-tabs-select " lay-filter="swiftadmin-nav">';
        allowclose += '         <li class="layui-nav-item" lay-unselect>';
        allowclose += "            <a></a>";
        allowclose += '            <dl class="layui-nav-child layui-anim-fadein">';
        allowclose += '               <dd sa-event="closeThisTabs" lay-unselect><a>关闭当前标签页</a></dd>';
        allowclose += '               <dd sa-event="closeOtherTabs" lay-unselect><a>关闭其它标签页</a></dd>';
        allowclose += '               <dd sa-event="closeAllTabs" lay-unselect><a>关闭全部标签页</a></dd>';
        allowclose += "            </dl>";
        allowclose += "         </li>";
        allowclose += "      </ul>";
        allowclose += "   </div>";
        allowclose += " </div>";

        if (options.moreLabel && reloadTabs) {
            $(BODY).html(allowclose);
            $(BODY).find('.layui-tab-content').css('top', '40px');
        } else if (!options.moreLabel) {
            $(BODY).find('.layui-tab-content').css('top', '0px');
        }

        let TabLists = admin.getConfig("TabLists") || [],
            refreshClearTab = admin.getStorage('refreshClearTab') !== false;
        if (!TabLists.length) {
            TabLists = options.TabLists;
        }
        let activeTab = admin.getConfig("activeTab");
        if (reloadTabs) {

            if (!TabLists.length) {
                res.id = res.url;
                res.home = true;
                TabLists.push(res);
            }

            if (options.moreLabel) {
                for (let i in TabLists) {

                    admin.createElementTabs({
                        id: TabLists[i].id,
                        url: TabLists[i].url,
                        title: TabLists[i].title,
                        home: TabLists[i].home || false,
                    })

                    if (refreshClearTab) {
                        break;
                    }
                }

            } else {

                admin.createElementTabs({
                    id: TabLists[0].id,
                    url: TabLists[0].url,
                    title: TabLists[0].title,
                    home: TabLists[0].home || false
                })
            }
        }

        element.render("nav");
        element.tabChange(TABFILTER, activeTab);
    }

    /**
     * 重新请求菜单项
     */
    admin.reloadLayout = function () {

        let oThis = this;
        $.ajax({
            type: 'get',
            url: oThis.options.authorizeUrl,
            success: function (result) {
                try {
                    result = typeof (result) !== "object" ? JSON.parse(result) : result;
                } catch (error) {
                    console.error('result not JSON');
                }

                oThis.setConfig('router', result);
                oThis.BasicLayout(null, result, false);
            },
            error: function (res) {
                $(LAYOUTADMIN).html(res.responseText);
            }
        })
    }

    /**
     * 入口函数，渲染界面
     * @param {*} res
     * @param {*} options
     */
    admin.render = function (res, options) {

        let othis = this;
        this.options = $.extend(this.options, options);
        this.options.layout = this.getStorage('layout') || this.options.layout;
        othis.setPageHeaderFooter() || othis.setPageHeaderFooter('footer');

        // 初始化Load效果
        if (!$(LAYOUTBODY).children('#loading').length) {
            $(LAYOUTBODY).append(admin.getSpinningHtml());
        }

        if (admin.screen() < 2) {
            admin.flexible();
        }

        // 可自行修改
        othis.options.authorizeUrl = $('#authorize').data('url');

        $.ajax({
            type: 'get',
            url: othis.options.authorizeUrl,
            success: function (result) {
                try {
                    result = typeof (result) !== "object" ? JSON.parse(result) : result;
                } catch (error) {
                    console.error('result not JSON');
                }


                othis.setConfig('router', result);
                othis.BasicLayout(res, result);
                admin.removeLoading('master');
            },
            error: function (res) {
                // 执行异常
                admin.removeLoading('master');
                $(LAYOUTADMIN).html(res.responseText);
            }
        })
    }

    exports('admin', admin);
});

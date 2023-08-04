/*!
 * center For Home - v1.0.0 - 2022-10-10
 * https://github.com/meystack/swiftadmin
 * Copyright (c) meystack
 * Licensed Apache2.0
 */
layui.use(['jquery', 'form', 'upload', 'table', 'dropdown'], function () {

    let $ = layui.$;
    let form = layui.form;
    let layer = layui.layer;
    let table = layui.table;
    let upload = layui.upload;
    let dropdown = layui.dropdown;

    /**
     * 短消息下拉框
     * @param count
     */
    window.bellMessage = function (count) {
        dropdown.render({
            elem: '#notice'
            , trigger: 'hover'
            , align: 'center'
            , data: [{
                title: !count ? '暂无消息' : '您有<b class="msg">' + count + '</b>条未读消息'
            }], ready: function (elemPanel, elem) {
            }
            , click: function (data, index) {
                let elem = $('.layui-nav-tree li [lay-href="/user/message"]');
                $(elem).parents('.layui-nav-item').addClass('layui-nav-itemed');
                $(elem).trigger('click');
            }
        });
    }

    /**
     * 前端全局对象
     * @access object
     * screen: 屏幕宽度
     * event: 事件对象
     * Cookie: Cookie操作对象
     * */
    window.Home = {
        screen: function () {
            let width = $(window).width()
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
        event: {
            closeDialog: function (that) {
                that = that || this;
                let _type = $(that).parents(".layui-layer").attr("type");
                if (typeof _type === "undefined") {
                    parent.layer.close(parent.layer.getFrameIndex(window.name));
                } else {
                    let layerId = $(that).parents(".layui-layer").attr("id").substring(11);
                    layer.close(layerId);
                }
            }
        },
        // cookie
        Cookie: { // 获取cookies
            'Set': function (name, value, days) {
                let exp = new Date();
                exp.setTime(exp.getTime() + days * 24 * 60 * 60 * 1000);
                document.cookie.match(new RegExp("(^| )" + name + "=([^;]*)(;|$)"));
                document.cookie = name + "=" + escape(value) + ";path=/;expires=" + exp.toUTCString();
            },
            'Get': function (name) {
                let arr = document.cookie.match(new RegExp("(^| )" + name + "=([^;]*)(;|$)"));
                if (arr != null) {
                    return unescape(arr[2]);
                }
                return null;
            },
            'Del': function (name) {
                let exp = new Date();
                exp.setTime(exp.getTime() - 1);
                let cval = this.Get(name);
                if (cval != null) {
                    document.cookie = name + "=" + escape(cval) + ";path=/;expires=" + exp.toUTCString();
                }
            }
        }
    }

    /**
     * 监听全局form表单提交
     * @param data
     * @param callback
     * @type  button lay-filter="submitIframe"
     */
    form.on('submit(submitIframe)', function (data) {
        let that = $(this), _form = that.parents('form'),
            _url = _form.attr("action") || false,
            _close = that.data("close") || undefined,
            _reload = that.data('reload');

        // 开启节流
        that.attr("disabled", true);
        setTimeout(function () {
            that.attr("disabled", false);
        }, 2000);
        $.post(_url,
            data.field, function (res) {
                if (res.code === 200) {
                    top.layer.msg(res.msg);
                    switch (_reload) {
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

                    if (typeof res.url !== 'undefined' && res.url) {
                        location.href = res.url;
                    }

                    // 默认关闭
                    if (_close === undefined) {
                        Home.event.closeDialog(that);
                    }
                } else {
                    top.layui.layer.msg(res.msg, {icon: 5});
                }

                try {
                    /**
                     * token重载下
                     * 框架也需要更新
                     */
                    if (typeof res.data.__token__ !== 'undefined') {
                        $('input#__token__').val(res.data.__token__);
                    }
                } catch (e) {}
            }, 'json');

        return false;
    })

    /**
     * 监听form表单搜索
     * 默认表格ID: lay-tableList
     */
    form.on('submit(formSearch)', function (data) {

        let field = data.field;
        for (const key in field) {
            if (!field[key]) {
                delete field[key];
            }
        }

        layui.table.reload('lay-tableList', {
            page: {curr: 1},
            where: field
        });
    })

    // 监听全局事件
    $(document).on("click", "*[sa-event]", function () {
        let name = $(this).attr("sa-event");
        let obj = Home.event[name];
        obj && obj.call(this, $(this));
    });

    let uploadURL = '/index/user/upload';
    layui.each($('*[lay-upload]'), function (index, elem) {

        let that = $(this),
            name = $(elem).attr('lay-upload') || undefined,
            type = $(elem).data('type') || 'normal',
            size = $(elem).data('size') || 51200, // 限制最大5M
            accept = $(elem).data('accept') || 'file';

        // 文件上传回调
        let uploadFiles = {
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
                let index = $('.layui-imagesbox .layui-input-inline');
                index = index.length ? index.length - 1 : 0;
                let html = '<div class="layui-input-inline">';
                html += '<img src="' + res.url + '" alt="alt" >';
                html += '<input type="text" name="' + name + '[' + index + '][src]" class="layui-hide" value="' + res.url + '">';
                html += '<input type="text" name="' + name + '[' + index + '][title]" class="layui-input" placeholder="图片简介">';
                html += '<span class="layui-badge layui-badge-red" onclick="layui.$(this).parent().remove();">删除</span></div>';
                $(elem).parent().before(html);
            }
        }

        // 执行上传操作
        upload.render({
            elem: elem
            , url: uploadURL
            , method: 'post'
            , size: size
            , accept: accept
            , before: function (res) {
                // 关闭按钮点击
                that.prop("disabled", true);
            }, done: function (res, index, file) {
                if (res.code === 200 && res.url !== '') {
                    if (typeof res.chunkId != 'undefined') {
                        layer.close(window[res.chunkId]);
                    }

                    layer.msg(res.msg);
                    uploadFiles[type](res, name);
                } else {
                    // 错误消息
                    layer.msg(res.msg, {icon: 5});
                }
                that.prop("disabled", false);
            }
        })
    })

    // 全局监听打开窗口
    $(document).on('click', "*[lay-open]", function () {
        let clickThis = $(this),
            config = {
                url: clickThis.data('url') || undefined,
                object: clickThis.data('object') || 'self',
                type: clickThis.data('type') || 2,
                area: clickThis.data('area') || "auto",
                offset: clickThis.data('offset') || "25%",
                title: clickThis.data('title') || false,
                maxmin: clickThis.data('maxmin') || false,
                auto: clickThis.data('auto') || "undefined",
                iframeAuto: false
            }

        let firstURL = config.url.substring(0, 1);
        if (firstURL && firstURL === '#') {
            config.type = 1;
            config.url = $(config.url).html();
        }

        if (config.area !== "auto") {
            config.area = config.area.split(',');
            if (config.area.length === 2 && config.area[1] === '100%') {
                config.offset = 0;
                if (typeof config.url == 'object') {
                    config.url = config.url[0];
                }
            } else if (config.area.length === 1) {
                config.iframeAuto = true;
            }
        }

        /**
         * 获取窗口索引
         * @type {Window | (WorkerGlobalScope & Window)}
         */
        let hierarchy = self;
        if (config.object === 'top') {
            hierarchy = top;
        } else if (config.object === 'parent') {
            hierarchy = parent;
        }
        hierarchy.layer.open({
            type: config.type,
            area: config.area,
            title: config.title,
            offset: config.offset,
            maxmin: config.maxmin,
            shadeClose: true,
            scrollbar: true,
            content: config.url,
            success: function (layero, index) {

                config.iframeAuto && layer.iframeAuto(index);

                // 页面层才渲染
                if (config.type === 1) {
                    layui.form.render();
                    layui.form.on("submit(submitPage)", function (post) {
                        let that = $(this),
                            url = that.parents('form').attr('action');

                        // 开始POST提交数据
                        that.attr('disabled', true);
                        $.post(url, post.field, function (res) {
                            if (res.code === 200) {
                                Home.event.closeDialog(that);
                                if ($(that).data('reload')) {
                                    location.reload();
                                }
                                layer.msg(res.msg);
                            } else {
                                layer.msg(res.msg, {icon: 5});
                            }
                            that.attr('disabled', false);
                        }, 'json');

                        return false;
                    })
                }
            }
        })
    })

    /**
     * 表格批量操作
     * @param obj
     */
    $(document).on("click", "*[lay-batch]", function (obj) {
        let that = $(this)
            , tableId = that.data("table") || null
            , fields = that.data("field") || undefined
            , list = table.checkStatus(tableId);

        let field = ['id'];
        if (typeof fields !== 'undefined') {
            field = field.concat(fields.split(','));
        }

        if (list.data.length === 0) {
            layer.msg('请勾选数据');
            return false;
        }

        let data = {};
        for (let n in field) {
            let e = field[n];
            field[e] = [];
            for (let i in list.data) {
                field[e].push(list.data[i][e]);
            }
            data[e] = field[e];
        }

        layer.confirm('确定执行批量操作', function (index) {

            $.ajax({
                url: that.data("url"),
                type: 'post',
                data: data,
                dataType: 'json',
                success: function (res) {
                    if (res.code === 200) {
                        layer.msg(res.msg);
                        table.reload(tableId);
                    } else {
                        layer.msg(res.msg, {icon: 5});
                    }
                }
            })
            layer.close(index);
        })
    })

    /**
     * 监听ajax操作
     * @param obj
     */
    $(document).on("click", "*[lay-ajax]", function (obj) {

        let clickThis = $(this), config = {
            url: clickThis.attr('data-url') || "undefined",
            type: clickThis.data('type') || 'post',
            dataType: clickThis.data('dataType') || 'json',
            timeout: clickThis.data('timeout') || '6000',
            tableId: clickThis.data('table') || clickThis.data('batch'),
            reload: clickThis.data('reload'),
            jump: clickThis.data('jump') || false,
            confirm: clickThis.data('confirm'),
        }, defer = $.Deferred();

        // 定义初始化对象
        let data = {}
            // 获取拼接参数
            , packet = clickThis.attr("data-data") || null
            , object = clickThis.attr("data-object") || undefined;

        if (config.confirm !== undefined) {
            config.confirm = config.confirm || '确定执行此操作吗?';
            layer.confirm(config.confirm, function (index) {
                runningAjax(config);
                layer.close(index);
            }, function (index) {
                layer.close(index);
                return false;
            })
        }

        // 传递类数据
        if (typeof object !== "undefined") {
            object = object.split(',');
            for (let i = 0; i < object.length; i++) {
                let ele = object[i].split(":");
                data[ele[0]] = $('.' + ele[1]).val();
            }
        }

        // 传递对象数据
        if (packet !== 'null') {
            packet = new Function("return " + packet)();
            data = $.extend({}, data, packet);
        }

        // 传递input表单数据
        let input = clickThis.data('input') || undefined;
        if (typeof input !== undefined) {
            let attribute = layui.$('.' + input).val();
        }

        // 回调函数
        let runningAjax = function (config) {
            // 执行AJAX操作
            $.ajax({
                url: config.url,
                type: config.type,
                dataType: config.dataType,
                timeout: config.timeout,
                data: data,
                xhrFields: {
                    withCredentials: true
                },
                crossDomain: true,
                success: function (res) {
                    if (res.code === 200) {
                        layer.msg(res.msg);

                        if (typeof res.data.text !== 'undefined') {
                            $(clickThis).text(res.data.text);
                        }

                        switch (config.reload) {
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

                        if (typeof (config.tableId) !== "undefined") {
                            layui.table.reload(config.tableId);
                        }

                    } else {
                        layer.msg(res.msg, {icon: 5});
                    }
                },
                error: function (res) {
                    layer.msg('Access methods failure', {icon: 5});
                }
            })
        }

        if (!config.confirm) {
            runningAjax(config);
        }
    })
})
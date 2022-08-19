/**
 * SAPHP 前端CommonJS
 * 默认提供一些基础的页面交互操作
 * 注：插件开发请勿直接将JS代码写入此文件
 */
layui.use(['jquery','form','upload'], function(){

    let $ = layui.$;
    let form = layui.form;
    let layer = layui.layer;
    let upload = layui.upload;

    // 注册为全局对象
    window.Home = {
        screen: function(){
            let width =$(window).width()
            if(width > 1200){
                return 3; //大屏幕
            } else if(width > 992){
                return 2; //中屏幕
            } else if(width > 768){
                return 1; //小屏幕
            } else {
                return 0; //超小屏幕
            }
        },
        event: {
            closeDialog:function(that) {
                that = that || this;
                let _type = $(that).parents(".layui-layer").attr("type");
                if (typeof _type === "undefined") {
                    parent.layer.close(parent.layer.getFrameIndex(window.name));
                }else {
                    let layerId = $(that).parents(".layui-layer").attr("id").substring(11);
                    layer.close(layerId);
                }
            }
        },
        // cookie
        Cookie : { // 获取cookies
            'Set': function (name, value, days) {
                let exp = new Date();
                exp.setTime(exp.getTime() + days * 24 * 60 * 60 * 1000);
                let arr = document.cookie.match(new RegExp("(^| )" + name + "=([^;]*)(;|$)"));
                document.cookie = name + "=" + escape(value) + ";path=/;expires=" + exp.toUTCString();
            },
            'Get': function (name) {
                let arr = document.cookie.match(new RegExp("(^| )" + name + "=([^;]*)(;|$)"));
                if (arr != null) {
                    return unescape(arr[2]);
                    return null;
                }
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

    // 监听全局form表单
    form.on('submit(submitIframe)', function(data){
        let that = $(this), _form = that.parents('form'),
            _url = _form.attr("action") || false,
            _close = that.data("close") || undefined,
            _reload = that.data('reload');
        $.post(_url,
            data.field,function(res){
                if(res.code === 200){

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
                }
                else{
                    top.layui.layer.msg(res.msg,'error');
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
     */
    form.on('submit(formSearch)', function (data) {

        var field = data.field;
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

    var uploadURL = '/user/upload';
    layui.each($('*[lay-upload]'), function (index, elem) {

        var that = $(this),
            name = $(elem).attr('lay-upload') || undefined,
            type = $(elem).data('type') || 'normal',
            size = $(elem).data('size') || 51200, // 限制最大5M
            accept = $(elem).data('accept') || 'images',
            multiple = $(elem).data('multiple') || false,
            callback = $(elem).attr('callback') || undefined;

        // 文件上传函数
        var uploadFiles = {
            normal: function (res, name) {
                $('input.' + name).prop('value', res.url);
                $('img.' + name).prop('src', res.url);
            },
            images: function (res, name) {
                var o = $('img.' + name);
                o.prop('src', res.url);
                o.parent('div').removeClass('layui-hide');
                $('input.' + name).val(res.url);
                $(elem).find('p,i,hr').addClass('layui-hide');
            },
            multiple: function (res, name) {
                var index = $('.layui-imagesbox .layui-input-inline');
                index = index.length ? index.length - 1 : 0;
                var html = '<div class="layui-input-inline">';
                html += '<img src="' + res.url + '" >';
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
            , accept: 'file'
            , before: function (res) {
                // 关闭按钮点击
                that.prop("disabled", true);
            }, done: function (res, index, file) {

                that.prop("disabled", false);

                if (res.code === 200 && res.url !== '') {

                    if (typeof res.chunkId != 'undefined' ) {
                        layer.close(window[res.chunkId]);
                    }

                    layer.msg(res.msg);
                    uploadFiles[type](res, name);
                } else {
                    // 错误消息
                    layer.error(res.msg);
                    that.prop("disabled", false);
                }
            }
        })

    })

    // 全局监听打开窗口
    $(document).on('click',"*[lay-open]",function(){
        let clickthis = $(this),
            config = {
                url: clickthis.data('url') || undefined,
                object: clickthis.data('object') || 'self',
                type: clickthis.data('type') || 2,
                area: clickthis.data('area') || "auto",
                offset: clickthis.data('offset') || "25%",
                title: clickthis.data('title') || false,
                maxmin: clickthis.data('maxmin') || false,
                auto: clickthis.data('auto') || "undefined",
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

        var layObject = self;
        if (config.object === 'top') {
            layObject = top;
        } else if (config.object === 'parent') {
            layObject = parent;
        }

        // 打开窗口
        layObject.layer.open({
            type: config.type,
            area: config.area,
            title: config.title,
            offset: config.offset,
            maxmin: config.maxmin,
            shadeClose: true,
            scrollbar: true,
            content:  config.url,
            success:function(layero,index){

               config.iframeAuto && layer.iframeAuto(index);

                // 页面层才渲染
                if (config.type === 1) {
                    layui.form.render();
                    layui.form.on("submit(submitPage)",function(post){
                        let that = $(this), _pageUrl = that.parents('form').attr('action');
                        // 开始POST提交数据
                        $.post(_pageUrl,
                            post.field, function(res){
                                if (res.code === 200) {
                                    Home.event.closeDialog(that);

                                    /**
                                     * 当前这个页面，也需要写成是否重载
                                     * 支持哪种重载方式，父页面 自身，还是其他。
                                     */
                                    if ($(that).data('reload')) {
                                        location.reload();
                                    }

                                    layer.msg(res.msg);
                                } else {
                                    layer.msg(res.msg,'error');
                                }

                            }, 'json');

                        return false;
                    })
                }
            }
        })
    })

    // 监听ajax操作
    $(document).on("click","*[lay-ajax]",function(obj) {

        let clickthis = $(this),config = {
            url : clickthis.attr('data-url')|| "undefined",
            type :  clickthis.data('type') || 'post',
            dataType :  clickthis.data('dataType') || 'json',
            timeout :  clickthis.data('timeout') || '6000',
            tableId :  clickthis.data('table') || clickthis.data('batch'),
            reload :  clickthis.data('reload'),
            jump :  clickthis.data('jump') || false,
            confirm :  clickthis.data('confirm'),
        }, defer = $.Deferred();

        // 定义初始化对象
        let data = {}
            // 获取拼接参数
            , packet = clickthis.attr("data-data") || null
            , object = clickthis.attr("data-object") || undefined;

        if (config.confirm !== undefined) {
            config.confirm = config.confirm || '确定执行此操作吗?';
            layer.confirm(config.confirm, function(index){
                runAjax(config);
                layer.close(index);
            },function(index){
                layer.close(index);
                return false;
            })
        }

        // 传递类数据
        if (typeof object !== "undefined") {
            object = object.split(',');
            for (let i = 0; i < object.length; i++) {
                let ele = object[i].split(":");
                let val = $('.'+ele[1]).val();
                data[ele[0]] = val;
            }
        }

        // 传递对象数据
        if (packet !== 'null') {
            packet = new Function("return "+packet)();
            data = $.extend({},data,packet);
        }

        // 传递input表单数据
        let input = clickthis.data('input') || undefined;
        if (typeof input !== undefined) {
            let attribute = layui.$('.'+input).val();
        }

        // 回调函数
        let runAjax = function(config) {
            // 执行AJAX操作
            $.ajax({
                url: config.url,
                type: config.type,
                dataType: config.dataType,
                timeout: config.timeout,
                data: data,
                // 需要支持跨域访问
                xhrFields: {
                    withCredentials: true
                },
                crossDomain: true,
                success: function(res) {
                    if (res.code === 200) {
                        layer.msg(res.msg);

                        if (typeof res.data.text !== 'undefined') {
                            $(clickthis).text(res.data.text);
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
                        layer.msg(res.msg,'error');
                    }
                },
                error: function(res) {
                    layer.msg('Access methods failure','error');
                }
            })
        }

        if (!config.confirm) {
            runAjax(config);
        }
    })
})
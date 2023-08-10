/*!
 * 文件管理组件
 * by meystack to www.swiftadmin.net
 * Apache2.0 Licensed
 */
layui.define(['laypage','dropdown'], function (exports) {
    "use strict";

    var $ = layui.$

        , laypage = layui.laypage
        , dropdown = layui.dropdown
        //模块名
        , MOD_NAME = 'fileManager'
        , MOD_INDEX = 'layui_' + MOD_NAME + '_index' //模块索引名
        , TAG_NAME = undefined
        //外部接口
        , MODULE_FILE_NAME = {
            config: {}
            , index: layui[MOD_NAME] ? (layui[MOD_NAME].index + 10000) : 0

            //设置全局项
            , set: function (options) {
                var that = this;
                that.config = $.extend({}, that.config, options);
                return that;
            }

            //事件
            , on: function (events, callback) {
                return layui.onevent.call(this, MOD_NAME, events, callback);
            }
        }

        //操作当前实例
        , thisTags = function () {
            var that = this
                , options = that.config
                , id = options.id || that.index;

            thisTags.that[id] = that; //记录当前实例对象

            return {
                config: options
                //重置实例
                , reload: function (options) {
                    that.reload.call(that, options);
                }
            }
        }

        //字符常量
        , STR_ELEM = 'layui-MODULE_FILE_NAME', STR_HIDE = 'layui-hide', STR_DISABLED = 'layui-disabled',
        STR_NONE = 'layui-none'
        , LAY_TAG_THIS = 'layui-tag-this', LAY_TAGS_RESULT = 'layui-tags-ajax'

        //主模板
        , TPL_MAIN = [''].join('')

        , TPL_NONE = [
            '<div style="text-align: center;margin-top:30px ">',
            "<svg style=\"margin-top: 10px\" width=\"64\" height=\"41\" viewBox=\"0 0 64 41\"><g transform=\"translate(0 1)\" fill=\"none\" fill-rule=\"evenodd\"><ellipse class=\"ant-empty-img-simple-ellipse\" fill=\"#F5F5F5\" cx=\"32\" cy=\"33\" rx=\"32\" ry=\"7\"></ellipse><g class=\"ant-empty-img-simple-g\" fill-rule=\"nonzero\" stroke=\"#D9D9D9\"><path d=\"M55 12.76L44.854 1.258C44.367.474 43.656 0 42.907 0H21.093c-.749 0-1.46.474-1.947 1.257L9 12.761V22h46v-9.24z\"></path><path d=\"M41.613 15.931c0-1.605.994-2.93 2.227-2.931H55v18.137C55 33.26 53.68 35 52.05 35h-40.1C10.32 35 9 33.259 9 31.137V13h11.16c1.233 0 2.227 1.323 2.227 2.928v.022c0 1.605 1.005 2.901 2.237 2.901h14.752c1.232 0 2.237-1.308 2.237-2.913v-.007z\" fill=\"#FAFAFA\" class=\"ant-empty-img-simple-path\"></path></g></g></svg>",
            '<div style="text-align: center; color: #d0cdcd">暂无数据</div>',
            '</div>',
        ].join('')

        //构造器
        , Class = function (options) {
            var that = this;
            that.index = ++MODULE_FILE_NAME.index;
            that.config = $.extend({}, that.config, MODULE_FILE_NAME.config, options);
            that.render();
        };

    //默认配置
    Class.prototype.config = {
        url: undefined,         // ajax的URL地址
        choose: false,          // 是否可选择文件
        limit: 30,              // 限定数量
        thumb: {
            width: 90,         // 宽度
            height: 90         // 高度
        },
        icons: [{                // 图标
            suffix: ['ppt', 'pptx'],
            icon: 'ppt'
        }, {
            suffix: ['doc', 'docx'],
            icon: 'doc'
        }, {
            suffix: ['xls', 'xlsx'],
            icon: 'xls'
        }, {
            suffix: ['pdf'],
            icon: 'pdf'
        }, {
            suffix: ['html', 'htm'],
            icon: 'htm'
        }, {
            suffix: ['txt'],
            icon: 'txt'
        }, {
            suffix: ['swf', 'docx'],
            icon: 'flash'
        }, {
            suffix: ['zip', 'rar', '7z'],
            icon: 'zip'
        }, {
            suffix: ['mp3', 'wav'],
            icon: 'mp3'
        }, {
            suffix: ['mp4', '3gp', 'rmvb', 'avi', 'flv'],
            icon: 'mp4'
        }, {
            suffix: ['psd'],
            icon: 'psd'
        }, {
            suffix: ['ttf'],
            icon: 'ttf'
        }, {
            suffix: ['apk'],
            icon: 'apk'
        }, {
            suffix: ['exe'],
            icon: 'exe'
        }, {
            suffix: ['torrent'],
            icon: 'bt'
        }, {
            suffix: ['gif', 'png', 'jpeg', 'jpg', 'bmp'],
            icon: 'img'
        }],
    };

    // 重载实例
    Class.prototype.reload = function (options) {
        var that = this;

        // 防止数组深度合并
        layui.each(options, function (key, item) {
            if (layui._typeof(item) === 'array') delete that.config[key];
        });

        that.config = $.extend(true, {}, that.config, options);
        that.render();
    };

    //渲染
    Class.prototype.render = function () {
        var that = this
            , options = that.config;

        // 获取当前元素
        var othis = options.elem = $(options.elem);
        if (!othis[0]) return;

        this.getData();
        that.events();
    };

    // 获取请求的数据
    Class.prototype.getData = function (page = 1, type = '', filename = '') {

        let that = this,
            options = this.config;

        $.ajax({
            url: options.url,
            type: 'post',
            data: {
                page: page,
                type: type,
                filename: filename,
                limit: options.limit
            },
            success: function (res) {
                if (res.code === 200) {
                    that.renderData(res);
                } else {
                    layer.error(res.msg);
                }
            },
            error: function (err) {
                layer.error('请求失败');
            }
        })
    };

    // 渲染请求的数据
    Class.prototype.renderData = function (data) {
        let that = this, options = this.config;
        let html = '';

        for (let index = 0; index < data.data.length; index++) {
            const element = data.data[index];
            html += '<div class="file-list-item" data-index="' + element.id + '" data-url="' + element.url + '">';
            html += '<div class="intro">';
            html += '<div style="height: ' + options.thumb.height + 'px">';
            html += '<img class="icon" data-ext="' + element.extension + '" ';
            if (element.mimetype && element.mimetype.indexOf('image') !== -1) {
                html += 'src="' + element.url + '" ';
                html += 'alt="' + element.filename + '" width="' + options.thumb.width + '" height="' + options.thumb.height + '" />';
            } else {
                html += 'src="' + that.fileExt(element.extension) + '" style="padding-top: 15px"';
                html += 'alt="' + element.filename + '" width="50" height="50" />';
            }

            html += '</div>';
            html += '<span class="name" >' + element.filename + '</span>';
            html += '</div>';
            html += '</div>';
        }

        options.elem.find('#files-content').html(html || TPL_NONE);
        $(options.elem).find('.file-list-item').each(function (index, item) {

            let data = [
                {
                    title: '下载'
                    , type: 'down'
                    , id: $(item).data('url')
                },
                {
                    title: '编辑'
                    , type: 'edit'
                    , id: $(item).data('index')
                }
                , {
                    title: '<span style="color: red; ">删除</span>'
                    , type: 'delete'
                    , id: $(item).data('index')
                }];

            if (options.choose) {
                data.unshift({
                    title: '选择',
                    type: 'choose',
                    id: item
                });
            }

            dropdown.render({
                elem: item
                , trigger: 'contextmenu'
                , id: 'file' + index
                , show: true
                , data: data
                , click: function (obj) {

                    let fileEl = '#files-ajax',
                        elem = '<div id="files-ajax" lay-open data-url="' + options.url + 'edit?id=' + obj.id + '" data-title="编辑附件" data-area="520px,398px">';

                    if (obj.type === 'down') {
                        window.open(obj.id);
                    } else if (obj.type === 'choose') {
                        that.choose(obj.id);
                    } else {
                        if (obj.type === 'delete') {
                            elem = '<div id="files-ajax" lay-ajax data-url="' + options.url + 'del?id=' + obj.id + '">';
                        }
                        $(options.elem).append(elem);
                        $(fileEl).trigger('click');
                        obj.type === 'delete' && that.reload();
                    }
                    $(fileEl).remove();
                }
            });
        })

        if (!options.elem.find('#files-page>div').length) {
            laypage.render({
                elem: options.elem.find('#files-page'),
                count: data.count,
                limit: options.limit,
                jump: function (obj, first) {
                    if (!first) {
                        that.getData(obj.curr, $('.layui-tab-title li.layui-this').data('type'), $('#filename').val());
                    }
                }
            });
        }
    }

    // 文件扩展名
    Class.prototype.fileExt = function (ext) {
        let type = 'file';
        let icons = this.config.icons;
        for (let i = 0; i < icons.length; i++) {
            icons[i].suffix.find(function (item) {
                if (ext.toLowerCase() === item) {
                    type = icons[i].icon;
                }
            })
        }
        return layui.cache.base + 'fileManager/ico/' + type + '.png';
    }

    //事件
    Class.prototype.events = function () {
        var that = this
            , options = that.config;

        $(options.elem).on('click', '.layui-tab-title li', function (res) {
            let type = $(this).data('type');
            $(options.elem).find('#files-page').html('');
            that.getData(1, type, $('#filename').val());
        });

        $('#file-search').click(function (e) {
            let filename = $('#filename').val();
            $(options.elem).find('#files-page').html('');
            that.getData(1, options.type, filename);
        })

        // 选择回显
        $(options.elem).on('dblclick', '.file-list-item', function (e) {
            if (options.choose) {
                that.choose(this);
            }
        })
    };

    // 选择文件
    Class.prototype.choose = function (obj) {

        let url = $(obj).data('url'),
            field = $('#_fileFiled').val(),
            type = $('#_fileType').val();
        if (!field || !type) {
            return layer.msg('请先配置选择参数');
        }

        if (type === 'images') {
            let o = parent.layui.$('img.' + field);
            o.prop('src', url);
            o.parent('div').removeClass('layui-hide');
            parent.layui.$(o).parents('.layui-upload-drag').find('p,i,hr').addClass('layui-hide');
            parent.layui.$('input.' + field).val(url);
        } else if (type === 'multiple') {
            let boxList = parent.layui.$('[lay-choose="' + field + '"]').parents('.layui-imagesbox').find('.layui-input-inline');
            let length = boxList.length;
            $(boxList).each(function (i, item) {
                $(item).find('input.layui-hide').prop('name', field + '[' + i + '][src]');
                $(item).find('input.layui-input').prop('name', field + '[' + i + '][title]');
            })
            let html = '<div class="layui-input-inline layui-uplpad-image">';
            html += '<img src="' + url + '" >';
            html += '<input type="text" name="' + field + '[' + (length - 1) + '][src]" class="layui-hide" value="' + url + '">';
            html += '<input type="text" name="' + field + '[' + (length - 1) + '][title]" class="layui-input" placeholder="图片简介">';
            html += '<span class="layui-badge layui-badge-red" onclick="layui.$(this).parent().remove();">删除</span></div>';
            let elem = parent.layui.$('[lay-upload="' + field + '"]');
            parent.layui.$(elem).parent().before(html);
            parent.layui.$(elem).parents('form').find('input#' + field + '_clear').remove();
        } else {
            parent.layui.$('input.' + field).val(url);
        }
        parent.layer.close($('#_fileChoose').data('index'));
    }

    //记录所有实例
    thisTags.that = {};

    //获取当前实例对象
    thisTags.getThis = function (id) {
        var that = thisTags.that[id];
        if (!that) hint.error(id ? (MOD_NAME + ' instance with ID \'' + id + '\' not found') : 'ID argument required');
        return that
    };

    //重载实例
    MODULE_FILE_NAME.reload = function (id, options) {
        var that = thisTags.that[id];
        that.reload(options);

        return thisTags.call(that);
    };

    //核心入口
    MODULE_FILE_NAME.render = function (options) {
        var inst = new Class(options);
        return thisTags.call(inst);
    };

    $('body').append('<style>.file-list-item{position:relative;display:inline-block;vertical-align:top;padding:8px 9px;margin:8px 0;cursor:pointer;}.file-list-item:hover{background-color:#F8F8F8;}.file-list-item .icon{overflow:hidden;margin:0 auto;display:block;}.file-list-item .name{width:90px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#333;font-size:12px;text-align:center;margin-top:12px;display:block;}</style>');
    exports(MOD_NAME, MODULE_FILE_NAME);
})
  
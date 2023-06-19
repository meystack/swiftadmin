/* 插件管理模块 */
layui.define(['i18n'], function (exports) {

    var $ = layui.jquery;
    var table = layui.table;
    var i18n = layui.i18n;
    var form = layui.form;
    var notice = layui.notice;

    i18n.render(layui.admin.getStorage('language') || 'zh-CN');
    var area = [$(window).width() > 800 ? '660px' : '85%', $(window).height() > 800 ? '680px' : '85%'];

    var plugin = {
        apiUrl: _global_.api,
        baseUrl: _global_.app,
        request: function (name, version, url) {

            let index = layer.load();
            let token = layui.admin.getStorage('api_cross_token') || null;
            if (token == null || token === 'undefined') {
                layer.close(index);
                plugin.login();
                return false;
            }

            $.post(plugin.baseUrl + url, {
                name: name,
                version: version,
                token: token,
            }, function (res) {

                let data;
                layer.closeAll();
                if (res.code === 200) {

                    layer.msg(res.msg);
                    let index = layui.sessionData('api_install_index').index,
                        elems = $('tr[data-index="' + index + '"]');
                    if (url.indexOf('install') !== -1) {
                        let c = '', h = '<input type="checkbox" lay-filter="pluginStatus" data-url="';
                        h += plugin.baseUrl + '/system/Plugin/status" value="' + name + '" lay-skin="switch" checked="">';
                        $(elems).find('[data-field="status"]').children('div').html(h);
                        data = res.data || [];
                        if (data.config) {
                            c += '<a class="layui-table-text" data-title="' + i18n.prop('配置插件') + '"'
                            c += 'data-area="' + data.area + '" data-maxmin="true"';
                            c += 'data-url="' + plugin.baseUrl + '/system/Plugin/config?name=' + name + '" '
                            c += 'lay-event="edit">' + i18n.prop('配置') + '</a>';
                            c += '<div class="layui-divider layui-divider-vertical"></div> ';
                        }
                        c += '<a class="layui-table-text uninstall" style="color:red"'
                        c += 'data-url="' + plugin.baseUrl + '/system/Plugin/uninstall/?name=' + name + '">' + i18n.prop('卸载') + '</a> ';
                        $(elems).find('td:last').children('div').html(c);
                        window.plugins[name] = res.data;
                    } else {
                        elems.find('.layui-upgrade-elem').remove();
                        elems.find('.layui-form-switch').addClass('bubble');
                        elems.find('.layui-form-switch').trigger('click', ['stopPropagation']);
                    }

                    form.render();
                    top.layui.admin.reloadLayout();

                } else {
                    notice.error({
                        message: res.msg,
                    })
                    // 登录超时
                    if (res.code === -101) {
                        plugin.login();
                        return false;
                    }

                    // 付费插件
                    if (res.code === -102) {
                        plugin.pay(res.data);
                        return false;
                    }
                }

            }, 'json');
        }
        ,upgrade: function (data, token, url) {
            let html = '<blockquote class="layui-elem-quote layui-elem-upgrade">温馨提示<br/>';
            html += '<span class="upgrade-title">确认升级 《' + data.pluginName + '》 '+data.v+' 版本吗？</span><br/>';
            html += '1、请务必做好服务器代码和数据库备份<br/>';
            html += '2、升级后如出现冗余数据，请根据需要移除即可<br/>';
            html += '3、请勿跨版本升级，如必要请参考插件使用文档<br/>';
            html += '4、已部署完成的插件，请确保服务器Web权限可读写<br/>';
            html += '5、生产环境下更新维护插件，请勿在流量高峰期操作<br/>';
            html += '</blockquote>';
            var confirm = layer.confirm(html, {
                title: i18n.prop('更新提示'),
            }, function () {
                layer.close(confirm);
                plugin.request(data.name, data.v, plugin.getUrl('Plugin', 'upgrade'));
            }, function () {});
        }
        , login: function () {
            layer.open({
                type: 1,
                title: '登录',
                shadeClose: true,
                area: ['500px', '350px'],
                content: plugin.getHtml(),
                success: function (index, layero) {
                    form.on('submit(login)', function (data) {
                        $.post(plugin.apiUrl + '/user/login',

                            data.field, function (res) {
                                if (res.code === 200) {
                                    layui.admin.setStorage('api_cross_token', res.data.token);
                                    layer.closeAll();
                                    plugin.againClick();
                                } else {
                                    notice.error({
                                        message: res.msg,
                                    })
                                }
                            }, 'json')

                        return false;
                    })
                }
            })
        }
        , clearLogin: function () {
            layer.msg('清除登录信息成功');
            layui.admin.setStorage('api_cross_token', null);
        }
        , pay: function (data) {
            layer.open({
                type: 2,
                title: i18n.prop('立即支付'),
                area: ['500px','550px'],
                offset: "30px",
                resize: false,
                shade: 0.8,
                shadeClose: true,
                content: data.pay_url,
                success: function (index, layero) {
                    // 父类消息监听
                    window.onmessage = function (res) {
                        let data = res.data;
                        if (res.data !== null && data.code === 200) {
                            layer.close(layero);
                            plugin.againClick();
                        }
                    }
                }
            });
        }
        , againClick: function () {
            try {

                var index = layui.sessionData('api_install_index').index,
                    install = $('tr[data-index="' + index + '"]').children().find('.install');
                if (install.length <= 0) {
                    install = $('[layui-value="' + index + '"]');
                }
                if (install && index != null) {
                    $(install).trigger("click");
                }

            } catch (error) {
                console.log(error);
            }
        }
        , uninstall: function (name, tables) {
            let appURL = plugin.baseUrl;
            $.post(appURL + plugin.getUrl('Plugin', 'uninstall'), {
                name: name,
                tables: tables,
            }, function (res) {

                if (res.code === 200) {
                    layer.msg(res.msg);
                    let index = layui.sessionData('api_install_index').index,
                        elems = $('tr[data-index="' + index + '"]');
                    $(elems).find('[data-field="status"]').children('div').html('--');
                    let html = '<a class="layui-table-text install" data-url="' + appURL;
                    html += '/system/Plugin/install/name/' + name + '">' + i18n.prop('安装') + '</a>';
                    let plugin = table.cache['lay-tableList'][index];
                    if (typeof plugin.demourl != 'undefined') {
                        html += '<div class="layui-divider layui-divider-vertical"></div>';
                        html += '<a class="layui-table-text" target="_blank" href="';
                        html += plugin.demourl + '">' + i18n.prop('演示') + '</a>';
                    }
                    delete window.plugins[name];
                    $(elems).find('td:last').children('div').html(html);
                } else {
                    notice.error({
                        message: res.msg,
                    })
                }

                layer.close(window.unIndex);
            }, 'json');
        }
        , getUrl(type, action) {
            return '/system/' + type + '/' + action;
        }
        , getTableData: function (that) {

            if (that == null || that === 'undefined') {
                return false;
            }

            var index = $(that).parents('tr').attr('data-index');
            index = table.cache['lay-tableList'][index];
            return index;
        }
        , getHtml: function () {
            var html = '<form class="layui-form layui-form-fixed" style="padding-right:15px;" >';
            html += '<blockquote class="layui-elem-quote layui-elem-plugin">';
            html += '</blockquote><div style="height:20px;"></div>';
            html += '<div class="layui-form-item">';
            html += '<label class="layui-form-label">用户邮箱</label>';
            html += '<div class="layui-input-block">';
            html += '<input type="text" name="nickname" style="width:330px;" lay-verify="required" placeholder="请输入邮箱或用户名" autocomplete="off" class="layui-input" >';
            html += '</div></div>';
            html += '<div class="layui-form-item"><label class="layui-form-label">密码</label>';
            html += '<div class="layui-input-block">';
            html += '<input type="password" name="pwd" style="width:330px;" lay-verify="required" placeholder="请输入密码" class="layui-input">';
            html += '</div></div>';
            html += '<div class="layui-form-item" style="margin-top: 22px;text-align: center;">';
            html += '<a class="layui-btn layui-btn-primary" href="http://www.swiftadmin.net/user/register" target="_blank">注册</a>';
            html += '<button type="submit" class="layui-btn layui-btn-normal" lay-submit lay-filter="login">登录</button>';
            html += '</div></form> ';
            return html;
        },
    };

    /**
     * 查询插件信息
     */
    $('.layui-plugin-select').click(function () {

        var that = $(this);

        if (that.hasClass('active') && !that.hasClass('first')) {
            that.removeClass('active');
            that.siblings('span.first').addClass('active');
        } else {
            that.siblings('.active').removeClass('active');
            that.addClass('active');
        }

        var data = {}, elem = $('.active');
        elem.each(function (e, n) {
            var value = $(n).attr('data-value') || ''
                , type = $(n).parent().attr('name');
            data[type] = value;
        })

        var b = ['type', 'pay', 'label'];

        for (let i in b) {
            if (!data[b[i]]) {
                data[b[i]] = '';
            }
        }

        table.reload('lay-tableList', {
            where: data,
            url: plugin.apiUrl + "plugin/index",
        });
    })

    /**
     * 安装插件
     * @param that
     * @returns {boolean}
     */
    $(document).on("click", ".install", function () {
        let name = plugin.getTableData(this)['name'];
        layui.sessionData('api_install_index', {
            key: 'index',
            value: plugin.getTableData(this)['LAY_INDEX'],
        });
        plugin.request(name, null, plugin.getUrl('Plugin', 'install'));
    })

    /**
     * 更改插件状态
     * @param that
     * @returns {boolean}
     */
    form.on('switch(pluginStatus)', function (obj) {

        let objs = {
            id: $(this).attr('value'),
            status: obj.elem.checked ? 1 : 0
        };

        let that = $(this), options = {
            error: function (res) {
                $(obj.elem).prop('checked', !obj.elem.checked);
                form.render('checkbox');
                notice.error({
                    message: res.msg,
                })
            },
            success: function (res) {
                layer.msg(res.msg);
                if (typeof window.plugins[objs.id] !== 'undefined') {
                    window.plugins[objs.id].status = objs.status;
                }
                top.layui.admin.reloadLayout();
            }
        };

        let bubble = $('.bubble');
        if (bubble.length) {
            $(bubble).removeClass('bubble');
            return false;
        }

        layui.admin.event.request(that, objs, options);
    });

    /**
     * 卸载插件
     * @param that
     * @returns {boolean}
     */
    $(document).on("click", ".uninstall", function (obj) {

        var name = plugin.getTableData(this)['name'];
        var html = '<form class="layui-form layui-form-fixed" style="padding-right:15px;background: #f2f2f2;" >';
        html += '<blockquote class="layui-elem-quote layui-elem-uninstall">温馨提示<br/>';
        html += '确认卸载 《 ' + name + ' 》 吗？<br/>';
        html += '1、卸载前请自行备份插件数据库 ！<br/>';
        html += '2、插件文件及数据库表删除后无法找回 ！<br/>';
        html += '3、插件如已被二次开发，请自行清除冗余文件！<br/>';
        html += '</blockquote>';
        html += '<div class="layui-form-item">';
        html += '<div class="layui-input-inline" style="padding-left: 5px;">';
        html += '<div class="layui-plugin-tables layui-badge-red"></div></div></div>';
        html += '<div class="layui-footer" style="margin-top: 22px;text-align: center;">';
        html += '<button type="submit" class="layui-btn layui-btn-normal" lay-submit lay-filter="start">确定</button>';
        html += '<button type="button" class="layui-btn layui-btn-primary" sa-event="closeDialog" >关闭</button>';
        html += '</div></form> ';
        layui.sessionData('api_install_index', {
            key: 'index',
            value: plugin.getTableData(this)['LAY_INDEX'],
        });
        layer.open({
            type: 1,
            title: i18n.prop('卸载插件'),
            shadeClose: true,
            area: ['380px', '300px'],
            content: html,
            success: function (index, layero) {
                /**
                 * 请求服务器
                 * @param 卸载数据表
                 */
                form.on('submit(start)', function (data) {
                    let tables = [];
                    let lists = $('.layui-plugin-tables').children('span');
                    lists.each(function (e, n) {
                        if (e >= 1) {
                            tables.push($(n).html());
                        }
                    })
                    window.unIndex = layer.load();
                    layer.close(layero);
                    plugin.uninstall(name, tables);
                    return false;
                })
            }
        })
    })

    /**
     * 重载本地插件
     * @param that
     */
    $('#pluginInstall').click(function (res) {
        table.reload('lay-tableList', {
            url: plugin.baseUrl + '/system/Plugin/index',
        });
    })

    /**
     * 更新插件缓存
     * @param that
     */
    $('#pluginCache').click(function (res) {
        var confirm = layer.confirm('确定要更新缓存吗？', {
            title: '更新提示'
        }, function () {
            $.get(plugin.baseUrl + plugin.getUrl('Admin', 'clear?type=plugin'), {}, function (res) {
                if (res.code === 200) {
                    layer.msg(res.msg);
                    layer.close(confirm);
                } else {
                    notice.error({
                        message: res.msg,
                    })
                }
            })
        });
    })

    /**
     * 插件幻灯片
     */
    $('body').on('click', '.layui-icon-picture', function (res) {

        let index = plugin.getTableData(this), data = [];
        if (typeof index.album !== 'undefined' && index.album.length) {
            for (let i in index.album) {
                data.push({
                    src: index.album[i].src,
                    thumb: index.album[i].src,
                })
            }
        }

        layer.photos({
            photos: {data: data},
            shadeClose: true,
            closeBtn: 2,
            anim: 10
        })
    })

    exports('plugin', plugin);
});
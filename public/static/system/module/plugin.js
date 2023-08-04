/* 插件管理模块 */
layui.define(['admin','show'], function (exports) {

    let $ = layui.jquery;
    let table = layui.table;
    let form = layui.form;
    let admin = layui.admin;
    let show = layui.show;

    let plugin = {
        apiUrl: app_Config.api,
        baseUrl: app_Config.app,
        data:{}, // 插件数据
        request: function (name, version, url) {

            let index = layer.load();
            let token = admin.getStorage('api_cross_token') || null;
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
                layer.closeAll();
                if (res.code === 200) {
                    show.msg(res.msg);
                    window.plugins[name] = res.data;
                    table.reloadData('lay-tableList');
                    top.layui.admin.reloadLayout();
                } else {
                    show.error(res.msg);
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
        , upgrade: function (data, token, url) {
            let html = '<blockquote class="layui-elem-quote layui-elem-upgrade">温馨提示<br/>';
            html += '<span class="upgrade-title">确认升级 《' + data.pluginName + '》 ' + data.version + ' 版本吗？</span><br/>';
            html += '1、请务必做好服务器代码和数据库备份<br/>';
            html += '2、升级后如出现冗余数据，请根据需要移除即可<br/>';
            html += '3、请勿跨版本升级，如必要请参考插件使用文档<br/>';
            html += '4、已部署完成的插件，请确保服务器Web权限可读写<br/>';
            html += '5、生产环境下更新维护插件，请勿在流量高峰期操作<br/>';
            html += '</blockquote>';
            let confirm = layer.confirm(html, {
                title: '更新提示',
            }, function () {
                layer.close(confirm);
                plugin.request(data.name, data.version, plugin.getUrl('Plugin', 'upgrade'));
            }, function () {
            });
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
                                    admin.setStorage('api_cross_token', res.data.token);
                                    layer.closeAll();
                                    plugin.againClick();
                                } else {
                                    show.error(res.msg);
                                }
                            }, 'json')

                        return false;
                    })
                }
            })
        }
        , clearLogin: function () {
            layer.msg('清除登录信息成功');
            admin.setStorage('api_cross_token', null);
        }
        , pay: function (data) {
            layer.open({
                type: 2,
                title: '立即支付',
                area: ['500px', '550px'],
                offset: "30px",
                resize: false,
                shade: 0.8,
                shadeClose: true,
                content: data.pay_url,
                success: function (index, layero) {
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
            if (plugin.data == null || plugin.data === 'undefined') {
                return false;
            }
            plugin.request(plugin.data.name, plugin.data.version, plugin.getUrl('Plugin', 'install'));
        }
        , install(name, version) {
            plugin.data = {name: name, version: version};
            plugin.request(name, version, plugin.getUrl('Plugin', 'install'));
        }
        , uninstall: function (name, tables) {
            let appURL = plugin.baseUrl;
            $.post(appURL + plugin.getUrl('Plugin', 'uninstall'), {
                name: name,
                tables: tables,
            }, function (res) {
                if (res.code === 200) {
                    show.msg(res.msg);
                    delete window.plugins[name];
                    table.reloadData('lay-tableList');
                } else {
                    show.error(res.msg);
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

            let index = $(that).parents('tr').attr('data-index');
            index = table.cache['lay-tableList'][index];
            return index;
        }
        , getHtml: function () {
            let html = '<form class="layui-form layui-form-fixed" style="padding-right:15px;" >';
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

        let that = $(this);

        if (that.hasClass('active') && !that.hasClass('first')) {
            that.removeClass('active');
            that.siblings('span.first').addClass('active');
        } else {
            that.siblings('.active').removeClass('active');
            that.addClass('active');
        }

        let data = {}, elem = $('.active');
        elem.each(function (e, n) {
            let type = $(n).parent().attr('name');
            data[type] = $(n).attr('data-value') || '';
        })

        let b = ['type', 'pay', 'label'];

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
        let version = plugin.getTableData(this)['version'];
        plugin.install(name, version);
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
                show.msg(res.msg);
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

        admin.event.request(that, objs, options);
    });

    /**
     * 卸载插件
     * @param that
     * @returns {boolean}
     */
    $(document).on("click", ".uninstall", function (obj) {

        let name = plugin.getTableData(this)['name'];
        let html = '<form class="layui-form layui-form-fixed" style="padding-right:15px;background: #f2f2f2;" >';
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

        layer.open({
            type: 1,
            title: '卸载插件',
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
        table.reloadData('lay-tableList', {
            url: plugin.baseUrl + '/system/Plugin/index',
        });
    })

    /**
     * 更新插件缓存
     * @param that
     */
    $('#pluginCache').click(function (res) {
        let confirm = layer.confirm('确定要更新缓存吗？', {
            title: '更新提示'
        }, function () {
            $.get(plugin.baseUrl + plugin.getUrl('Admin', 'clear?type=plugin'), {}, function (res) {
                if (res.code === 200) {
                    layer.msg(res.msg);
                    layer.close(confirm);
                } else {
                    show.error(res.msg);
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
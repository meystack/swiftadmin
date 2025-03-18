!function () {
    "use strict";
    var a = tinymce.util.Tools.resolve("tinymce.PluginManager"), t = "图片本地化", e = function (a) {
        var e = function () {
            const t = a.getDoc().getElementsByTagName("img");
            t.length >= 1 ? void 0 !== a.settings.download ? function (a, t, e) {
                var i = [], l = location.origin;
                if (a.length >= 1) {
                    for (var n in a) {
                        var o = a[n].src;
                        o && -1 !== (o = o.replace(l, "")).indexOf("://") && i.push(a[n])
                    }
                    if (i.length >= 1) {
                        var r = 0, c = layui.layer.load(), s = function (a) {
                            var e = new FormData;
                            e.append("url", a), layui.$.ajax({
                                url: t,
                                type: "post",
                                data: e,
                                contentType: !1,
                                processData: !1,
                                dataType: "json",
                                success: function (a) {
                                    200 === a.code ? (layui.$(i[r]).attr("src", a.url), layui.$(i[r]).attr("data-mce-src", a.url), layui.$(i[r]).each((function () {
                                        try {
                                            layui.$.each(this.attributes, (function () {
                                                -1 === ["src", "alt", "width", "height", "data-mce-src"].lastIndexOf(this.name) && $(i[r]).removeAttr(this.name)
                                            }))
                                        } catch (a) {
                                        }
                                    })), (r += 1) >= i.length ? (layui.layer.close(c), layui.layer.msg("远程同步已完成")) : s(i[r].src)) : (layui.layer.close(c), layui.layer.msg(a.msg))
                                },
                                error: function () {
                                    layui.layer.close(c), layui.layer.msg("请求上传接口出现异常")
                                }
                            })
                        };
                        s(i[r].src)
                    } else layui.layer.msg("无需同步或已完成");
                    e.focus()
                }
            }(t, a.settings.download, a) : layui.layer.msg("请配置前端同步地址") : layui.layer.msg("暂无图片需要同步")
        };
        a.ui.registry.addButton("imagelocal", {
            icon: "imglocal",
            tooltip: t,
            onAction: e
        }), a.ui.registry.addMenuItem("imagelocal", {icon: "imglocal", text: t, onAction: e})
    };
    a.add("imagelocal", (function (a) {
        return e(a), {}
    }))
}();
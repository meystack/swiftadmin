/**
 * Copyright meystack
 * Licensed Apache2.0
 * Version: 0.0.1 图片本地化
 */
!function () {
    "use strict";
    var i = tinymce.util.Tools.resolve("tinymce.PluginManager"), o = "图片本地化";
    i.add("imagelocal", (function (i) {
        return function (i) {
            var t = function () {
                const o = i.getDoc().getElementsByTagName("img");
                o.length >= 1 ? layui.upload.local(o, _global_.app + "/Ajax/getImage", i) : layui.layer.info("暂无图片需要同步")
            };
            i.ui.registry.addButton("imagelocal", {
                icon: "imglocal",
                tooltip: o,
                onAction: t
            }), i.ui.registry.addMenuItem("imagelocal", {icon: "imglocal", text: o, onAction: t})
        }(i), {}
    }))
}();
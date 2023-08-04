/** SwiftAdmin iframe Apache2.0 License By http://www.swiftadmin.net */

// 以下代码是配置layui扩展模块的目录，每个页面都需要引入
layui.config({
    version: 'v1.1.9',
    base: getProjectUrl() + 'module/'
}).extend({
    tags: 'tags/tags',
    i18n: 'i18n/jquery.i18n.properties',
    cascader: 'cascader/cascader',
    fileManager: 'fileManager/fileManager',
    iconPicker: 'iconPicker/iconPicker',
    echarts: "echarts/echarts",
    xmselect: 'xmselect/xmselect',
    tableSelect: 'tableSelect/tableSelect',
    formDesign: 'formDesign/formDesign',
    soulTable: 'soulTable/soulTable',
    tableChild: 'soulTable/tableChild',
    tableMerge: 'soulTable/tableMerge',
    tableFilter: 'soulTable/tableFilter',
    excel: 'soulTable/excel',
}).use(['admin', 'jquery', 'show'], function () {
    let $ = layui.jquery;       // jquery的
    let admin = layui.admin;    // amdin模块的
});

// 获取当前项目的根路径，通过获取layui.js全路径截取assets之前的地址
function getProjectUrl() {

    let layuiDir = layui.cache.dir;
    if (!layuiDir) {
        let js = document.scripts, last = js.length - 1, src;
        for (let i = last; i > 0; i--) {
            if (js[i].readyState === 'interactive') {
                src = js[i].src;
                break;
            }
        }
        let jsPath = src || js[last].src;
        layuiDir = jsPath.substring(0, jsPath.lastIndexOf('/') + 1);
    }

    return layuiDir.substring(0, layuiDir.indexOf('layui'));
}

layui.define(['notice'], function (exports) {
    "use strict";

    let MODULE_SHOW_NAME = {};
    let notice = layui.notice, MOD_NAME = 'show';

    // 正常消息通知
    MODULE_SHOW_NAME.msg = function (msg) {
        notice.success({
            message: msg,
        });
    }

    // 错误消息通知
    MODULE_SHOW_NAME.error = function (msg) {
        notice.error({
            message: msg,
        });
    }

    // 警告消息通知
    MODULE_SHOW_NAME.warning = function (msg) {
        notice.warning({
            message: msg,
        });
    }

    // 信息消息通知
    MODULE_SHOW_NAME.info = function (msg) {
        notice.info({
            message: msg,
        });
    }

    // 消息通知
    MODULE_SHOW_NAME.notice = function (title, msg, options = {}) {
        notice.show({
            title: title,
            message: msg,
            ...options
        });
    }

    exports(MOD_NAME, MODULE_SHOW_NAME);
})
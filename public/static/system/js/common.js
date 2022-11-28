/** SwiftAdmin iframe Apache2.0 License By http://www.swiftadmin.net */

// 以下代码是配置layui扩展模块的目录，每个页面都需要引入
layui.config({
    version: 'v1.1.5',
    base: getProjectUrl() + 'module/'
}).extend({
    tags: 'tags/tags',
    i18n: 'i18n/jquery.i18n.properties',
    cascader: 'cascader/cascader',
    fileManager: 'fileManager/fileManager',
    iconPicker: 'iconPicker/iconPicker',
    echarts: "echarts/echarts",
    xmselect: 'xmselect/xmselect',
    treetable: 'treetable/treetable',
    tableSelect: 'tableSelect/tableSelect',
    formDesign: 'formDesign/formDesign',
    soulTable: 'soulTable/soulTable',
    tableChild: 'soulTable/tableChild',
    tableMerge: 'soulTable/tableMerge',
    tableFilter: 'soulTable/tableFilter',
    excel: 'soulTable/excel',
}).use(['admin','jquery'], function () {
    var $ = layui.jquery;       // jquery的
    var admin = layui.admin;    // amdin模块的
});

// 获取当前项目的根路径，通过获取layui.js全路径截取assets之前的地址
function getProjectUrl() {

    var layuiDir = layui.cache.dir;
    if (!layuiDir) {
        var js = document.scripts, last = js.length - 1, src;
        for (var i = last; i > 0; i--) {
            if (js[i].readyState === 'interactive') {
                src = js[i].src;
                break;
            }
        }
        var jsPath = src || js[last].src;
        layuiDir = jsPath.substring(0, jsPath.lastIndexOf('/') + 1);
    }

    return layuiDir.substring(0, layuiDir.indexOf('layui'));
}
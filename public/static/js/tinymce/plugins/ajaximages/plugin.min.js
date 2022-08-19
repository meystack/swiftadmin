/**
 * Copyright meystack
 * Licensed Apache2.0
 * Version: 0.0.1 付费内容插入
 */
(function () {
    'use strict';

    var global = tinymce.util.Tools.resolve('tinymce.PluginManager');
    var pluginNama = '插入付费内容';
    var setContent = function (editor, html) {

        editor.focus();
        editor.undoManager.transact(function () {
            editor.setContent(html);
        });
        editor.selection.setCursorLocation();
        editor.nodeChanged();
    };

    var getContent = function (editor) {
        return editor.getContent({ source_view: true });
    };

    var open = function (editor) {

        var editorContent = getContent(editor);

        editor.windowManager.open({
            title: pluginNama,
            size: 'small',
            body: {
                type: 'panel',
                items: [{
                    type: 'textarea',
                    name: 'code'
                }]
            },
            buttons: [
                {
                    type: 'cancel',
                    name: 'cancel',
                    text: 'Cancel'
                },
                {
                    type: 'submit',
                    name: 'save',
                    text: 'Insert',
                    primary: true
                }
            ],
            initialData: { code: editorContent },
            onSubmit: function (api) {
                setContent(editor, api.getData().code);
                api.close();
            }
        });
    };

    // 付费内容图标
    global.ui.registry.getAll().icons.ajaximages || global.ui.registry.addIcon('ajaximages','<?xml version="1.0" encoding="UTF-8"?><svg width="18" height="18" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 14C4 12.8954 4.89543 12 6 12H42C43.1046 12 44 12.8954 44 14V40C44 41.1046 43.1046 42 42 42H6C4.89543 42 4 41.1046 4 40V14Z" fill="none" stroke="#333" stroke-width="4" stroke-linejoin="round"/><path d="M19 19L24 24L29 19" stroke="#333" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/><path d="M18 25H30" stroke="#333" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/><path d="M18 31H30" stroke="#333" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/><path d="M24 25V35" stroke="#333" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/><path d="M8 6H40" stroke="#333" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/></svg>');

    var register$1 = function (editor) {
        editor.addCommand('mceCodeEditor', function () {
            open(editor);
        });
    };

    var register = function (editor) {
        var onAction = function () {
            return editor.execCommand('mceCodeEditor');
        };
        editor.ui.registry.addButton('ajaximages', {
            icon: 'sourcecode',
            tooltip: pluginName,
            onAction: onAction
        });
        editor.ui.registry.addMenuItem('ajaximages', {
            icon: 'sourcecode',
            text: pluginName,
            onAction: onAction
        });
    };

    function Plugin () {
        global.add('code', function (editor) {
            register$1(editor);
            register(editor);
            return {};
        });
    }

    Plugin();

}());

// tinymce.PluginManager.add('ajaximages', function(editor, url) {
//     var pluginName='插入付费内容';
//     var baseURL=tinymce.baseURL;
//     var iframe1 = baseURL+'/plugins/ajaximages/map.html';
//     var ajaximages_width = function (editor) {
//         return editor.getParam('ajaximages_width', 560);
//     };
//     var ajaximages_height = function (editor) {
//         return editor.getParam('ajaximages_height', 362);
//     };
//
//     window.tinymceLng='';
//     window.tinymceLat='';
//
//     var openDialog = function() {
//         console.log(editor.windowManager)
//         return editor.windowManager.open({
//             title: pluginName,
//             size: 'small',
//             body: {
//                 type: 'panel',
//                 items: [{
//                     type: 'textarea',
//                     name: 'code'
//                 }]
//             },
//             buttons: [
//                 {
//                     type: 'cancel',
//                     text: 'Close'
//                 },
//                 {
//                     type: 'custom',
//                     text: 'Insert',
//                     name: 'save',
//                     primary: true
//                 },
//             ],
//             onAction: function (api, details) {
//                 switch (details.name) {
//                     case 'save':
//                         // html='<iframe src="'+baseURL+'/plugins/ajaximages/bd.html?center='+tinymceLng+'%2C'+tinymceLat+'&zoom=14&width='+(ajaximages_width(editor)-2)+'&height='+(ajaximages_height(editor)-2)+'" frameborder="0" style="width:'+ajaximages_width(editor)+'px;height:'+ajaximages_height(editor)+'px;">';
//
//                         var html = '123';
//                         editor.insertContent(html);
//                         api.close();
//                         break;
//                     default:
//                         break;
//                 }
//
//             }
//         });
//     };
//
//     // 付费内容
//
//
//     // 图片本地化
//     // editor.ui.registry.getAll().icons.ajaximages || editor.ui.registry.addIcon('ajaximages','<?xml version="1.0" encoding="UTF-8"?><svg width="20" height="20" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="48" height="48" fill="white" fill-opacity="0.01"/><rect x="6" y="6" width="36" height="36" rx="3" fill="none" stroke="#333" stroke-width="4" stroke-linejoin="round"/><path d="M32 28L24 36L16 28" stroke="#333" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/><path d="M24 20V35.5" stroke="#333" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/><path d="M16 14H32" stroke="#333" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/></svg>');
//
//     editor.ui.registry.addButton('ajaximages', {
//         icon: 'ajaximages',
//         tooltip: pluginName,
//         onAction: function() {
//             openDialog();
//         }
//     });
//
//
//     editor.ui.registry.addMenuItem('ajaximages', {
//         text: pluginName,
//         onAction: function() {
//             openDialog();
//         }
//     });
//     return {
//         getMetadata: function() {
//             return  {
//                 name: pluginName,
//                 url: "http://tinymce.ax-z.cn/more-plugins/ajaximages.php",
//             };
//         }
//     };
// });

// ;(function() {
//     'use strict'
//
//     // 通过 tinymce.util.Tools.resolve 加载 tinymce 的模块
//     var global = tinymce.util.Tools.resolve('tinymce.PluginManager'),
//         pluginName = 'ajaximages';
//
//
//     var register = function(editor) {
//         // 相关的业务逻辑
//         editor.addCommand('InsertHorizontalRule', function() {})
//     }
//     var Commands = { register: register }
//
//     // 主窗体
//     var openDialog = function () {
//         console.log(123)
//     }
//
//     global.ui.registry.getAll().icons.imgicon || global.ui.registry.addIcon('imgicon','<svg viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg" width="24" height="24"><path d="M670.8,733c21.1-46.1,45.6-73.7,64.5-90.5,10.3-9.2,21.1-16.8,32.5-21.7-6-8.1-10.6-15.3-15.2-22.2-8.4-12.6-17.3-27.1-21.7-34.1-28.7,15.7-55.9,48.2-77.5,77-12.5,16.7-23.8,34.7-34.7,53.7l52,37.9zM450.2,803.5c6,14.6,9.2,29.3,13.5,40.6,1.6,7.1,3.2,13.5,5.4,19.5,19.5-6,39-12,58-20.1,32.5-13.6,71-33,95.4-58.5l-45-48.2c-8.1,8.1-17.9,14.7-30.4,22.8-21.1,13.6-51.5,30.4-97,43.9zM373.8,204.1c0-52,43.4-95.4,95.4-95.4,53.7,0,94.8,43.4,94.8,95.4,0,53.6-41.2,95.4-94.8,95.4-52,0-95.4-41.7-95.4-95.4zm-105.7,0c0,20.1,2.7,39.6,8.7,58.5h-2.2c16.8,39,35.8,79.9,52,111.1l27.1,52c47.5,91.2,105.7,191.3,114.3,204.3,1.1,0,0.547,0,1.1,1.1,32.5-54.7,60.6-104.1,81.8-143.6l34.1-63.4,26.6-52c15.8-30.9,34.7-71,51.5-110.6h-1.6c6-19,8.7-38.5,8.7-58.5,0-110.6-90.5-200.5-201.1-200.5-111.1,0-201.1,90-201.1,200.5zM229.6,800.2c15.2,11.4,31.3,21.3,48.2,30.4,30.2,16,68.8,34.1,112.7,40.7,1.6-13.1,2.5-29.9,4.3-42.3l3.3-21.7c-11.9-2.2-25.6-5.6-40.1-10.8-24.5-8.8-55.8-23.8-90.5-48.2-9.2,13.5-18.6,25.7-26,35.8l-11.9,16.3zM17.2,949.8c0,41.2,33.6,74.2,74.2,74.2H932.5a74,74,0,0,0,74.2-74.2V336.9A74,74,0,0,0,932.5,262.6H728.2L692.4,362.9H894.6c9.2,0,15.7,6,15.7,15.2V511.4c-28.7-0.531-73.2,2.2-116.5,23.3l5.4,14.6,17.3,45c25.5-8.1,47.7-14.2,65-16.8l28.7-4.3V911.9c0,9.2-6.5,15.1-15.7,15.1H133.2c-9.2,0-15.7-5.9-15.7-15.1V704.8a261.1,261.1,0,0,0,15.2,14.1c9.3,8,23.3,21.1,41.2,36.3,10.3-9.2,20-21.2,28.2-29.8,4.8-5,9.2-9.8,13-14.6-42.8-35.8-86.2-86.2-97.5-100.3V378c0-8.7,6.5-15.2,15.7-15.2h53L209.5,262.5H91.4c-40.7,0-74.2,33.1-74.2,74.3V949.7z"/></svg>');
//
//     var register$1 = function(editor) {
//
//         // 按钮相关的业务逻辑
//         editor.ui.registry.addButton('ajaximages', {
//             icon: 'imgicon',
//             tooltip: pluginName,
//             onAction: function() {
//                 openDialog();
//             }
//         })
//
//         // 菜单相关逻辑
//         editor.ui.registry.addMenuItem('ajaximages', {
//             text: pluginName,
//             onAction: function() {
//                 openDialog();
//             }
//         })
//     }
//
//     var Buttons = { register: register$1 }
//
//     function Plugin() {
//         console.log(global)
//         // global 其实是 PluginManager
//         // 通过 add 把当前 hr 插件注册到 tinymce 插件中
//         global.add('ajaximages', function(editor) {
//             // 注册公共方法
//             Commands.register(editor)
//             // 注册 toolbar 的按钮
//             Buttons.register(editor)
//         })
//     }
//
//     // 入口方法
//     Plugin()
// })
/**
 * Copyright meystack
 * Licensed Apache2.0
 * Version: 0.0.1 付费内容插入
 */
(function () {
    'use strict';

    var global = tinymce.util.Tools.resolve('tinymce.PluginManager');
    var pluginName = '插入付费内容';

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
            title: pluginName,
            size: 'small',
            body: {
                type: 'panel',
                items: [{
                    type: 'textarea',
                    placeholder: '请输入付费内容',
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
                    text: 'Save',
                    primary: true
                }
            ],
            onSubmit: function (api) {
                if (api.getData().code) {
                    setContent(editor, editorContent + '[paybegin]'+ api.getData().code +'[payend]');
                }
                api.close();
            }
        });
    };

    var register$1 = function (editor) {
        editor.addCommand('payEditor', function () {
            open(editor);
        });
    };

    var register = function (editor) {
        var onAction = function () {
            return editor.execCommand('payEditor');
        };
        editor.ui.registry.addButton('paycontent', {
            icon: 'payicon',
            tooltip: pluginName,
            onAction: onAction
        });
        editor.ui.registry.addMenuItem('paycontent', {
            icon: 'payicon',
            text: pluginName,
            onAction: onAction
        });
    };

    function Plugin () {
        global.add('paycontent', function (editor) {
            register$1(editor);
            register(editor);
            return {};
        });
    }

    Plugin();

}());
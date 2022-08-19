/* 内容管理模块 */
layui.define(['jquery','form', 'layer'], function (exports) {

    var $ = layui.jquery;
    var form = layui.form;
    var layer = layui.layer;
    var content = {
        tinymce: function (elem) {
            elem = elem || 'content';
            var obj = tinymce.init({
                selector: '#' + elem,
                language: 'zh_CN',
                plugins: 'paylabel imagelocal code print preview searchreplace paste autolink directionality visualblocks visualchars fullscreen image link media template code codesample table charmap hr pagebreak nonbreaking anchor quickbars insertdatetime advlist lists wordcount textpattern emoticons autosave bdmap indent2em autoresize formatpainter axupimgs',
                toolbar: 'code undo redo restoredraft | cut copy paste pastetext table | image imagelocal axupimgs media print preview bdmap paylabel forecolor backcolor formatpainter bold italic underline strikethrough link anchor fullscreen | alignleft aligncenter alignright alignjustify outdent indent  | \
                styleselect formatselect fontselect fontsizeselect | bullist numlist | blockquote subscript superscript removeformat | \
                charmap emoticons hr pagebreak insertdatetime|  indent2em lineheight ',
                height: 650, //编辑器高度
                min_height: 400,
                max_width: 1200,
                importcss_append: true,
                relative_urls: false,
                remove_script_host: false,
                // 图片本地化
                download: _global_.app + '/Ajax/getImage',
                images_upload_handler: function (block, success, failure) {
                    var file = block.blob();
                    var reader = new FileReader();
                    reader.readAsDataURL(file);
                    reader.onload = function (e) {
                        var formData = new FormData();
                        formData.append('file', file);
                        $.ajax({
                            type: 'post',
                            url: _global_.app + '/Ajax/upload',
                            data: formData,
                            async: false,
                            cache: false,
                            contentType: false,
                            processData: false,
                            success: function (res) {
                                if (res.code === 200) {
                                    success(res.url);
                                    layer.msg(res.msg);
                                } else {
                                    failure(res.msg);
                                    return;
                                }
                            },// 请求失败触发的方法
                            error: function (XMLHttpRequest, textStatus, errorThrown) {
                                failure(textStatus);
                                return;
                            }
                        });
                    };
                },
                init_instance_callback: function (editor) {
                },
                setup: function (editor) {
                    editor.on('change', function () {
                        editor.save();
                    });
                },
                toolbar_sticky: true,
                branding: false,
                autosave_ask_before_unload: false,
            });
            return obj;
        },
        markdown: function (id, elem) {
            $(elem).hide();
            let height = $(elem).data('height') || '400px';
            let _id = 'markdown_' + Math.round(Math.random() * 36);
            $(elem).after('<div id="' + _id + '" ></div>');
            window.cherry = new Cherry({
                id: _id,
                value: $(elem).text(),
                toolbars: {
                    theme: 'light',
                    toolbar: [
                        'switchModel',
                        'undo',
                        '|',
                        'bold',
                        'italic',
                        'strikethrough',
                        '|',
                        'color',
                        'header',
                        '|',
                        'list',
                        {
                            insert: ['image','code',  'table','link', 'hr'],
                        },
                        '|',
                        // 'fullScreen',
                        'settings',
                        'export',

                    ],
                    float: false,
                    bubble : false // array or false
                },
                editor: {
                    height: height,
                    defaultModel: 'editOnly',
                },
                fileUpload(file, callback) {
                    let formData = new FormData();
                    formData.append('file', file, file.name);
                    $.ajax({
                        url: _global_.app + '/ajax/upload'
                        , type: 'post'
                        , data: formData
                        , contentType: false
                        , processData: false
                        , dataType: 'json'
                        //成功回调
                        , success: function (res) {
                            if (res.code === 200) {
                                callback(res.url);
                                layer.msg(res.msg);
                            }else {
                                layer.msg(res.msg, 'error');
                            }
                        }
                        //异常回调
                        , error: function () {
                            layer.msg('服务器异常','info');
                        }
                    });
                },
                callback: {
                    afterChange: function(){
                        $(elem).text(cherry.getMarkdown());
                    },
                    afterInit: function(){
                        /**
                         * 初始化回调
                         */
                        $('#'+_id).find('.cherry').css('min-height', height);
                    },
                }
            });
        },
        xmselect: function (elem, data, initvalue, group = true, category = false) // 下拉菜单
        {
            if (!elem) {
                layer.msg('elem error', 'error');
                return false;
            }

            if (group) {
                return xmSelect.render({
                    el: '#' + elem,
                    name: elem,
                    tips: '请选择',
                    size: 'small',
                    theme: {
                        color: '#0081ff',
                    },
                    prop: {
                        name: 'title',
                        value: 'id',
                    },
                    data: data,
                    initValue: initvalue,
                })
            } else {
                return xmSelect.render({
                    el: '#' + elem,
                    name: elem,
                    tips: '请选择',
                    height: 'auto',
                    data: data,
                    radio: true,
                    clickClose: true,
                    initValue: initvalue,
                    prop: {
                        value: 'id',
                        name: 'title'
                    },
                    tree: {
                        show: true,
                        strict: false,
                        showLine: false,
                        clickExpand: false,
                    },
                    model: {
                        icon: 'hidden',
                        label: {
                            type: 'text'
                        }
                    },
                    on: function (e) {
                        if (category && e.arr[0]) {
                            const pid = e.arr[0].id;
                            let url = location.pathname;
                            // xmSelect 无改变之前属性 RELOAD
                            url = url.substring(0, url.indexOf('?'));
                            location.href = url + '?pid=' + pid;
                        }
                    },
                    theme: {
                        color: '#1890FF'
                    }
                })
            }
        }
    };

    form.on('select(change_category)', function (e) {
        let url = location.pathname;
        url = url.substring(0, url.indexOf('?'));
        location.href = url + '?pid=' + e.value;
    })

    exports('content', content);
});
/**
 * Layui图标选择器
 * @author wujiawei0926@yeah.net
 * @version 1.1
 */

layui.define(['laypage', 'form'], function (exports) {
    "use strict";

    var IconPicker =function () {
        this.v = '1.1';
    }, _MOD = 'iconPicker',
        _this = this,
        $ = layui.jquery,
        laypage = layui.laypage,
        form = layui.form,
        BODY = 'body',
        TIPS = '请选择图标';

    /**
     * 渲染组件
     */
    IconPicker.prototype.render = function(options){
        var opts = options,
            // DOM选择器
            elem = opts.elem,
            // 数据类型：fontClass/unicode
            type = opts.type == null ? 'fontClass' : opts.type,
            // 是否分页：true/false
            page = opts.page == null ? true : opts.page,
            // 每页显示数量
            limit = opts.limit == null ? 12 : opts.limit,
            // 是否开启搜索：true/false
            search = opts.search == null ? true : opts.search,
            // 每个图标格子的宽度：'43px'或'20%'
            cellWidth = opts.cellWidth,
            // 点击回调
            click = opts.click,
            // 渲染成功后的回调
            success = opts.success,
            // json数据
            data = {},
            // 唯一标识
            tmp = new Date().getTime(),
            // 是否使用的class数据
            isFontClass = opts.type === 'fontClass',
            // 初始化时input的值
            ORIGINAL_ELEM_VALUE = $(elem).val(),
            TITLE = 'layui-select-title',
            TITLE_ID = 'layui-select-title-' + tmp,
            ICON_BODY = 'layui-iconpicker-' + tmp,
            PICKER_BODY = 'layui-iconpicker-body-' + tmp,
            PAGE_ID = 'layui-iconpicker-page-' + tmp,
            LIST_BOX = 'layui-iconpicker-list-box',
            selected = 'layui-form-selected',
            unselect = 'layui-unselect';

        var a = {
            init: function () {
                data = common.getData[type]();

                a.hideElem().createSelect().createBody().toggleSelect();
                a.preventEvent().inputListen();
                common.loadCss();
                
                if (success) {
                    success(this.successHandle());
                }

                return a;
            },
            successHandle: function(){
                var d = {
                    options: opts,
                    data: data,
                    id: tmp,
                    elem: $('#' + ICON_BODY)
                };
                return d;
            },
            /**
             * 隐藏elem
             */
            hideElem: function () {
                $(elem).hide();
                return a;
            },
            /**
             * 绘制select下拉选择框
             */
            createSelect: function () {
                var oriIcon = '<i class="layui-icon">';
                
                // 默认图标
                if(ORIGINAL_ELEM_VALUE === '') {
                    if(isFontClass) {
                        ORIGINAL_ELEM_VALUE = 'layui-icon-circle-dot';
                    } else {
                        ORIGINAL_ELEM_VALUE = '&#xe617;';
                    }
                }

                if (isFontClass) {
                    oriIcon = '<i class="layui-icon '+ ORIGINAL_ELEM_VALUE +'">';
                } else {
                    oriIcon += ORIGINAL_ELEM_VALUE; 
                }
                oriIcon += '</i>';

                var selectHtml = '<div class="layui-iconpicker layui-unselect layui-form-select" id="'+ ICON_BODY +'">' +
                    '<div class="'+ TITLE +'" id="'+ TITLE_ID +'">' +
                        '<div class="layui-iconpicker-item">'+
                            '<span class="layui-iconpicker-icon layui-unselect">' +
                                oriIcon +
                            '</span>'+
                            '<i class="layui-edge"></i>' +
                        '</div>'+
                    '</div>' +
                    '<div class="layui-anim layui-anim-upbit" style="">' +
                        '123' +
                    '</div>';
                $(elem).after(selectHtml);
                return a;
            },
            /**
             * 展开/折叠下拉框
             */
            toggleSelect: function () {
                var item = '#' + TITLE_ID + ' .layui-iconpicker-item,#' + TITLE_ID + ' .layui-iconpicker-item .layui-edge';
                a.event('click', item, function (e) {
                    var $icon = $('#' + ICON_BODY);
                    if ($icon.hasClass(selected)) {
                        $icon.removeClass(selected).addClass(unselect);
                    } else {
                        // 隐藏其他picker
                        $('.layui-form-select').removeClass(selected);
                        // 显示当前picker
                        $icon.addClass(selected).removeClass(unselect);
                    }
                    e.stopPropagation();
                });
                return a;
            },
            /**
             * 绘制主体部分
             */
            createBody: function () {
                // 获取数据
                var searchHtml = '';

                if (search) {
                    searchHtml = '<div class="layui-iconpicker-search">' +
                        '<input class="layui-input">' +
                        '<i class="layui-icon">&#xe615;</i>' +
                        '</div>';
                }

                // 组合dom
                var bodyHtml = '<div class="layui-iconpicker-body" id="'+ PICKER_BODY +'">' +
                    searchHtml +
                        '<div class="'+ LIST_BOX +'"></div> '+
                     '</div>';
                $('#' + ICON_BODY).find('.layui-anim').eq(0).html(bodyHtml);
                a.search().createList().check().page();

                return a;
            },
            /**
             * 绘制图标列表
             * @param text 模糊查询关键字
             * @returns {string}
             */
            createList: function (text) {
                var d = data,
                    l = d.length,
                    pageHtml = '',
                    listHtml = $('<div class="layui-iconpicker-list">')//'<div class="layui-iconpicker-list">';

                // 计算分页数据
                var _limit = limit, // 每页显示数量
                    _pages = l % _limit === 0 ? l / _limit : parseInt(l / _limit + 1), // 总计多少页
                    _id = PAGE_ID;

                // 图标列表
                var icons = [];

                for (var i = 0; i < l; i++) {
                    var obj = d[i];

                    // 判断是否模糊查询
                    if (text && obj.indexOf(text) === -1) {
                        continue;
                    }

                    // 是否自定义格子宽度
                    var style = '';
                    if (cellWidth !== null) {
                        style += ' style="width:' + cellWidth + '"';
                    }

                    // 每个图标dom
                    var icon = '<div class="layui-iconpicker-icon-item" title="'+ obj +'" '+ style +'>';
                    if (isFontClass){
                        icon += '<i class="layui-icon '+ obj +'"></i>';
                    } else {
                        icon += '<i class="layui-icon">'+ obj.replace('amp;', '') +'</i>';
                    }
                    icon += '</div>';

                    icons.push(icon);
                }

                // 查询出图标后再分页
                l = icons.length;
                _pages = l % _limit === 0 ? l / _limit : parseInt(l / _limit + 1);
                for (var i = 0; i < _pages; i++) {
                    // 按limit分块
                    var lm = $('<div class="layui-iconpicker-icon-limit" id="layui-iconpicker-icon-limit-' + tmp + (i+1) +'">');

                    for (var j = i * _limit; j < (i+1) * _limit && j < l; j++) {
                        lm.append(icons[j]);
                    }

                    listHtml.append(lm);
                }

                // 无数据
                if (l === 0) {
                    listHtml.append('<p class="layui-iconpicker-tips">无数据</p>');
                }

                // 判断是否分页
                if (page){
                    $('#' + PICKER_BODY).addClass('layui-iconpicker-body-page');
                    pageHtml = '<div class="layui-iconpicker-page" id="'+ PAGE_ID +'">' +
                        '<div class="layui-iconpicker-page-count">' +
                        '<span id="'+ PAGE_ID +'-current">1</span>/' +
                        '<span id="'+ PAGE_ID +'-pages">'+ _pages +'</span>' +
                        ' (<span id="'+ PAGE_ID +'-length">'+ l +'</span>)' +
                        '</div>' +
                        '<div class="layui-iconpicker-page-operate">' +
                        '<i class="layui-icon" id="'+ PAGE_ID +'-prev" data-index="0" prev>&#xe603;</i> ' +
                        '<i class="layui-icon" id="'+ PAGE_ID +'-next" data-index="2" next>&#xe602;</i> ' +
                        '</div>' +
                        '</div>';
                }


                $('#' + ICON_BODY).find('.layui-anim').find('.' + LIST_BOX).html('').append(listHtml).append(pageHtml);
                return a;
            },
            // 阻止Layui的一些默认事件
            preventEvent: function() {
                var item = '#' + ICON_BODY + ' .layui-anim';
                a.event('click', item, function (e) {
                    e.stopPropagation();
                });
                return a;
            },
            // 分页
            page: function () {
                var icon = '#' + PAGE_ID + ' .layui-iconpicker-page-operate .layui-icon';

                $(icon).unbind('click');
                a.event('click', icon, function (e) {
                   var elem = e.currentTarget,
                       total = parseInt($('#' +PAGE_ID + '-pages').html()),
                       isPrev = $(elem).attr('prev') !== undefined,
                       // 按钮上标的页码
                       index = parseInt($(elem).attr('data-index')),
                       $cur = $('#' +PAGE_ID + '-current'),
                       // 点击时正在显示的页码
                       current = parseInt($cur.html());

                    // 分页数据
                    if (isPrev && current > 1) {
                        current=current-1;
                        $(icon + '[prev]').attr('data-index', current);
                    } else if (!isPrev && current < total){
                        current=current+1;
                        $(icon + '[next]').attr('data-index', current);
                    }
                    $cur.html(current);

                    // 图标数据
                    $('#'+ ICON_BODY + ' .layui-iconpicker-icon-limit').hide();
                    $('#layui-iconpicker-icon-limit-' + tmp + current).show();
                    e.stopPropagation();
                });
                return a;
            },
            /**
             * 搜索
             */
            search: function () {
                var item = '#' + PICKER_BODY + ' .layui-iconpicker-search .layui-input';
                a.event('input propertychange', item, function (e) {
                    var elem = e.target,
                        t = $(elem).val();
                    a.createList(t);
                });
                return a;
            },
            /**
             * 点击选中图标
             */
            check: function () {
                var item = '#' + PICKER_BODY + ' .layui-iconpicker-icon-item';
                a.event('click', item, function (e) {
                    var el = $(e.currentTarget).find('.layui-icon'),
                        icon = '';
                    if (isFontClass) {
                        var clsArr = el.attr('class').split(/[\s\n]/),
                            cls = clsArr[1],
                            icon = cls;
                        $('#' + TITLE_ID).find('.layui-iconpicker-item .layui-icon').html('').attr('class', clsArr.join(' '));
                    } else {
                        var cls = el.html(),
                            icon = cls;
                        $('#' + TITLE_ID).find('.layui-iconpicker-item .layui-icon').html(icon);
                    }

                    $('#' + ICON_BODY).removeClass(selected).addClass(unselect);
                    $(elem).val(icon).attr('value', icon);
                    // 回调
                    if (click) {
                        click({
                            icon: icon
                        });
                    }

                });
                return a;
            },
            // 监听原始input数值改变
            inputListen: function(){
                var el = $(elem);
                a.event('change', elem, function(){
                    var value = el.val();
                })
                // el.change(function(){
                    
                // });
                return a;
            },
            event: function (evt, el, fn) {
                $(BODY).on(evt, el, fn);
            }
        };

        var common = {
            /**
             * 加载样式表
             */
            loadCss: function () {
                var css = '.layui-iconpicker {max-width: 280px;}.layui-iconpicker .layui-anim{display:none;position:absolute;left:0;top:42px;padding:5px 0;z-index:899;min-width:100%;border:1px solid #d2d2d2;max-height:300px;overflow-y:auto;background-color:#fff;border-radius:2px;box-shadow:0 2px 4px rgba(0,0,0,.12);box-sizing:border-box;}.layui-iconpicker-item{border:1px solid #e6e6e6;width:90px;height:33px;border-radius:4px;cursor:pointer;position:relative;}.layui-iconpicker-icon{border-right:1px solid #e6e6e6;-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;display:block;width:60px;height:100%;float:left;text-align:center;background:#fff;transition:all .3s;}.layui-iconpicker-icon i{line-height:36px;font-size:18px;}.layui-iconpicker-item > .layui-edge{left:70px;}.layui-iconpicker-item:hover{border-color:#D2D2D2!important;}.layui-iconpicker-item:hover .layui-iconpicker-icon{border-color:#D2D2D2!important;}.layui-iconpicker.layui-form-selected .layui-anim{display:block;}.layui-iconpicker-body{padding:6px;}.layui-iconpicker .layui-iconpicker-list{background-color:#fff;border:1px solid #ccc;border-radius:4px;}.layui-iconpicker .layui-iconpicker-icon-item{display:inline-block;width:21.1%;line-height:36px;text-align:center;cursor:pointer;vertical-align:top;height:36px;margin:4px;border:1px solid #ddd;border-radius:2px;transition:300ms;}.layui-iconpicker .layui-iconpicker-icon-item i.layui-icon{font-size:17px;}.layui-iconpicker .layui-iconpicker-icon-item:hover{background-color:#eee;border-color:#ccc;-webkit-box-shadow:0 0 2px #aaa,0 0 2px #fff inset;-moz-box-shadow:0 0 2px #aaa,0 0 2px #fff inset;box-shadow:0 0 2px #aaa,0 0 2px #fff inset;text-shadow:0 0 1px #fff;}.layui-iconpicker-search{position:relative;margin:0 0 6px 0;border:1px solid #e6e6e6;border-radius:2px;transition:300ms;}.layui-iconpicker-search:hover{border-color:#D2D2D2!important;}.layui-iconpicker-search .layui-input{cursor:text;display:inline-block;width:86%;border:none;padding-right:0;margin-top:1px;}.layui-iconpicker-search .layui-icon{position:absolute;top:11px;right:4%;}.layui-iconpicker-tips{text-align:center;padding:8px 0;cursor:not-allowed;}.layui-iconpicker-page{margin-top:6px;margin-bottom:-6px;font-size:12px;padding:0 2px;}.layui-iconpicker-page-count{display:inline-block;}.layui-iconpicker-page-operate{display:inline-block;float:right;cursor:default;}.layui-iconpicker-page-operate .layui-icon{font-size:12px;cursor:pointer;}.layui-iconpicker-body-page .layui-iconpicker-icon-limit{display:none;}.layui-iconpicker-body-page .layui-iconpicker-icon-limit:first-child{display:block;}';
                var $style = $('head').find('style[iconpicker]');
                if ($style.length === 0) {
                    $('head').append('<style rel="stylesheet" iconpicker>'+css+'</style>');
                }
            },
            /**
             * 获取数据
             */
            getData: {
                fontClass: function () {
                    var arr = ["fa-glass","fa-music","fa-search","fa-envelope-o","fa-heart","fa-star","fa-star-o","fa-user","fa-film","fa-th-large","fa-th","fa-th-list","fa-check","fa-remove","fa-close","fa-times","fa-search-plus","fa-search-minus","fa-power-off","fa-signal","fa-gear","fa-cog","fa-trash-o","fa-home","fa-file-o","fa-clock-o","fa-road","fa-download","fa-arrow-circle-o-down","fa-arrow-circle-o-up","fa-inbox","fa-play-circle-o","fa-rotate-right","fa-repeat","fa-refresh","fa-list-alt","fa-lock","fa-flag","fa-headphones","fa-volume-off","fa-volume-down","fa-volume-up","fa-qrcode","fa-barcode","fa-tag","fa-tags","fa-book","fa-bookmark","fa-print","fa-camera","fa-font","fa-bold","fa-italic","fa-text-height","fa-text-width","fa-align-left","fa-align-center","fa-align-right","fa-align-justify","fa-list","fa-dedent","fa-outdent","fa-indent","fa-video-camera","fa-photo","fa-image","fa-picture-o","fa-pencil","fa-map-marker","fa-adjust","fa-tint","fa-edit","fa-pencil-square-o","fa-share-square-o","fa-check-square-o","fa-arrows","fa-step-backward","fa-fast-backward","fa-backward","fa-play","fa-pause","fa-stop","fa-forward","fa-fast-forward","fa-step-forward","fa-eject","fa-chevron-left","fa-chevron-right","fa-plus-circle","fa-minus-circle","fa-times-circle","fa-check-circle","fa-question-circle","fa-info-circle","fa-crosshairs","fa-times-circle-o","fa-check-circle-o","fa-ban","fa-arrow-left","fa-arrow-right","fa-arrow-up","fa-arrow-down","fa-mail-forward","fa-share","fa-expand","fa-compress","fa-plus","fa-minus","fa-asterisk","fa-exclamation-circle","fa-gift","fa-leaf","fa-fire","fa-eye","fa-eye-slash","fa-warning","fa-exclamation-triangle","fa-plane","fa-calendar","fa-random","fa-comment","fa-magnet","fa-chevron-up","fa-chevron-down","fa-retweet","fa-shopping-cart","fa-folder","fa-folder-open","fa-arrows-v","fa-arrows-h","fa-bar-chart-o","fa-bar-chart","fa-twitter-square","fa-facebook-square","fa-camera-retro","fa-key","fa-gears","fa-cogs","fa-comments","fa-thumbs-o-up","fa-thumbs-o-down","fa-star-half","fa-heart-o","fa-sign-out","fa-linkedin-square","fa-thumb-tack","fa-external-link","fa-sign-in","fa-trophy","fa-github-square","fa-upload","fa-lemon-o","fa-phone","fa-square-o","fa-bookmark-o","fa-phone-square","fa-twitter","fa-facebook-f","fa-facebook","fa-github","fa-unlock","fa-credit-card","fa-feed","fa-rss","fa-hdd-o","fa-bullhorn","fa-bell","fa-certificate","fa-hand-o-right","fa-hand-o-left","fa-hand-o-up","fa-hand-o-down","fa-arrow-circle-left","fa-arrow-circle-right","fa-arrow-circle-up","fa-arrow-circle-down","fa-globe","fa-wrench","fa-tasks","fa-filter","fa-briefcase","fa-arrows-alt","fa-group","fa-users","fa-chain","fa-link","fa-cloud","fa-flask","fa-cut","fa-scissors","fa-copy","fa-files-o","fa-paperclip","fa-save","fa-floppy-o","fa-square","fa-navicon","fa-reorder","fa-bars","fa-list-ul","fa-list-ol","fa-strikethrough","fa-underline","fa-table","fa-magic","fa-truck","fa-pinterest","fa-pinterest-square","fa-google-plus-square","fa-google-plus","fa-money","fa-caret-down","fa-caret-up","fa-caret-left","fa-caret-right","fa-columns","fa-unsorted","fa-sort","fa-sort-down","fa-sort-desc","fa-sort-up","fa-sort-asc","fa-envelope","fa-linkedin","fa-rotate-left","fa-undo","fa-legal","fa-gavel","fa-dashboard","fa-tachometer","fa-comment-o","fa-comments-o","fa-flash","fa-bolt","fa-sitemap","fa-umbrella","fa-paste","fa-clipboard","fa-lightbulb-o","fa-exchange","fa-cloud-download","fa-cloud-upload","fa-user-md","fa-stethoscope","fa-suitcase","fa-bell-o","fa-coffee","fa-cutlery","fa-file-text-o","fa-building-o","fa-hospital-o","fa-ambulance","fa-medkit","fa-fighter-jet","fa-beer","fa-h-square","fa-plus-square","fa-angle-double-left","fa-angle-double-right","fa-angle-double-up","fa-angle-double-down","fa-angle-left","fa-angle-right","fa-angle-up","fa-angle-down","fa-desktop","fa-laptop","fa-tablet","fa-mobile-phone","fa-mobile","fa-circle-o","fa-quote-left","fa-quote-right","fa-spinner","fa-circle","fa-mail-reply","fa-reply","fa-github-alt","fa-folder-o","fa-folder-open-o","fa-smile-o","fa-frown-o","fa-meh-o","fa-gamepad","fa-keyboard-o","fa-flag-o","fa-flag-checkered","fa-terminal","fa-code","fa-mail-reply-all","fa-reply-all","fa-star-half-empty","fa-star-half-full","fa-star-half-o","fa-location-arrow","fa-crop","fa-code-fork","fa-unlink","fa-chain-broken","fa-question","fa-info","fa-exclamation","fa-superscript","fa-subscript","fa-eraser","fa-puzzle-piece","fa-microphone","fa-microphone-slash","fa-shield","fa-calendar-o","fa-fire-extinguisher","fa-rocket","fa-maxcdn","fa-chevron-circle-left","fa-chevron-circle-right","fa-chevron-circle-up","fa-chevron-circle-down","fa-html5","fa-css3","fa-anchor","fa-unlock-alt","fa-bullseye","fa-ellipsis-h","fa-ellipsis-v","fa-rss-square","fa-play-circle","fa-ticket","fa-minus-square","fa-minus-square-o","fa-level-up","fa-level-down","fa-check-square","fa-pencil-square","fa-external-link-square","fa-share-square","fa-compass","fa-toggle-down","fa-caret-square-o-down","fa-toggle-up","fa-caret-square-o-up","fa-toggle-right","fa-caret-square-o-right","fa-euro","fa-eur","fa-gbp","fa-dollar","fa-usd","fa-rupee","fa-inr","fa-cny","fa-rmb","fa-yen","fa-jpy","fa-ruble","fa-rouble","fa-rub","fa-won","fa-krw","fa-bitcoin","fa-btc","fa-file","fa-file-text","fa-sort-alpha-asc","fa-sort-alpha-desc","fa-sort-amount-asc","fa-sort-amount-desc","fa-sort-numeric-asc","fa-sort-numeric-desc","fa-thumbs-up","fa-thumbs-down","fa-youtube-square","fa-youtube","fa-xing","fa-xing-square","fa-youtube-play","fa-dropbox","fa-stack-overflow","fa-instagram","fa-flickr","fa-adn","fa-bitbucket","fa-bitbucket-square","fa-tumblr","fa-tumblr-square","fa-long-arrow-down","fa-long-arrow-up","fa-long-arrow-left","fa-long-arrow-right","fa-apple","fa-windows","fa-android","fa-linux","fa-dribbble","fa-skype","fa-foursquare","fa-trello","fa-female","fa-male","fa-gittip","fa-gratipay","fa-sun-o","fa-moon-o","fa-archive","fa-bug","fa-vk","fa-weibo","fa-renren","fa-pagelines","fa-stack-exchange","fa-arrow-circle-o-right","fa-arrow-circle-o-left","fa-toggle-left","fa-caret-square-o-left","fa-dot-circle-o","fa-wheelchair","fa-vimeo-square","fa-turkish-lira","fa-try","fa-plus-square-o","fa-space-shuttle","fa-slack","fa-envelope-square","fa-wordpress","fa-openid","fa-institution","fa-bank","fa-university","fa-mortar-board","fa-graduation-cap","fa-yahoo","fa-google","fa-reddit","fa-reddit-square","fa-stumbleupon-circle","fa-stumbleupon","fa-delicious","fa-digg","fa-pied-piper-pp","fa-pied-piper-alt","fa-drupal","fa-joomla","fa-language","fa-fax","fa-building","fa-child","fa-paw","fa-spoon","fa-cube","fa-cubes","fa-behance","fa-behance-square","fa-steam","fa-steam-square","fa-recycle","fa-automobile","fa-car","fa-cab","fa-taxi","fa-tree","fa-spotify","fa-deviantart","fa-soundcloud","fa-database","fa-file-pdf-o","fa-file-word-o","fa-file-excel-o","fa-file-powerpoint-o","fa-file-photo-o","fa-file-picture-o","fa-file-image-o","fa-file-zip-o","fa-file-archive-o","fa-file-sound-o","fa-file-audio-o","fa-file-movie-o","fa-file-video-o","fa-file-code-o","fa-vine","fa-codepen","fa-jsfiddle","fa-life-bouy","fa-life-buoy","fa-life-saver","fa-support","fa-life-ring","fa-circle-o-notch","fa-ra","fa-resistance","fa-rebel","fa-ge","fa-empire","fa-git-square","fa-git","fa-y-combinator-square","fa-yc-square","fa-hacker-news","fa-tencent-weibo","fa-qq","fa-wechat","fa-weixin","fa-send","fa-paper-plane","fa-send-o","fa-paper-plane-o","fa-history","fa-circle-thin","fa-header","fa-paragraph","fa-sliders","fa-share-alt","fa-share-alt-square","fa-bomb","fa-soccer-ball-o","fa-futbol-o","fa-tty","fa-binoculars","fa-plug","fa-slideshare","fa-twitch","fa-yelp","fa-newspaper-o","fa-wifi","fa-calculator","fa-paypal","fa-google-wallet","fa-cc-visa","fa-cc-mastercard","fa-cc-discover","fa-cc-amex","fa-cc-paypal","fa-cc-stripe","fa-bell-slash","fa-bell-slash-o","fa-trash","fa-copyright","fa-at","fa-eyedropper","fa-paint-brush","fa-birthday-cake","fa-area-chart","fa-pie-chart","fa-line-chart","fa-lastfm","fa-lastfm-square","fa-toggle-off","fa-toggle-on","fa-bicycle","fa-bus","fa-ioxhost","fa-angellist","fa-cc","fa-shekel","fa-sheqel","fa-ils","fa-meanpath","fa-buysellads","fa-connectdevelop","fa-dashcube","fa-forumbee","fa-leanpub","fa-sellsy","fa-shirtsinbulk","fa-simplybuilt","fa-skyatlas","fa-cart-plus","fa-cart-arrow-down","fa-diamond","fa-ship","fa-user-secret","fa-motorcycle","fa-street-view","fa-heartbeat","fa-venus","fa-mars","fa-mercury","fa-intersex","fa-transgender","fa-transgender-alt","fa-venus-double","fa-mars-double","fa-venus-mars","fa-mars-stroke","fa-mars-stroke-v","fa-mars-stroke-h","fa-neuter","fa-genderless","fa-facebook-official","fa-pinterest-p","fa-whatsapp","fa-server","fa-user-plus","fa-user-times","fa-hotel","fa-bed","fa-viacoin","fa-train","fa-subway","fa-medium","fa-yc","fa-y-combinator","fa-optin-monster","fa-opencart","fa-expeditedssl","fa-battery-4","fa-battery","fa-battery-full","fa-battery-3","fa-battery-three-quarters","fa-battery-2","fa-battery-half","fa-battery-1","fa-battery-quarter","fa-battery-0","fa-battery-empty","fa-mouse-pointer","fa-i-cursor","fa-object-group","fa-object-ungroup","fa-sticky-note","fa-sticky-note-o","fa-cc-jcb","fa-cc-diners-club","fa-clone","fa-balance-scale","fa-hourglass-o","fa-hourglass-1","fa-hourglass-start","fa-hourglass-2","fa-hourglass-half","fa-hourglass-3","fa-hourglass-end","fa-hourglass","fa-hand-grab-o","fa-hand-rock-o","fa-hand-stop-o","fa-hand-paper-o","fa-hand-scissors-o","fa-hand-lizard-o","fa-hand-spock-o","fa-hand-pointer-o","fa-hand-peace-o","fa-trademark","fa-registered","fa-creative-commons","fa-gg","fa-gg-circle","fa-tripadvisor","fa-odnoklassniki","fa-odnoklassniki-square","fa-get-pocket","fa-wikipedia-w","fa-safari","fa-chrome","fa-firefox","fa-opera","fa-internet-explorer","fa-tv","fa-television","fa-contao","fa-500px","fa-amazon","fa-calendar-plus-o","fa-calendar-minus-o","fa-calendar-times-o","fa-calendar-check-o","fa-industry","fa-map-pin","fa-map-signs","fa-map-o","fa-map","fa-commenting","fa-commenting-o","fa-houzz","fa-vimeo","fa-black-tie","fa-fonticons","fa-reddit-alien","fa-edge","fa-credit-card-alt","fa-codiepie","fa-modx","fa-fort-awesome","fa-usb","fa-product-hunt","fa-mixcloud","fa-scribd","fa-pause-circle","fa-pause-circle-o","fa-stop-circle","fa-stop-circle-o","fa-shopping-bag","fa-shopping-basket","fa-hashtag","fa-bluetooth","fa-bluetooth-b","fa-percent","fa-gitlab","fa-wpbeginner","fa-wpforms","fa-envira","fa-universal-access","fa-wheelchair-alt","fa-question-circle-o","fa-blind","fa-audio-description","fa-volume-control-phone","fa-braille","fa-assistive-listening-systems","fa-asl-interpreting","fa-american-sign-language-interpreting","fa-deafness","fa-hard-of-hearing","fa-deaf","fa-glide","fa-glide-g","fa-signing","fa-sign-language","fa-low-vision","fa-viadeo","fa-viadeo-square","fa-snapchat","fa-snapchat-ghost","fa-snapchat-square","fa-pied-piper","fa-first-order","fa-yoast","fa-themeisle","fa-google-plus-circle","fa-google-plus-official","fa-fa","fa-font-awesome","fa-handshake-o","fa-envelope-open","fa-envelope-open-o","fa-linode","fa-address-book","fa-address-book-o","fa-vcard","fa-address-card","fa-vcard-o","fa-address-card-o","fa-user-circle","fa-user-circle-o","fa-user-o","fa-id-badge","fa-drivers-license","fa-id-card","fa-drivers-license-o","fa-id-card-o","fa-quora","fa-free-code-camp","fa-telegram","fa-thermometer-4","fa-thermometer","fa-thermometer-full","fa-thermometer-3","fa-thermometer-three-quarters","fa-thermometer-2","fa-thermometer-half","fa-thermometer-1","fa-thermometer-quarter","fa-thermometer-0","fa-thermometer-empty","fa-shower","fa-bathtub","fa-s15","fa-bath","fa-podcast","fa-window-maximize","fa-window-minimize","fa-window-restore","fa-times-rectangle","fa-window-close","fa-times-rectangle-o","fa-window-close-o","fa-bandcamp","fa-grav","fa-etsy","fa-imdb","fa-ravelry","fa-eercast","fa-microchip","fa-snowflake-o","fa-superpowers","fa-wpexplorer","fa-meetup","layui-icon-rate-half","layui-icon-rate","layui-icon-rate-solid","layui-icon-cellphone","layui-icon-vercode","layui-icon-login-wechat","layui-icon-login-qq","layui-icon-login-weibo","layui-icon-password","layui-icon-username","layui-icon-refresh-3","layui-icon-auz","layui-icon-spread-left","layui-icon-shrink-right","layui-icon-snowflake","layui-icon-tips","layui-icon-note","layui-icon-home","layui-icon-senior","layui-icon-refresh","layui-icon-refresh-1","layui-icon-flag","layui-icon-theme","layui-icon-notice","layui-icon-website","layui-icon-console","layui-icon-face-surprised","layui-icon-set","layui-icon-template-1","layui-icon-app","layui-icon-template","layui-icon-praise","layui-icon-tread","layui-icon-male","layui-icon-female","layui-icon-camera","layui-icon-camera-fill","layui-icon-more","layui-icon-more-vertical","layui-icon-rmb","layui-icon-dollar","layui-icon-diamond","layui-icon-fire","layui-icon-return","layui-icon-location","layui-icon-read","layui-icon-survey","layui-icon-face-smile","layui-icon-face-cry","layui-icon-cart-simple","layui-icon-cart","layui-icon-next","layui-icon-prev","layui-icon-upload-drag","layui-icon-upload","layui-icon-download-circle","layui-icon-component","layui-icon-file-b","layui-icon-user","layui-icon-find-fill","layui-icon-loading","layui-icon-loading-1","layui-icon-add-1","layui-icon-play","layui-icon-pause","layui-icon-headset","layui-icon-video","layui-icon-voice","layui-icon-speaker","layui-icon-fonts-del","layui-icon-fonts-code","layui-icon-fonts-html","layui-icon-fonts-strong","layui-icon-unlink","layui-icon-picture","layui-icon-link","layui-icon-face-smile-b","layui-icon-align-left","layui-icon-align-right","layui-icon-align-center","layui-icon-fonts-u","layui-icon-fonts-i","layui-icon-tabs","layui-icon-radio","layui-icon-circle","layui-icon-edit","layui-icon-share","layui-icon-delete","layui-icon-form","layui-icon-cellphone-fine","layui-icon-dialogue","layui-icon-fonts-clear","layui-icon-layer","layui-icon-date","layui-icon-water","layui-icon-code-circle","layui-icon-carousel","layui-icon-prev-circle","layui-icon-layouts","layui-icon-util","layui-icon-templeate-1","layui-icon-upload-circle","layui-icon-tree","layui-icon-table","layui-icon-chart","layui-icon-chart-screen","layui-icon-engine","layui-icon-triangle-d","layui-icon-triangle-r","layui-icon-file","layui-icon-set-sm","layui-icon-add-circle","layui-icon-404","layui-icon-about","layui-icon-up","layui-icon-down","layui-icon-left","layui-icon-right","layui-icon-circle-dot","layui-icon-search","layui-icon-set-fill","layui-icon-group","layui-icon-friends","layui-icon-reply-fill","layui-icon-menu-fill","layui-icon-log","layui-icon-picture-fine","layui-icon-face-smile-fine","layui-icon-list","layui-icon-release","layui-icon-ok","layui-icon-help","layui-icon-chat","layui-icon-top","layui-icon-star","layui-icon-star-fill","layui-icon-close-fill","layui-icon-close","layui-icon-ok-circle","layui-icon-add-circle-fine"];
                    return arr;
                },
                unicode: function () {
                    return ["&amp;#xe6c9;","&amp;#xe67b;","&amp;#xe67a;","&amp;#xe678;","&amp;#xe679;","&amp;#xe677;","&amp;#xe676;","&amp;#xe675;","&amp;#xe673;","&amp;#xe66f;","&amp;#xe9aa;","&amp;#xe672;","&amp;#xe66b;","&amp;#xe668;","&amp;#xe6b1;","&amp;#xe702;","&amp;#xe66e;","&amp;#xe68e;","&amp;#xe674;","&amp;#xe669;","&amp;#xe666;","&amp;#xe66c;","&amp;#xe66a;","&amp;#xe667;","&amp;#xe7ae;","&amp;#xe665;","&amp;#xe664;","&amp;#xe716;","&amp;#xe656;","&amp;#xe653;","&amp;#xe663;","&amp;#xe6c6;","&amp;#xe6c5;","&amp;#xe662;","&amp;#xe661;","&amp;#xe660;","&amp;#xe65d;","&amp;#xe65f;","&amp;#xe671;","&amp;#xe65e;","&amp;#xe659;","&amp;#xe735;","&amp;#xe756;","&amp;#xe65c;","&amp;#xe715;","&amp;#xe705;","&amp;#xe6b2;","&amp;#xe6af;","&amp;#xe69c;","&amp;#xe698;","&amp;#xe657;","&amp;#xe65b;","&amp;#xe65a;","&amp;#xe681;","&amp;#xe67c;","&amp;#xe601;","&amp;#xe857;","&amp;#xe655;","&amp;#xe770;","&amp;#xe670;","&amp;#xe63d;","&amp;#xe63e;","&amp;#xe654;","&amp;#xe652;","&amp;#xe651;","&amp;#xe6fc;","&amp;#xe6ed;","&amp;#xe688;","&amp;#xe645;","&amp;#xe64f;","&amp;#xe64e;","&amp;#xe64b;","&amp;#xe62b;","&amp;#xe64d;","&amp;#xe64a;","&amp;#xe64c;","&amp;#xe650;","&amp;#xe649;","&amp;#xe648;","&amp;#xe647;","&amp;#xe646;","&amp;#xe644;","&amp;#xe62a;","&amp;#xe643;","&amp;#xe63f;","&amp;#xe642;","&amp;#xe641;","&amp;#xe640;","&amp;#xe63c;","&amp;#xe63b;","&amp;#xe63a;","&amp;#xe639;","&amp;#xe638;","&amp;#xe637;","&amp;#xe636;","&amp;#xe635;","&amp;#xe634;","&amp;#xe633;","&amp;#xe632;","&amp;#xe631;","&amp;#xe630;","&amp;#xe62f;","&amp;#xe62e;","&amp;#xe62d;","&amp;#xe62c;","&amp;#xe629;","&amp;#xe628;","&amp;#xe625;","&amp;#xe623;","&amp;#xe621;","&amp;#xe620;","&amp;#xe61f;","&amp;#xe61c;","&amp;#xe60b;","&amp;#xe619;","&amp;#xe61a;","&amp;#xe603;","&amp;#xe602;","&amp;#xe617;","&amp;#xe615;","&amp;#xe614;","&amp;#xe613;","&amp;#xe612;","&amp;#xe611;","&amp;#xe60f;","&amp;#xe60e;","&amp;#xe60d;","&amp;#xe60c;","&amp;#xe60a;","&amp;#xe609;","&amp;#xe605;","&amp;#xe607;","&amp;#xe606;","&amp;#xe604;","&amp;#xe600;","&amp;#xe658;","&amp;#x1007;","&amp;#x1006;","&amp;#x1005;","&amp;#xe608;"];
                }
            }
        };

        a.init();
        return new IconPicker();
    };

    /**
     * 选中图标
     * @param filter lay-filter
     * @param iconName 图标名称，自动识别fontClass/unicode
     */
    IconPicker.prototype.checkIcon = function (filter, iconName){
        var el = $('*[lay-filter='+ filter +']'),
            p = el.next().find('.layui-iconpicker-item .layui-icon'),
            c = iconName;

        if (c.indexOf('#xe') > 0){
            p.html(c);
        } else {
            p.html('').attr('class', 'layui-icon ' + c);
        }
        el.attr('value', c).val(c);
    };

    var iconPicker = new IconPicker();
    exports(_MOD, iconPicker);
});
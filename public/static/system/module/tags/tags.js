/*!
 * tags标签组件
 * by meystack to www.swiftadmin.net
 * Apache2.0 Licensed
 */
layui.define(function(exports){
    "use strict";
    
    var $ = layui.$
    
    //模块名
    ,MOD_NAME = 'tags'
    ,MOD_INDEX = 'layui_'+ MOD_NAME +'_index' //模块索引名
    ,TAG_NAME = undefined
    //外部接口
    ,MODULE_TAGS_NAME = {
      config: {}
      ,index: layui[MOD_NAME] ? (layui[MOD_NAME].index + 10000) : 0
  
      //设置全局项
      ,set: function(options){
        var that = this;
        that.config = $.extend({}, that.config, options);
        return that;
      }
      
      //事件
      ,on: function(events, callback){
        return layui.onevent.call(this, MOD_NAME, events, callback);
      }
    }

    //操作当前实例
    ,thisTags = function(){
      var that = this
      ,options = that.config
      ,id = options.id || that.index;
      
      thisTags.that[id] = that; //记录当前实例对象
      
      return {
        config: options
        //重置实例
        ,reload: function(options){
          that.reload.call(that, options);
        }
      }
    }
  
    //字符常量
    ,STR_ELEM = 'layui-MODULE_TAGS_NAME', STR_HIDE = 'layui-hide', STR_DISABLED = 'layui-disabled', STR_NONE = 'layui-none'
    ,LAY_TAG_THIS ='layui-tag-this', LAY_TAGS_RESULT = 'layui-tags-ajax'
    
    //主模板
    ,TPL_MAIN = [''].join('')
  
    //构造器
    ,Class = function(options){
      var that = this;
      that.index = ++MODULE_TAGS_NAME.index;
      that.config = $.extend({}, that.config, MODULE_TAGS_NAME.config, options);
      that.render();
    };

    //默认配置
    Class.prototype.config = {
        url: undefined,     // ajax的URL地址
        limit: 3,           // 限定数量
        length: 10,         // 汉字最大长度
        data: [],           // 初始化数据，默认渲染INPUT表单
    };
    
    // 重载实例
    Class.prototype.reload = function(options){
      var that = this;
      
      // 防止数组深度合并
      layui.each(options, function(key, item){
        if(layui._typeof(item) === 'array') delete that.config[key];
      });
      
      that.config = $.extend(true, {}, that.config, options);
      that.render();
    };
  
    //渲染
    Class.prototype.render = function(){
      var that = this
      ,options = that.config;

      // 获取当前元素
      var othis = options.elem = $(options.elem);
      if(!othis[0]) return;

      // 隐藏原生标签
      othis.hide();

      // 获取数据信息
      var data = that.config.data;

      if (!data.length) {
        data = othis.val().replace(/\s+/g,"").replace('，',',').split(',');
      }

      // 加载标签模板
      that.config.id = 'lay-tags-'+ that.config.elem.attr('id');
      var html = '<div id="'+ that.config.id +'" class="layui-tags">';
          html += '<input class="layui-tags-input" placeholder="请输入SEO关键词">';
          html += '</div>';
      $(othis).parent().append(html);

      // 初始化标签
      for (var index = 0; index < that.config.limit; index++) {
        if (index < data.length) {
            var element = data[index];
            if (element) {
              that.drawring(element, null);
            }
        }
      }

      that.events();
    };

    // 增加标签
    Class.prototype.drawring = function(element, that) {

      element = element.replace(/(^\s*)|(\s*$)/g, "");
      if(!(/[a-zA-Z0-9]+$/.test(element))) {
          if (element.length >= this.config.length) {
            return layui.layer.msg('超出最大长度','error');
          }
      }

      var othis = this;
      var html = '<span class="tag-elem"><span>'+element; 
      html += '</span><i class="layui-icon layui-icon-close" title="移除标签"></i></span>';
      $('#'+ this.config.id + ' .layui-tags-input').before(html);

      // 限定隐藏元素
      if (this.getData().length == this.config.limit) {
        $('#'+this.config.id + ' .layui-tags-input').hide();
      }

      // 清空表单
      that && that.prop('value','');

      // 点击删除标签
      $('.tag-elem .layui-icon-close').click(function(e) {

          e.stopPropagation();
          e.preventDefault();

          $(this).parent('.tag-elem').remove();
          $('#'+othis.config.id+' input').show();

          // 重载数据
          othis.getData();
      });

      typeof this.config.done === 'function' && this.config.done(element, othis.getData());
    };

    // 获取标签总数
    Class.prototype.getData = function (keyword) {

      // 查询所有标签
      var keyword = [];
      $('#'+this.config.id).children('span.tag-elem').each(function(i,e) {
        keyword.push($(e).text());
      })
      
      // 修改标签属性
      $(this.config.elem).attr('value',keyword.join(','));


      return keyword;
    };


    $(document).on('click',function() {
      $('#'+LAY_TAGS_RESULT).hide();
    })
    
    //事件
    Class.prototype.events = function(){
      var that = this
      ,options = that.config
      ,input = '#'+options.id+' input';


      // 选中元素
      $('body').on('mouseup', input ,function(e) {});

      // 监听输入框键盘事件
      $('body').on('keyup',input,function(e) {


        var elem = $(this),keyCode = e.keyCode;
        if (options.url !== undefined && keyCode != 13) {
    
            if (elem.val() != that.TAG_NAME) {
                $.ajax({
                  type: "get",
                  url: options.url,
                  data: {
                    tag: elem.val()
                  },
                  // 获取TAGS关键词
                  success: function(res) {
                    if (res.code == 200) {
                        that.drawHtml(res.data,elem);
                    }
                  }
              })

              that.TAG_NAME = elem.val();
            }  

            var self = $('.'+LAY_TAG_THIS);
            // 上键
            if (keyCode == 38) {
              var prev = self.prev();
              if (prev.length != 0) {
                self.removeClass(LAY_TAG_THIS);
                prev.addClass(LAY_TAG_THIS);
              }
            } // 下键
            else if (keyCode == 40) { 
              var next = self.next();
              if (next.length != 0) {
                self.removeClass(LAY_TAG_THIS);
                next.addClass(LAY_TAG_THIS);
              }
            }
        }

        // 按下回车 或者空格
        if (keyCode === 13) {

          e.preventDefault();
          e.stopPropagation();

          // 选取元素后隐藏
          var result = $('#'+LAY_TAGS_RESULT);
          if (result.length && !result.is(':hidden')) {
            result.css('display','none');
            that.drawring($('.'+LAY_TAG_THIS).text(),$(input));
          }
          else  {

            if (!$(this).val()) {
              return false;
            }

            that.drawring($(this).val(),$(this));
          }
        }
      });

      // 点击下列列表
      $('body').on('click','.tag-item',function(e) {
          that.drawring($(this).text(),$(input));
      });

      $('body').on('click','.layui-tags',function(e) {
       $(this).find('input').focus();
      });
    };

    // 渲染AJAX元素
    Class.prototype.drawHtml = function (data,wordElem) {

      var head = '<div id="'+LAY_TAGS_RESULT+'"></div>';
      var elem = $('#'+LAY_TAGS_RESULT);
      if (elem.length == 0) {
        $('body').append(head);
        elem = $('#'+LAY_TAGS_RESULT);
      }

      // 循环设置元素
      var tagsHtml = '';
      for (var element in data) {
        tagsHtml += '<li class="tag-item">'+data[element].name+'</li>';  
      }

      // 清空元素
      elem.html(tagsHtml);
      var status = tagsHtml !== '' ? 'block' : 'none';
      var left = wordElem.offset().left + document.body.scrollLeft - 10;
      var top = wordElem.offset().top + document.body.scrollTop + 30;  
      elem.css({left: left,top: top,display:status});
      $('#'+LAY_TAGS_RESULT+' .tag-item:first').addClass(LAY_TAG_THIS);
    }
    
    //记录所有实例
    thisTags.that = {};
    
    //获取当前实例对象
    thisTags.getThis = function(id){
      var that = thisTags.that[id];
      if(!that) hint.error(id ? (MOD_NAME +' instance with ID \''+ id +'\' not found') : 'ID argument required');
      return that
    };
    
    //重载实例
    MODULE_TAGS_NAME.reload = function(id, options){
      var that = thisTags.that[id];
      that.reload(options);
      
      return thisTags.call(that);
    };
  
    //核心入口
    MODULE_TAGS_NAME.render = function(options){
      var inst = new Class(options);
      return thisTags.call(inst);
    };
	
    layui.link(layui.cache.base + 'tags/tags.css?v1.0b');
    exports(MOD_NAME, MODULE_TAGS_NAME);
  })
  
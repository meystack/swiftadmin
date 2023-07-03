<?php

declare(strict_types=1);
// +----------------------------------------------------------------------
// | swiftAdmin 极速开发框架 [基于WebMan开发]
// +----------------------------------------------------------------------
// | Copyright (c) 2020-2030 http://www.swiftadmin.net
// +----------------------------------------------------------------------
// | swiftAdmin.net High Speed Development Framework
// +----------------------------------------------------------------------
// | Author: meystack <coolsec@foxmail.com> Apache2 License
// +----------------------------------------------------------------------

namespace system;
use think\Facade;

/**
 * 表单生成器
 * SAPHP框架专用
 */

class Form extends Facade
{
    protected static function getFacadeClass()
    {
    	return 'system\FormBuilder';
    }
}

class FormBuilder
{

    /**
     * Item宽度
     *
     * @var integer
     */
    public $width = 100;

    /**
     * 标签宽度
     *
     * @var integer
     */
    public $labelwidth = 110;

    /**
     * 公用属性
     *
     * @var array
     */
    public $attrs = [
        'type',
        'name',
        'min',
        'max',
        'maxlength',
        'required',
        'readonly',
        'disabled',
        'placeholder',
    ];

    public $replace = [];

    /**
     * @var object 对象实例
     */
    protected static $instance = null;

    /**
     * 表单类型
     *
     * @var boolean
     */
    protected $formtype = true;

    /**
     * 类构造函数
     * class constructor.
     */
    public function __construct()
    {}

    /**
     * 初始化
     * @access public
     * @param array $options 参数
     * @return object|FormBuilder|null
     */
    public static function instance(array $options = [])
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($options);
        }

        // 返回实例
        return self::$instance;
    }

    /**
     * 开始生成元素
     *
     * @param array $data
     * @param bool $formType
     * @return string
     */
    public function itemElem(array $data = [], bool $formType = true): string
    {

        $this->formtype = $formType;

        if ($data['tag'] == 'tab') {
            return $this->tab($data);
        }

        if ($data['tag'] == 'grid') {
            return $this->grid($data);
        }

        $itemHtml = '<div class="layui-form-item" ';

        if (isset($data['width']) && $data['width']) {
            if ($data['width'] != $this->width) {
                $itemHtml .= 'style="width:' . $data['width'] . '%;"';
            }
        }

        $itemHtml .= '>' . PHP_EOL;
        if (isset($data['label'])) {
            $itemHtml .= $this->label($data['label'], $data) . PHP_EOL;
        }

        $itemHtml .= $this->block($data) . PHP_EOL;
        $itemHtml .= '</div>' . PHP_EOL;

        return $itemHtml;
    }

    /**
     * 生成Label标签
     *
     * @param string $text
     * @param array  $data
     * @return string
     */
    public function label(string $text, array $data = []): string
    {
        $label = '<label class="layui-form-label';
        if ($data['labelhide']) {
            $label .= ' layui-hide';
        }

        $label .= '"';
        if ($data['labelwidth'] && $data['labelwidth'] != $this->labelwidth) {
            $label .= ' style="width:' . $data['labelwidth'] . 'px;"';
        }
        $label .= '>';
        if (isset($data['required']) && $data['required']) {
            $label .= '<font color="red">* </font>';
        }

        return $label .= $text . '</label>';
    }

    /**
     * 生成BLOCK区块
     *
     * @param array $data
     * @return string
     */
    public function block(array $data = []): string
    {
        $block = '<div class="layui-input-block"';

        if (isset($data['labelhide'])) {
            if ($data['labelhide']) {
                $style = 'margin-left:0';
            } else {
                if ($data['labelwidth'] && $data['labelwidth'] != $this->labelwidth) {
                    $style = 'margin-left:' . ($data['labelwidth'] + 30) . 'px';
                }
            }
        }

        if (isset($style)) {
            $block .= ' style="' . $style . '"';
        }

        $block .= '>';
        $block .= call_user_func([Form::instance(), $data['tag']], $data);
        $block .= '</div>';

        return $block;
    }

    /**
     * 获取input
     *
     * @param array $data
     * @return string
     */
    public function input(array $data = []): string
    {
        $value = $this->formtype ? 'value="{$data.' . $data['name'] . '}"' : '';
        return '<input class="layui-input" ' . $this->attributes($data) . $value . ' >';
    }

    /**
     * 获取多行编辑
     *
     * @param array $data
     * @return string
     */
    public function textarea(array $data = []): string
    {
        $value = $this->formtype ? '{$data.' . $data['name'] . '}' : '';
        return '<textarea class="layui-textarea"' . $this->attributes($data) . ' >' . $value . '</textarea>';
    }

    /**
     * 获取单选框
     *
     * @param array $data
     * @return string|string[]
     * @throws \Exception
     */
    public function radio(array $data = [])
    {
        return $this->radioCheckSelect($data,'radio');
    }

    /**
     * 获取多选框
     *
     * @param array $data
     * @return string|string[]
     * @throws \Exception
     */
    public function checkbox(array $data = [])
    {
        if (!$this->formtype) {
            throw new \Exception('多选框不支持生成内置表单');
        }
        return $this->radioCheckSelect($data,'checkbox','[]');
    }

    /**
     * 获取下拉框
     *
     * @param array $data
     * @return string|string[]
     * @throws \Exception
     */
    public function select(array $data = [])
    {
        return $this->radioCheckSelect($data,'select');
    }

    /**
     * 验证选项
     *
     * @param array $options
     * @return string|string[]|null
     * @throws \Exception
     */
    public function validOptions(array $options = [])
    {
        if (!is_array($options) || !$options) {
            throw new \Exception("Options is Empty", 1);
        }

        $export = var_exports($options, true);
        return preg_replace('/\s+/', '', $export);
    }

    /**
     * 获取PHP代码
     *
     * @param [type] $argc
     * @param [type] $options
     * @return string
     */
    public function getVarPHPList($argc = null, $options = null): string
    {
        return PHP_EOL . "<php>$$argc = $options;</php>";
    }

    /**
     * 获取模板
     *
     * @param array $data
     * @param string $type
     * @param string $attr
     * @return string|string[]
     * @throws \Exception
     */
    public function radioCheckSelect(array $data = [], string $type = '', string $attr = '' )
    {
        $options = $this->validOptions($data['options']);
        $varName = ucfirst($data['name']).'_LIST';
        $getAttr = $this->attributes($data,$attr);
        $varHtml = $this->getVarPHPList($varName, $options);
        $varHtml .= read_file($this->getHtmlTpl($type));

        $this->replace = [
            'varlist' => $varName,
            'field' => $data['name'],
            'attributes' => $getAttr,
        ];

        foreach ($this->replace as $key => $value) {
            $varHtml = str_replace("{%$key%}", $value, $varHtml);
        }

        return $varHtml;
    }

    /**
     * 获取日期
     *
     * @param array $data
     * @return string
     */
    public function date(array $data = []): string
    {
        $value = $this->formtype ? 'value="{$data.' . $data['name'] . '}"' : '';
        return '<input class="layui-input" lay-datetime="" ' . $this->attributes($data) . $value . ' >';
    }

    /**
     * 获取颜色选择器
     *
     * @param array $data
     * @return string
     * @throws \Exception
     */
    public function colorpicker(array $data = []): string
    {
        $value = $this->formtype ? 'value="{$data.' . $data['name'] . '}"' : '';

        if (!$this->formtype) {
            throw new \Exception('颜色选择器不支持生成内置表单');
        }

        return <<<Eof
            <input class="layui-input layui-hide" {$this->attributes($data)} {$value} >
            <div lay-colorpicker="{$data['name']}"></div>
        Eof;
    }

    /**
     * 获取滑块
     *
     * @param array $data
     * @return string
     */
    public function slider(array $data = []): string
    { 
        $value = $this->formtype ? 'value="{$data.' . $data['name'] . '}"' : '';
        return <<<Eof
            <input class="layui-input layui-hide" name="{$data['name']}" {$value} >
            <div class="lay-slider" lay-slider="{$data['name']}" {$this->attributes($data)} ></div>
        Eof;
    }

    /**
     * 获取评分
     *
     * @param array $data
     * @return string
     * @throws \Exception
     */
    public function rate(array $data = []): string
    {

        $value = $this->formtype ? 'value="{$data.' . $data['name'] . '}"' : '';

        if (!$this->formtype) {
            throw new \Exception("评分组件不支持生成内置表单");
        }

        return <<<Eof
            <input class="layui-input layui-hide" name="{$data['name']}" {$value} >
            <div lay-rate="{$data['name']}" {$this->attributes($data)} ></div>
        Eof;
    }

    /**
     * 获取开关
     *
     * @param array $data
     * @return string
     */
    public function switch(array $data = []): string
    {
        $value = $this->formtype ? 'value="{$data.' . $data['name'] . '}"' : '';
        $param = '$data.' . $data['name'];
        if ($this->formtype) {
            return <<<Eof
            <input  type="hidden" type="checkbox" name="{$data['name']}" value="0" />
            <input type="checkbox" name="{$data['name']}" value="$value" <eq name="{$param}" value="1" > checked </eq> lay-skin="switch" />
        Eof;
        }
        return <<<Eof
            <input  type="hidden" type="checkbox" name="{$data['name']}" value="0" />
            <input type="checkbox" name="{$data['name']}" value="1" lay-skin="switch" />
        Eof;
    }

    /**
     * 获取级联选择器
     *
     * @param array $data
     * @return string
     * @throws \Exception
     */
    public function cascader(array $data = [])
    {
        if (!$this->formtype) {
            throw new \Exception("级联选择器不支持生成内置表单");
        }
        $value = 'value="{$data.' . $data['name'] . '}"';
        return <<<Eof
            <input type="text" id="{$data['name']}" class="layui-hide" lay-cascader="" {$this->attributes($data)} {$value} />
        Eof;
    }

    /**
     * 获取富文本
     *
     * @param array $data
     * @return string
     * @throws \Exception
     */
    public function editor(array $data = []): string
    {
        if (!$this->formtype) {
            throw new \Exception("富文本不支持生成内置表单");
        }
        // 非INPUT表单 值
        $value = '{$data.' . $data['name'] . '}';
        return <<<Eof
            <textarea id="{$data['name']}" {$data['editorType']} class="layui-hide" {$this->attributes($data)} type="layui-textarea" >{$value}</textarea>
        Eof;
    }

    /**
     * 获取上传模板
     *
     * @param array $data
     * @return false|string|string[]
     * @throws \Exception
     */
    public function upload(array $data = [])
    {

        if (!$this->formtype && ($data['uploadtype'] == 'multiple' || $data['uploadtype'] == 'images')) {
            throw new \Exception("上传组件仅支持 File类型 生成内置表单");
        }

        $value = $this->formtype ? '{$data.' . $data['name'] . '}' : '';
        $varHtml = read_file($this->getHtmlTpl($data['uploadtype']));
        $this->replace = [
            'value' => $value,
            'field' => $data['name'],
            'accept' => $data['data_accept'],
            'size' => (string)$data['data_size'],
        ];

        foreach ($this->replace as $key => $value) {
            $varHtml = str_replace("{%$key%}", $value, $varHtml);
        }

        return $varHtml;
    }

    /**
     * 获取TAGS模板
     *
     * @param array $data
     * @return string
     * @throws \Exception
     */
    public function tags(array $data = []): string
    {
        $value = 'value="{$data.' . $data['name'] . '}"';
        return '<input type="text" lay-tags="" id="' . $data['name'] . '" name="' . $data['name'] .'" '. $value .' class="layui-input" >';
    }

    /**
     * 获取JSON模板
     *
     * @param array $data
     * @return false|string|string[]
     * @throws \Exception
     */
    public function json(array $data = [])
    {
        if (!$this->formtype) {
            throw new \Exception("JSON组件不支持生成内置表单");
        }

        $value = $this->formtype ? 'value="{$data.' . $data['name'] . '}"' : '';
        $jsonHtml = read_file($this->getHtmlTpl($data['tag']));

        $this->replace = [
            'value' => $value,
            'field' => $data['name'],
        ];

        foreach ($this->replace as $key => $value) {
            $jsonHtml = str_replace("{%$key%}", $value, $jsonHtml);
        }

        return $jsonHtml;
    }

    /**
     * 获取提示器
     *
     * @param array $data
     * @return string
     */
    public function tips(array $data = []): string
    {
        return '<div class="layui-input-inline"><i class="layui-icon layui-icon-about" lay-tips="' . $data['msg'] . '" data-offset="' . $data['offset'] . '"></i></div>';
    }

    /**
     * 获取便签
     *
     * @param array $data
     * @return string
     */
    public function note(array $data = []): string
    {
        return '<blockquote class="layui-elem-quote">' . $data['textarea'] . '</blockquote>';
    }

    /**
     * 获取横线
     *
     * @param array $data
     * @return string
     */
    public function subtraction(array $data = []): string
    {
        return '<hr class="' . $data['border'] . '">';
    }

    /**
     * 获取行高
     *
     * @param array $data
     * @return string
     */
    public function space(array $data = []): string
    {
        return '<div style="height:' . $data['height'] . 'px;"></div>';
    }

    /**
     * 获取选项卡
     *
     * @param array $data
     * @return string
     */
    public function tab(array $data = []): string
    {
        $tabHtml = '<div id="layui-tab" id="' . $data['name'] . '" class="layui-tab layui-tab-brief">';
        $tabHtml .= '<ul class="layui-tab-title">';
        $tabContent = '';
        foreach ($data['options'] as $key => $option) {
            $tabHtml .= '<li class="' . ($option['checked'] ? 'layui-this' : '') . '">' . $option['title'] . '</li>';
            $tabContent .= '<div class="layui-tab-item ' . ($option['checked'] ? 'layui-show ' : '') . '" data-index="' . $key . '">';

            foreach ($data['children'][$key] as $children) {
                foreach ($children as $elem) {
                    $tabContent .= $this->itemElem($elem);
                }
            }

            $tabContent .= '</div>';
        }

        $tabHtml .=  '</ul>';
        $tabHtml .= '<div class="layui-tab-content">' . $tabContent . '</div>';
        $tabHtml .= '</div>';
        return $tabHtml;
    }

    /**
     * 获取布局组件
     *
     * @param array $data
     * @return string
     */
    public function grid(array $data = []): string
    {
        $gridHtml = '<div class="layui-form-item layui-row" >';
        $col = 12 / $data['column'];
        for ($key=0; $key < $data['column']; $key++) { 

            $gridHtml .= '<div class="layui-col-md' .$col. ' layui-grid-' .$key. '" data-index="' .$key. '">';
            
            foreach ($data['children'][$key] as $children) {
                foreach ($children as $elem) {
                    $gridHtml .= $this->itemElem($elem);
                }
            }

            $gridHtml .= '</div>';
        }

        $gridHtml .= '</div>';

        return $gridHtml;
    }

    /**
     * 获取表单属性
     *
     * @param array $data
     * @param string $suffix
     * @return string
     */
    public function attributes(array $data = [], string $suffix = ''): string
    {
        $vars = [];
        foreach ($data as $key => $elem) {
            if (array_search($key, $this->attrs)) {

                if (!$elem) {
                    continue;
                }

                // 单独处理NAME值
                if ($key == 'name') {
                    $elem .= $suffix;
                }

                $vars[] = $key . '="' . $elem . '"';
            } else {
                if (strstr($key, 'lay_') || strstr($key, 'data_')) {
                    $_key = str_replace('_', '-', $key);
                    $vars[] = $_key . '="' . $elem . '"';
                }
            }
        }

        return count($vars) > 0 ? ' ' . implode(' ', $vars) : '';
    }

    /**
     * 获取模板文件
     *
     * @param [type] $name
     * @return string
     */
    protected function getHtmlTpl($name): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'form' . DIRECTORY_SEPARATOR . $name . '.html';
    }
}

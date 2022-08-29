<?php

declare(strict_types=1);
// +----------------------------------------------------------------------
// | swiftAdmin 极速开发框架 [基于WebMan开发]
// +----------------------------------------------------------------------
// | Copyright (c) 2020-2030 http://www.swiftadmin.net
// +----------------------------------------------------------------------
// | swiftAdmin.net High Speed Development Framework
// +----------------------------------------------------------------------
// | Author: meystack <coolsec@foxmail.com> Apache 2.0 License
// +----------------------------------------------------------------------
namespace app\common\library;

use Psr\SimpleCache\InvalidArgumentException;

/**
 * 全局模型数据处理类
 * 1、自动设置字段属性
 * 2、执行数据库事件回调
 */
class ParseData
{

    /**
     * 获取标题拼音
     * @access      public
     * @param string $pinyin 属性值
     * @param array $data 当前数组
     * @return      string
     */
    public static function setPinyinAttr(string $pinyin, array $data): string
    {
        if (empty($pinyin)) {
            return pinyin($data['title'], true);
        }

        return trim($pinyin);
    }

    /**
     * 获取标题首字母
     * @access      public
     * @param string $letter 属性值
     * @param array $data 当前数组
     * @return      string
     */
    public static function setLetterAttr(string $letter, array $data): string
    {
        if (empty($letter)) {

            $pinyin = pinyin($data['title'], true);
            return substr($pinyin, 0, 1);
        }
        return $letter;
    }


    /**
     * 自动获取描述
     * @access      public
     * @param string $description 属性值
     * @param array $data 当前数组
     * @return      string
     */
    public static function setDescriptionAttr(string $description, array $data): string
    {
        if (empty($description) && !empty($data['content'])) {
            return msubstr($data['content'], 0, 80);
        }

        return $description;
    }

    /**
     * 内容数据修改器
     * @access  public
     * @param string $content
     * @return  string
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public static function setContentAttr(string $content): string
    {
        if ($prefix = cdn_Prefix()) {
            $content = str_replace($prefix, '', $content);
        }

        return $content;
    }

    /**
     * 获取内容数据
     * @access  public
     * @param string $content
     * @return  string
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public static function getContentAttr(string $content): string
    {
        if (!empty($content)) {

            // 是否开启前缀
            if ($prefix = cdn_Prefix()) {
                $pattern = "/<img.*?src=\"(.*?)\"/i";
                if (preg_match_all($pattern, $content, $images)) {
                    $images = array_unique($images[1]);
                    foreach ($images as $value) {
                        $value = urldecode($value);
                        if (!strpos($value, '://')) {
                            $content = str_replace($value, $prefix . $value, $content);
                        }
                    }
                }
            }
        }

        return $content;
    }

    /**
     * 自动补全图片
     * @access  public
     * @param string $image
     * @param $data
     * @param bool $ready
     * @return  string
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public static function setImageAttr(string $image, $data, bool $ready = false): string
    {
        if (empty($image) && !empty($data['content']) && $ready) {
            $pattern = "/<img.*?src=\"(.*?)\"/i";
            $prefix = cdn_Prefix();
            if (preg_match($pattern, $data['content'], $images)) {
                return $prefix ? str_replace($prefix, '', $images[1]) : $images[1];
            }
        }

        return self::changeImages($image, false);
    }

    /**
     * 获取图片链接
     * @access  public
     * @param string $image
     * @return  string
     */
    public static function getImageAttr(string $image): string
    {
        if (!empty($image)) {
            $image = urldecode($image);
        }

        if ($image && strpos($image, '://')) {
            return $image;
        }

        return self::changeImages($image);
    }

    /**
     * 处理图片实例
     * @access  public
     * @param string $image 图片地址
     * @param bool $bool 链接OR替换
     * @return  string
     */
    protected static function changeImages(string $image, bool $bool = true): string
    {
        $prefix = cdn_Prefix();
        var_dump($prefix);
        if (!empty($prefix) && $image) {
            if (!strpos($image, 'data:image')) {
                return $bool ? $prefix . $image : str_replace($prefix, '', $image);
            }
        }

        return $image;
    }

    /**
     * 获取IP转换
     * @access  public
     * @param  $ip
     * @return mixed
     */
    public static function getIPAttr($ip)
    {
        if (!empty($ip)) {
            $ip = long2ip($ip);
        }

        return $ip;
    }

    /**
     * 设置IP转换
     * @access  public
     * @param  $ip
     * @return mixed
     */
    public static function setIPAttr($ip)
    {
        if (!empty($ip)) {
            $ip = ip2long($ip);
        }

        return $ip;
    }

    /**
     * 设置独立模板
     * @access  public
     * @param string $skin 模板名称
     * @return  string
     */
    public static function setSkinAttr(string $skin): string
    {
        if ($skin) {
            $skin = str_replace(['.html', '.htm'], '', $skin);
        }

        return $skin;
    }
}

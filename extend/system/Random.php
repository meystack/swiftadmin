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

class Random
{

    /**
     * @var object 对象实例
     */
    protected static $instance = null;

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
     * @return self
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
     * 生成大小写字母
     *
     * @param integer $length
     * @return string
     */
    public static function alpha(int $length = 6): string
    {
        return self::Generate('alpha', $length);
    }

    /**
     * 生成纯数字
     * @param integer $length
     * @return string
     */
    public static function number(int $length = 6): string
    {
        return self::Generate('number', $length);
    }

    /**
     * 生成小写字母
     * @param integer $length
     * @return string
     */
    public static function lower(int $length = 6): string
    {
        return self::Generate('lower', $length);
    }

    /**
     * 生成大写字母
     * @param integer $length
     * @return string
     */
    public static function upper(int $length = 6): string
    {
        return self::Generate('upper', $length);
    }

    /**
     * 下划线随机
     *
     * @param integer $length
     * @return string
     */
    public static function alphaDash(int $length = 6): string
    {
        return self::Generate('alphaDash', $length);
    }

    /**
     * 生成数字+字母
     * @param integer $length
     * @return string
     */
    public static function alphaNum(int $length = 6): string
    {
        return self::Generate('alphaNum', $length);
    }

    /**
     * 生成随机字符
     * @param string $type
     * @param integer $length
     * @return string
     */
    public static function Generate(string $type = 'alpha', int $length = 6): string
    {
        $config = [
            'number'    => '1234567890',
            'lower'     => 'abcdefghijklmnopqrstuvwxyz',
            'upper'     => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'alpha'     => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'alphaDash' => '_abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'alphaNum'  => '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
        ];

        $letter = str_shuffle($config[$type]);
        return substr($letter, 0, $length);
    }

    /**
     * 生成订单ID
     *
     * @param boolean $other
     * @return string
     */
    public static function orderId(bool $other = false): string
    {
        if (!$other) {
            return date('Ymd') . str_pad((string)mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        } else {
            return date('Ymd') . substr(implode('', array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
        }
    }
    
}

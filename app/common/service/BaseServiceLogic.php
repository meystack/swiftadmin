<?php

namespace app\common\service;

/**
 * 基础服务类
 * @package app\common\service
 */
class BaseServiceLogic
{
    /**
     * 返回状态码
     * @var int
     */
    protected static int $code = 0;

    /**
     * 错误信息
     * @var string
     */
    protected static string $error;

    /**
     * 返回数据
     * @var array
     */
    protected static array $data;

    /**
     * 设置返回状态码
     * @param int $code
     * @return void
     */
    public static function setCode(int $code): void
    {
        self::$code = $code;
    }

    /**
     * 获取返回状态码
     * @return int
     * @return void
     */
    public static function getCode(): int
    {
        return self::$code;
    }

    /**
     * 设置错误信息
     * @param string $error
     * @return void
     */
    public static function setError(string $error): void
    {
        self::$error = $error;
    }

    /**
     * 获取错误信息
     * @return string
     * @return void
     */
    public static function getError(): string
    {
        return self::$error;
    }

    /**
     * 设置返回数据
     * @param array $data
     * @return void
     */
    public static function setData(array $data): void
    {
        self::$data = $data;
    }

    /**
     * 获取返回数据
     * @return array
     * @return void
     */
    public static function getData(): array
    {
        return self::$data;
    }

}
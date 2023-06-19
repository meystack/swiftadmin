<?php

namespace app\common\exception;

/**
 * 全局操作异常类
 * Class OperateException
 * @package app\common\exception
 */
class DumpException extends \Exception
{
    /**
     * 附加数据
     * @var array
     */
    public array $data    = [];

    public function __construct($message = '', $code = 0, array $data = [], \Throwable $previous = null)
    {
        $this->data     = $data;
        $this->code     = $code;
        $this->message  = $message;
        parent::__construct($this->message, $this->code, $previous);
    }
}
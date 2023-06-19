<?php

namespace app\common\exception;

use app\common\library\ResultCode;

/**
 * 全局操作异常类
 * Class OperateException
 * @package app\common\exception
 */
class OperateException extends \Exception
{
    /**
     * 附加数据
     * @var array
     */
    public array $data    = [];

    public function __construct($message = '', $code = 0, array $data = [], \Throwable $previous = null)
    {
        $this->data     = $data;
        $this->code     = $code    ?: ResultCode::UNKNOWN['code'];
        $this->message  = $message ?: ResultCode::UNKNOWN['msg'];
        parent::__construct($this->message, $this->code, $previous);
    }
}
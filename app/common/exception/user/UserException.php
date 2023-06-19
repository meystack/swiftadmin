<?php

namespace app\common\exception\user;
use app\common\library\ResultCode;

/**
 * 用户异常类
 * Class UserException
 */
class UserException extends \Exception
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
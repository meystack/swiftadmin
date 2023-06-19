<?php

namespace app\common\exception;

use app\common\model\system\SystemLog;
use Psr\SimpleCache\InvalidArgumentException;
use support\exception\BusinessException;
use think\db\exception\DataNotFoundException;
use think\exception\ValidateException;
use Webman\Exception\ExceptionHandler;
use Webman\Http\Request;
use Webman\Http\Response;
use Throwable;

class ExceptionHandle extends ExceptionHandler
{
    public $dontReport = [
        BusinessException::class,
        ValidateException::class,
        DataNotFoundException::class,
        OperateException::class,
        DumpException::class,
    ];

    /**
     * 异常日志记录
     * @param Throwable $exception
     * @throws InvalidArgumentException
     */
    public function report(Throwable $exception)
    {
        if (saenv('system_exception')
            && !$this->shouldntReport($exception)) {
            $logs['module'] = request()->app;
            $logs['controller'] = request()->controller;
            $logs['action'] = request()->action;
            $logs['params'] = serialize(request()->all());
            $logs['method'] = request()->method();
            $logs['url'] = request()->url();
            $logs['ip'] = request()->getRealIp();
            $logs['name'] = session('AdminLogin.name') ?? 'system';
            $logs['type'] = 1;
            $logs['code'] = $exception->getCode();
            $logs['file'] = $exception->getFile();
            $logs['line'] = $exception->getLine();
            $logs['error'] = $exception->getMessage();
            SystemLog::write($logs);
        }
    }

    /**
     * @param Throwable $exception
     * @param Request $request
     * @return Response
     */
    public function render(Request $request, Throwable $exception): Response
    {
        switch (true) {
            case $exception instanceof OperateException:
            case $exception instanceof ValidateException:
                return json(['code' => $exception->getCode() ?? 101, 'msg' => $exception->getMessage()]);
            case $exception instanceof DumpException:
                return \response($exception->getMessage());
            default:
                break;
        }

        return get_env('APP_DEBUG') ? parent::render($request, $exception) : view(config('app.exception_tpl'), ['trace' => $exception]);
    }
}
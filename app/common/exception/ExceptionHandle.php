<?php

namespace app\common\exception;

use app\common\model\system\SystemLog;
use Psr\SimpleCache\InvalidArgumentException;
use support\exception\BusinessException;
use Webman\Http\Request;
use Webman\Http\Response;
use Throwable;

class ExceptionHandle extends \Webman\Exception\ExceptionHandler
{
    public $dontReport = [
        BusinessException::class,
    ];

    public function report(Throwable $exception)
    {

        try {

            if (saenv('system_exception') && !empty($exception->getMessage())) {

                $data = [
                    'module'     => request()->app,
                    'controller' => request()->controller,
                    'action'     => request()->action,
                    'params'     => serialize(request()->all()),
                    'method'     => request()->method(),
                    'url'        => request()->url(),
                    'ip'         => request()->getRealIp(),
                    'name'       => session('AdminLogin.name'),
                ];

                if (empty($data['name'])) {
                    $data['name'] = 'system';
                }

                $data['type'] = 1;
                $data['code'] = $exception->getCode();
                $data['file'] = $exception->getFile();
                $data['line'] = $exception->getLine();
                $data['error'] = $exception->getMessage();
                SystemLog::write($data);
            }

        } catch (InvalidArgumentException $e) {
        }
        parent::report($exception);
    }

    public function render(Request $request, Throwable $exception): Response
    {
        if (!file_exists(root_path(). '.env')) {
            return parent::render($request, $exception);
        }
        return getenv('APP_DEBUG') ? parent::render($request, $exception) : view(config('app.exception_tpl'), ['trace' => $exception]);
    }
}
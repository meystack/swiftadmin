<?php

namespace app\common\exception;

use app\common\model\system\SystemLog;
use Psr\SimpleCache\InvalidArgumentException;
use support\exception\BusinessException;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use Webman\Exception\ExceptionHandler;
use Webman\Http\Request;
use Webman\Http\Response;
use Throwable;

class ExceptionHandle extends ExceptionHandler
{
    public $dontReport = [
        BusinessException::class,
    ];

    /**
     *
     * @param Throwable $exception
     * @return void|mixed
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function report(Throwable $exception)
    {
        try {
            if (saenv('system_exception')
                && !empty($exception->getMessage())) {
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

    /**
     * @param Throwable $exception
     * @param Request $request
     * @return Response
     */
    public function render(Request $request, Throwable $exception): Response
    {
        if (!file_exists(root_path(). '.env')) {
            return parent::render($request, $exception);
        }
        return getenv('APP_DEBUG') ? parent::render($request, $exception) : view(config('app.exception_tpl'), ['trace' => $exception]);
    }
}
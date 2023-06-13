<?php
declare (strict_types=1);
// +----------------------------------------------------------------------
// | swiftAdmin 极速开发框架 [基于WebMan开发]
// +----------------------------------------------------------------------
// | Copyright (c) 2020-2030 http://www.swiftadmin.net
// +----------------------------------------------------------------------
// | swiftAdmin.net High Speed Development Framework
// +----------------------------------------------------------------------
// | Author: meystack <coolsec@foxmail.com> Apache 2.0 License
// +----------------------------------------------------------------------
namespace app;
use support\Log;
use support\Response;
use think\db\exception\BindParamException;
use think\facade\Db;
use think\helper\Str;
use think\Validate;
use Webman\Http\Request;
use Webman\Captcha\CaptchaBuilder;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class BaseController
{
    /**
     * 数据库实例
     * @var object
     */
    public object $model;

    /**
     * 是否验证
     * @var bool
     */
    public bool $isValidate = true;

    /**
     * 验证场景
     * @var string
     */
    public string $scene = '';

    /**
     * 是否批量验证
     * @var bool
     */
    protected bool $batchValidate = false;

    /**
     * 获取访问来源
     * @var string
     */
    public mixed $referer;

    public function __construct()
    {
        $this->referer = \request()->header('referer');
    }

    /**
     * 验证数据
     * @access protected
     * @param array $data 数据
     * @param $validate
     * @param array $message 提示信息
     * @param bool $batch 是否批量验证
     * @return bool
     */
    protected function validate(array $data, $validate, array $message = [], bool $batch = false): bool
    {
        if (is_array($validate)) {
            $v = new Validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                [$validate, $scene] = explode('.', $validate);
            }
            $class = str_contains($validate, '\\') ? $validate : $this->parseClass('validate', $validate);
            $v = new $class();
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        $v->message($message);

        // 是否批量验证
        if ($batch || $this->batchValidate) {
            $v->batch();
        }

        return $v->failException()->check($data);
    }

    /**
     * 解析应用类的类名
     * @access public
     * @param string $layer 层名 controller model ...
     * @param string $name 类名
     * @return string
     */
    protected function parseClass(string $layer, string $name): string
    {
        $name = str_replace(['/', '.'], '\\', $name);
        $array = explode('\\', $name);
        $class = Str::studly(array_pop($array));
        $path = $array ? implode('\\', $array) . '\\' : '';
        return 'app' . '\\' . $layer . '\\' . $path . $class;
    }

    /**
     * 操作成功跳转的快捷方法
     * @access protected
     * @param mixed $msg 提示信息
     * @param null $url 跳转的URL地址
     * @param mixed $data 返回的数据
     * @param int $count
     * @param int $code
     * @param integer $wait 跳转等待时间
     * @param array $header 发送的Header信息
     * @return Response
     */
    protected function success(mixed $msg = '', $url = null, mixed $data = '', int $count = 0, int $code = 200, int $wait = 3, array $header = []): Response
    {
        if (is_null($url) && isset($_SERVER["HTTP_REFERER"])) {
            $url = $_SERVER["HTTP_REFERER"];
        }

        $msg = !empty($msg) ? __($msg) : __('操作成功！');
        $result = [
            'code'  => $code,
            'msg'   => $msg,
            'data'  => $data,
            'count' => $count,
            'url'   => (string)$url,
            'wait'  => $wait,
        ];

        $type = $this->getResponseType();
        if ($type == 'html') {
            return view(config('app.dispatch_success'), $result);
        }

        return json($result);
    }

    /**
     * 操作错误跳转的快捷方法
     * @access protected
     * @param mixed $msg 提示信息
     * @param null $url 跳转的URL地址
     * @param mixed $data 返回的数据
     * @param int $code
     * @param integer $wait 跳转等待时间
     * @param array $header 发送的Header信息
     * @return Response
     */
    protected function error(mixed $msg = '', $url = null, mixed $data = '', int $code = 101, int $wait = 3, array $header = []): Response
    {
        if (is_null($url)) {
            $url = request()->isAjax() ? '' : 'javascript:history.back(-1);';
        }

        $msg = !empty($msg) ? __($msg) : __('操作失败！');
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'data' => $data,
            'url'  => (string)$url,
            'wait' => $wait,
        ];

        $type = $this->getResponseType();
        if ($type == 'html') {
            return view(config('app.dispatch_error'), $result);
        }

        return json($result);
    }


    /**
     * URL重定向
     * @access protected
     * @param string $url 跳转的URL表达式
     * @param integer $code http code
     * @param array $headers
     * @return Response
     */
    protected function redirect(string $url, int $code = 302, array $headers = []): Response
    {
        return redirect($url, $code, $headers);
    }

    /**
     * 获取当前的response 输出类型
     * @access protected
     * @return string
     */
    protected function getResponseType(): string
    {
        $mask=request()->input('_ajax')==1 ||request()->input('_pjax')==1;
        return request()->isAjax() || request()->acceptJson() || $mask ? 'json' : 'html';
    }

    /**
     * 返回错误信息
     * @param string $msg
     * @param int $code
     * @param string $app
     * @return Response
     */
    protected function retResponseError(string $msg = '404 not found', int $code = 404, string $app = 'index'): Response
    {
        if (\request()->expectsJson()) {
            return json(['code' => 404, 'msg' => $msg]);
        }
        return response(request_error($app), $code);
    }

    /**
     * 获取模型字段集
     * @param null $model
     */
    protected function getTableFields($model = null)
    {
        $model = $model ?: $this->model;
        $tableFields = $model->getTableFields();
        if (!empty($tableFields) && is_array($tableFields)) {
            foreach ($tableFields as $key => $value) {
                $filter = ['update_time', 'create_time', 'delete_time'];
                if (!in_array($value, $filter)) {
                    $tableFields[$value] = '';
                }

                unset($tableFields[$key]);
            }
        }

        return $tableFields;
    }

    /**
     * 输出验证码图像
     * @param Request $request
     * @return Response
     */
    public function captcha(Request $request): Response
    {
        $builder = new CaptchaBuilder;
        $builder->build();
        $request->session()->set('captcha', strtolower($builder->getPhrase()));
        $img_content = $builder->get();
        return response($img_content, 200, ['Content-Type' => 'image/jpeg']);
    }

    /**
     * 导入数据
     * @return Response
     * @throws Exception
     * @throws BindParamException
     */
    public function import(): Response
    {
        $file = request()->file('file');
        if (!$file || !$file->isValid()) {
            return $this->error('上传文件校验失败！');
        }

        // 获取临时目录
        $filePath = uniqid() . '.' . strtolower($file->getUploadExtension());
        $resource = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filePath;
        if (!$file->move($resource)) {
            return $this->error('上传文件读写失败！');
        }

        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        if (!in_array($ext, ['xls', 'xlsx'])) {
            return $this->error('仅支持xls xlsx文件格式！');
        }

        try {
            // 实例化Excel对象
            $fileType = IOFactory::identify($filePath);
            $reader = IOFactory::createReader($fileType);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($resource);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }

        // 默认获取第一张表
        $currentSheet = $spreadsheet->getSheet(0);
        $listSheetData = $currentSheet->toArray();

        // 数据量最小为1条
        $listRows = count($listSheetData);
        if ($listRows <= 2) {
            return $this->error('数据行最小为2！');
        }

        // 获取Excel首行预处理
        $fields = $listSheetData[0];
        array_shift($listSheetData);

        // 获取数据表字段注释
        $table = $this->model->getTable();
        $columns = Db::query("SHOW FULL COLUMNS FROM {$table}");
        $comments = array_column($columns, 'Comment', 'Field');
        $columnType = !isset($this->columnType) ? 'comment' : $this->columnType;

        // 循环处理要插入的row
        $inserts = [];
        foreach ($listSheetData as $row => $item) {
            foreach ($fields as $key => $value) {
                $excelValue = function ($field, $value) {
                    if (in_array($field, ['create_time', 'update_time']) && !empty($value)) {
                        $time = Date::excelToTimestamp($value);
                        $value = strlen((string)$time) >= 12 ? $value : $time;
                        if ($value <= 1) { // 负值时间戳
                            $value = time();
                        }
                    }
                    return $value;
                };
                // 默认首行为注释模式
                if (strtolower($columnType) == 'comment') {
                    $field = array_search($value, $comments);
                    if (!empty($field)) {
                        $inserts[$row][$field] = $excelValue($field, $item[$key]);
                    }
                } else if (array_key_exists($value, $comments)) {
                    $inserts[$row][$value] = $excelValue($value, $item[$key]);
                }
            }

            // 录入登录用户ID
            if (array_key_exists('admin_id', $comments)) {
                $entry_id = $inserts[$row]['admin_id'] ?? 0;
                if (empty($entry_id)) {
                    $inserts[$row]['admin_id'] = get_admin_id();
                }
            }
        }

        // 判断是否有可导入的数据
        if (count($inserts) == 0) {
            return $this->error('没有可导入的数据！');
        }

        try {
            // 批量插入数据
            $this->model->insertAll($inserts);
            unlink($resource);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }

        return $this->success('导入成功！', '/');
    }

    /**
     * 导出数据
     * @return Response
     * @throws BindParamException
     */
    public function export(): Response
    {
        if (\request()->isAjax()) {

            // 获取分页
            $page = input('page', 1);
            $limit = input('limit', 1000);

            // 查询表数据
            $table = $this->model->getTable();
            $columns = Db::query("SHOW FULL COLUMNS FROM {$table}");
            $titles = array_column($columns, 'Comment', 'Field');
            // 支持导出空白数据 用于数据导入模板
            $data = $this->model->limit($limit)->page($page)->select()->toArray();
            $folder = date('Y-m-d', time());
            // 使用表注释为文件名称
            $tableInfo = Db::query("SHOW TABLE STATUS LIKE '{$table}'");
            $Comment = $tableInfo[0]['Comment'] ?: '数据_';
            $fileName = $Comment . $folder . '.xlsx';
            $filePath = public_path('upload/files') . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $fileName;
            if (!$this->exportThread($titles, $data, $filePath)) {
                return $this->error('导出失败！');
            }

            $downUrl = str_replace(public_path(), '', $filePath);
            return $this->success('导出成功！', $downUrl);
        }

        return $this->error('非法请求！');
    }

    /**
     * @param array $titles
     * @param array $data
     * @param string $filePath
     * @return bool
     */
    protected function exportThread(array $titles, array $data, string $filePath): bool
    {
        // 实例化Xls接口
        $spreadSheet = new Spreadsheet();
        $activeSheet = $spreadSheet->getActiveSheet();

        // 设表列头样式居中
        $activeSheet->getStyle('A1:AZ1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $columnType = !isset($this->columnType) ? 'comment' : $this->columnType;

        try {

            $titCol = 'A';
            foreach ($titles as $key => $value) {
                $value = $columnType == 'comment' ? $value : $key;
                $activeSheet->setCellValue($titCol . '1', $value);
                $titCol++;
            }

            $rowLine = 2;
            foreach ($data as $item) {
                $rowCol = 'A';
                foreach ($item as $value) {
                    $activeSheet->setCellValue($rowCol . $rowLine, $value);
                    $rowCol++;
                }
                $rowLine++;
            }

            $writer = IOFactory::createWriter($spreadSheet, 'Xlsx');
            mk_dirs(dirname($filePath));
            $writer->save($filePath);
            $spreadSheet->disconnectWorksheets();
            unset($spreadsheet);
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * 检查验证码
     * @param string $text
     * @return bool
     */
    protected function captchaCheck(string $text): bool
    {
        $captcha = $text ?? \request()->post('captcha');
        if (strtolower($captcha) !== \request()->session()->get('captcha')) {
            return false;
        }

        return true;
    }
}
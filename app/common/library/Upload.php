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
namespace app\common\library;

use Psr\SimpleCache\InvalidArgumentException;
use system\Http;

use app\common\model\system\Attachment;
use app\common\validate\system\UploadFile;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use Webman\Event\Event;

/**
 * UPLOAD文件上传类
 */
define('DS', DIRECTORY_SEPARATOR);

class Upload
{
    /**
     * @var object 对象实例
     */
    protected static $instance = null;

    /**
     * 文件类型
     */
    protected mixed $fileClass = '';

    /**
     * 文件名称
     */
    protected mixed $filename = '';

    /**
     * 文件保存路径
     */
    protected mixed $filepath = '';

    /**
     * 文件全路径名称
     */
    protected mixed $resource = '';

    /**
     * 附件信息
     */
    protected mixed $fileInfo = [];

    /**
     * 图形对象实例
     */
    protected mixed $Images = '';

    /**
     * 错误信息
     */
    protected string $_error = '';

    /**
     * 配置文件
     */
    protected mixed $config = [];

    /**
     * 类构造函数
     * class constructor.
     * @throws InvalidArgumentException
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function __construct()
    {
        $this->Images = new Images();
        if ($config = saenv('upload', true)) {
            $this->config = array_merge($this->config, $config);
        }
    }

    /**
     * 初始化
     * @access public
     * @param array $options 参数
     * @return self
     * @throws DataNotFoundException
     * @throws DbException
     * @throws InvalidArgumentException
     * @throws ModelNotFoundException
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
     * 文件上传
     * @return array|false
     * @throws \Exception|InvalidArgumentException
     */
    public function upload()
    {
        $param = request()->all();
        $action = input('action');
        $file = request()->file('file');

        if (!$file || !$file->isValid()) {
            $this->setError('上传文件读写失败！');
            return false;
        }

        if (!$this->fileFilter($file)) {
            $this->setError($this->_error);
            return false;
        }

        if ($action == 'marge') {
            return $this->multiMarge($param);
        } else if (isset($param['chunkId']) && $param['chunkId']) {
            return $this->multiPartUpload($file, $param);
        } else {

            try {
                $this->getFileSavePath($file);
                $file->move($this->resource);
            } catch (\Exception $e) {
                $this->setError($e->getMessage());
                return false;
            }

            Event::emit('uploadFileAfter', [
                'fileName' => $this->filename,
                'resource' => $this->resource
            ]);

            /**
             * 过滤gif文件
             */
            if ($this->fileClass == "images" && !str_contains($file->getUploadExtension(), 'gif')) {
                if ($this->config['upload_water']) {
                    $this->Images->waterMark($this->resource, $this->config);
                }
                if ($this->config['upload_thumb']) {
                    $this->Images->thumb(public_path() . '/' . $this->filepath, $this->filename, $this->config);
                }
            }

            $this->attachment($this->resource, $file->getUploadName(), $file->getUploadMineType());
            return $this->success('上传成功', $this->resource);
        }
    }

    /**
     * 分片上传
     * @param object $file
     * @param array $params
     * @return array|false
     * @throws InvalidArgumentException
     */
    public function multiPartUpload(object $file, array $params = [])
    {
        $index = $params['index'];
        $chunkId = $params['chunkId'];
        $chunkName = $chunkId . '_' . $index . '.part';

        // 校验分片名称
        if (!preg_match('/^[0-9\-]/', $chunkId)) {
            $this->setError('文件信息错误');
            return false;
        }

        $this->getFileSavePath($file);
        $chunkSavePath = root_path('runtime/chunks');
        $this->resource = $chunkSavePath . $chunkName;
        if (!$file->move($this->resource)) {
            $this->setError('请检查服务器读写权限！');
            return false;
        }

        $fileStream = [
            'index'    => $index,
            'fileName' => sha1($chunkId),
            'fileExt'  => $params['fileExt'],
            'filePath' => $this->filepath,
            'resource' => $this->resource
        ];

        Event::emit('uploadFileMultipart', $fileStream);
        return $this->success('分片上传成功', '', [
            'chunkId' => $chunkId,
            'index'   => intval($index)
        ]);
    }

    /**
     * 分片合并
     * @param array $params
     * @return array|false
     * @throws InvalidArgumentException
     */
    public function multiMarge(array $params = [])
    {
        $chunkId = $params['chunkId'];
        $source = $params['source'];
        $fileExt = $params['fileExt'];
        $fileSize = $params['fileSize'];
        $chunkCount = $params['chunkCount'];
        $mimeType = $params['mimeType'];

        if (!preg_match('/^[0-9\-]/', $chunkId)) {
            $this->setError('文件名错误');
            return false;
        }

        $filePath = root_path('runtime/chunks') . $chunkId;
        if (is_file($filePath)) {
            @unlink($filePath);
        }

        if (!$sourceFile = @fopen($filePath, "wb")) {
            $this->setError('文件读写错误');
            return false;
        }

        try {

            // Acquire an exclusive lock (writer).
            flock($sourceFile, LOCK_EX);
            for ($i = 0; $i < $chunkCount; $i++) {
                $partFile = "{$filePath}_{$i}.part";
                if (is_file($partFile)) {
                    if (!$handle = @fopen($partFile, "rb")) {
                        break;
                    }
                    while ($buff = fread($handle, filesize($partFile))) {
                        fwrite($sourceFile, $buff);
                    }
                    @fclose($handle);
                    @unlink($partFile);
                }
            }

            flock($sourceFile, LOCK_UN);
            @fclose($sourceFile);
            if (filesize($filePath) != $fileSize) {
                throw new \Exception('文件异常，请重新上传');
            }

        } catch (\Throwable $th) {
            $this->setError($th->getMessage());
            return false;
        }

        $newFilePath = $filePath . '.' . $fileExt;
        @rename($filePath, $newFilePath);
        $file = new \Webman\Http\UploadFile($newFilePath, $source, $mimeType, 200);
        if (!$this->fileFilter($file)) {
            $this->setError($this->_error);
            return false;
        }

        try {
            $this->getFileSavePath($file);
            $file->move($this->resource);
        } catch (\Exception $e) {
            Event::emit('uploadExceptionDelete', [
                'fileName' => $this->resource
            ]);
            $this->setError($e->getMessage());
            return false;
        }

        $this->attachment($this->resource, $file->getUploadName(), $file->getUploadMineType());

        return $this->success('上传成功', $this->resource, [
            'chunkId' => $params['chunkId'],
            'status'  => 'success',
        ]);
    }

    /**
     * 文件下载函数
     * @param string|null $url
     * @return array|false
     * @throws InvalidArgumentException
     */
    public function download(string $url = null)
    {
        if (empty($url)) {
            $this->setError('下载地址不能为空！');
            return false;
        }

        $fileUrl = htmlspecialchars_decode(urldecode($url));
        $fileUrl = parse_url($fileUrl);
        $urlPath = str_replace('/', '', explode('.', $fileUrl['path']));
        $fileExt = end($urlPath);
        if (!in_array($fileExt, ['jpg', 'png', 'gif', 'jpeg'])) {
            $fileExt = 'jpg';
        }

        $content = Http::get($url);
        $this->filename = uniqid() . '.' . $fileExt;
        $this->filepath = '/' . $this->config['upload_path'] . '/images/' . date($this->config['upload_style']);
        $this->resource = public_path() . $this->filepath . '/' . $this->filename;

        if (!write_file($this->resource, $content)) {
            $this->setError('写入文件失败！');
            return false;
        }

        Event::emit('uploadFileAfter', [
            'fileName' => $this->filename,
            'resource' => $this->resource
        ]);

        $this->attachment($this->resource, current($urlPath) . '.' . $fileExt, mime_content_type($this->resource));

        return $this->success('文件上传成功！', $this->resource);
    }

    /**
     * 删除本地文件
     * @return void
     * @throws InvalidArgumentException
     */
    public function uploadAfterDelete()
    {
        try {
            if (saenv('upload_del')) {
                @unlink($this->resource);
            }
        } catch (\Throwable $th) {
        }
    }

    /**
     * 附件数据库保存
     * @param $file
     * @param string $source
     * @param string|null $mimeType
     * @return false|void
     */
    public function attachment($file, string $source, string $mimeType = null)
    {
        try {
            $file = new \Webman\Http\UploadFile($file, $source, $mimeType, 200);
            $filePath = str_replace('\\', '/', $this->filepath . DS . $this->filename);
            $this->fileInfo = [
                'type'      => $this->fileClass,
                'filename'  => $file->getUploadName(),
                'filesize'  => $file->getSize(),
                'url'       => $filePath,
                'extension' => $file->getUploadExtension(),
                'mimetype'  => $file->getUploadMineType(),
                'user_id'   => request()->cookie('uid') ?? 0,
                'sha1'      => md5_file($file->getPathname()),
            ];
            Attachment::create($this->fileInfo);
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }

    /**
     * 获取文件扩展名
     * @param object $file
     * @return false|string
     */
    public function getFileExt(object $file)
    {
        $fileExt = $file->getUploadExtension();
        if (empty($fileExt)) {
            $textsName = explode('.', $file->getUploadName());
            return end($textsName);
        }
        return $fileExt;
    }

    /**
     * 验证文件类型
     * @param $file
     * @return bool
     */
    public function fileFilter($file): bool
    {
        $validate = new UploadFile();
        $rules = get_object_vars($validate)['rule'];
        $fileExt = $this->getFileExt($file);
        foreach ($rules as $key => $value) {
            $fileExtArr = explode(',', $value['fileExt']);
            if (in_array(strtolower($fileExt), $fileExtArr)) {
                if ($file->getSize() > $value['fileSize']) {
                    $this->setError('文件最大支持' . format_bytes($value['fileSize']));
                    return false;
                }
                $this->fileClass = $key;
                break;
            }
        }
        if (in_array($file->getUploadMineType(), ['text/x-php', 'text/html'])) {
            $this->fileClass = null;
        }
        if (is_empty($this->fileClass)) {
            $this->_error = '禁止上传的文件类型';
            return false;
        }
        // 未找到类型或验证文件失败
        return !empty($this->fileClass);
    }

    /**
     * @param object $file
     * @param string|null $dir
     * @return void
     */
    public function getFileSavePath(object $file, string $dir = null): void
    {
        $this->filename = uniqid() . '.' . strtolower($file->getUploadExtension());
        $this->filepath = DS . $this->config['upload_path'] . DS . ($dir ?? $this->fileClass) . DS . date($this->config['upload_style']);
        $this->resource = public_path() . $this->filepath . DS . $this->filename;
    }

    /**
     * @param string $msg
     * @param string $filePath
     * @param array $extend
     * @return array
     * @throws InvalidArgumentException
     */
    public function success(string $msg, string $filePath, array $extend = []): array
    {
        $prefix = cdn_Prefix();
        $filePath = str_replace(public_path(), '', $filePath);
        $filePath = str_replace(['//', '\\'], '/', $filePath);
        if (!empty($prefix)) {
            $filePath = $prefix . $filePath;
        }
        return array_merge(['code' => 200, 'msg' => __($msg), 'url' => $filePath], $extend);
    }

    /**
     * @param $msg
     * @param array $extend
     * @return array
     */
    public function error($msg, array $extend = []): array
    {
        return array_merge(['code' => 101, 'msg' => __($msg)], $extend);
    }

    /**
     * 获取最后产生的错误
     * @return string
     */
    public function getError(): string
    {
        return $this->_error;
    }

    /**
     * 设置错误
     * @param string $error 信息信息
     */
    protected function setError(string $error): void
    {
        $this->_error = $error;
    }
}
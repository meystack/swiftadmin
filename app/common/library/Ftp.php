<?php

declare(strict_types=1);
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

use system\Random;

/**
 * FTP上传类
 *
 */
class Ftp
{

    /**
     * @var object 对象实例
     */
    protected static $instance = null;

    //默认配置
    protected $config = [
        'upload_ftp_host' => '127.0.0.1',               // 服务器地址
        'upload_ftp_port' => 21,                        // 服务器端口
        'upload_ftp_user' => 'username',                // FTP用户名
        'upload_ftp_pass' => 'password',                // FTP密码
        'upload_path'     => 'upload',                  // 上传路径
    ];

    /**
     * 类构造函数
     * class constructor.
     */
    public function __construct()
    {
        if ($upload = saenv('upload', true)) {
            $this->config = array_merge($this->config, $upload);
        }
    }

    /**
     * 初始化
     * @access public
     * @param array $options 参数
     * @return Ftp
     */

    public static function instance($options = [])
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($options);
        }
        // 返回实例
        return self::$instance;
    }

    /**
     * FTP上传函数
     * @access public
     * @param string $source 源文件
     * @param string $filepath 文件路径
     * @param string $filename 文件名称
     * @return bool   true|false
     */
    public function ftpUpload(string $source, string $filepath, string $filename): bool
    {

        if (!empty($source) && !empty($filepath)) {

            // 链接FTP
            $connect = @ftp_connect($this->config['upload_ftp_host'], $this->config['upload_ftp_port']) or die('Could not connect');

            if (!ftp_login($connect, $this->config['upload_ftp_user'], $this->config['upload_ftp_pass'])) {
                return false;
            }

            // 开启被动模式
            ftp_pasv($connect, TRUE);
            $source = fopen($source, "r");

            // 循环创建文件夹
            $filepath = str_replace("\\", '/', $filepath);
            $dirs = explode('/', $filepath);
            foreach ($dirs as $val) {
                if (@ftp_chdir($connect, $val) == false) {
                    if (!ftp_mkdir($connect, $val)) {
                        //创建失败
                        return false;
                    }
                    // 切换目录
                    @ftp_chdir($connect, $val);
                }
            }

            if (!@ftp_fput($connect, $filename, $source, FTP_BINARY)) {
                return false;
            }

            ftp_close($connect);
            return true;

        } else {
            return false;
        }
    }

    /**
     * FTP测试函数
     * @access public
     * @param array $config 配置信息
     * @return bool     true|false
     */
    public function ftpTest(array $config): bool
    {

        $connect = @ftp_connect($config['host'], (int)$config['port']) or die('Could not connect');
        if (@ftp_login($connect, $config['user'], $config['pass'])) {

            try {
                // 开启被动模式
                ftp_pasv($connect, TRUE);
                $folder = Random::alpha(16);
                if (ftp_mkdir($connect, $folder)) {

                    // 读取测试文件
                    $location = __DIR__;
                    $source = fopen($location, "r"); // 上传测试文件
                    $filename = $folder . "/target.txt";
                    ftp_fput($connect, $filename, $source, FTP_BINARY);

                    // 删除测试文件
                    ftp_delete($connect, $filename);
                    ftp_rmdir($connect, $folder);
                    ftp_close($connect);

                    return true;
                }

            } catch (\Throwable $th) {
                return $th->getMessage();
            }
        }

        return false;
    }

}
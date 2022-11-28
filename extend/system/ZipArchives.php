<?php

declare(strict_types=1);
// +----------------------------------------------------------------------
// | swiftAdmin 极速开发框架 [基于WebMan开发]
// +----------------------------------------------------------------------
// | Copyright (c) 2020-2030 http://www.swiftadmin.net
// +----------------------------------------------------------------------
// | swiftAdmin.net High Speed Development Framework
// +----------------------------------------------------------------------
// | Author: meystack <coolsec@foxmail.com> Apache2 License
// +----------------------------------------------------------------------
namespace system;

use FilesystemIterator;

/**
 * 文件压缩类
 * @author meystack
 * @version 1.0
 */
class ZipArchives
{
    /**
     * 解压文件
     * @param string $fileName
     * @param string $filePath
     * @param string $search
     * @param bool $delete
     * @return mixed
     * @throws \Exception
     */
    public static function unzip(string $fileName, string $filePath = '', string $search = '', bool $delete = false)
    {
        if (!is_file($fileName) && preg_match('/^[a-z]{3,32}/', $fileName)) {
            $fileName = plugin_path() . $fileName . '.zip';
        }

        if (!is_file($fileName)) {
            throw new \Exception(__('解压文件不存在'), -113);
        }

        $fileStream = '';
        $filePath = $filePath ?: plugin_path();
        $zip = new \ZipArchive();
        if ($zip->open($fileName) !== TRUE) {
            throw new \Exception(__("访问解压文件失败"), -114);
        }
        try {
            if (!empty($search)) {
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $filePath = str_replace('\\','/',$zip->getNameIndex($i));
                    $fileName = explode('/', $filePath);
                    if (end($fileName) == $search) {
                        $fileStream = $zip->getFromIndex($i);
                        break;
                    }
                }
            } else {
                if (!is_dir($filePath)) {
                    @mkdir($filePath, 0755, true);
                }
                $zip->extractTo($filePath);
            }
        } catch (\Throwable $th) {
            throw new \Exception("解压 " . $fileName . " 包失败", -115);
        } finally {
            $zip->close();
            if ($delete && !$search) {
                unlink($fileName);
            }
        }

        return $search ? $fileStream : $filePath;
    }

    /**
     * 压缩文件夹
     * @param string $fileName
     * @param string $filePath
     * @param string $rootPath
     * @return bool
     * @throws \Exception
     */
    public static function compression(string $fileName, string $filePath, string $rootPath = ''): bool
    {
        $zip = new \ZipArchive();
        try {

            @unlink($fileName);
            $zip->open($fileName, \ZipArchive::CREATE);
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($filePath, FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );

            // 默认为插件目录
            $rootPath = $rootPath ?: plugin_path();
            foreach ($files as $fileinfo) {
                if ($fileinfo->isFile()) {
                    // 过滤冗余文件
                    $filePath = str_replace('\\','/',$fileinfo->getRealPath());
                    if (!in_array($fileinfo->getFilename(), ['.git', '.vscode', 'Thumbs.db'])) {
                        $zip->addFile($filePath, str_replace($rootPath, '', $filePath));
                    }
                } else {
                    $localDir = str_replace('\\','/',$fileinfo->getPathName());
                    $localDir = str_replace($rootPath, '', $localDir);
                    $zip->addEmptyDir($localDir);
                }
            }

        } catch (\Throwable $th) {
            var_dump($th->getMessage());
            throw new \Exception("压缩 " . $fileName . " 包失败", -115);
        } finally {
            $zip->close();
        }

        return true;
    }
}
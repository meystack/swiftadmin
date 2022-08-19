<?php

namespace system;

/**
 * 文件操作类
 * @author meystack
 */
class File
{

    /**
     * 递归创建文件夹
     * @param string $dirs
     */
    public static function mkDirs(string $dirs)
    {
        if (!is_dir($dirs)) {
            self::mkDirs(dirname($dirs));
            mkdir($dirs, 0755);
        }
    }

    /**
     * 递归删除文件夹
     * @param string $dirs
     * @return mixed
     */
    public static function rmDirs(string $dirs)
    {
        if (!is_dir($dirs)) {
            return false;
        }

        $files = scandir($dirs);
        foreach ($files as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            if (is_dir($dirs . '/' . $file)) {
                self::rmDirs($dirs . '/' . $file);
            } else {
                unlink($dirs . '/' . $file);
            }
        }
        rmdir($dirs);
    }

    /**
     * 获取当前文件夹大小
     * @param string $dirs
     * @return mixed
     */
    public static function getDirSize(string $dirs)
    {
        $handle = opendir($dirs);
        $size = 0;
        while (false !== ($FolderOrFile = readdir($handle))) {
            if ($FolderOrFile != "." && $FolderOrFile != "..") {
                if (is_dir("$dirs/$FolderOrFile")) {
                    $size += self::getDirSize("$dirs/$FolderOrFile");
                } else {
                    $size += filesize("$dirs/$FolderOrFile");
                }
            }
        }
        closedir($handle);
        return $size;
    }

    /**
     * 获取文件夹文件列表
     * @param string $dirs
     * @return array
     */
    public static function getDirFile(string $dirs): array
    {
        $handle = opendir($dirs);
        $file = [];
        while (false !== ($FolderOrFile = readdir($handle))) {
            if ($FolderOrFile != "." && $FolderOrFile != "..") {
                if (is_dir("$dirs/$FolderOrFile")) {
                    $file[] = self::getDirFile("$dirs/$FolderOrFile");
                } else {
                    $file[] = "$dirs/$FolderOrFile";
                }
            }
        }
        closedir($handle);
        return $file;
    }

    /**
     * 返回 [app, public] 的路径
     * @param string $name
     * @return array
     */
    public static function getCopyDirs(string $name): array
    {
        return [
            plugin_path($name) . 'app',
            plugin_path($name) . 'public'
        ];
    }

    /**
     * 文件比较
     * @param $source
     * @param $destFileOrPath
     * @param string $prefix
     * @param bool $onlyFiles
     * @return mixed
     */
    public static function mutexCompare($source, $destFileOrPath, string $prefix = '', bool $onlyFiles = false): array
    {
        $list = [];
        $destFileOrPath = $destFileOrPath ?: root_path();
        if (!is_array($source) && is_file($source) && is_file($destFileOrPath)) {
            return md5_file($source) !== md5_file($destFileOrPath);
        }

        foreach ($source as $filesPath) {
            if (is_dir($filesPath)) {
                $files = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($filesPath, \FilesystemIterator::SKIP_DOTS),
                    \RecursiveIteratorIterator::CHILD_FIRST
                );

                foreach ($files as $file) {
                    if ($file->isFile()) {
                        $filePath = $file->getPathname();
                        $appPath = str_replace($prefix, '', $filePath);
                        $destPath = $destFileOrPath . $appPath;
                        if ($onlyFiles) {
                            if (is_file($destPath)) {
                                if (md5_file($filePath) != md5_file($destPath)) {
                                    $list[] = $appPath;
                                }
                            }
                        } else {
                            $list[] = $appPath;
                        }

                    }
                }
            }
        }

        return $list;
    }
}
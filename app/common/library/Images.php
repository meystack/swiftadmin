<?php
declare (strict_types=1);

namespace app\common\library;

use think\Image;

/**
 * @mixin \think\Images
 */
class Images
{
    /**
     * 水印函数
     * @access public
     * @param string $filename 文件路径
     * @param array $config 配置数组
     * @return mixed
     * @throws \Exception
     */
    public function waterMark(string $filename, array $config)
    {

        try {

            // 获取文件信息
            $Image = Image::open($filename);
            $ImageInfo = getimagesize($filename);

            // 判断水印类型
            if ($config['upload_water_type']) { // 文字水印
                $size = $config['upload_water_size'] ? $config['upload_water_size'] : 15;
                $color = $config['upload_water_color'] ?: '#000000';
                $ttf = public_path() . '/static/font/default.ttf';
                if (!preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
                    $color = '#000000';
                }

                // 设置透明度
                $transparency = intval((100 - $config['upload_water_pct']) * (127 / 100));
                $color .= dechex($transparency);
                $resWater = $Image->text($config['upload_water_font'], $ttf, $size, $color, $config['upload_water_pos'])->save($filename);

            } else {

                if (!file_exists($config['upload_water_img'])) {
                    return false;
                }

                $ImageWaterInfo = getimagesize($config['upload_water_img']);

                // 对比图片大小
                if ($ImageWaterInfo[0] >= $ImageInfo[0] ||
                    $ImageWaterInfo[1] >= $ImageInfo[1]) {
                    return false;
                }

                // 检查图片
                $resWater = $Image->water($config['upload_water_img'], $config['upload_water_pos'], $config['upload_water_pct'])->save($filename);
            }

            return $resWater ?? false;
        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
    }

    /**
     * 微缩图函数
     * @access public
     * @param $filepath
     * @param $filename
     * @param $config
     * @param bool $avatar
     * @param bool $newfile
     * @return Image|void
     */
    public function thumb($filepath, $filename, $config, bool $avatar = false, bool $newfile = true)
    {

        $resource = $filepath . '/' . $filename;

        try {
            // 判断图片大小，原图尺寸不得小于微缩图
            // 120x140  120x141 121x140 110x140
            $ImageInfo = getimagesize($resource);
            if ($ImageInfo[0] >= $config['upload_thumb_w'] && $ImageInfo[1] >= $config['upload_thumb_h']) {

                $Image = Image::open($resource);
                // 判断微缩图类型
                if (!empty($avatar)) {  // 用户头像模式/替换原来的图片
                    $resThumb = $Image->thumb(110, 110, 6)->save($resource, NULL, 90);
                } else {
                    if ($newfile) {
                        // 保留原来的图片 新文件名建议源文件名+_thumb.jpg 格式
                        $resource = $filepath . '/thumb_' . $filename;
                    }

                    $resThumb = $Image->thumb($config['upload_thumb_w'], $config['upload_thumb_h'], 6)->save($resource, NULL, 90);
                }

                return $resThumb;
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}

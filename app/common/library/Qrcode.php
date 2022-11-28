<?php
declare (strict_types=1);

namespace app\common\library;

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Label\Alignment\LabelAlignmentCenter;
use Endroid\QrCode\Label\Font\NotoSans;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\Result\ResultInterface;

/**
 * 生成二维码
 * Class Qrcode
 * @package app\common\library
 * @author meystack
 */
class Qrcode
{
    /**
     * @param array $params
     * @return ResultInterface
     */
    public static function build(array $params = []): ResultInterface
    {
        $params['data'] = $params['data'] ?? 'Hello world!';
        $params['size'] = $params['size'] ?? 280;
        $params['margin'] = $params['margin'] ?? 0;
        $params['format'] = $params['format'] ?? 'png';
        $params['foreground'] = $params['foreground'] ?? "#000000";
        $params['background'] = $params['background'] ?? "#ffffff";
        $params['label'] = $params['label'] ?? '';
        $params['logo'] = $params['logo'] ?? '';
        $params['logosize'] = $params['logosize'] ?? 50;

        // 二维码颜色
        list($r, $g, $b) = sscanf($params['foreground'], "#%02x%02x%02x");
        $foregroundColor = new Color($r, $g, $b);

        // 背景色调
        list($r, $g, $b) = sscanf($params['background'], "#%02x%02x%02x");
        $backgroundColor = new Color($r, $g, $b);

        // 创建对象
        $qrcode = Builder::create()
            ->writer(new PngWriter())
            ->writerOptions([])
            ->data($params['data'])
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->size((int)$params['size'])
            ->margin((int)$params['margin'])
            ->roundBlockSizeMode(new RoundBlockSizeModeMargin())
            ->foregroundColor($foregroundColor)
            ->backgroundColor($backgroundColor);

        // 设置LOGO
        if (!empty($params['logo'])) {
            $qrcode = $qrcode->logoPath($params['logo'])
                ->logoResizeToWidth($params['logosize'])
                ->logoResizeToHeight($params['logosize']);
        }

        // 返回实例对象
        return $qrcode->labelText($params['label'])
            ->labelFont(new NotoSans(20))
            ->labelAlignment(new LabelAlignmentCenter())
            ->build();
    }
}
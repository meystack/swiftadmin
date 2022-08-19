<?php
declare (strict_types = 1);

namespace WordAnalysis;

class Analysis
{
    /**
     * Notes:关键字提取
     * @auther: xxf
     * Date: 2019/8/19
     * Time: 11:09
     * @param string $content
     * @param int $num 获取数量
     * @return string
     */
    public static function getKeywords(string $content = null,int $num = 2) {

        if (empty ($content )) {
            return '';
        }

        require_once 'phpanalysis.class.php';
        \PhpAnalysis::$loadInit = false;
        $pa = new \PhpAnalysis ( 'utf-8', 'utf-8', false );
        $pa->LoadDict();
        $pa->SetSource($content);
        $pa->StartAnalysis(true);
        return $pa->GetFinallyKeywords($num); 
    }

}
<?php

namespace support;

use Psr\SimpleCache\InvalidArgumentException;
use support\view\ThinkPHP;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 * 模板类
 * Class Template
 * @package support
 * @method static string fetch(string $template = '', array $vars = [], array $config = [])
 */
class Template extends ThinkPHP
{
    /**
     * @param string $template
     * @param array $vars
     * @param string|null $app
     * @return array|false|string|string[]|null
     * @throws DataNotFoundException
     * @throws DbException
     * @throws InvalidArgumentException
     * @throws ModelNotFoundException
     */
    public static function render(string $template, array $vars, string $app = null)
    {
        $content = parent::render($template, $vars, $app);
        if (saenv('minify_page')) {
            $content = preg_replace('/\s+/i', ' ', $content);
        }
        return $content;
    }
}
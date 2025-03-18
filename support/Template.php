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
     * @param string|null $plugin
     * @return string
     */
    public static function render(string $template, array $vars, string $app = null, string $plugin = null): string
    {
        $request = request();
        $app = $app === null ? $request->app : $app;
        $viewPath = app_path() . "/$app/view/";
        $defaultOptions = [
            'view_path' => $viewPath,
            'cache_path' => runtime_path() . '/views/',
            'view_suffix' => 'html'
        ];
        $options = $defaultOptions + config("view.options", []);
        $options['taglib_pre_load'] = rtrim($options['taglib_pre_load'], ',');
        foreach (get_plugin_list() as $index => $item) {
            if (empty($item['status'])) {
                continue;
            }
            $name = $item['name'];
            $taglibPath = plugin_path($name . DIRECTORY_SEPARATOR . 'taglib');
            $tagList = glob($taglibPath . '*.php');
            foreach ($tagList as $key => $tag) {
                $tag = pathinfo($tag, PATHINFO_FILENAME);
                $options['taglib_pre_load'] .= ',plugin\\' . $name . '\\taglib\\' . $tag;
            }
        }

        $views = new \think\Template($options);
//        ob_start();
//        $vars = array_merge($options, $vars);
//        $views->fetch($template, $vars);
//        $content = ob_get_clean();
//        static::$vars = [];
//        if (saenv('minify_page') && strtolower($app) == 'index') {
//            $content = preg_replace('/\s+/i', ' ', $content);
//        }
//        return $content;

        ob_start();
        if(isset($request->_view_vars)) {
            $vars = array_merge((array)$request->_view_vars, $vars);
        }
        $views->fetch($template, $vars);
        return ob_get_clean();

    }
}
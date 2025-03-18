<?php

/**
 * This file is part of webman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

use app\common\exception\DumpException;
use Symfony\Component\VarDumper\Caster\ReflectionCaster;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\ContextProvider\SourceContextProvider;
use Symfony\Component\VarDumper\Dumper\ContextualizedDumper;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;

// Project base path

if (!function_exists('halt')) {
    /**
     * 调试变量并且中断输出
     * @param mixed $vars 调试变量或者信息
     * @throws DumpException
     * @return void
     */
    function halt(...$vars): void
    {
        try {
            ob_start();
            $cloner = new VarCloner();
            $cloner->addCasters(ReflectionCaster::UNSET_CLOSURE_FILE_INFO);
            $dumper = new HtmlDumper();
            $dumper = new ContextualizedDumper($dumper, [new SourceContextProvider()]);
            foreach ($vars as $var) {
                $dumper->dump($cloner->cloneVar($var));
            }
            $ob_response = (string)ob_get_clean();
        } catch (\Throwable $e) {
            $ob_response = $e->getMessage();
        }
        throw new DumpException($ob_response, 600);
    }
}

if (!function_exists('get_env')) {
    /**
     * Get environment variable
     */
    function get_env($var, $default = '')
    {
        $dir = str_replace('\\', '/', realpath(__DIR__ . '/../'));
        $env_path = $dir . '/.env';
        static $env_info = [];
        if (is_file($env_path) && !$env_info) {
            $env_info = parse_ini_file($env_path, true);
        }
        return $env_info[$var] ?? $default;
    }
}
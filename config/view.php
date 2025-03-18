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

use support\Template;
use support\view\Raw;
use support\view\Twig;
use support\view\Blade;
use support\view\ThinkPHP;

return [
    // 重载模板 方便加载框架插件使用
    'handler' => Template::class,
//    'handler' => ThinkPHP::class,
    'options' => [
        'tpl_cache'          => true,
        'taglib_begin'       => '<',
        'taglib_end'         => '>',
        'taglib_pre_load'    => 'app\common\taglib\SaLibs',
        'tpl_replace_string' => [
            '__STATIC__'       => '/static/',
            '__STATICJS__'     => '/static/js/',
            '__STATICCSS__'    => '/static/css/',
            '__STATICIMAGES__' => '/static/images/',
            '__STATICADMIN__'  => '/static/system/',
            '__ADMINIMAGES__'  => '/static/system/images/',
            '__ADMINPLUGIN__'  => '/static/system/plugin/',
            '__HOMEPLUGIN__'   => '/static/plugin/',
        ]
    ]
];

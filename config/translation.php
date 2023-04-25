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

/**
 * Multilingual configuration
 */
return [
    // Default language
    'locale' => get_env('LANG_DEFAULT_LANG') ?? 'zh-CN',
    // Fallback language
    'fallback_locale' => ['zh-CN', 'en-US'],
    // Folder where language files are stored
    'path' => base_path() . '/resource/translations',
];
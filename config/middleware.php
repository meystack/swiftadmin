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

return [
    '' => [
        app\common\middleware\AccessCross::class,
        app\common\middleware\AppInitialize::class,
        app\common\middleware\AppLang::class,
    ],
    'api' => [
        \app\api\middleware\system\ApiPermissions::class,
    ],
    'index' => [
        \app\index\middleware\system\IndexInitialize::class,
        \app\index\middleware\system\IndexPermissions::class,
    ],
    'admin' => [
        \app\admin\middleware\system\AdminLogin::class,
        \app\admin\middleware\system\AdminPermissions::class,
    ],
];
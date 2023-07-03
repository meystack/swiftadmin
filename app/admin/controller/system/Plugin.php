<?php
declare (strict_types=1);

// +----------------------------------------------------------------------
// | swiftAdmin 极速开发框架 [基于WebMan开发]
// +----------------------------------------------------------------------
// | Copyright (c) 2020-2030 http://www.swiftadmin.net
// +----------------------------------------------------------------------
// | swiftAdmin.net High Speed Development Framework
// +----------------------------------------------------------------------
// | Author: meystack <coolsec@foxmail.com>  Apache 2.0 License
// +----------------------------------------------------------------------

namespace app\admin\controller\system;
use GuzzleHttp\Exception\TransferException;
use process\Monitor;
use support\Response;
use system\File;
use system\Http;
use system\ZipArchives;
use app\AdminController;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use Throwable;
use app\common\library\DataBase;
use app\common\model\system\AdminRules;

/**
 * 插件市场
 * Class Plugin
 * @package app\admin\controller\system
 */
class Plugin extends AdminController
{
    /**
     * 查询最大数量
     * @var mixed
     */
    protected mixed $limit = 500;

    /**
     * 错误信息
     * @var mixed
     */
    static mixed $ServerBody = '';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取本地插件列表
     * @return Response
     */
    public function index(): Response
    {
        $pluginList = get_plugin_list();
        if (request()->isAjax()) {
            $onlinePlugin = $this->getPluginList($pluginList);
            return $this->success('获取成功', null, $onlinePlugin, count($onlinePlugin));
        }

        return view('/system/plugin/index', ['plugin' => json_encode($pluginList)]);
    }

    /**
     * 安装插件
     * @return Response|void
     * @throws \Exception|\Psr\SimpleCache\InvalidArgumentException
     */
    public function install()
    {
        if (request()->isPost()) {

            $name = input('name');
            $pluginPath = plugin_path($name);
            if (is_file($pluginPath . 'config.json')) {
                return $this->error('请勿重复安装插件');
            }

            try {

                $pluginZip = self::downLoad($name, ['name' => $name, 'token' => input('token')]);
                ZipArchives::unzip($pluginZip, plugin_path(), '', true);
                $listFiles = File::mutexCompare(File::getCopyDirs($name), root_path(), $pluginPath, true);
                if (!empty($listFiles)) {
                    throw new \Exception(sprintf("存在文件冲突：%s", implode(',', $listFiles)), -117);
                }

                $pluginClass = get_plugin_instance($name);
                $pluginClass->install();
                self::pluginMenu($name);
                self::executeSql($name);
                self::enabled($name);
            } catch (\Throwable $th) {
                recursive_delete($pluginPath);
                return $this->error($th->getMessage(), null, self::$ServerBody, $th->getCode());
            }

            return $this->success('插件安装成功', null, get_plugin_config($name, true));
        }
    }

    /**
     * 卸载插件
     * @return Response|void
     * @throws \Exception|\Psr\SimpleCache\InvalidArgumentException
     */
    public function uninstall()
    {

        if (request()->isAjax()) {

            $name = input('name');
            $config = get_plugin_config($name, true);
            if (empty($config) || $config['status']) {
                return $this->error('插件不存在或未禁用');
            }

            try {

                $pluginPath = plugin_path($name);
                $pluginClass = get_plugin_instance($name);
                $pluginClass->uninstall();
                if (get_env('APP_DEBUG') && Auth::instance()->SuperAdmin()) {
                    self::executeSql($name, 'uninstall');
                }

                AdminRules::disabled($name, true);
                recursive_delete($pluginPath);
                plugin_refresh_hooks();
            } catch (Throwable $th) {
                return $this->error($th->getMessage());
            }

            return $this->success('插件卸载成功');
        }
    }

    /**
     * 插件升级
     * @return mixed|void
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Exception
     */
    public function upgrade()
    {
        if (request()->isPost()) {

            try {

                $name = input('name');
                $token = input('token');
                $version = input('version');

                $pluginInfo = get_plugin_config($name, true);
                if (!$pluginInfo) {
                    return $this->error('插件不存在');
                }

                if ($pluginInfo['status']) {
                    return $this->error('请禁用插件后再升级');
                }

                $pluginPath = plugin_path($name);
                $pluginZip = self::downLoad($name, ['name' => $name, 'token' => $token, 'version' => $version]);
                $formIndex = ZipArchives::unzip($pluginZip, plugin_path(), 'config.json');
                $upgradeInfo = json_decode($formIndex, true);

                // 判断升级版本号
                if (version_compare($upgradeInfo['version'], $pluginInfo['version'], "<=")) {
                    throw new \Exception('升级版本不能低于已安装版本');
                }

                // 备份当前插件
                $backupDir = root_path() . $name . '_' . $pluginInfo['version'] . '.zip';
                ZipArchives::compression($backupDir, $pluginPath, plugin_path());
                ZipArchives::unzip($pluginZip, plugin_path(), '', true);
                $pluginClass = get_plugin_instance($name, 'upgrade');
                $pluginClass->execute($pluginInfo['version'], $upgradeInfo['version']);
                $data = array_merge($upgradeInfo, [
                    'extends' => $pluginInfo['extends'],
                    'rewrite' => $pluginInfo['rewrite'],
                ]);

                write_file($pluginPath . 'config.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                self::pluginMenu($name);
                self::executeSql($name);
                self::enabled($name);
            } catch (\Throwable $th) {
                return $this->error($th->getMessage(), null, self::$ServerBody, $th->getCode());
            }

            return $this->success('插件更新成功', null, $data);
        }
    }

    /**
     * 启用插件
     * @param string $name
     * @return bool
     * @throws \Exception|\Psr\SimpleCache\InvalidArgumentException
     */
    public static function enabled(string $name): bool
    {
        if (!$name || !is_dir(plugin_path($name))) {
            throw new \Exception(__('插件数据不存在'), -117);
        }

        Monitor::pause();
        $pluginDir = plugin_path($name);
        foreach (File::getCopyDirs($name) as $copyDir) {
            copydirs($copyDir, root_path() . str_replace($pluginDir, '', $copyDir));
        }

        try {
            $pluginClass = get_plugin_instance($name);
            $pluginClass->enabled();
            AdminRules::enabled($name);
            set_plugin_config($name, ['status' => 1]);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return true;
    }

    /**
     * 禁用插件
     * @param string $name
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Exception
     */
    public static function disabled(string $name): bool
    {
        if (!$name || !is_dir(plugin_path($name))) {
            throw new \Exception(__('插件数据不存在'), -117);
        }

        try {

            // 清理插件文件
            $pluginDir = plugin_path($name);
            foreach (File::getCopyDirs($name) as $dir) {
                if (is_dir($dir)) {
                    $files = new \RecursiveIteratorIterator(
                        new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
                        \RecursiveIteratorIterator::CHILD_FIRST
                    );
                    foreach ($files as $fileinfo) {
                        $dirFile = str_replace($pluginDir, root_path(), $fileinfo->getPathname());
                        if ($fileinfo->isFile()) {
                            @unlink($dirFile);
                        } else if ($fileinfo->isDir()) {
                            remove_empty_dir($dirFile);
                        }
                    }
                }
            }

            $pluginClass = get_plugin_instance($name);
            $pluginClass->disabled();
            AdminRules::disabled($name);
            set_plugin_config($name, ['status' => 0]);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return true;
    }

    /**
     * 修改插件配置
     * @return Response
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function config(): Response
    {
        $name = input('name');
        if (!empty($name)) {
            $name = strtolower(trim($name));
        }
        if (preg_replace('/[^a-zA-Z0-9]/i', '', $name) !== $name) {
            return $this->error('插件名称只能是字母和数字');
        }
        $config = get_plugin_config(strtolower($name), true);
        if (empty($config)) {
            return $this->error('插件不存在');
        }

        if (request()->isPost()) {
            $post['extends'] = input('extends');
            $post['rewrite'] = input('rewrite');
            foreach ($post['rewrite'] as $kk => $vv) {
                if ($kk[0] != '/') return $this->error('伪静态变量名称“' . $kk . '" 必须以“/”开头');
                $post['rewrite'][$kk] = str_replace('\\', '/', trim($vv, '/\\'));
                $value = explode('/', $post['rewrite'][$kk]);
                if (count($value) < 2) {
                    return $this->error('伪静态不符合规则');
                }
                if (strtoupper($value[count($value) - 2][0]) !== $value[count($value) - 2][0]) {
                    return $this->error('控制器首字母必须大写');
                }
            }
            $config = array_merge($config, $post);
            try {
                set_plugin_config($name, $config);
            } catch (Throwable $th) {
                return $this->error($th->getMessage());
            }
            return $this->success();
        }

        return view($config['path'] . '/config.html', ['config' => $config]);
    }

    /**
     * 修改插件状态
     * 启用 / 禁用
     * @return Response|void
     */
    public function status()
    {
        if (request()->isAjax()) {
            try {
                call_user_func([$this, input('status') == 1 ? 'enabled' : 'disabled'], input('id'));
            } catch (Throwable $th) {
                return $this->error($th->getMessage());
            }
            return $this->success();
        }
    }

    /**
     * 插件下载
     * @param string $name
     * @param array $extends
     * @return string
     * @throws \Exception
     */
    public static function downLoad(string $name, array $extends): string
    {
        try {

            $query = get_plugin_query();
            $response = Http::get($query, $extends);
            $body = json_decode($response, true);
            $url = '';
            if (isset($body['data']['url'])) {
                $url = $body['data']['url'];
            }
            if (!empty($url) && stristr($url, 'download')) {
                $content = Http::get($url);
                $filePath = plugin_path() . $name . '.zip';
                write_file($filePath, $content);
            } else {
                self::$ServerBody = $body['data'];
                throw new \Exception($body['msg'], $body['code']);
            }

        } catch (TransferException $th) {
            throw new \Exception(__("安装包下载失败"), -111);
        }

        return $filePath;
    }

    /**
     * 执行SQL脚本文件
     * @param string $name
     * @param string $type
     */
    public static function executeSql(string $name, string $type = 'install')
    {
        $pluginPath = plugin_path($name);
        $sqlFile = $pluginPath . $type . '.sql';
        DataBase::importSql($sqlFile);
    }

    /**
     * 获取菜单项
     * @param string $name
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function pluginMenu(string $name)
    {
        $pluginPath = plugin_path($name);
        $pluginMenu = $pluginPath . 'data/menu.php';
        if (is_file($pluginMenu)) {
            $data = include($pluginMenu);
            AdminRules::createMenu($data, $name);
        }
    }

    /**
     * 获取服务器插件列表
     * @param array $pluginList
     * @return array
     */
    protected function getPluginList(array $pluginList = []): array
    {
        $PluginApiList = Http::get(config('app.api_url') . '/plugin/index', ['limit' => $this->limit]);
        $PluginApiList = json_decode($PluginApiList, true)['data'];
        foreach ($pluginList as $name => $plugin) {
            $result = list_search($PluginApiList, ['name' => $plugin['name']]);
            if (!empty($result)) {
                $pluginList[$name] = $result;
            }
        }
        return $pluginList;
    }
}

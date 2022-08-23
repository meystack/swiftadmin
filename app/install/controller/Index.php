<?php
declare (strict_types=1);

namespace app\install\controller;

use app\common\library\DataBase;
use think\facade\Cache;
use app\BaseController;

const SUCCESS = 'layui-icon-ok-circle';
const ERROR = 'layui-icon-close-fill';

class Index extends BaseController
{
    /**
     * 使用协议
     *
     * @return \support\Response
     */
    public function index(): \support\Response
    {
        Cache::clear();
        return view('/index/index');
    }

    /**
     * 检测安装环境
     *
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function step1()
    {

        if (request()->isPost()) {

            // 检测生产环境
            foreach ($this->checkEnv() as $key => $value) {
                if ($key == 'php' && (float)$value < 7.3) {
                    return $this->error('PHP版本过低！');
                }
            }

            // 检测目录权限
            foreach ($this->checkDirFile() as $value) {
                if ($value[1] == ERROR
                    || $value[2] == ERROR) {
                    return $this->error($value[3] . ' 权限读写错误！');
                }
            }

            Cache::set('checkEnv', 'success');
            return json(['code' => 200, 'url' => '/install/index/step2']);
        }

        return view('/index/step1', [
            'checkEnv' => $this->checkEnv(),
            'checkDirFile' => $this->checkDirFile(),
        ]);
    }


    /**
     * 检测环境变量
     * @return array
     */
    protected function checkEnv(): array
    {
        $items['php'] = PHP_VERSION;
        $items['mysqli'] = extension_loaded('mysqli');
        $items['redis'] = extension_loaded('redis');
        $items['curl'] = extension_loaded('curl');
        $items['fileinfo'] = extension_loaded('fileinfo');
        $items['exif'] = extension_loaded('exif');
        return $items;
    }

    /**
     * 检测读写环境
     * @return array
     */
    protected function checkDirFile(): array
    {
        $items = array(
            array('dir', SUCCESS, SUCCESS, './'),
            array('dir', SUCCESS, SUCCESS, './public'),
            array('dir', SUCCESS, SUCCESS, './public/upload'),
            array('dir', SUCCESS, SUCCESS, './runtime'),
            array('dir', SUCCESS, SUCCESS, './extend'),
        );

        foreach ($items as &$value) {

            $item = root_path() . $value[3];

            // 写入权限
            if (!is_writable($item)) {
                $value[1] = ERROR;
            }

            // 读取权限
            if (!is_readable($item)) {
                $value[2] = ERROR;
            }
        }

        return $items;
    }


    /**
     * 检查环境变量
     *
     * @return \support\Response
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function step2(): \support\Response
    {

        if (!Cache::get('checkEnv')) {
            return redirect('/install/index/step1');
        }

        if (request()->isPost()) {

            // 链接数据库
            $params = request()->all();
            $connect = @mysqli_connect($params['hostname'] . ':' . $params['hostport'], $params['username'], $params['password']);
            if (!$connect) {
                return $this->error('数据库链接失败');
            }

            // 检测MySQL版本
            $mysqlInfo = @mysqli_get_server_info($connect);
            if ((float)$mysqlInfo < 5.6) {
                return $this->error('MySQL版本过低');
            }

            // 查询数据库名
            $mysql_table = @mysqli_query($connect, 'SHOW DATABASES');
            while ($row = @mysqli_fetch_assoc($mysql_table)) {
                if ($row['Database'] == $params['database']) {
                    return $this->error('数据库已存在，请勿重复安装');
                }
            }

            $query = "CREATE DATABASE IF NOT EXISTS `" . $params['database'] . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;";
            if (!@mysqli_query($connect, $query)) {
                return $this->error('数据库创建失败或已存在，请手动修改');
            }

            Cache::set('mysqlInfo', $params);
            return json(['code' => 200, 'url' => '/install/index/step3']);
        }

        return view('/index/step2');
    }

    /**
     * 初始化数据库
     * @return \support\Response
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function step3(): \support\Response
    {
        $mysqlInfo = Cache::get('mysqlInfo');
        if (!$mysqlInfo) {
            return redirect('/install/index/step2');
        }

        return view('/index/step3');
    }

    /**
     * 安装数据缓存
     * @return \support\Response|void
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function install()
    {
        if (request()->isAjax()) {

            $mysqlInfo = Cache::get('mysqlInfo');
            if (is_file('../extend/conf/install.lock') || !$mysqlInfo) {
                return $this->error('请勿重复安装本系统');
            }

            // 读取SQL文件加载进缓存
            $mysqlPath = root_path('app/install') . 'install.sql';
            $sqlRecords = file_get_contents($mysqlPath);
            $sqlRecords = str_ireplace("\r", "\n", $sqlRecords);

            // 替换数据库表前缀
            $sqlRecords = explode(";\n", $sqlRecords);
            $sqlRecords = str_replace(" `__PREFIX__", " `{$mysqlInfo['prefix']}", $sqlRecords);

            $sqlConnect = @mysqli_connect($mysqlInfo['hostname'] . ':' . $mysqlInfo['hostport'], $mysqlInfo['username'], $mysqlInfo['password']);
            mysqli_select_db($sqlConnect, $mysqlInfo['database']);
            mysqli_query($sqlConnect, "set names utf8mb4");

            foreach ($sqlRecords as $index => $sqlLine) {
                $sqlLine = trim($sqlLine);
                if (!empty($sqlLine)) {
                    try {
                        // 创建表数据
                        if (mysqli_query($sqlConnect, $sqlLine) === false) {
                            throw new \Exception(mysqli_error($sqlConnect));
                        }
                    } catch (\Throwable $th) {
                        return $this->error($th->getMessage());
                    }
                }
            }

            $pwd = encryptPwd($mysqlInfo['pwd']);
            mysqli_query($sqlConnect, "UPDATE {$mysqlInfo['prefix']}admin SET pwd='{$pwd}' where id = 1");

            return $this->success('success');
        }
    }

    /**
     * 清理安装文件包
     *
     * @return \support\Response|void
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function clear()
    {

        if (request()->isAjax()) {
            try {

                $mysqlInfo = Cache::get('mysqlInfo');
                $env = root_path('app/install') . 'install.env';
                $parse = parse_ini_file($env, true);
                $parse['DATABASE_HOSTNAME'] = $mysqlInfo['hostname'];
                $parse['DATABASE_HOSTPORT'] = $mysqlInfo['hostport'];
                $parse['DATABASE_DATABASE'] = $mysqlInfo['database'];
                $parse['DATABASE_USERNAME'] = $mysqlInfo['username'];
                $parse['DATABASE_PASSWORD'] = $mysqlInfo['password'];
                $parse['DATABASE_PREFIX'] = $mysqlInfo['prefix'];
                $parseInfo = parse_array_ini($parse);
                write_file(root_path() . '.env', $parseInfo);
                write_file(root_path() . 'extend/conf/install.lock', 'success');

                // 清理安装包
                Cache::clear();
                 recursive_delete(root_path('app' . DIRECTORY_SEPARATOR . 'install'));
                system_reload();
            } catch (\Throwable $th) {
                return $this->error($th->getMessage());
            }

            return $this->success('安装成功,如install模块未删除，请手动删除');
        }
    }
}
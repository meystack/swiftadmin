<?php

declare(strict_types=1);
// +----------------------------------------------------------------------
// | swiftAdmin 极速开发框架 [基于WebMan开发]
// +----------------------------------------------------------------------
// | Copyright (c) 2020-2030 http://www.swiftadmin.net
// +----------------------------------------------------------------------
// | swiftAdmin.net High Speed Development Framework
// +----------------------------------------------------------------------
// | Author: meystack <coolsec@foxmail.com> Apache 2.0 License
// +----------------------------------------------------------------------
namespace app\admin\controller;

use app\AdminController;
use app\common\library\Email;
use app\common\library\Ftp;
use think\cache\driver\Memcached;
use think\cache\driver\Redis;
use think\facade\Cache;
use Webman\Event\Event;
use app\common\model\system\Attachment;
use app\common\model\system\Config;
use app\common\model\system\User;
use app\common\model\system\UserGroup;
use app\common\model\system\UserThird;
use app\common\model\system\UserValidate;
use system\Random;
use think\facade\Db;

class Index extends AdminController
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \Exception
     */
    public function index()
    {
        return view('index/index');
    }

    /**
     * 控制台首页
     * @return mixed
     * @throws \think\db\exception\BindParamException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function console()
    {
        $dataList = [];
        $dateBefore = date('Y-m-d', strtotime('-30 day'));
        $dateAfter = date('Y-m-d 23:59:59');

        if (request()->isPost()) {

            $cycle = input('cycle');
            if (Event::hasListener('cmsuserEcharts')) {
                [$dataList, $seriesList] = Event::emit('cmsuserEcharts', $cycle, true);
                if (empty($seriesList)) {
                    return $this->error('暂无数据');
                }

                $userChartsOptions = $this->getEchartsData(array_values($dataList), $seriesList);
                return $this->success('操作成功', '', $userChartsOptions);
            }

            return $this->error('请安装CMS插件');
        }

        for ($i = -29; $i <= 0; $i++) {
            $dataList[date('m-d', strtotime($i . ' day'))] = date('m-d', strtotime($i . ' day'));
        }

        $seriesList = [];
        $condition = '%m-%d';
        $columns = ['用户注册' => 'create_time', '用户登录' => 'login_time', '邀请注册' => 'invite_id'];
        foreach ($columns as $index => $field) {
            $time = str_replace('invite_id', 'create_time', $field);
            $resultList = User::where($time, 'between time', [$dateBefore, $dateAfter])
                              ->when($condition, function ($query) use ($condition, $time, $field) {
                                  $query->field("FROM_UNIXTIME($time, '$condition') as day,count(*) as count");
                                  if ($field == 'invite_id') {
                                      $query->where('invite_id', '<>', 0);
                                  }
                                  $query->group($time);
                              })->select()->toArray();
            $tempList = [];
            foreach ($dataList as $key => $item) {
                $data = list_search($resultList, ['day' => $item]);
                if (!empty($data)) {
                    $tempList[$key] = $data;
                } else {
                    $tempList[$key] = ['day' => $item, 'count' => 0];
                }
            }

            $seriesList[] = [
                'name'       => $index,
                'type'       => 'line',
                'stack'      => 'Total',
                'showSymbol' => false,
                'itemStyle'  => ['normal' => ['areaStyle' => ['type' => 'default']]],
                'data'       => array_column($tempList, 'count'),
            ];
        }

        $registerChartsOptions = $this->getEchartsData(array_keys($dataList), $seriesList);

        $userGroupData = [];
        $userList = User::field('group_id,count(id) as count')->group('group_id')->select()->toArray();
        foreach ($userList as $item) {
            $title = UserGroup::where('id', $item['group_id'])->value('title');
            if (!empty($title)) {
                $userGroupData[] = [
                    'name'  => $title,
                    'value' => $item['count']
                ];

            } else {
                $userGroupData[] = [
                    'name'  => '未定义',
                    'value' => $item['count']
                ];
            }
        }

        $userGroupData[] = ['name' => '性别(男)', 'value' => User::where('gender', 1)->count()];
        $userGroupData[] = ['name' => '性别(女)', 'value' => User::where('gender', 0)->count()];

        // 搜索词云数据
        if (Event::hasListener('cmsHotSearch')) {
            $searchWords = Event::emit('cmsHotSearch', null, true);
        } else {  // 模拟数据
            for ($i = 0; $i < 50; $i++) {
                $searchWords[] = [
                    'name'  => Random::alpha(),
                    'value' => Random::number(),
                ];
            }
        }

        $pluginList = get_plugin_list();
        $tableList = Db::query('SHOW TABLE STATUS');

        $assetsInfo = [
            'pluginCount'     => count($pluginList),
            'pluginRunning'   => array_sum(array_column($pluginList, 'status')),
            'tableCount'      => count($tableList),
            'dbSize'          => format_bytes(array_sum(array_map(function ($item) {
                return $item['Data_length'] + $item['Index_length'];
            }, $tableList))),
            'attachmentCount' => Attachment::count(),
            'attachmentSize'  => format_bytes((int)Attachment::sum('filesize')),
        ];

        $theLogsCount = Db::name('system_log')->count('id');
        $exceptionCount = Db::name('system_log')->where('line', '>', 0)->count('id');
        $devOpsData = [
            $theLogsCount,
            [
                'value'     => $exceptionCount,
                'itemStyle' => [
                    'color' => '#a90000'
                ]
            ],
            $theLogsCount - $exceptionCount,
            UserValidate::whereNotNull('email')->count('id'),
            UserValidate::whereNotNull('mobile')->count('id'),
            User::count('id'),
            UserThird::count('id'),
        ];

        return view('/index/console', [
            'assetsInfo'            => $assetsInfo,
            'workplace'             => [],
            'devOpsData'            => json_encode($devOpsData, JSON_UNESCAPED_UNICODE),
            'searchWords'           => json_encode($searchWords, JSON_UNESCAPED_UNICODE),
            'userGroupData'         => json_encode($userGroupData, JSON_UNESCAPED_UNICODE),
            'RegisterChartsOptions' => json_encode($registerChartsOptions, JSON_UNESCAPED_UNICODE),
        ]);
    }

    /**
     * 获取数据结构
     * @param array $dataList
     * @param array $seriesList
     * @return array
     */
    protected function getEchartsData(array $dataList, array $seriesList): array
    {
        return [
            'color'   => ['#1890ff', '#ee6666', '#b0e689'],
            'tooltip' => ['trigger' => 'axis'],
            'legend'  => [
                'orient' => 'horizontal',
            ],
            'grid'    => [
                'left'   => '5%',
                'top'    => '13%',
                'bottom' => '15%',
                'right'  => '5%'
            ],
            'xAxis'   => [
                'type'        => 'category',
                'boundaryGap' => true,
                'data'        => $dataList,
            ],
            'yAxis'   => [
                'type' => 'value',
            ],
            'series'  => $seriesList
        ];
    }

    /**
     * 分析页
     * @return mixed
     */
    public function analysis(): \support\Response
    {
        return view('/index/analysis');
    }

    /**
     * 监控页
     * @return mixed
     */
    public function monitor(): \support\Response
    {
        return view('/index/monitor');
    }

    /**
     * 获取系统配置
     */
    public function basecfg(): \support\Response
    {
        $config = Config::all();
        $config['fsockopen'] = function_exists('fsockopen');
        $config['stream_socket_client'] = function_exists('stream_socket_client');
        return view('/index/basecfg', ['config' => $config]);
    }

    /**
     * 编辑系统配置
     *
     * @param array $config
     * @return \support\Response
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function baseSet(): \support\Response
    {
        if (request()->isPost()) {
            $config = [];
            $post = request()->all();
            $list = Config::select()->toArray();
            foreach ($list as $key => $value) {
                $name = $value['name'];
                if (isset($post[$name])) {
                    $option['id'] = $value['id'];
                    if ('array' == trim($value['type'])) {
                        $option['value'] = json_encode($post[$name], JSON_UNESCAPED_UNICODE);
                    } else {
                        $option['value'] = $post[$name];
                    }

                    $config[$key] = $option;
                }
            }

            try {
                (new Config())->saveAll($config);
                $env = base_path() . '/.env';
                $parse = parse_ini_file($env, true);
                $parse['CACHE_DRIVER'] = $post['cache_type'];
                $parse['CACHE_HOSTNAME'] = $post['cache_host'];
                $parse['CACHE_HOSTPORT'] = $post['cache_port'];
                $parse['CACHE_SELECT'] = min($post['cache_select'], 1);
                $parse['CACHE_USERNAME'] = $post['cache_user'];
                $parse['CACHE_PASSWORD'] = $post['cache_pass'];
                write_file($env, parse_array_ini($parse));
            } catch (\Throwable $th) {
                return $this->error($th->getMessage());
            }

            // 清理系统缓存
            $configList = Cache::get('config_list');
            foreach ($configList as $item) {
                Cache::delete($item);
            }
        }

        return $this->success('保存成功!');
    }

    /**
     * FTP测试上传
     */
    public function testFtp(): \support\Response
    {
        if (request()->isPost()) {
            if (Ftp::instance()->ftpTest(request()->post())) {
                return $this->success('上传测试成功！');
            }
        }

        return $this->error('上传测试失败！');
    }

    /**
     * 邮件测试
     */
    public function testEmail()
    {
        if (request()->isPost()) {
            $info = Email::instance()->testEMail(request()->post());
            return $info === true ? $this->success('测试邮件发送成功！') : $this->error($info);
        }
    }

    /**
     * 缓存测试
     */
    public function testCache()
    {
        if (request()->isPost()) {

            $param = request()->post();
            if (!isset($param['type']) || empty($param['host']) || empty($param['port'])) {
                return $this->error('参数错误!');
            }

            $options = [
                'host'     => $param['host'],
                'port'     => (int)$param['port'],
                'username' => $param['user'],
                'password' => $param['pass']
            ];

            try {
                if (strtolower($param['type']) == 'redis') {
                    $drive = new Redis($options);
                } else {
                    $drive = new Memcached($options);
                }
            } catch (\Throwable $th) {
                return $this->error($th->getMessage());
            }

            if ($drive->set('test', 'cacheOK', 1000)) {
                return $this->success('缓存测试成功！');
            } else {
                return $this->error('缓存测试失败！');
            }
        }

        return false;
    }
}

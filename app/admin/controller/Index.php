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

use Psr\SimpleCache\InvalidArgumentException;
use support\Response;
use think\db\exception\BindParamException;
use think\facade\Cache;
use think\facade\Db;
use Webman\Event\Event;
use system\Random;
use think\cache\driver\Memcached;
use think\cache\driver\Redis;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use app\AdminController;
use app\common\library\Email;
use app\common\library\Ftp;
use app\common\model\system\AdminNotice;
use app\common\model\system\Attachment;
use app\common\model\system\Config;
use app\common\model\system\User;
use app\common\model\system\UserGroup;
use app\common\model\system\UserThird;
use app\common\model\system\UserValidate;

class Index extends AdminController
{

    /**
     * 初始化函数
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @throws \Exception
     */
    public function index()
    {
        $notice_count = AdminNotice::where('status', 0)->count();
        return view('index/index', [
            'notice_count' => $notice_count,
        ]);
    }

    /**
     * 控制台首页
     * @return response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws BindParamException
     */
    public function console()
    {
        $dataList = [];
        $dateBefore = date('Y-m-d', strtotime('-30 day'));
        $dateAfter = date('Y-m-d 23:59:59');

        if (request()->isPost()) {

            $cycle = input('cycle');

            [$dataList, $seriesList] = $this->getUserEcharts($cycle);
            if (empty($seriesList)) {
                return $this->error('暂无数据');
            }

            $userChartsOptions = $this->getEchartsData(array_values($dataList), $seriesList);
            return $this->success('操作成功', '', $userChartsOptions);
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
            $searchWords = Event::emit('cmsHotSearch', [], true);
        } else {  // 模拟数据
            for ($i = 0; $i < 50; $i++) {
                $searchWords[] = [
                    'name'  => Random::lower(),
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
     *
     * @param string $cycle
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    protected function getUserEcharts(string $cycle): array
    {
        $seriesList = [];
        $dataList = [];
        $condition = '%w';
        $dateBefore = date('Y-m-d', strtotime('-7 day'));
        $dateAfter = date('Y-m-d 23:59:59');
        switch ($cycle) {
            case 'week':
                $dataList = array('周日', '周一', '周二', '周三', '周四', '周五', '周六');
                break;
            case 'month':
                $condition = '%d';
                $dateBefore = date('Y-m-01');
                $dateAfter = date('Y-m-d', strtotime("+1 day"));
                $dataList = array('01' => ['1'], '02' => ['2'], '03' => ['3'], '04' => ['4'], '05' => ['5'], '06' => ['6'], '07' => ['7'], '08' => ['8'],
                                  '09' => ['9'], '10' => ['10'], '11' => ['11'], '12' => ['12'], '13' => ['13'], '14' => ['14'], '15' => ['15'], '16' => ['16'],
                                  '17' => ['17'], '18' => ['18'], '19' => ['19'], '20' => ['20'], '21' => ['21'], '22' => ['22'], '23' => ['23'], '24' => ['24'],
                                  '25' => ['25'], '26' => ['26'], '27' => ['27'], '28' => ['28'], '29' => ['29'], '30' => ['30'], '31' => ['31']);
                break;
            case 'year':
                $condition = '%m';
                $dateBefore = date('Y-01-01');
                $dateAfter = date('Y-12-31 23:59:59');
                $dataList = array('01' => ['一月'], '02' => ['二月'], '03' => ['三月'], '04' => ['四月'], '05' => ['五月'], '06' => ['六月'],
                                  '07' => ['七月'], '08' => ['八月'], '09' => ['九月'], '10' => ['十月'], '11' => ['十一月'], '12' => ['十二月']);
                break;
            default:
                break;
        }

        $resultList = $this->getCycleEcharts($dateBefore, $dateAfter, $condition);
        foreach ($resultList as $index => $item) {
            $tempList = [];

            foreach ($dataList as $key => $value) {
                $data = list_search($item, ['day' => $key]);
                if (!empty($data)) {
                    $tempList[$key] = $data;
                } else {
                    $tempList[$key] = ['day' => $value, 'count' => 0];
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

        return [$dataList, $seriesList];
    }

    /**
     * 获取一段时间内订单列表
     * @param $dateBefore
     * @param $dateAfter
     * @param $condition
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    protected function getCycleEcharts($dateBefore, $dateAfter, $condition): array
    {
        $resultList = [];
        $columns = ['用户注册' => 'create_time', '用户登录' => 'login_time', '邀请注册' => 'invite_id'];
        foreach ($columns as $index => $field) {
            $time = str_replace('invite_id', 'create_time', $field);
            $resultList[$index] = \app\common\model\system\User::where($time, 'between time', [$dateBefore, $dateAfter])
                ->when($condition, function ($query) use ($condition, $time, $field) {
                    $query->field("FROM_UNIXTIME($time, '$condition') as day,count(*) as count");
                    if ($field == 'invite_id') {
                        $query->where('invite_id', '<>', 0);
                    }
                    $query->group("FROM_UNIXTIME($time, '$condition')");
                })->order($time, 'asc')->select()->toArray();
        }

        return $resultList;
    }

    /**
     * 分析页
     * @return mixed
     */
    public function analysis(): Response
    {
        return view('/index/analysis');
    }

    /**
     * 监控页
     * @return mixed
     */
    public function monitor(): Response
    {
        return view('/index/monitor');
    }

    /**
     * 获取系统配置
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function basecfg(): Response
    {
        $config = Config::all();
        $config['fsockopen'] = function_exists('fsockopen');
        $config['stream_socket_client'] = function_exists('stream_socket_client');
        return view('/index/basecfg', ['config' => $config]);
    }

    /**
     * 编辑系统配置
     *
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws InvalidArgumentException
     */
    public function baseSet(): Response
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
    public function testFtp(): Response
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

<?php

require __DIR__ . '/../vendor/autoload.php';

use Workerman\Worker;
use Workerman\Timer;
use Workerman\RedisQueue\Client;

$worker = new Worker();
$worker->onWorkerStart = function () {
    $client = new Client('redis://127.0.0.1:6379');
    $client->subscribe('user-1', function ($data) {
        echo "user-1\n";
        var_export($data);
    }, function ($data) {
        echo "user-1 failed\n";
        var_export($data);
    });
    $client->subscribe('user-2', function ($data) {
        echo "user-2\n";
        var_export($data);
    }, function ($data) {
        echo "user-2 failed\n";
        var_export($data);
    });
    $client->onConsumeFailure(function (\Throwable $exception, $package) {
        echo "consume failure\n";
        echo $exception->getMessage(), "\n";
        var_export($package);
    });
    Timer::add(1, function () use ($client) {
        $client->send('user-1', [666, 777]);
    });
};

Worker::runAll();

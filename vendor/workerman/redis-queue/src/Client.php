<?php
/**
 * This file is part of workerman.
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

namespace Workerman\RedisQueue;

use RuntimeException;
use Workerman\Timer;
use Workerman\Redis\Client as Redis;

/**
 * Class Client
 * @package Workerman\RedisQueue
 */
class Client
{
    /**
     * Queue waiting for consumption
     */
    const QUEUE_WAITING = '{redis-queue}-waiting';

    /**
     * Queue with delayed consumption
     */
    const QUEUE_DELAYED = '{redis-queue}-delayed';

    /**
     * Queue with consumption failure
     */
    const QUEUE_FAILED = '{redis-queue}-failed';

    /**
     * @var Redis
     */
    protected $_redisSubscribe;

    /**
     * @var Redis
     */
    protected $_redisSend;

    /**
     * @var array
     */
    protected $_subscribeQueues = [];

    /**
     * @var array
     */
    protected $_options = [
        'retry_seconds' => 5,
        'max_attempts'  => 5,
        'auth'          => '',
        'db'            => 0,
        'prefix'        => '',
    ];

    /**
     * Client constructor.
     * @param $address
     * @param array $options
     */
    public function __construct($address, $options = [])
    {
        $this->_redisSubscribe = new Redis($address, $options);
        $this->_redisSubscribe->brPoping = 0;
        $this->_redisSend = new Redis($address, $options);
        if (isset($options['auth']) && $options['auth'] !== '') {
            $this->_redisSubscribe->auth($options['auth']);
            $this->_redisSend->auth($options['auth']);
        }
        if (isset($options['db'])) {
            $this->_redisSubscribe->select($options['db']);
            $this->_redisSend->select($options['db']);
        }
        $this->_options = array_merge($this->_options, $options);
    }

    /**
     * Send.
     *
     * @param $queue
     * @param $data
     * @param int $delay
     * @param callable $cb
     */
    public function send($queue, $data, $delay = 0, $cb = null)
    {
        static $_id = 0;
        $id = \microtime(true) . '.' . (++$_id);
        $now = time();
        $package_str = \json_encode([
            'id'       => $id,
            'time'     => $now,
            'delay'    => $delay,
            'attempts' => 0,
            'queue'    => $queue,
            'data'     => $data
        ]);
        if (\is_callable($delay)) {
            $cb = $delay;
            $delay = 0;
        }
        if ($cb) {
            $cb = function ($ret) use ($cb) {
                $cb((bool)$ret);
            };
            if ($delay == 0) {
                $this->_redisSend->lPush($this->_options['prefix'] . static::QUEUE_WAITING . $queue, $package_str, $cb);
            } else {
                $this->_redisSend->zAdd($this->_options['prefix'] . static::QUEUE_DELAYED, $now + $delay, $package_str, $cb);
            }
            return;
        }
        if ($delay == 0) {
            $this->_redisSend->lPush($this->_options['prefix'] . static::QUEUE_WAITING . $queue, $package_str);
        } else {
            $this->_redisSend->zAdd($this->_options['prefix'] . static::QUEUE_DELAYED, $now + $delay, $package_str);
        }
    }

    /**
     * Subscribe.
     *
     * @param string|array $queue
     * @param callable $callback
     */
    public function subscribe($queue, callable $callback)
    {
        $queue = (array)$queue;
        foreach ($queue as $q) {
            $redis_key = $this->_options['prefix'] . static::QUEUE_WAITING . $q;
            $this->_subscribeQueues[$redis_key] = $callback;
        }
        $this->pull();
    }

    /**
     * Unsubscribe.
     *
     * @param string|array $queue
     * @return void
     */
    public function unsubscribe($queue)
    {
        $queue = (array)$queue;
        foreach ($queue as $q) {
            $redis_key = $this->_options['prefix'] . static::QUEUE_WAITING . $q;
            unset($this->_subscribeQueues[$redis_key]);
        }
    }

    /**
     * tryToPullDelayQueue.
     */
    protected function tryToPullDelayQueue()
    {
        static $retry_timer = 0;
        if ($retry_timer) {
            return;
        }
        $retry_timer = Timer::add(1, function () {
            $now = time();
            $options = ['LIMIT', 0, 128];
            $this->_redisSend->zrevrangebyscore($this->_options['prefix'] . static::QUEUE_DELAYED, $now, '-inf', $options, function ($items) {
                if ($items === false) {
                    throw new RuntimeException($this->_redisSend->error());
                }
                foreach ($items as $package_str) {
                    $this->_redisSend->zRem($this->_options['prefix'] . static::QUEUE_DELAYED, $package_str, function ($result) use ($package_str) {
                        if ($result !== 1) {
                            return;
                        }
                        $package = \json_decode($package_str, true);
                        if (!$package) {
                            $this->_redisSend->lPush($this->_options['prefix'] . static::QUEUE_FAILED, $package_str);
                            return;
                        }
                        $this->_redisSend->lPush($this->_options['prefix'] . static::QUEUE_WAITING . $package['queue'], $package_str);
                    });
                }
            });
        });
    }

    /**
     * pull.
     */
    public function pull()
    {
        $this->tryToPullDelayQueue();
        if (!$this->_subscribeQueues || $this->_redisSubscribe->brPoping) {
            return;
        }
        $cb = function ($data) use (&$cb) {
            if ($data) {
                $this->_redisSubscribe->brPoping = 0;
                $redis_key = $data[0];
                $package_str = $data[1];
                $package = json_decode($package_str, true);
                if (!$package) {
                    $this->_redisSend->lPush($this->_options['prefix'] . static::QUEUE_FAILED, $package_str);
                } else {
                    if (!isset($this->_subscribeQueues[$redis_key])) {
                        // 取消订阅，放回队列
                        $this->_redisSend->rPush($redis_key, $package_str);
                    } else {
                        $callback = $this->_subscribeQueues[$redis_key];
                        try {
                            \call_user_func($callback, $package['data']);
                        } catch (\Exception $e) {
                            if (++$package['attempts'] > $this->_options['max_attempts']) {
                                $package['error'] = (string) $e;
                                $this->fail($package);
                            } else {
                                $this->retry($package);
                            }
                            echo $e;
                        } catch (\Error $e) {
                            if (++$package['attempts'] > $this->_options['max_attempts']) {
                                $package['error'] = (string) $e;
                                $this->fail($package);
                            } else {
                                $this->retry($package);
                            }
                            echo $e;
                        }
                    }
                }
            }
            if ($this->_subscribeQueues) {
                $this->_redisSubscribe->brPoping = 1;
                Timer::add(0.000001, [$this->_redisSubscribe, 'brPop'], [\array_keys($this->_subscribeQueues), 1, $cb], false);
            }
        };
        $this->_redisSubscribe->brPoping = 1;
        $this->_redisSubscribe->brPop(\array_keys($this->_subscribeQueues), 1, $cb);
    }

    /**
     * @param $package
     */
    protected function retry($package)
    {
        $delay = time() + $this->_options['retry_seconds'] * ($package['attempts']);
        $this->_redisSend->zAdd($this->_options['prefix'] . static::QUEUE_DELAYED, $delay, \json_encode($package));
    }

    /**
     * @param $package
     */
    protected function fail($package)
    {
        $this->_redisSend->lPush($this->_options['prefix'] . static::QUEUE_FAILED, \json_encode($package));
    }
}

<?php

declare(strict_types=1);

namespace Yansongda\Supports\Traits;

/**
 * Trait ShouldThrottle.
 *
 * @property \Redis $redis
 */
trait ShouldThrottle
{
    /**
     * @var array
     */
    protected $throttle = [
        'limit' => 60,
        'period' => 60,
        'count' => 0,
        'reset_time' => 0,
    ];

    /**
     * isThrottled.
     */
    public function isThrottled(string $key, int $limit = 60, int $period = 60, bool $autoAdd = false): bool
    {
        if (-1 === $limit) {
            return false;
        }

        $now = microtime(true) * 1000;

        $this->redis->zRemRangeByScore($key, 0, $now - $period * 1000);

        $this->throttle = [
            'limit' => $limit,
            'period' => $period,
            'count' => $this->getThrottleCounts($key, $period),
            'reset_time' => $this->getThrottleResetTime($key, $now),
        ];

        if ($this->throttle['count'] < $limit) {
            if ($autoAdd) {
                $this->throttleAdd($key, $period);
            }

            return false;
        }

        return true;
    }

    /**
     * 限流 + 1.
     */
    public function throttleAdd(string $key, int $period = 60): void
    {
        $now = microtime(true) * 1000;

        $this->redis->zAdd($key, $now, $now);
        $this->redis->expire($key, $period * 2);
    }

    /**
     * 获取下次重置时间.
     *
     * @param float $now 现在的毫秒时间
     */
    public function getThrottleResetTime(string $key, float $now): int
    {
        $data = $this->redis->zRangeByScore(
            $key,
            $now - $this->throttle['period'] * 1000,
            $now,
            ['limit' => [0, 1]]
        );

        if (0 === count($data)) {
            return $this->throttle['reset_time'] = time() + $this->throttle['period'];
        }

        return intval(reset($data) / 1000) + $this->throttle['period'];
    }

    /**
     * 获取限流相关信息.
     *
     * @param mixed $default
     *
     * @return mixed
     */
    public function getThrottleInfo(?string $key = null, $default = null)
    {
        if (is_null($key)) {
            return $this->throttle;
        }

        if (isset($this->throttle[$key])) {
            return $this->throttle[$key];
        }

        return $default;
    }

    /**
     * 获取已使用次数.
     */
    public function getThrottleCounts(string $key, int $period = 60): int
    {
        $now = microtime(true) * 1000;

        return $this->redis->zCount($key, $now - $period * 1000, $now);
    }
}

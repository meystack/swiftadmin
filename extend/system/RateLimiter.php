<?php

namespace system;

use support\Cache;

/**
 * 限流器
 * Class RateLimiter
 * @package system
 */
class RateLimiter
{
    /**
     * 基础限流名
     */
    const TOKEN_LIMITER = 'token_limiter_';

    /**
     * 令牌桶限流名
     */
    const RATE_LIMIT_BUCKET = 'rate_limit_bucket_';

    /**
     * 基础限流器
     * @param string $name
     * @param int $rate
     * @return bool
     */
    public static function baseLimit(string $name, int $rate = 10): bool
    {
        $bucketKey = self::TOKEN_LIMITER . $name;
        $bucketData = Cache::get($bucketKey);
        if (!$bucketData) {
            $bucketData = [
                'tokens'         => $rate,
                'lastRefillTime' => time()
            ];
            Cache::set($bucketKey, $bucketData, 1);
        }

        $currentTime = time();
        $timePassed = $currentTime - $bucketData['lastRefillTime'];
        $tokensToAdd = $timePassed * $rate;
        $tokensToAdd = min($tokensToAdd, $rate);
        $bucketData['tokens'] += $tokensToAdd;
        $bucketData['lastRefillTime'] = $currentTime;
        if ($bucketData['tokens'] <= 0) {
            return false;
        }

        $bucketData['tokens']--;
        Cache::set($bucketKey, $bucketData, 1);
        return true;
    }

    /**
     * 令牌桶限流器
     * @param string $name
     * @param int $rate
     * @param int $capacity
     * @return bool
     */
    public static function BucketLimit(string $name, int $rate = 10, int $capacity = 100): bool
    {
        $bucketKey = self::RATE_LIMIT_BUCKET . $name;
        $bucketData = Cache::get($bucketKey);
        if (!$bucketData) {
            $bucketData = [
                'tokens'         => $capacity,
                'lastRefillTime' => time()
            ];
            Cache::set($bucketKey, $bucketData);
        }

        $currentTime = time();
        $elapsedTime = $currentTime - $bucketData['lastRefillTime'];
        $refillTokens = (int)($elapsedTime * $rate);
        $bucketData['tokens'] = min($bucketData['tokens'] + $refillTokens, $capacity);
        $bucketData['lastRefillTime'] = $currentTime;
        if ($bucketData['tokens'] <= 0) {
            return false;
        }

        $bucketData['tokens']--;
        Cache::set($bucketKey, $bucketData);
        return true;
    }
}
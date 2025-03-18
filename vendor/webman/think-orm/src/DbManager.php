<?php

declare (strict_types = 1);

namespace Webman\ThinkOrm;

use Illuminate\Events\Dispatcher;
use Webman\Context;
use Workerman\Coroutine\Pool;
use Throwable;
use think\db\ConnectionInterface;
use think\db\BaseQuery;
use think\db\Query;

/**
 * Class DbManager.
 *
 * @mixin BaseQuery
 * @mixin Query
 */
class DbManager extends \think\DbManager
{

    /**
     * @var Pool[]
     */
    protected static array $pools = [];

    /**
     * Get instance of connection.
     *
     * @param string|null $name
     * @param bool $force
     * @return ConnectionInterface
     * @throws Throwable
     */
    protected function instance(?string $name = null, bool $force = false): ConnectionInterface
    {
        if (empty($name)) {
            $name = $this->getConfig('default', 'mysql');
        }
        $key = "think-orm.connections.$name";
        $connection = Context::get($key);
        if (!$connection) {
            if (!isset(static::$pools[$name])) {
                $poolConfig = $this->config['connections'][$name]['pool'] ?? [];
                $pool = new Pool($poolConfig['max_connections'] ?? 10, $poolConfig);
                $pool->setConnectionCreator(function () use ($name) {
                    return $this->createConnection($name);
                });
                $pool->setConnectionCloser(function ($connection) {
                    $this->closeConnection($connection);
                });
                $pool->setHeartbeatChecker(function ($connection) {
                    $connection->query('select 1');
                });
                static::$pools[$name] = $pool;
            }
            try {
                $connection = static::$pools[$name]->get();
                Context::set($key, $connection);
            } finally {
                Context::onDestroy(function () use ($connection, $name) {
                    try {
                        $connection && static::$pools[$name]->put($connection);
                    } catch (Throwable) {
                        // ignore
                    }
                });
            }
        }
        return $connection;
    }

    /**
     * Close connection.
     *
     * @param ConnectionInterface $connection
     * @return void
     */
    protected function closeConnection(ConnectionInterface $connection)
    {
        $connection->close();
        $clearProperties = function () {
            $this->db = null;
            $this->cache = null;
            $this->builder = null;
        };
        $clearProperties->call($connection);
    }
}

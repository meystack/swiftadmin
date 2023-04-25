<?php

namespace think\facade {

    class Db
    {
        /**
         * 指定当前数据表名（不含前缀）
         * @access public
         * @param string $name 不含前缀的数据表名字
         * @return \think\Db
         */
        public static function name(string $name)
        {
            /** @var \think\Db $instance */
            return $instance->name($name);
        }
        /**
         * 指定当前操作的数据表
         * @access public
         * @param mixed $table 表名
         * @return \think\Db
         */
        public static function table($table)
        {
            /** @var \think\Db $instance */
            return $instance->table($table);
        }

        /**
         * 执行数据库事务
         * @access public
         * @param callable $callback 数据操作方法回调
         * @return mixed
         */
        public static function transaction(callable $callback)
        {
            /** @var \think\Db $instance */
            return $instance->transaction($callback);
        }

        /**
         * 启动事务
         * @access public
         * @return void
         */
        public static function startTrans(): void
        {
            /** @var \think\Db $instance */
            $instance->startTrans();
        }

        /**
         * 用于非自动提交状态下面的查询提交
         * @access public
         * @return void
         * @throws \PDOException
         */
        public static function commit(): void
        {
            /** @var \think\Db $instance */
            $instance->commit();
        }

        /**
         * 事务回滚
         * @access public
         * @return void
         * @throws \PDOException
         */
        public static function rollback(): void
        {
            /** @var \think\Db $instance */
            $instance->rollback();
        }
        /**
         * 执行查询 返回数据集
         * @access public
         * @param string $sql  sql指令
         * @param array  $bind 参数绑定
         * @return array
         * @throws \think\db\exception\BindParamException
         * @throws \PDOException
         */
        public static function query(string $sql, array $bind = []): array
        {
            /** @var \think\Db $instance */
            return $instance->query($sql,$bind);
        }

        /**
         * 设置从主服务器读取数据
         * @access public
         * @param bool $readMaster 是否从主服务器读取
         * @return \think\Db
         */
        public function master(bool $readMaster = true)
        {
            /** @var \think\Db $instance */
            return $instance->master($readMaster);
        }
        /**
         * 执行语句
         * @access public
         * @param string $sql  sql指令
         * @param array  $bind 参数绑定
         * @return int
         * @throws \think\db\exception\BindParamException
         * @throws \PDOException
         */
        public static function execute(string $sql, array $bind = []): int
        {
            /** @var \think\Db $instance */
            return $instance->execute($sql,$bind);
        }

        /**
         * 创建/切换数据库连接查询
         * @access public
         * @param string|null $name  连接配置标识
         * @param bool        $force 强制重新连接
         * @return \think\db\BaseQuery
         */
        public function connect(string $name = null, bool $force = false): \think\db\BaseQuery
        {

            /** @var \think\Db $instance */
            return $instance->connect($name,$force);
        }

        /**
         * 设置配置对象
         * @access public
         *
         * @param Config $config 配置对象
         *
         * @return void
         */
        public static function setConfig($config): void
        {

            /** @var \think\Db $instance */
            $instance->setConfig($config);
        }

        /**
         * 获取配置参数
         * @access public
         *
         * @param string $name    配置参数
         * @param mixed  $default 默认值
         *
         * @return mixed
         */
        public static function getConfig(string $name = '', $default = null)
        {

            /** @var \think\Db $instance */
            return $instance->getConfig($name, $default);
        }

        /**
         * 设置Event对象
         *
         * @param Event $event
         */
        public static function setEvent(Event $event): void
        {

            /** @var \think\Db $instance */
            $instance->setEvent($event);
        }

        /**
         * 注册回调方法
         * @access public
         *
         * @param string   $event    事件名
         * @param callable $callback 回调方法
         *
         * @return void
         */
        public static function event(string $event, callable $callback): void
        {

            /** @var \think\Db $instance */
            $instance->event($event, $callback);
        }

        /**
         * 触发事件
         * @access public
         *
         * @param string $event  事件名
         * @param mixed  $params 传入参数
         * @param bool   $once
         *
         * @return mixed
         */
        public static function trigger(string $event, $params = null, bool $once = false)
        {

            /** @var \think\Db $instance */
            return $instance->trigger($event, $params, $once);
        }

    }
   
}

namespace think {

    /**
     * @method static \think\db\Query where(mixed $field, string $op = null, mixed $condition = null)  查询条件
     * @method static \think\db\Query whereTime(string $field, string $op, mixed $range = null) 查询日期和时间
     * @method static \think\db\Query whereBetweenTime(string $field, mixed $startTime, mixed $endTime) 查询日期或者时间范围
     * @method static \think\db\Query whereBetweenTimeField(string $startField, string $endField) 查询当前时间在两个时间字段范围
     * @method static \think\db\Query whereYear(string $field, string $year = 'this year') 查询某年
     * @method static \think\db\Query whereMonth(string $field, string $month = 'this month') 查询某月
     * @method static \think\db\Query whereDay(string $field, string $day = 'today') 查询某日
     * @method static \think\db\Query whereRaw(string $where, array $bind = []) 表达式查询
     * @method static \think\db\Query whereExp(string $field, string $condition, array $bind = []) 字段表达式查询
     * @method static \think\db\Query when(mixed $condition, mixed $query, mixed $otherwise = null) 条件查询
     * @method static \think\db\Query join(mixed $join, mixed $condition = null, string $type = 'INNER') JOIN查询
     * @method static \think\db\Query view(mixed $join, mixed $field = null, mixed $on = null, string $type = 'INNER') 视图查询
     * @method static \think\db\Query with(mixed $with) 关联预载入
     * @method static \think\db\Query count(string $field) Count统计查询
     * @method static \think\db\Query min(string $field) Min统计查询
     * @method static \think\db\Query max(string $field) Max统计查询
     * @method static \think\db\Query sum(string $field) SUM统计查询
     * @method static \think\db\Query avg(string $field) Avg统计查询
     * @method static \think\db\Query field(mixed $field, boolean $except = false) 指定查询字段
     * @method static \think\db\Query fieldRaw(string $field, array $bind = []) 指定查询字段
     * @method static \think\db\Query union(mixed $union, boolean $all = false) UNION查询
     * @method static \think\db\Query limit(mixed $offset, integer $length = null) 查询LIMIT
     * @method static \think\db\Query order(mixed $field, string $order = null) 查询ORDER
     * @method static \think\db\Query orderRaw(string $field, array $bind = []) 查询ORDER
     * @method static \think\db\Query cache(mixed $key = null, integer $expire = null) 设置查询缓存
     * @method mixed value(string $field) 获取某个字段的值
     * @method array column(string $field, string $key = '') 获取某个列的值
     * @method Model find(mixed $data = null) 查询单个记录 不存在返回Null
     * @method Model findOrEmpty(mixed $data = null) 查询单个记录 不存在返回空模型
     * @method \think\model\Collection select(mixed $data = null) 查询多个记录
     * @method Model withAttr(array $name, \Closure $closure) 动态定义获取器
     */
    class Model
    {

        /**
         * 指定当前数据表名（不含前缀）
         * @access public
         * @param string $name 不含前缀的数据表名字
         * @return \think\Db
         */
        public static function name(string $name)
        {
            /** @var \think\Db $instance */
            return $instance->name($name);
        }
        /**
         * 指定当前操作的数据表
         * @access public
         * @param mixed $table 表名
         * @return \think\Db
         */
        public static function table($table)
        {
            /** @var \think\Db $instance */
            return $instance->table($table);
        }

        /**
         * 执行数据库事务
         * @access public
         * @param callable $callback 数据操作方法回调
         * @return mixed
         */
        public static function transaction(callable $callback)
        {
            /** @var \think\Db $instance */
            return $instance->transaction($callback);
        }

        /**
         * 启动事务
         * @access public
         * @return void
         */
        public static function startTrans(): void
        {
            /** @var \think\Db $instance */
            $instance->startTrans();
        }

        /**
         * 用于非自动提交状态下面的查询提交
         * @access public
         * @return void
         * @throws \PDOException
         */
        public static function commit(): void
        {
            /** @var \think\Db $instance */
            $instance->commit();
        }

        /**
         * 事务回滚
         * @access public
         * @return void
         * @throws \PDOException
         */
        public static function rollback(): void
        {
            /** @var \think\Db $instance */
            $instance->rollback();
        }
        /**
         * 执行查询 返回数据集
         * @access public
         * @param string $sql  sql指令
         * @param array  $bind 参数绑定
         * @return array
         * @throws \think\db\exception\BindParamException
         * @throws \PDOException
         */
        public function query(string $sql, array $bind = []): array
        {
            /** @var \think\Db $instance */
            return $instance->query($sql,$bind);
        }

        /**
         * 设置从主服务器读取数据
         * @access public
         * @param bool $readMaster 是否从主服务器读取
         * @return \think\Db
         */
        public function master(bool $readMaster = true)
        {
            /** @var \think\Db $instance */
            return $instance->master($readMaster);
        }
        /**
         * 执行语句
         * @access public
         * @param string $sql  sql指令
         * @param array  $bind 参数绑定
         * @return int
         * @throws \think\db\exception\BindParamException
         * @throws \PDOException
         */
        public function execute(string $sql, array $bind = []): int
        {
            /** @var \think\Db $instance */
            return $instance->execute($sql,$bind);
        }

        /**
         * 创建/切换数据库连接查询
         * @access public
         * @param string|null $name  连接配置标识
         * @param bool        $force 强制重新连接
         * @return \think\db\BaseQuery
         */
        public function connect(string $name = null, bool $force = false): \think\db\BaseQuery
        {

            /** @var \think\Db $instance */
            return $instance->connect($name,$force);
        }

        /**
         * 设置配置对象
         * @access public
         *
         * @param Config $config 配置对象
         *
         * @return void
         */
        public static function setConfig($config): void
        {

            /** @var \think\Db $instance */
            $instance->setConfig($config);
        }

        /**
         * 获取配置参数
         * @access public
         *
         * @param string $name    配置参数
         * @param mixed  $default 默认值
         *
         * @return mixed
         */
        public static function getConfig(string $name = '', $default = null)
        {

            /** @var \think\Db $instance */
            return $instance->getConfig($name, $default);
        }

        /**
         * 设置Event对象
         *
         * @param Event $event
         */
        public static function setEvent(Event $event): void
        {

            /** @var \think\Db $instance */
            $instance->setEvent($event);
        }

        /**
         * 注册回调方法
         * @access public
         *
         * @param string   $event    事件名
         * @param callable $callback 回调方法
         *
         * @return void
         */
        public static function event(string $event, callable $callback): void
        {

            /** @var \think\Db $instance */
            $instance->event($event, $callback);
        }

        /**
         * 触发事件
         * @access public
         *
         * @param string $event  事件名
         * @param mixed  $params 传入参数
         * @param bool   $once
         *
         * @return mixed
         */
        public static function trigger(string $event, $params = null, bool $once = false)
        {

            /** @var \think\Db $instance */
            return $instance->trigger($event, $params, $once);
        }

    }
}
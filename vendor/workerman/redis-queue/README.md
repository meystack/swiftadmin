# redis-queue
Message queue system written in PHP based on [workerman](https://github.com/walkor/workerman) and backed by Redis.

# Install
```
composer require workerman/redis-queue
```

# Usage
test.php
```php
<?php
require __DIR__ . '/vendor/autoload.php';

use Workerman\Worker;
use Workerman\Lib\Timer;
use Workerman\RedisQueue\Client;

$worker = new Worker();
$worker->onWorkerStart = function () {
    $client = new Client('redis://127.0.0.1:6379');
    $client->subscribe('user-1', function($data){
        echo "user-1\n";
        var_export($data);
    });
    $client->subscribe('user-2', function($data){
        echo "user-2\n";
        var_export($data);
    });
    Timer::add(1, function()use($client){
        $client->send('user-1', ['some', 'data']);
    });
};

Worker::runAll();
```

Run with command `php test.php start` or `php test.php start -d`.

# API

  * <a href="#construct"><code>Client::<b>__construct()</b></code></a>
  * <a href="#send"><code>Client::<b>send()</b></code></a>
  * <a href="#subscribe"><code>Client::<b>subscribe()</b></code></a>
  * <a href="#unsubscribe"><code>Client::<b>unsubscribe()</b></code></a>

-------------------------------------------------------

<a name="construct"></a>
### __construct (string $address, [array $options])

Create an instance by $address and $options.

  * `$address`  for example `redis://ip:6379`. 

  * `$options` is the client connection options. Defaults:
    * `auth`: default ''
    * `db`: default 0
    * `retry_seconds`: Retry interval after consumption failure
    * `max_attempts`: Maximum number of retries after consumption failure
   
-------------------------------------------------------

<a name="send"></a>
### send(String $queue, Mixed $data, [int $dely=0])

Send a message to a queue

* `$queue` is the queue to publish to, `String`
* `$data` is the message to publish, `Mixed`
* `$dely` is delay seconds for delayed consumption, `Int`
  
-------------------------------------------------------

<a name="subscribe"></a>
### subscribe(mixed $queue, callable $callback)

Subscribe to a queue or queues

* `$queue` is a `String` queue or an `Array` which has as keys the queue name to subscribe.
* `$callback` - `function (Mixed $data)`, `$data` is the data sent by `send($queue, $data)`.

-------------------------------------------------------

<a name="unsubscribe"></a>
### unsubscribe(mixed $queue)

Unsubscribe from a queue or queues

* `$queue` is a `String` queue or an array of queue to unsubscribe from

-------------------------------------------------------

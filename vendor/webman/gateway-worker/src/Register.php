<?php
namespace Webman\GatewayWorker;

class Register extends \GatewayWorker\Register
{
    public function __construct($config = [])
    {
        $propertyMap = [
            'secretKey',
//            'reloadable',
//            'user',
//            'group',
        ];
        foreach ($propertyMap as $property) {
            if (isset($config[$property])) {
                $this->$property = $config[$property];
            }
        }
    }

    public function onWorkerstart()
    {
        // 设置 onMessage 连接回调
        $this->onConnect = array($this, 'onConnect');

        // 设置 onMessage 回调
        $this->onMessage = array($this, 'onMessage');

        // 设置 onClose 回调
        $this->onClose = array($this, 'onClose');

        // 记录进程启动的时间
        $this->_startTime = time();

        // 强制使用text协议
        $this->protocol = '\Workerman\Protocols\Text';

        // reusePort
        $this->reusePort = false;
    }

}

<?php
namespace Webman\GatewayWorker;

class Gateway extends \GatewayWorker\Gateway
{
    /**
     * @var string 
     */
    protected $_autoloadRootPath = '';
    
    public function __construct($config)
    {
        foreach ($config as $key => $value)
        {
            $this->$key = $value;
        }

        $this->router = array("\\GatewayWorker\\Gateway", 'routerBind');
    }

    public function onConnect($connection)
    {
        parent::onClientConnect($connection);
    }

    public function onClose($connection)
    {
        parent::onClientClose($connection);
    }

    public function onMessage($connection, $data)
    {
        parent::onClientMessage($connection, $data);
    }

    public function onWorkerStart()
    {
        // 保存用户的回调，当对应的事件发生时触发
        $this->_onWorkerStart = $this->onWorkerStart;
        $this->onWorkerStart  = array($this, 'onWorkerStart');
        // 保存用户的回调，当对应的事件发生时触发
        $this->_onConnect = $this->onConnect;
        $this->onConnect  = array($this, 'onClientConnect');

        // onMessage禁止用户设置回调
        $this->onMessage = array($this, 'onClientMessage');

        // 保存用户的回调，当对应的事件发生时触发
        $this->_onClose = $this->onClose;
        $this->onClose  = array($this, 'onClientClose');
        // 保存用户的回调，当对应的事件发生时触发
        $this->_onWorkerStop = $this->onWorkerStop;
        $this->onWorkerStop  = array($this, 'onWorkerStop');

        if (!is_array($this->registerAddress)) {
            $this->registerAddress = array($this->registerAddress);
        }

        // 记录进程启动的时间
        $this->_startTime = time();

        $args = func_get_args();
        $this->id = $args[0]->id;

        $worker = func_get_arg(0);
        $this->_gatewayPort = substr(strrchr($worker->getSocketName(),':'),1);

        parent::onWorkerStart();
    }
}

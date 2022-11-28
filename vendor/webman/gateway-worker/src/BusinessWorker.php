<?php
namespace Webman\GatewayWorker;

class BusinessWorker extends \GatewayWorker\BusinessWorker
{
    public function __construct($config)
    {
        foreach ($config as $key => $value)
        {
            $this->$key = $value;
        }
    }

    public function onWorkerStart()
    {
        $this->_onWorkerStart  = $this->onWorkerStart;
        $this->_onWorkerReload = $this->onWorkerReload;
        $this->_onWorkerStop = $this->onWorkerStop;
        $this->onWorkerStop   = array($this, 'onWorkerStop');
        $this->onWorkerStart   = array($this, 'onWorkerStart');
        $this->onWorkerReload  = array($this, 'onWorkerReload');

        $args = func_get_args();
        $this->id = $args[0]->id;
        parent::onWorkerStart();
    }
}
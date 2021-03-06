<?php
namespace PHPMake\Firmata;
use PHPMake\Firmata;
use PHPMake\Firmata\WebSocketServer\ConnectionHub;
use PHPMake\Firmata\WebSocketServer\ComponentInterface;

class WebSocketServer {
    private $_component;
    private $_device;
    private $_loop;
    private $_server;

    public function __construct(ComponentInterface $component, $port = 80, $address = '0.0.0.0') {
        $this->_component = $component;
        $this->_device = $this->_component->getDevice();
        $this->_initLoop();
        $this->_initServer($port, $address);
    }

    private function _initLoop() {
        $component = $this->_component;
        $device = $this->_device;
        $previous = 0;
        $interval = $this->_component->getInterval()/1000000;
        $tick = function () use ($component, $device, $previous, $interval) {
            $current = microtime(true);
            $elapsed = $current - $previous;
            if ($elapsed >= $interval) {
                $component->tick($device);
                $device->noop();
                $previous = $current;
            }
        };
        $baseInterval = Firmata\Device::getDeviceLoopMinIntervalInMicroseconds();
        $this->_loop = new \React\EventLoop\StreamSelectLoop();
        $this->_loop->addPeriodicTimer($baseInterval/1000000, $tick);
    }

    private function _initServer($port, $address) {
        $socket = new \React\Socket\Server($this->_loop);
        $socket->listen($port, $address);
        $mci = new \Ratchet\Http\HttpServer(new \Ratchet\WebSocket\WsServer($this->_component));
        $this->_server = new \Ratchet\Server\IoServer($mci, $socket, $this->_loop);
    }

    public function run() {
        $this->_device->reportAnalogPinAll();
        $this->_server->run();
    }
}

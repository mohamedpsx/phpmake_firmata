<?php
namespace PHPMake\Firmata\WebSocketServer\JsonCommand;
use \PHPMake\Firmata;

class QueryPinState extends JsonCommandAdapter {

    public function execute(
        $commandName,
        $signature,
        array $arguments,
        Firmata\Device $device,
        \Ratchet\ConnectionInterface $from,
        \Iterator $connections)
    {
        $targetPin = $arguments[0];
        $device->updatePin($targetPin);
        $pin = $device->getPin($targetPin);
        $data = (object)array(
            'pin' => $targetPin,
            'mode' => Firmata::modeStringFromCode($pin->getMode()),
            'state' => $pin->getState()
        );
        $this->send($from, $commandName, $signature, $data);
    }
}

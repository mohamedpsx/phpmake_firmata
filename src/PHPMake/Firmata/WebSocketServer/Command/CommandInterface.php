<?php
namespace PHPMake\Firmata\WebSocketServer\Command;

interface CommandInterface {

    public function execute(
        $commandName,
        $signature,
        array $arguments,
        \PHPMake\Firmata\Device $device,
        \Ratchet\ConnectionInterface $connectionFrom,
        \Iterator $connections);
}

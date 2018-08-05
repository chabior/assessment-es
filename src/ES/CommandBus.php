<?php

namespace App\ES;


use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\GenericEvent;

class CommandBus extends EventDispatcher
{
    public function onCommand(string $commandName, $listener)
    {
        $this->addListener($commandName, $listener);
    }

    public function dispatchCommand($command)
    {
        $this->dispatch(get_class($command), new GenericEvent($command));
    }

    protected function doDispatch($listeners, $eventName, Event $event)
    {
        foreach ($listeners as $listener) {
            $listener($event->getSubject());
        }
    }
}
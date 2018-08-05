<?php

namespace App\ES;


use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;

class EventBus extends EventDispatcher
{
    protected function doDispatch($listeners, $eventName, Event $event)
    {
        foreach ($listeners as $listener) {
            \call_user_func($listener, $event->getSubject(), $eventName, $this);
        }
    }
}
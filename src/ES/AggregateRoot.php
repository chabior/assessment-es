<?php

namespace App\ES;


abstract class AggregateRoot
{
    /**
     * @var array
     */
    private $events = [];

    abstract protected function apply($event);

    protected function recordThat($event)
    {
        $this->events[] = $event;
    }

    public function popEvents():array
    {
        return $this->events;
    }
}
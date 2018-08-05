<?php

namespace App\ES;


class AggregateRootTranslator extends AggregateRoot
{
    public function popRecordedEvents(AggregateRoot $aggregateRoot): array
    {
        return $aggregateRoot->popEvents();
    }

    public function restoreFromHistory(array $events, AggregateRoot $aggregateRoot)
    {
        foreach ($events as $event) {
            $aggregateRoot->apply(unserialize($event['event']));
        }
    }

    protected function apply($event)
    {}

    public function getId():string
    {}
}
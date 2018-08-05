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
            $aggregateRoot->apply($event);
        }
    }

    protected function apply($event)
    {}
}
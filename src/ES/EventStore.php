<?php

namespace App\ES;


use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\GenericEvent;

class EventStore
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var EventBus
     */
    private $eventBus;

    /**
     * @var AggregateRootTranslator
     */
    private $aggregateRootTranslator;

    public function __construct(Connection $connection, EventBus $eventBus)
    {
        $this->connection = $connection;
        $this->eventBus = $eventBus;
        $this->aggregateRootTranslator = new AggregateRootTranslator();
    }

    public function store(AggregateRoot $aggregateRoot)
    {
        $this->connection->beginTransaction();
        $events = $this->aggregateRootTranslator->popRecordedEvents($aggregateRoot);
        foreach ($events as $event) {
            $this->storeEvent($aggregateRoot->getId(), get_class($aggregateRoot), $event);
            $this->eventBus->dispatch(get_class($event), new GenericEvent($event));
        }
        $this->connection->commit();
    }

    public function getAggregateRoot(string $id, string $aggregateName):?object
    {
        $events = $this->getEvents($id, $aggregateName);
//        print '<pre>';
//        print_r($events);
//        die;
        $aggregate = new $aggregateName;
        $this->aggregateRootTranslator->restoreFromHistory($events, $aggregate);

        return $aggregate;
    }

    private function getEvents(string $id, string $aggregateName):array
    {
        $qb = $this->connection->createQueryBuilder();

        $qb
            ->select(['*'])
            ->from('event', 'e')
            ->andWhere('e.aggregate_name = :aggregateName')
            ->andWhere('e.aggregate_id = :aggregateId')

            ->setParameter('aggregateId', $id)
            ->setParameter('aggregateName', $aggregateName)
        ;

        return $qb->execute()->fetchAll();
    }

    private function storeEvent(string $id, string $aggregateName, $event)
    {
        $qb = $this->connection->createQueryBuilder();

        $qb
            ->insert('event')
            ->values([
                'aggregate_id' => ':aggregateId',
                'aggregate_name' => ':aggregateName',
                'event' => ':event',
                'created_at' => 'NOW()',
            ])
            ->orderBy('id', 'ASC')
            ->setParameters([
                'aggregateId' => $id,
                'aggregateName' => $aggregateName,
                'event' => serialize($event),
            ])
        ;

        $qb->execute();
    }
}
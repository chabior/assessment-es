<?php

namespace App\Infrastructure;


use App\ES\EventStore;
use App\Model\Player;

class PlayerRepository
{
    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     *
     * @param EventStore $eventStore
     */
    public function __construct(EventStore $eventStore)
    {
        $this->eventStore = $eventStore;
    }

    public function save(Player $player)
    {
        $this->eventStore->store($player);
    }

    public function get(string $id):?Player
    {
        $player = $this->eventStore->getAggregateRoot($id, Player::class);
        /**
         * @var Player $player
         */
        return $player;
    }
}
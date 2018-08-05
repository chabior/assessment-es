<?php

namespace App\Model\Command;


class Login
{
    /**
     * @var int
     */
    private $playerId;

    /**
     *
     * @param int $playerId
     */
    public function __construct(int $playerId)
    {
        $this->playerId = $playerId;
    }

    public function getPlayerId():int
    {
        return $this->playerId;
    }
}
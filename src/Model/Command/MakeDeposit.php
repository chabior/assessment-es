<?php

namespace App\Model\Command;


use App\Model\ValueObject\Money;

class MakeDeposit
{
    /**
     * @var string
     */
    private $playerId;

    /**
     * @var Money
     */
    private $deposit;

    /**
     *
     * @param string $playerId
     * @param Money $deposit
     */
    public function __construct(string $playerId, Money $deposit)
    {
        $this->playerId = $playerId;
        $this->deposit = $deposit;
    }

    /**
     * @return string
     */
    public function getPlayerId(): string
    {
        return $this->playerId;
    }

    /**
     * @return Money
     */
    public function getDeposit(): Money
    {
        return $this->deposit;
    }
}
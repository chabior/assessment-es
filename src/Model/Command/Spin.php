<?php

namespace App\Model\Command;


use App\Model\ValueObject\Money;

class Spin
{
    /**
     * @var string
     */
    private $playerId;

    /**
     * @var Money
     */
    private $bet;

    /**
     * @var Money|null
     */
    private $reward;

    /**
     *
     * @param string $playerId
     * @param Money $bet
     * @param Money $reward
     */
    public function __construct(string $playerId, Money $bet, ?Money $reward)
    {
        if ($bet->isLessOrEqualZero()) {
            throw new \InvalidArgumentException('Bet should be greater than 1!');
        }

        $this->playerId = $playerId;
        $this->bet = $bet;
        $this->reward = $reward;
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
    public function getBet(): Money
    {
        return $this->bet;
    }

    /**
     * @return Money|null
     */
    public function getReward(): ?Money
    {
        return $this->reward;
    }

}
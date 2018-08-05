<?php

namespace App\Model\Entity;


use App\Model\ValueObject\Money;

class BonusWallet extends Wallet
{
    /**
     * @var Bonus[]
     */
    private $bonus;

    public function __construct(Bonus $bonus)
    {
        parent::__construct($bonus->calculate(null));

        $this->bonus = $bonus;
    }

    public function isWagered(Money $money)
    {
        return $this->bonus->isWagered($this->current->add($money));
    }

    public function getWageredMoney(Money $amount):?Money
    {
        if ($this->bonus->isWagered($amount)) {
            return $this->bonus->subtractWagering($this->current->add($amount));
        }

        return null;
    }
}
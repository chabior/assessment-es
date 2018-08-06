<?php

namespace App\Model\Entity;


use App\Model\ValueObject\Money;
use Ramsey\Uuid\Uuid;

class BonusWallet extends Wallet
{

    /**
     * @var string
     */
    private $id;

    /**
     * @var Bonus[]
     */
    private $bonus;

    public function __construct(Bonus $bonus)
    {
        parent::__construct($bonus->calculate(null));

        $this->bonus = $bonus;
        $this->id = Uuid::uuid4()->toString();
    }

    public function isWagered(Money $money)
    {
        return $this->bonus->isWagered($this->current->add($money));
    }

    public function getWageredMoney(Money $amount):Money
    {
        if ($this->bonus->isWagered($this->current->add($amount))) {
            return $this->bonus->subtractWagering($this->current->add($amount));
        }

        return new Money(0);
    }

    public function getId():string
    {
        return $this->id;
    }
}
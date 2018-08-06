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

    public function isWagered():bool
    {
        return $this->bonus->isWagered($this->current);
    }

    public function getWageredMoney():Money
    {
        return $this->bonus->subtractWagering($this->current);
    }

    public function getId():string
    {
        return $this->id;
    }
}
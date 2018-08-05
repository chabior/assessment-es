<?php

namespace App\Model\Entity;


use App\Model\ValueObject\BonusReward;
use App\Model\ValueObject\Money;

class Bonus
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var BonusReward
     */
    protected $reward;

    /**
     * @var int
     */
    private $wageringMultiplier;

    public function __construct(int $id, string $name, BonusReward $reward, int $wageringMultiplier)
    {
        $this->id = $id;
        $this->name = $name;
        $this->reward = $reward;
        $this->wageringMultiplier = $wageringMultiplier;
    }

    public function getId():int
    {
        return $this->id;
    }

    public function calculate(?Money $deposit): Money
    {
        return $this->reward->calculate($deposit);
    }

    public function isWagered(Money $walletValue): bool
    {
        return $walletValue->isGreater($this->calculateWageringMoney());
    }

    public function subtractWagering(Money $walletValue): Money
    {
        return $walletValue->subtract($this->calculateWageringMoney());
    }

    private function calculateWageringMoney():Money
    {
        return $this->reward->calculate(null)->multiply($this->wageringMultiplier);
    }
}
<?php

namespace App\Model\Entity;


use App\Model\ValueObject\BonusReward;
use App\Model\ValueObject\Money;

class DepositBonus extends Bonus
{
    /**
     * @var Money
     */
    private $minDeposit;

    public function __construct(
        int $id,
        string $name,
        BonusReward $reward,
        int $wageringMultiplier,
        Money $minDeposit
    ) {
        parent::__construct($id, $name, $reward, $wageringMultiplier);

        $this->minDeposit = $minDeposit;
    }

    public function calculate(?Money $deposit): Money
    {
        return $this->reward->calculate($deposit);
    }

    public function isApplicable(Money $deposit):bool
    {
        return $deposit->isGreaterOrEqual($this->minDeposit);
    }
}
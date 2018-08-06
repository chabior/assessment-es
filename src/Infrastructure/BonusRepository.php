<?php

namespace App\Infrastructure;



use App\Model\Entity\Bonus;
use App\Model\Entity\DepositBonus;
use App\Model\ValueObject\FixedValueBonusReward;
use App\Model\ValueObject\Money;

class BonusRepository
{
    public function getDepositBonus():?DepositBonus
    {
        return new DepositBonus(
            2,
            'deposit',
            new FixedValueBonusReward(new Money(10)),
            1,
            new Money(1)
        );
    }

    public function getLoginBonus():?Bonus
    {
        return new Bonus(
            1,
            'login',
            new FixedValueBonusReward(new Money(25)),
            1
        );
    }
}
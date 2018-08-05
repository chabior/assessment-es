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
        return null;
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
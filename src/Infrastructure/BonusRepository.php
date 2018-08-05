<?php

namespace App\Infrastructure;



use App\Model\Entity\Bonus;
use App\Model\Entity\DepositBonus;

class BonusRepository
{
    public function getDepositBonus():?DepositBonus
    {
        return null;
    }

    public function getLoginBonus():?Bonus
    {
        return null;
    }
}
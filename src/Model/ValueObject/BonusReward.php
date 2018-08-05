<?php

namespace App\Model\ValueObject;


interface BonusReward
{
    public function calculate(?Money $base):Money;
}
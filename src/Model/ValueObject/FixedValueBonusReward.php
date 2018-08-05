<?php

namespace App\Model\ValueObject;


class FixedValueBonusReward implements BonusReward
{
    /**
     * @var Money
     */
    private $value;

    /**
     *
     * @param Money $value
     */
    public function __construct(Money $value)
    {
        $this->value = $value;
    }

    public function calculate(?Money $base): Money
    {
        return $this->value;
    }
}
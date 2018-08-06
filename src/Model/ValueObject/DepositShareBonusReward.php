<?php

namespace App\Model\ValueObject;


use App\Exception\ModelException;

class DepositShareBonusReward implements BonusReward
{
    /**
     * @var float
     */
    private $share;

    /**
     *
     * @param float $share
     */
    public function __construct(float $share)
    {
        $this->share = $share;
    }

    public function calculate(?Money $base): Money
    {
        if (empty($base)) {
            throw ModelException::depositValueRequired();
        }

        return $base->multiply($this->share * 100)->divide(100);
    }
}
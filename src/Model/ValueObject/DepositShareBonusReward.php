<?php

namespace App\Model\ValueObject;


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
            throw new \InvalidArgumentException('Deposit value is required to calculate reward!');
        }

        return $base->multiply($this->share * 100)->divide(100);
    }
}
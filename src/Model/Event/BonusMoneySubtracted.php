<?php

namespace App\Model\Event;


use App\Model\Entity\BonusWalletCollection;
use App\Model\ValueObject\Money;

class BonusMoneySubtracted
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var Money
     */
    private $value;

    /**
     * @var BonusWalletCollection
     */
    private $wallet;

    /**
     *
     * @param string $id
     * @param Money $value
     * @param BonusWalletCollection $wallet
     */
    public function __construct(string $id, Money $value, BonusWalletCollection $wallet)
    {
        $this->id = $id;
        $this->value = $value;
        $this->wallet = $wallet;
    }

    /**
     * @return Money
     */
    public function getValue(): Money
    {
        return $this->value;
    }

    /**
     * @return BonusWalletCollection
     */
    public function getWallet(): BonusWalletCollection
    {
        return $this->wallet;
    }
}
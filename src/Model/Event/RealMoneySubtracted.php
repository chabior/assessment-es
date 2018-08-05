<?php

namespace App\Model\Event;


use App\Model\Entity\Wallet;
use App\Model\ValueObject\Money;

class RealMoneySubtracted
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
     * @var Wallet
     */
    private $wallet;

    /**
     *
     * @param string $id
     * @param Money $value
     * @param Wallet $wallet
     */
    public function __construct(string $id, Money $value, Wallet $wallet)
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
     * @return Wallet
     */
    public function getWallet(): Wallet
    {
        return $this->wallet;
    }
}
<?php

namespace App\Model\Entity;


use App\Model\ValueObject\Money;

class Wallet implements WalletInterface
{
    /**
     * @var Money
     */
    private $initial;

    /**
     * @var Money
     */
    protected $current;

    public function __construct(Money $initialValue)
    {
        $this->initial = $initialValue;
        $this->current = clone $initialValue;
    }

    public function add(Money $money):WalletInterface
    {
        $obj = clone $this;
        $obj->current = $obj->current->add($money);
        return $obj;
    }

    public function merge(Wallet $wallet):WalletInterface
    {
        return $this->add($wallet->current);
    }

    public function difference(Money $money):Money
    {
        return $money->subtract($this->current);
    }

    public function subtract(Money $money):WalletInterface
    {
        $obj = clone $this;
        $obj->current = $obj->current->subtract($money);
        return $obj;
    }

    public function valueEquals(Money $money):bool
    {
        return $this->current->isEqual($money);
    }

    public function isDepleted():bool
    {
        return $this->current->isZero();
    }

    public function getAmount():int
    {
        return $this->current->amount();
    }
}
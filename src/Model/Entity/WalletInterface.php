<?php

namespace App\Model\Entity;


use App\Model\ValueObject\Money;

interface WalletInterface
{
    public function add(Money $money):WalletInterface;

    public function difference(Money $money):Money;

    public function subtract(Money $money):WalletInterface;

    public function valueEquals(Money $money):bool;

    public function isDepleted():bool;

    public function getAmount():int;
}
<?php

namespace App\Model\Entity;


use App\Model\ValueObject\Money;

class BonusWalletCollection implements WalletInterface
{
    /**
     * @var BonusWallet[]
     */
    private $bonusWallets = [];

    public function addBonus(Bonus $bonus):BonusWalletCollection
    {
        $obj = clone $this;
        $obj->bonusWallets[] = new BonusWallet(
            $bonus
        );

        return $obj;
    }

    public function add(Money $money): WalletInterface
    {
        $obj = clone $this;

        foreach ($obj->bonusWallets as $key => $wallet) {
            if ($money->isGreaterThanZero()) {
                $obj->bonusWallets[$key] = $wallet->add($wallet->getWageredMoney());
                $money = $money->subtract($wallet->getWageredMoney());
            }
        }

        return $obj;
    }

    public function difference(Money $money): Money
    {
        foreach ($this->bonusWallets as $wallet) {
            $money = $wallet->difference($money);
            if ($money->isLessOrEqualZero()) {
                break;
            }
        }

        return $money;
    }

    public function subtract(Money $money): WalletInterface
    {
        $obj = clone $this;

        foreach ($obj->bonusWallets as $key => $wallet) {
            $moneyCopy = clone $money;
            $money = $wallet->difference($money);
            if ($money->isGreaterThanZero()) {
                $moneyCopy = new Money($wallet->getAmount());
            }
            $obj->bonusWallets[$key] = $wallet->subtract($moneyCopy);

            if ($money->isLessOrEqualZero()) {
                break;
            }
        }

        return $obj;
    }

    public function removeDepleted():BonusWalletCollection
    {
        $obj = clone $this;
        foreach ($obj->bonusWallets as $key => $wallet) {
            if ($wallet->isDepleted()) {
                unset($obj->bonusWallets[$key]);
            }
        }

        $obj->bonusWallets = array_values($obj->bonusWallets);

        return $obj;
    }

    public function valueEquals(Money $money): bool
    {
        $wallets = $this->bonusWallets;
        $sumWallet = array_shift($wallets);
        foreach ($wallets as $wallet) {
            $sumWallet = $sumWallet->merge($wallet);
        }

        return $sumWallet->valueEquals($money);
    }

    public function isDepleted(): bool
    {
        return empty($this->bonusWallets);
    }

    /**
     * Returns how much money we can add to bonus wallets
     *
     * @param Money $money
     * @return Money
     */
    public function getWageredMoney(Money $money):Money
    {
        foreach ($this->bonusWallets as $wallet) {
            $money = $money->subtract($wallet->getWageredMoney());
        }
        return $money;
    }

    public function getAmount(): int
    {
        return array_reduce($this->bonusWallets, function ($carry, BonusWallet $bonusWallet) {
            return $bonusWallet->getAmount() + $carry;
        }, 0);
    }

    public function getWallets():array
    {
        return $this->bonusWallets;
    }
}
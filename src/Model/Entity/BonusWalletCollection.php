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
        $this->assertEmpty();

        $obj = clone $this;
        $obj->bonusWallets[0] = $obj->bonusWallets[0]->add($money);
        return $obj;
    }

    public function difference(Money $money): Money
    {
        $this->assertEmpty();

        return $this->bonusWallets[0]->difference($money);
    }

    public function subtract(Money $money): WalletInterface
    {
        $this->assertEmpty();
        $obj = clone $this;

        foreach ($obj->bonusWallets as $key => $wallet) {
            $moneyCopy = clone $money;
            $money = $wallet->difference($money);
            $obj->bonusWallets[$key] = $wallet->subtract($moneyCopy);

            if ($money->isZero()) {
                break;
            }
        }

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

    public function getWageredMoney(Money $money):?Money
    {
        foreach ($this->bonusWallets as $wallet) {
            if ($wallet->isWagered($money)) {
                $money = $wallet->getWageredMoney($money);
            }

            if ($money && $money->isZero()) {
                break;
            }
        }

        return $money;
    }

    private function assertEmpty()
    {
        if ($this->isDepleted()) {
            throw new \InvalidArgumentException('Cant add money to empty wallet');
        }
    }
}
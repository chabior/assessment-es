<?php

namespace App\Model;


use App\ES\AggregateRoot;
use App\Model\Entity\Bonus;
use App\Model\Entity\BonusWalletCollection;
use App\Model\Entity\DepositBonus;
use App\Model\Entity\Wallet;
use App\Model\Event\BonusApplied;
use App\Model\Event\BonusMoneyAdded;
use App\Model\Event\BonusMoneySubtracted;
use App\Model\Event\DepositMade;
use App\Model\Event\RealMoneyAdded;
use App\Model\Event\RealMoneySubtracted;
use App\Model\Event\Registered;
use App\Model\ValueObject\Money;

class Player extends AggregateRoot
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var Wallet
     */
    private $realMoneyWallet;

    /**
     * @var BonusWalletCollection
     */
    private $bonusWallet;

    public static function create(string $id):Player
    {
        $obj = new self();
        $obj->id = $id;
        $obj->recordThat(new Registered($id));
        return $obj;
    }

    public function deposit(Money $deposit, ?DepositBonus $bonus)
    {
        $this->handleDeposit($deposit);
        $this->recordThat(new DepositMade($this->id, $deposit, $this->realMoneyWallet));

        if ($bonus) {
            $this->addBonus($bonus);
        }
    }

    public function addBonus(Bonus $bonus)
    {
        $this->handleBonus($bonus);

        $this->recordThat(new BonusApplied($this->id, $bonus->getId(), $this->bonusWallet));
    }

    public function successSpin(Money $bet, Money $reward)
    {
        $this->assertHasWallet();
        if (!$this->hasSufficientMoney($bet)) {
            throw new \InvalidArgumentException('Player has no sufficient money to place bet!');
        }

        $this->subtractBet($bet);

        //add reward to wallets
        $wageredMoney = $reward;
        if ($this->bonusWallet) {
            $wageredMoney = $this->bonusWallet->getWageredMoney($reward);

            if ($wageredMoney) {
                $reward = $reward->subtract($wageredMoney);
                $this->bonusWallet = $this->bonusWallet->add($reward);
                $this->recordThat(new BonusMoneyAdded($this->id, $reward, $this->bonusWallet));
            }
        }

        if ($wageredMoney) {
            $this->realMoneyWallet = $this->realMoneyWallet->add($wageredMoney);
            $this->recordThat(new RealMoneyAdded($this->id, $wageredMoney, $this->realMoneyWallet));
        }
    }

    public function failSpin(Money $bet)
    {
        $this->assertHasWallet();
        if (!$this->hasSufficientMoney($bet)) {
            throw new \InvalidArgumentException('Player has no sufficient money to place bet!');
        }

        $this->subtractBet($bet);
    }

    protected function apply($event): void
    {
        switch (get_class($event)) {
            case Registered::class:
                $this->id = $event->getId();
                break;
            default:
                throw new \InvalidArgumentException(
                    sprintf('Event %s is not handled!', get_class($event))
                );

        }
    }

    private function handleDeposit(Money $deposit)
    {
        if (empty($this->realMoneyWallet)) {
            $this->realMoneyWallet = new Wallet(
                $deposit
            );
        } else {
            $this->realMoneyWallet = $this->realMoneyWallet->add($deposit);
        }
    }

    private function handleBonus(Bonus $bonus)
    {
        if (empty($this->bonusWallet)) {
            $this->bonusWallet = new BonusWalletCollection();
        }

        $this->bonusWallet = $this->bonusWallet->addBonus($bonus);
    }

    private function handleRealMoneySubtracted(Money $value)
    {
        $this->realMoneyWallet = $this->realMoneyWallet->subtract($value);
    }

    private function assertHasWallet()
    {
        if (!$this->realMoneyWallet && !$this->bonusWallet) {
            throw new \InvalidArgumentException('Can spin without money!');
        }

        if ($this->realMoneyWallet->isDepleted() && $this->bonusWallet->isDepleted()) {
            throw new \InvalidArgumentException('Can spin without money!');
        }
    }

    private function hasSufficientMoney(Money $difference):bool
    {
        $difference = $this->realMoneyWallet->difference($difference);
        if ($this->bonusWallet) {
            $difference = $this->bonusWallet->difference($difference);
        }
        return $difference->isLessOrEqualZero();
    }

    private function subtractBet(Money $bet)
    {
        $difference = $this
            ->realMoneyWallet
            ->difference(
                $bet
            )
        ;

        if ($difference->isGreaterThanZero()) {
            $bet = $bet->subtract($difference);
        }

        if (!$this->realMoneyWallet->isDepleted()) {
            $this->handleRealMoneySubtracted($bet);
            $this->recordThat(new RealMoneySubtracted($this->id, $bet, $this->realMoneyWallet));
        }

        if ($difference->isGreaterThanZero()) {
            $this->bonusWallet = $this->bonusWallet->subtract($difference);
            $this->recordThat(new BonusMoneySubtracted($this->id, $difference, $this->bonusWallet));
        }
    }
}
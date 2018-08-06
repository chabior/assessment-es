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
        $obj->realMoneyWallet = new Wallet(new Money(0));
        $obj->bonusWallet = new BonusWalletCollection();
        $obj->recordThat(new Registered($id));
        return $obj;
    }

    public function getId():string
    {
        return $this->id;
    }

    public function deposit(Money $deposit, ?DepositBonus $bonus)
    {
        if ($deposit->isLessOrEqualZero()) {
            throw new \InvalidArgumentException('Deposit should be greater than 0');
        }
        
        $this->handleDeposit($deposit);
        $this->recordThat(new DepositMade($this->id, $deposit, $this->realMoneyWallet));

        if ($bonus) {
            $this->addBonus($bonus, $deposit);
        }
    }

    public function addBonus(Bonus $bonus, Money $deposit = null)
    {
        $this->handleBonus($bonus);

        $this->recordThat(new BonusApplied($this->id, $bonus->getId(), $this->bonusWallet, $bonus->calculate($deposit)));
    }

    public function spin(Money $bet, Money $reward = null)
    {
        if ($bet->isLessOrEqualZero()) {
            throw new \InvalidArgumentException('Bet must be greater than 0');
        }

        if ($reward && $reward->isLessOrEqualZero()) {
            throw new \InvalidArgumentException('Reward must be greater than 0');
        }

        $this->assertHasWallet();
        if (!$this->hasSufficientMoney($bet)) {
            throw new \InvalidArgumentException('Player has no sufficient money to place bet!');
        }

        $this->subtractBet($bet);

        if ($reward) {
            $this->assignReward($reward);
        }

        $this->bonusWallet = $this->bonusWallet->removeDepleted();
    }

    protected function apply($event): void
    {
        switch (get_class($event)) {
            case Registered::class:
                $this->id = $event->getId();
                $this->realMoneyWallet = new Wallet(new Money(0));
                $this->bonusWallet = new BonusWalletCollection();
                break;
            case DepositMade::class:
                $this->realMoneyWallet = $event->getWallet();
                break;
            case RealMoneySubtracted::class:
            case RealMoneyAdded::class:
                $this->realMoneyWallet = $event->getWallet();
                break;
            case BonusMoneySubtracted::class:
            case BonusMoneyAdded::class:
                $this->bonusWallet = $event->getWallet();
                $this->bonusWallet = $this->bonusWallet->removeDepleted();
                break;
            case BonusApplied::class:
                $this->bonusWallet = $event->getBonusWallet();
                break;
            default:
                throw new \InvalidArgumentException(
                    sprintf('Event %s is not handled!', get_class($event))
                );
        }
    }

    private function handleDeposit(Money $deposit)
    {
        $this->realMoneyWallet = $this->realMoneyWallet->add($deposit);
    }

    private function handleBonus(Bonus $bonus)
    {
        $this->bonusWallet = $this->bonusWallet->addBonus($bonus);
    }

    private function handleRealMoneySubtracted(Money $value)
    {
        $this->realMoneyWallet = $this->realMoneyWallet->subtract($value);
    }

    private function assertHasWallet()
    {
        if ($this->realMoneyWallet->isDepleted() && $this->bonusWallet->isDepleted()) {
            throw new \InvalidArgumentException('Can spin without money!');
        }
    }

    private function hasSufficientMoney(Money $difference):bool
    {
        $difference = $this->realMoneyWallet->difference($difference);
        if (!$this->bonusWallet->isDepleted()) {
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

    private function assignReward(Money $reward)
    {
        //add reward to wallets
        $wageredMoney = $reward;
        if (!$this->bonusWallet->isDepleted()) {
            $wageredMoney = $this->bonusWallet->getWageredMoney($reward);
            $reward = $reward->subtract($wageredMoney);
            if ($reward->isGreaterThanZero()) {
                $this->bonusWallet = $this->bonusWallet->add($reward);
                $this->recordThat(new BonusMoneyAdded($this->id, $reward, $this->bonusWallet));
            }
        }

        if ($wageredMoney->isGreaterThanZero()) {
            $this->realMoneyWallet = $this->realMoneyWallet->add($wageredMoney);
            $this->recordThat(new RealMoneyAdded($this->id, $wageredMoney, $this->realMoneyWallet));
        }
    }
}
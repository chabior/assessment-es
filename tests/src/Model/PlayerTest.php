<?php

namespace App\Tests\Model;


use App\Model\Entity\Bonus;
use App\Model\Entity\DepositBonus;
use App\Model\Event\BonusApplied;
use App\Model\Event\BonusMoneyAdded;
use App\Model\Event\BonusMoneySubtracted;
use App\Model\Event\DepositMade;
use App\Model\Event\RealMoneyAdded;
use App\Model\Event\RealMoneySubtracted;
use App\Model\Player;
use App\Model\ValueObject\FixedValueBonusReward;
use App\Model\ValueObject\Money;
use App\Tests\TestCase;

class PlayerTest extends TestCase
{
    public function testDeposit()
    {
        $deposit = new Money(100);
        $player = Player::create('1');
        $player->deposit($deposit, null);

        $events = $this->popRecordedEvents($player);

        $this->assertCount(2, $events);

        $this->assertDepositMade($events[1], new Money(100), new Money(100));
    }

    public function testDepositWithBonus()
    {
        $player = Player::create('1');
        $deposit = new Money(100);
        $bonus = $this->getDepositBonus(new Money(10));
        $player->deposit($deposit, $bonus);

        $events = $this->popRecordedEvents($player);

        $this->assertCount(3, $events);

        $this->assertBonusApplied($events[2], new Money(10), new Money(10));
    }

    public function testMultipleDeposits()
    {
        $player = Player::create('1');
        $player->deposit(new Money(100), null);
        $player->deposit(new Money(20), null);

        $events = $this->popRecordedEvents($player);

        $this->assertCount(3, $events);

        $this->assertDepositMade($events[1], new Money(100), new Money(100));
        $this->assertDepositMade($events[2], new Money(20), new Money(120));
    }

    public function testMultipleDepositsWithBonus()
    {
        $deposit = new Money(100);
        $player = Player::create('1');
        $bonus = $this->getDepositBonus(new Money(10));
        $player->deposit($deposit, $bonus);
        $player->deposit($deposit, $bonus);

        $events = $this->popRecordedEvents($player);

        $this->assertCount(5, $events);

        $this->assertBonusApplied($events[2], new Money(10), new Money(10));

        $this->assertBonusApplied($events[4], new Money(10), new Money(20));
    }

    public function testSuccessSpinWithoutBonuses()
    {
        $player = Player::create('1');
        $player->deposit(new Money(100), null);

        $player->spin(new Money(10), new Money(20));

        $events = $this->popRecordedEvents($player);
        $this->assertCount(4, $events);

        $this->assertRealMoneySubtracted($events[2], new Money(10), new Money(90));

        $this->assertRealMoneyAdded($events[3], new Money(20), new Money(110));
    }

    public function testMultipleSuccessSpinWithoutBonuses()
    {
        $player = Player::create('1');
        $deposit = new Money(100);
        $player->deposit($deposit, null);

        $reward = new Money(20);
        $bet = new Money(10);
        $player->spin($bet, $reward);
        $player->spin($bet, $reward);

        $events = $this->popRecordedEvents($player);
        $this->assertCount(6, $events);

        $this->assertRealMoneyAdded($events[5], new Money(20), new Money(120));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSuccessSpinWithoutDeposit()
    {
        $player = Player::create('1');
        $reward = new Money(10);
        $player->spin(new Money(10), $reward);
    }

    public function testFailSpinWithoutBonuses()
    {
        $player = Player::create('1');
        $deposit = new Money(100);
        $player->deposit($deposit, null);

        $bet = new Money(10);
        $player->spin($bet);
        $events = $this->popRecordedEvents($player);

        $this->assertCount(3, $events);

        $this->assertRealMoneySubtracted($events[2], new Money(10), new Money(90));
    }

    public function testMultipleFailSpinWithoutBonuses()
    {
        $player = Player::create('1');
        $deposit = new Money(100);
        $player->deposit($deposit, null);

        $bet = new Money(10);
        $player->spin($bet);
        $player->spin($bet);
        $events = $this->popRecordedEvents($player);

        $this->assertCount(4, $events);

        $this->assertRealMoneySubtracted($events[3], new Money(10), new Money(80));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFailSpinWithInsufficientFounds()
    {
        $player = Player::create('1');
        $deposit = new Money(5);
        $player->deposit($deposit, null);

        $bet = new Money(10);
        $player->spin($bet);
    }

    public function testSuccessSpinWithBonuses()
    {
        $deposit = new Money(100);
        $player = Player::create('1');
        $bonus = $this->getDepositBonus(new Money(10));
        $player->deposit($deposit, $bonus);
        $player->spin(new Money(10), new Money(15));

        $events = $this->popRecordedEvents($player);

        $this->assertCount(5, $events);

        $this->assertRealMoneyAdded($events[4], new Money(15), new Money(105));
    }

    public function testFromAssessment()
    {
        $deposit = new Money(100);
        $player = Player::create('1');
        $loginBonus = $this->getLoginBonus(new Money(10));
        $player->deposit($deposit, null);
        $player->addBonus($loginBonus);

        $player->spin(new Money(105));

        $player->spin(new Money(5), new Money(20));

        $player->spin(new Money(20), new Money(30));

        $events = $this->popRecordedEvents($player);
        $this->assertCount(12, $events);

        //fail spin start
        $realMoneySubtracted = $events[3];
        $this->assertRealMoneySubtracted($events[3], new Money(100), new Money(0));

        $this->assertBonusMoneySubtracted($events[4], new Money(5), new Money(5));
        //fail spin end

        //first success spin start
        $this->assertBonusMoneySubtracted($events[5], new Money(5), new Money(0));

        $this->assertBonusMoneyAdded($events[6], new Money(10), new Money(10));

        $this->assertRealMoneyAdded($events[7], new Money(10), new Money(10));
        //first success spin end

        //second spin start
        $this->assertRealMoneySubtracted($events[8], new Money(10), new Money(0));

        $this->assertBonusMoneySubtracted($events[9], new Money(10), new Money(0));

        $this->assertBonusMoneyAdded($events[10], new Money(10), new Money(10));

        $this->assertRealMoneyAdded($events[11], new Money(20), new Money(20));
    }

    public function testFailSpinWithBonuses()
    {
        $deposit = new Money(100);
        $player = Player::create('1');
        $loginBonus = $this->getLoginBonus(new Money(10));
        $player->deposit($deposit, null);
        $player->addBonus($loginBonus);

        $player->spin(new Money(50));

        $player->spin(new Money(60));

        $events = $this->popRecordedEvents($player);
        $this->assertCount(6, $events);

        $this->assertRealMoneySubtracted($events[3], new Money(50), new Money(50));

        $this->assertRealMoneySubtracted($events[4], new Money(50), new Money(0));

        $this->assertBonusMoneySubtracted($events[5], new Money(10), new Money(0));
    }

    public function testBonus()
    {
        $deposit = new Money(25);
        $player = Player::create('1');
        $player->deposit($deposit, null);
        $loginBonus = $this->getLoginBonus($deposit);
        $player->addBonus($loginBonus);

        $player->spin(new Money(15), new Money(25));

        $events = $this->popRecordedEvents($player);

        $this->assertRealMoneySubtracted($events[3], new Money(15), new Money(10));

        $this->assertRealMoneyAdded($events[4], new Money(25), new Money(35));
    }

    public function testNextBonusAfterDepleted()
    {
        $deposit = new Money(25);
        $player = Player::create('1');
        $player->deposit($deposit, null);
        $loginBonus = $this->getLoginBonus($deposit);
        $player->addBonus($loginBonus);

        $player->spin($deposit);
        $player->spin($deposit);

        $player->addBonus($loginBonus);

        $events = $this->popRecordedEvents($player);

        $bonusAppliedEvent = $events[5];
        /**
         * @var BonusApplied $bonusAppliedEvent
         */
        $this->assertSame(BonusApplied::class, get_class($bonusAppliedEvent));
        $this->assertTrue($bonusAppliedEvent->getBonusWallet()->valueEquals(new Money(25)));
        $this->assertBonusApplied($events[5], new Money(25), new Money(25));
    }

    public function testMultipleLoginBonusAndSuccessSpin()
    {
        $deposit = new Money(25);
        $player = Player::create('1');
        $player->deposit($deposit, null);
        $loginBonus = $this->getLoginBonus($deposit);
        $player->addBonus($loginBonus);
        $player->addBonus($loginBonus);

        $player->spin(new Money(15), new Money(25));

        $events = $this->popRecordedEvents($player);
        $this->assertCount(6, $events);

        $this->assertBonusApplied($events[3], new Money(25), new Money(50));

        $this->assertRealMoneyAdded($events[5], new Money(25), new Money(35));
    }

    public function testFailSpinWithMultipleBonusesAndEmptyRealMoney()
    {
        $deposit = new Money(25);
        $player = Player::create('1');
        $player->deposit($deposit, null);
        $loginBonus = $this->getLoginBonus($deposit);
        $player->addBonus($loginBonus);
        $player->addBonus($loginBonus);

        $player->spin(new Money(25));
        $player->spin(new Money(10));

        $events = $this->popRecordedEvents($player);
        $this->assertCount(6, $events);

        $this->assertBonusApplied($events[3], new Money(25), new Money(50));

        $this->assertRealMoneySubtracted($events[4], new Money(25), new Money(0));

        $this->assertBonusMoneySubtracted($events[5], new Money(10), new Money(40));
    }

    public function testSubtractFromBonusWallets()
    {
        $player = Player::create('1');
        $loginBonus = $this->getLoginBonus(new Money(25));
        $player->addBonus($loginBonus);
        $player->addBonus($loginBonus);

        $player->spin(new Money(12));
        $player->spin(new Money(17));

        $events = $this->popRecordedEvents($player);
        $this->assertCount(5, $events);

        $this->assertBonusMoneySubtracted($events[3], new Money(12), new Money(38));

        $this->assertBonusMoneySubtracted($events[4], new Money(17), new Money(21));
    }

    public function testWageringMultiplier()
    {
        $player = Player::create('1');
        $player->addBonus($this->getLoginBonus(new Money(25), 2));

        $player->spin(new Money(10), new Money(50));

        $events = $this->popRecordedEvents($player);

        $this->assertCount(5, $events);
        $this->assertBonusMoneyAdded($events[3], new Money(35), new Money(50));

        $this->assertRealMoneyAdded($events[4], new Money(15), new Money(15));
    }

    public function testWageringWithMultipleBonusWallets()
    {
        $player = Player::create('1');
        $loginBonus = $this->getLoginBonus(new Money(25), 2);
        $player->addBonus($loginBonus);
        $loginBonus = $this->getLoginBonus(new Money(30), 2);
        $player->addBonus($loginBonus);

        $player->spin(new Money(20));
        $player->spin(new Money(20), new Money(100));
        $events = $this->popRecordedEvents($player);
        $this->assertCount(7, $events);

        $this->assertBonusMoneySubtracted($events[3], new Money(20), new Money(35));

        $this->assertBonusMoneySubtracted($events[4], new Money(20), new Money(15));

        $this->assertBonusMoneyAdded($events[5], new Money(95), new Money(110));

        $this->assertRealMoneyAdded($events[6], new Money(5), new Money(5));
    }

    /**
     * @param RealMoneySubtracted $realMoneySubtracted
     * @param Money $value
     * @param Money $wallet
     */
    private function assertRealMoneySubtracted($realMoneySubtracted, Money $value, Money $wallet)
    {
        $this->assertSame(RealMoneySubtracted::class, get_class($realMoneySubtracted));
        $this->assertTrue($realMoneySubtracted->getValue()->isEqual($value));
        $this->assertTrue($realMoneySubtracted->getWallet()->valueEquals($wallet));
    }

    /**
     * @param BonusMoneySubtracted $bonusMoneySubtracted
     * @param Money $value
     * @param Money $wallet
     */
    private function assertBonusMoneySubtracted($bonusMoneySubtracted, Money $value, Money $wallet)
    {
        $this->assertSame(BonusMoneySubtracted::class, get_class($bonusMoneySubtracted));
        $this->assertTrue($bonusMoneySubtracted->getValue()->isEqual($value));
        $this->assertTrue($bonusMoneySubtracted->getWallet()->valueEquals($wallet));
    }

    /**
     * @param BonusMoneyAdded $bonusMoneyAdded
     * @param Money $value
     * @param Money $wallet
     */
    private function assertBonusMoneyAdded($bonusMoneyAdded, Money $value, Money $wallet)
    {
        $this->assertSame(BonusMoneyAdded::class, get_class($bonusMoneyAdded));
        $this->assertTrue($bonusMoneyAdded->getValue()->isEqual($value));
        $this->assertTrue($bonusMoneyAdded->getWallet()->valueEquals($wallet));
    }

    /**
     * @param RealMoneyAdded $realMoneyAdded
     * @param Money $value
     * @param Money $wallet
     */
    private function assertRealMoneyAdded($realMoneyAdded, Money $value, Money $wallet)
    {
        $this->assertSame(RealMoneyAdded::class, get_class($realMoneyAdded));
        $this->assertTrue($realMoneyAdded->getValue()->isEqual($value));
        $this->assertTrue($realMoneyAdded->getWallet()->valueEquals($wallet));
    }

    /**
     * @param BonusApplied $bonusApplied
     * @param Money $value
     * @param Money $wallet
     */
    private function assertBonusApplied($bonusApplied, Money $value, Money $wallet)
    {
        $this->assertSame(BonusApplied::class, get_class($bonusApplied));
        $this->assertTrue($bonusApplied->getValue()->isEqual($value));
        $this->assertTrue($bonusApplied->getBonusWallet()->valueEquals($wallet));
    }

    /**
     * @param DepositMade $event
     * @param Money $value
     * @param Money $wallet
     */
    private function assertDepositMade($event, Money $value, Money $wallet)
    {
        $this->assertSame(DepositMade::class, get_class($event));
        $this->assertTrue($event->getValue()->isEqual($value));
        $this->assertTrue($event->getWallet()->valueEquals($wallet));
    }

    private function getLoginBonus(Money $bonus, int $wageringMultiplier = 1):Bonus
    {
        return new Bonus(
            1,
            'login-bonus',
            new FixedValueBonusReward($bonus),
            $wageringMultiplier
        );
    }

    private function getDepositBonus(Money $bonusValue, int $wageringMultiplier = 1):DepositBonus
    {
        return new DepositBonus(
            1,
            'deposit',
            new FixedValueBonusReward($bonusValue),
            $wageringMultiplier,
            new Money(50)
        );
    }
}
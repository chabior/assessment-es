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

        $event = $events[1];
        /**
         * @var DepositMade $event
         */

        $this->assertSame(DepositMade::class, get_class($event));
        $this->assertSame($deposit, $event->getValue());
        $this->assertTrue($event->getWallet()->valueEquals($deposit));
    }

    public function testDepositWithBonus()
    {
        $player = Player::create('1');
        $deposit = new Money(100);
        $bonusValue = new Money(10);
        $bonus = new DepositBonus(
            1,
            'deposit',
            new FixedValueBonusReward($bonusValue),
            25,
            new Money(50)
        );
        $player->deposit($deposit, $bonus);

        $events = $this->popRecordedEvents($player);

        $this->assertCount(3, $events);

        $bonusAppliedEvent = $events[2];
        /**
         * @var BonusApplied $bonusAppliedEvent
         */

        $this->assertSame(BonusApplied::class, get_class($bonusAppliedEvent));
        $this->assertSame($bonus->getId(), $bonusAppliedEvent->getBonusId());
        $this->assertTrue($bonusAppliedEvent->getBonusWallet()->valueEquals($bonusValue));
    }

    public function testMultipleDeposits()
    {
        $player = Player::create('1');
        $depositFirst = new Money(100);
        $player->deposit($depositFirst, null);
        $depositSecond = new Money(20);
        $player->deposit($depositSecond, null);

        $events = $this->popRecordedEvents($player);

        $this->assertCount(3, $events);

        $event = $events[1];
        /**
         * @var DepositMade $event
         */

        $this->assertSame(DepositMade::class, get_class($event));
        $this->assertSame($depositFirst, $event->getValue());
        $this->assertTrue($event->getWallet()->valueEquals($depositFirst));

        $this->assertSame($depositSecond, $events[2]->getValue());
        $this->assertTrue($events[2]->getWallet()->valueEquals($depositFirst->add($depositSecond)));
    }

    public function testMultipleDepositsWithBonus()
    {
        $deposit = new Money(100);
        $player = Player::create('1');
        $bonusValue = new Money(10);
        $bonus = new DepositBonus(
            1,
            'deposit',
            new FixedValueBonusReward($bonusValue),
            25,
            new Money(50)
        );
        $player->deposit($deposit, $bonus);
        $player->deposit($deposit, $bonus);

        $events = $this->popRecordedEvents($player);

        $this->assertCount(5, $events);

        $bonusAppliedEvent = $events[2];
        /**
         * @var BonusApplied $bonusAppliedEvent
         */

        $this->assertSame(BonusApplied::class, get_class($bonusAppliedEvent));
        $this->assertSame($bonus->getId(), $bonusAppliedEvent->getBonusId());
        $this->assertTrue($bonusAppliedEvent->getBonusWallet()->valueEquals($bonusValue));

        $bonusAppliedEvent = $events[4];
        /**
         * @var BonusApplied $bonusAppliedEvent
         */

        $this->assertSame(BonusApplied::class, get_class($bonusAppliedEvent));
        $this->assertSame($bonus->getId(), $bonusAppliedEvent->getBonusId());
        $this->assertTrue($bonusAppliedEvent->getBonusWallet()->valueEquals(new Money(20)));
    }

    public function testSuccessSpinWithoutBonuses()
    {
        $player = Player::create('1');
        $deposit = new Money(100);
        $player->deposit($deposit, null);

        $reward = new Money(20);
        $bet = new Money(10);
        $player->successSpin($bet, $reward);

        $events = $this->popRecordedEvents($player);
        $this->assertCount(4, $events);

        $event = $events[2];
        /**
         * @var RealMoneySubtracted $event
         */
        $this->assertSame(RealMoneySubtracted::class, get_class($event));
        $this->assertTrue($event->getValue()->isEqual($bet));
        $this->assertTrue($event->getWallet()->valueEquals(new Money(90)));

        $event = $events[3];
        /**
         * @var RealMoneyAdded $event
         */
        $this->assertSame(RealMoneyAdded::class, get_class($event));
        $this->assertTrue($event->getValue()->isEqual($reward));
        $this->assertTrue($event->getWallet()->valueEquals(new Money(110)));
    }

    public function testMultipleSuccessSpinWithoutBonuses()
    {
        $player = Player::create('1');
        $deposit = new Money(100);
        $player->deposit($deposit, null);

        $reward = new Money(20);
        $bet = new Money(10);
        $player->successSpin($bet, $reward);
        $player->successSpin($bet, $reward);

        $events = $this->popRecordedEvents($player);
        $this->assertCount(6, $events);

        $event = $events[5];
        /**
         * @var RealMoneyAdded $event
         */

        $this->assertSame(RealMoneyAdded::class, get_class($event));
        $this->assertTrue($event->getValue()->isEqual($reward));
        $this->assertTrue($event->getWallet()->valueEquals(new Money(120)));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSuccessSpinWithoutDeposit()
    {
        $player = Player::create('1');
        $reward = new Money(10);
        $player->successSpin(new Money(10), $reward);
    }

    public function testFailSpinWithoutBonuses()
    {
        $player = Player::create('1');
        $deposit = new Money(100);
        $player->deposit($deposit, null);

        $bet = new Money(10);
        $player->failSpin($bet);
        $events = $this->popRecordedEvents($player);

        $this->assertCount(3, $events);
        $event = $events[2];
        /**
         * @var RealMoneySubtracted $event
         */

        $this->assertSame(RealMoneySubtracted::class, get_class($event));
        $this->assertTrue($event->getWallet()->valueEquals($deposit->subtract($bet)));
    }

    public function testMultipleFailSpinWithoutBonuses()
    {
        $player = Player::create('1');
        $deposit = new Money(100);
        $player->deposit($deposit, null);

        $bet = new Money(10);
        $player->failSpin($bet);
        $player->failSpin($bet);
        $events = $this->popRecordedEvents($player);

        $this->assertCount(4, $events);
        $event = $events[3];
        /**
         * @var RealMoneySubtracted $event
         */

        $this->assertSame(RealMoneySubtracted::class, get_class($event));
        $this->assertTrue($event->getWallet()->valueEquals($deposit->subtract($bet)->subtract($bet)));
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
        $player->failSpin($bet);
    }

    public function testSuccessSpinWithBonuses()
    {
        $deposit = new Money(100);
        $player = Player::create('1');
        $bonusValue = new Money(10);
        $bonus = new DepositBonus(
            1,
            'deposit',
            new FixedValueBonusReward($bonusValue),
            1,
            new Money(50)
        );
        $player->deposit($deposit, $bonus);

        $reward = new Money(15);
        $bet = new Money(10);
        $player->successSpin($bet, $reward);

        $events = $this->popRecordedEvents($player);

        $this->assertCount(6, $events);

        $realMoneyAdded = $events[5];
        /**
         * @var RealMoneyAdded $realMoneyAdded
         */
        $this->assertSame(RealMoneyAdded::class, get_class($realMoneyAdded));
        $this->assertTrue($realMoneyAdded->getWallet()->valueEquals(new Money(105)));
    }

    public function testFromAssessment()
    {
        $deposit = new Money(100);
        $player = Player::create('1');
        $loginBonusValue = new Money(10);
        $loginBonus = new Bonus(
            1,
            'login-bonus',
            new FixedValueBonusReward($loginBonusValue),
            1
        );
        $player->deposit($deposit, null);
        $player->addBonus($loginBonus);

        $bet = new Money(105);
        $player->failSpin($bet);

        $secondBet = new Money(5);
        $reward = new Money(20);
        $player->successSpin($secondBet, $reward);

        $thirdBet = new Money(20);
        $secondReward = new Money(30);
        $player->successSpin($thirdBet, $secondReward);

        $events = $this->popRecordedEvents($player);
        $this->assertCount(12, $events);

        //fail spin start
        $realMoneySubtracted = $events[3];
        /**
         * @var RealMoneySubtracted $realMoneySubtracted
         */
        $this->assertSame(RealMoneySubtracted::class, get_class($realMoneySubtracted));
        $this->assertTrue($realMoneySubtracted->getValue()->isEqual($deposit));
        $this->assertTrue($realMoneySubtracted->getWallet()->valueEquals(new Money(0)));

        $bonusMoneySubtracted = $events[4];
        /**
         * @var BonusMoneySubtracted $bonusMoneySubtracted
         */
        $this->assertSame(BonusMoneySubtracted::class, get_class($bonusMoneySubtracted));
        $this->assertTrue($bonusMoneySubtracted->getValue()->isEqual(new Money(5)));
        $this->assertTrue($bonusMoneySubtracted->getWallet()->valueEquals(new Money(5)));
        //fail spin end

        //first success spin start
        $bonusMoneySubtracted = $events[5];
        $this->assertSame(BonusMoneySubtracted::class, get_class($bonusMoneySubtracted));
        $this->assertTrue($bonusMoneySubtracted->getValue()->isEqual(new Money(5)));
        $this->assertTrue($bonusMoneySubtracted->getWallet()->valueEquals(new Money(0)));

        $bonusMoneyAdded = $events[6];
        /**
         * @var BonusMoneyAdded $bonusMoneyAdded
         */
        $this->assertSame(BonusMoneyAdded::class, get_class($bonusMoneyAdded));
        $this->assertTrue($bonusMoneyAdded->getValue()->isEqual(new Money(10)));
        $this->assertTrue($bonusMoneyAdded->getWallet()->valueEquals(new Money(10)));

        $realMoneyAdded = $events[7];
        /**
         * @var RealMoneyAdded $realMoneyAdded
         */
        $this->assertSame(RealMoneyAdded::class, get_class($realMoneyAdded));
        $this->assertTrue($realMoneyAdded->getValue()->isEqual(new Money(10)));
        $this->assertTrue($realMoneyAdded->getWallet()->valueEquals(new Money(10)));
        //first success spin end

        //second spin start
        $realMoneySubtracted = $events[8];
        $this->assertSame(RealMoneySubtracted::class, get_class($realMoneySubtracted));
        $this->assertTrue($realMoneySubtracted->getValue()->isEqual(new Money(10)));
        $this->assertTrue($realMoneySubtracted->getWallet()->valueEquals(new Money(0)));

        $bonusMoneySubtracted = $events[9];
        $this->assertSame(BonusMoneySubtracted::class, get_class($bonusMoneySubtracted));
        $this->assertTrue($bonusMoneySubtracted->getValue()->isEqual(new Money(10)));
        $this->assertTrue($bonusMoneySubtracted->getWallet()->valueEquals(new Money(0)));

        $bonusMoneyAdded = $events[10];
        $this->assertSame(BonusMoneyAdded::class, get_class($bonusMoneyAdded));
        $this->assertTrue($bonusMoneyAdded->getValue()->isEqual(new Money(10)));
        $this->assertTrue($bonusMoneyAdded->getWallet()->valueEquals(new Money(10)));

        $realMoneyAdded = $events[11];
        $this->assertSame(RealMoneyAdded::class, get_class($realMoneyAdded));
        $this->assertTrue($realMoneyAdded->getValue()->isEqual(new Money(20)));
        $this->assertTrue($realMoneyAdded->getWallet()->valueEquals(new Money(20)));
    }

    public function testFailSpinWithBonuses()
    {
        $deposit = new Money(100);
        $player = Player::create('1');
        $loginBonusValue = new Money(10);
        $loginBonus = new Bonus(
            1,
            'login-bonus',
            new FixedValueBonusReward($loginBonusValue),
            1
        );
        $player->deposit($deposit, null);
        $player->addBonus($loginBonus);

        $bet = new Money(50);
        $player->failSpin($bet);

        $bet = new Money(60);
        $player->failSpin($bet);

        $events = $this->popRecordedEvents($player);
        $this->assertCount(6, $events);

        $realMoneySubtracted = $events[3];
        /**
         * @var RealMoneySubtracted $realMoneySubtracted
         */
        $this->assertSame(RealMoneySubtracted::class, get_class($realMoneySubtracted));
        $this->assertTrue($realMoneySubtracted->getValue()->isEqual(new Money(50)));
        $this->assertTrue($realMoneySubtracted->getWallet()->valueEquals(new Money(50)));

        $realMoneySubtracted = $events[4];
        $this->assertSame(RealMoneySubtracted::class, get_class($realMoneySubtracted));
        $this->assertTrue($realMoneySubtracted->getValue()->isEqual(new Money(50)));
        $this->assertTrue($realMoneySubtracted->getWallet()->valueEquals(new Money(0)));

        $bonusMoneySubtracted = $events[5];
        /**
         * @var BonusMoneySubtracted $bonusMoneySubtracted
         */
        $this->assertSame(BonusMoneySubtracted::class, get_class($bonusMoneySubtracted));
        $this->assertTrue($bonusMoneySubtracted->getWallet()->valueEquals(new Money(0)));
        $this->assertTrue($bonusMoneySubtracted->getValue()->isEqual(new Money(10)));
    }

    public function testBonus()
    {
        $deposit = new Money(25);
        $player = Player::create('1');
        $player->deposit($deposit, null);
        $loginBonus = new Bonus(
            1,
            'login-bonus',
            new FixedValueBonusReward($deposit),
            1
        );
        $player->addBonus($loginBonus);

        $player->successSpin(new Money(15), new Money(25));

        $events = $this->popRecordedEvents($player);
        $realMoneySubtracted = $events[3];
        /**
         * @var RealMoneySubtracted $realMoneySubtracted
         */
        $this->assertSame(RealMoneySubtracted::class, get_class($realMoneySubtracted));
        $this->assertTrue($realMoneySubtracted->getValue()->isEqual(new Money(15)));
        $this->assertTrue($realMoneySubtracted->getWallet()->valueEquals(new Money(10)));

        $realMoneyAdded = $events[4];
        /**
         * @var RealMoneyAdded $realMoneyAdded
         */
        $this->assertSame(RealMoneyAdded::class, get_class($realMoneyAdded));
        $this->assertTrue($realMoneyAdded->getValue()->isEqual(new Money(25)));
        $this->assertTrue($realMoneyAdded->getWallet()->valueEquals(new Money(35)));
    }

    public function testNextBonusAfterDepleted()
    {
        $deposit = new Money(25);
        $player = Player::create('1');
        $player->deposit($deposit, null);
        $loginBonus = new Bonus(
            1,
            'login-bonus',
            new FixedValueBonusReward($deposit),
            1
        );
        $player->addBonus($loginBonus);

        $player->failSpin($deposit);
        $player->failSpin($deposit);

        $player->addBonus($loginBonus);

        $events = $this->popRecordedEvents($player);

        $bonusAppliedEvent = $events[5];
        /**
         * @var BonusApplied $bonusAppliedEvent
         */
        $this->assertSame(BonusApplied::class, get_class($bonusAppliedEvent));
        $this->assertTrue($bonusAppliedEvent->getBonusWallet()->valueEquals(new Money(25)));
    }

    public function testMultipleLoginBonusAndSuccessSpin()
    {
        $deposit = new Money(25);
        $player = Player::create('1');
        $player->deposit($deposit, null);
        $loginBonus = new Bonus(
            1,
            'login-bonus',
            new FixedValueBonusReward($deposit),
            1
        );
        $player->addBonus($loginBonus);
        $player->addBonus($loginBonus);

        $player->successSpin(new Money(15), new Money(25));

        $events = $this->popRecordedEvents($player);
        $this->assertCount(6, $events);

        $bonusApplied = $events[3];
        /**
         * @var BonusApplied $bonusApplied
         */
        $this->assertSame(BonusApplied::class, get_class($bonusApplied));
        $this->assertTrue($bonusApplied->getValue()->isEqual(new Money(25)));
        $this->assertTrue($bonusApplied->getBonusWallet()->valueEquals(new Money(50)));

        $realMoneyAddedEvent = $events[5];
        /**
         * @var RealMoneyAdded $realMoneyAddedEvent
         */
        $this->assertSame(RealMoneyAdded::class, get_class($realMoneyAddedEvent));
        $this->assertTrue($realMoneyAddedEvent->getValue()->isEqual(new Money(25)));
        $this->assertTrue($realMoneyAddedEvent->getWallet()->valueEquals(new Money(35)));
    }

    public function testFailSpinWithMultipleBonusesAndEmptyRealMoney()
    {
        $deposit = new Money(25);
        $player = Player::create('1');
        $player->deposit($deposit, null);
        $loginBonus = new Bonus(
            1,
            'login-bonus',
            new FixedValueBonusReward($deposit),
            1
        );
        $player->addBonus($loginBonus);
        $player->addBonus($loginBonus);

        $player->failSpin(new Money(25));
        $player->failSpin(new Money(10));

        $events = $this->popRecordedEvents($player);
        $this->assertCount(6, $events);

        $bonusApplied = $events[3];
        /**
         * @var BonusApplied $bonusApplied
         */
        $this->assertSame(BonusApplied::class, get_class($bonusApplied));
        $this->assertTrue($bonusApplied->getValue()->isEqual(new Money(25)));
        $this->assertTrue($bonusApplied->getBonusWallet()->valueEquals(new Money(50)));

        $realMoneySubtracted = $events[4];
        /**
         * @var RealMoneySubtracted $realMoneySubtracted
         */
        $this->assertSame(RealMoneySubtracted::class, get_class($realMoneySubtracted));
        $this->assertTrue($realMoneySubtracted->getValue()->isEqual(new Money(25)));
        $this->assertTrue($realMoneySubtracted->getWallet()->valueEquals(new Money(0)));

        $bonusMoneySubtracted = $events[5];
        /**
         * @var BonusMoneySubtracted $bonusMoneySubtracted
         */
        $this->assertSame(BonusMoneySubtracted::class, get_class($bonusMoneySubtracted));
        $this->assertTrue($bonusMoneySubtracted->getValue()->isEqual(new Money(10)));
        $this->assertTrue($bonusMoneySubtracted->getWallet()->valueEquals(new Money(40)));
    }
}
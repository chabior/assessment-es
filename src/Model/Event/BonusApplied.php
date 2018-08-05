<?php

namespace App\Model\Event;


use App\Model\Entity\BonusWalletCollection;
use App\Model\ValueObject\Money;

class BonusApplied
{
    /**
     * @var string
     */
    private $playerId;

    /**
     * @var int
     */
    private $bonusId;

    /**
     * @var BonusWalletCollection
     */
    private $bonusWallet;

    /**
     * @var Money
     */
    private $value;

    public function __construct(string $playerId, int $bonusId, BonusWalletCollection $bonusWallet, Money $value)
    {
        $this->playerId = $playerId;
        $this->bonusId = $bonusId;
        $this->bonusWallet = $bonusWallet;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getPlayerId(): string
    {
        return $this->playerId;
    }

    /**
     * @return int
     */
    public function getBonusId(): int
    {
        return $this->bonusId;
    }

    /**
     * @return BonusWalletCollection
     */
    public function getBonusWallet(): BonusWalletCollection
    {
        return $this->bonusWallet;
    }

    /**
     * @return Money
     */
    public function getValue(): Money
    {
        return $this->value;
    }
}
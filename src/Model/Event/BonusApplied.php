<?php

namespace App\Model\Event;


use App\Model\Entity\BonusWalletCollection;

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

    public function __construct(string $playerId, int $bonusId, BonusWalletCollection $bonusWallet)
    {
        $this->playerId = $playerId;
        $this->bonusId = $bonusId;
        $this->bonusWallet = $bonusWallet;
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
}
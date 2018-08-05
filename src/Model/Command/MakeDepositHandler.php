<?php

namespace App\Model\Command;


use App\Infrastructure\BonusRepository;
use App\Infrastructure\PlayerRepository;

class MakeDepositHandler
{
    /**
     * @var PlayerRepository
     */
    private $playerRepository;

    /**
     * @var BonusRepository
     */
    private $bonusRepository;

    /**
     *
     * @param PlayerRepository $playerRepository
     * @param BonusRepository $bonusRepository
     */
    public function __construct(PlayerRepository $playerRepository, BonusRepository $bonusRepository)
    {
        $this->playerRepository = $playerRepository;
        $this->bonusRepository = $bonusRepository;
    }

    public function __invoke(MakeDeposit $makeDeposit)
    {
        $player = $this->playerRepository->get($makeDeposit->getPlayerId());
        $player->deposit($makeDeposit->getDeposit(), $this->bonusRepository->getDepositBonus());
        $this->playerRepository->save($player);
    }
}
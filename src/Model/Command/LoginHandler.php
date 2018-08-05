<?php

namespace App\Model\Command;


use App\Infrastructure\BonusRepository;
use App\Infrastructure\PlayerRepository;

class LoginHandler
{
    /**
     * @var PlayerRepository
     */
    private $playerRepository;

    /**
     * @var BonusRepository
     */
    private $bonusRepository;

    public function __invoke(Login $login)
    {
        $bonus = $this->bonusRepository->getLoginBonus();
        if ($bonus) {
            $player = $this->playerRepository->get($login->getPlayerId());
            $player->addBonus($bonus);
            $this->playerRepository->save($player);
        }
    }
}
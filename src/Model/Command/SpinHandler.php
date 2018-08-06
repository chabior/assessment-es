<?php

namespace App\Model\Command;


use App\Infrastructure\PlayerRepository;

class SpinHandler
{
    /**
     * @var PlayerRepository
     */
    private $playerRepository;

    /**
     *
     * @param PlayerRepository $playerRepository
     */
    public function __construct(PlayerRepository $playerRepository)
    {
        $this->playerRepository = $playerRepository;
    }

    public function __invoke(Spin $spin)
    {
        $player = $this->playerRepository->get($spin->getPlayerId());
        $player->spin($spin->getBet(), $spin->getReward());
        $this->playerRepository->save($player);
    }

}
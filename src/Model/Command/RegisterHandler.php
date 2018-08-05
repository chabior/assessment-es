<?php

namespace App\Model\Command;


use App\Infrastructure\PlayerRepository;
use App\Model\Player;

class RegisterHandler
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

    public function __invoke(Register $register)
    {
        $player = Player::create($register->getId());
        $this->playerRepository->save($player);
    }
}
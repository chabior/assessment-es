<?php

namespace App\Controller;


use App\ES\CommandBus;
use App\Model\Command\Login;
use App\Model\Command\MakeDeposit;
use App\Model\Command\Register;
use App\Model\Command\Spin;
use App\Model\ValueObject\Money;
use App\Projection\PlayerProjection;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PlayController extends Controller
{
    /**
     * @var CommandBus
     */
    protected $commandBus;

    /**
     * @var PlayerProjection
     */
    protected $playerProjection;

    public function __construct(CommandBus $commandBus, PlayerProjection $playerProjection)
    {
        $this->commandBus = $commandBus;
        $this->playerProjection = $playerProjection;
    }

    public function hudAction():Response
    {
        $player = $this->playerProjection->getPlayerMoney($this->getPlayerId());
        if (empty($player)) {
            return $this->redirect($this->generateUrl('app_register'));
        }

        return $this->render('play/hud.html.twig', [
            'player' => $player,
        ]);
    }

    public function depositAction(Request $request)
    {
        $this->commandBus->dispatchCommand(new MakeDeposit(
            $this->getPlayerId(),
            new Money((int)$request->request->get('deposit'))
        ));

        return $this->redirect($this->generateUrl('app_home'));
    }

    public function loginAction()
    {
        $this->commandBus->dispatchCommand(new Login($this->getPlayerId()));
        return $this->redirect($this->generateUrl('app_home'));
    }

    public function spinAction(Request $request)
    {
        $bet = new Money((int)$request->request->get('bet'));
        //random reward
        $reward = mt_rand(0, 1) === 1 ? $bet->add(new Money(10)) : null;

        try {
            $this->commandBus->dispatchCommand(new Spin(
                $this->getPlayerId(),
                $bet,
                $reward
            ));

            $this->addFlash('spin', $reward ? 'Won' : 'Lost');

        } catch (\InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirect($this->generateUrl('app_home'));
    }

    public function registerAction()
    {
        $this->commandBus->dispatchCommand(new Register(
            $this->getPlayerId()
        ));

        return $this->redirect($this->generateUrl('app_home'));
    }

    protected function getPlayerId():string
    {
        return '1';
    }
}
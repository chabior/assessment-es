<?php

namespace App\Projection;


use App\Model\Event\BonusApplied;
use App\Model\Event\BonusMoneyAdded;
use App\Model\Event\BonusMoneySubtracted;
use App\Model\Event\DepositMade;
use App\Model\Event\RealMoneyAdded;
use App\Model\Event\RealMoneySubtracted;
use App\Model\Event\Registered;
use Doctrine\DBAL\Connection;

class PlayerProjection
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getPlayerMoney(string $playerId)
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->select(['*'])
            ->from('player_projection')
            ->andWhere('player_id = :playerId')
            ->setParameter('playerId', $playerId)
        ;

        $player = $qb->execute()->fetch() ?: [];

        if ($player) {
            $qb = $this->connection->createQueryBuilder();

            $qb
                ->select(['*'])
                ->from('player_bonus_projection')
                ->andWhere('player_id = :playerId')
                ->setParameter('playerId', $playerId)
            ;

            $player['bonus_wallets'] = $qb->execute();
        }

        return $player;
    }

    public function onRegistered(Registered $registered)
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->insert('player_projection')
            ->values([
                'player_id' => ':playerId',
                'real_money' => 0,
            ])
            ->setParameter('playerId', $registered->getId())
        ;

        $qb->execute();
    }

    public function onDepositMade(DepositMade $depositMade)
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->update('player_projection')
            ->set('real_money', 'real_money + :realMoneyAdded')
            ->andWhere('player_id = :playerId')

            ->setParameter('playerId', $depositMade->getId())
            ->setParameter('realMoneyAdded', $depositMade->getValue()->amount())
        ;

        $qb->execute();
    }

    public function onBonusApplied(BonusApplied $bonusApplied)
    {
        $wallets = $bonusApplied->getBonusWallet()->getWallets();
        foreach ($wallets as $wallet) {
            $stmt = $this->connection->prepare('INSERT INTO player_bonus_projection (wallet_id, player_id, money) 
                VALUES (:id, :playerId, :money)
                ON DUPLICATE KEY UPDATE money = VALUES(money)
            ');

            $stmt->bindValue('id', $wallet->getId());
            $stmt->bindValue('playerId', $bonusApplied->getPlayerId());
            $stmt->bindValue('money', $wallet->getAmount());
            $stmt->execute();
        }
    }

    public function onBonusMoneyAdded(BonusMoneyAdded $bonusMoneyAdded)
    {
        $wallets = $bonusMoneyAdded->getWallet()->getWallets();
        foreach ($wallets as $wallet) {
            $stmt = $this->connection->prepare('INSERT INTO player_bonus_projection (wallet_id, player_id, money) 
                VALUES (:id, :playerId, :money)
                ON DUPLICATE KEY UPDATE money = VALUES(money)
            ');

            $stmt->bindValue('id', $wallet->getId());
            $stmt->bindValue('playerId', $bonusMoneyAdded->getId());
            $stmt->bindValue('money', $wallet->getAmount());
            $stmt->execute();
        }

        $qb = $this->connection->createQueryBuilder();
        $qb
            ->delete('player_bonus_projection')
            ->andWhere('player_id = :playerId')
            ->andWhere('money = 0')
            ->setParameter('playerId', $bonusMoneyAdded->getId())
        ;

        $qb->execute();
    }

    public function onBonusMoneySubtracted(BonusMoneySubtracted $bonusMoneySubtracted)
    {
        $wallets = $bonusMoneySubtracted->getWallet()->getWallets();
        foreach ($wallets as $wallet) {
            $stmt = $this->connection->prepare('INSERT INTO player_bonus_projection (wallet_id, player_id, money) 
                VALUES (:id, :playerId, :money)
                ON DUPLICATE KEY UPDATE money = VALUES(money)
            ');

            $stmt->bindValue('id', $wallet->getId());
            $stmt->bindValue('playerId', $bonusMoneySubtracted->getId());
            $stmt->bindValue('money', $wallet->getAmount());
            $stmt->execute();
        }
    }

    public function onRealMoneyAdded(RealMoneyAdded $realMoneyAdded)
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->update('player_projection')
            ->set('real_money', 'real_money + :realMoneyAdded')
            ->andWhere('player_id = :playerId')

            ->setParameter('playerId', $realMoneyAdded->getId())
            ->setParameter('realMoneyAdded', $realMoneyAdded->getValue()->amount())
        ;

        $qb->execute();
    }

    public function onRealMoneySubtracted(RealMoneySubtracted $realMoneySubtracted)
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->update('player_projection')
            ->set('real_money', 'real_money - :realMoneySubtracted')
            ->andWhere('player_id = :playerId')

            ->setParameter('playerId', $realMoneySubtracted->getId())
            ->setParameter('realMoneySubtracted', $realMoneySubtracted->getValue()->amount())
        ;

        $qb->execute();
    }
}
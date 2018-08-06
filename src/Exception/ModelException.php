<?php

namespace App\Exception;


class ModelException extends \InvalidArgumentException
{
    public static function depositValueRequired(): ModelException
    {
        return new ModelException('Deposit value is required to calculate reward!');
    }

    public static function depositGreaterThanZero(): ModelException
    {
        return new ModelException('Deposit should be greater than 0!');
    }

    public static function betGreaterThanZero(): ModelException
    {
        return new ModelException('Bet must be greater than 0');
    }

    public static function rewardGreaterThanZero(): ModelException
    {
        return new ModelException('Reward must be greater than 0');
    }

    public static function playerHasNoSufficientFund(): ModelException
    {
        return new ModelException('Player has no sufficient money to place bet!');
    }

    public static function notHandledEvent(string $event)
    {
        return new ModelException(sprintf('Event %s is not handled!', get_class($event)));
    }

    public static function noMoneyInWallet()
    {
        return new ModelException('Player has no money in wallet');
    }
}
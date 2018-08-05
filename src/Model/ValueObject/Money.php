<?php

namespace App\Model\ValueObject;


class Money
{
    /**
     * @var int
     */
    private $amount;

    public function __construct(int $amount)
    {
        $this->amount = $amount;
    }

    public function isGreaterOrEqual(Money $comparator):bool
    {
        return $this->isGreater($comparator) || $this->isEqual($comparator);
    }

    public function isGreater(Money $comparator):bool
    {
        return $this->amount > $comparator->amount;
    }

    public function isLess(Money $comparator):bool
    {
        return $this->amount < $comparator->amount;
    }

    public function isEqual(Money $comparator):bool
    {
        return $this->amount === $comparator->amount;
    }

    public function add(Money $adder):Money
    {
        return new Money($this->amount + $adder->amount);
    }

    public function subtract(Money $subtract):Money
    {
        return new Money($this->amount - $subtract->amount);
    }

    public function multiply(int $multiplier):Money
    {
        return new Money($this->amount * $multiplier);
    }

    public function divide(int $divider):Money
    {
        return new Money(round($this->amount / $divider));
    }

    public function isZero():bool
    {
        return $this->amount === 0;
    }

    public function isGreaterThanZero():bool
    {
        return $this->isGreater(new self(0));
    }

    public function isLessOrEqualZero():bool
    {
        $zero = new self(0);
        return $this->isLess($zero) || $this->isEqual($zero);
    }

    public function isLessThanZero():bool
    {
        return $this->isLess(new self(0));
    }

    public function amount():int
    {
        return $this->amount;
    }
}
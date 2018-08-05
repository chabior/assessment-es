<?php

namespace App\Model\Event;


class Registered
{
    /**
     * @var string
     */
    private $id;

    /**
     *
     * @param string $id
     */
    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function getId():string
    {
        return $this->id;
    }
}
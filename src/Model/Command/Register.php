<?php

namespace App\Model\Command;


class Register
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
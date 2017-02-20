<?php

namespace CanalTP\TyrComponent\Exception;


class InvalidApplicationNameException extends \LogicException
{
    public function __construct($previous = null)
    {
        parent::__construct("Application name is not valid", 0, $previous);
    }
}

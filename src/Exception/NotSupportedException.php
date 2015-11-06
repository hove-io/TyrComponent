<?php

namespace CanalTP\TyrComponent\Exception;

class NotSupportedException extends \LogicException
{
    public function __construct($previous)
    {
        parent::__construct(
            "Only Guzzle 3 or 5 is supported.",
            0,
            $previous
        );
    }
}

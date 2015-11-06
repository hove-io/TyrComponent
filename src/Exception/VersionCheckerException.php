<?php

namespace CanalTP\TyrComponent\Exception;

class VersionCheckerException extends \LogicException
{
    public function __construct($wantedVersion, $className, $previous = null)
    {
        parent::__construct(
            "The class $className uses version $wantedVersion of Guzzle.",
            0,
            $previous
        );
    }
}

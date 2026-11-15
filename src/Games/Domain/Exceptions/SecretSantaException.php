<?php

namespace Src\Games\Domain\Exceptions;

use Exception;

class SecretSantaException extends Exception
{
    public function __construct(string $message, public readonly ?int $memberOid = null)
    {
        parent::__construct($message);
    }
}

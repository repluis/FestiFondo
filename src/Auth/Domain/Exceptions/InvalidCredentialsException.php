<?php

namespace Src\Auth\Domain\Exceptions;

use Src\Auth\Domain\Exceptions\AuthException;

class InvalidCredentialsException extends AuthException
{
    public function __construct()
    {
        parent::__construct('Las credenciales proporcionadas no coinciden con nuestros registros.');
    }
}

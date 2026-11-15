<?php

namespace Src\Games\Domain\Exceptions;

class SecretSantaNotFoundException extends SecretSantaException
{
    public function __construct()
    {
        parent::__construct('Este enlace no existe o ya no es válido.');
    }
}

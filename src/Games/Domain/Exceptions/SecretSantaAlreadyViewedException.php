<?php

namespace Src\Games\Domain\Exceptions;

class SecretSantaAlreadyViewedException extends SecretSantaException
{
    public function __construct()
    {
        parent::__construct('Este amigo secreto ya fue revelado. Si necesitas verlo de nuevo, pídele al administrador que lo restaure.');
    }
}

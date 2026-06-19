<?php

namespace Src\FundRaising\Domain\Exceptions;

class FundRaisingNameAlreadyExistsException extends FundRaisingException
{
    public static function withName(string $name): self
    {
        return new self("A fund raising campaign with this name already exists: {$name}");
    }
}

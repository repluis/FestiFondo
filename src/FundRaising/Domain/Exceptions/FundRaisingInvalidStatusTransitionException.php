<?php

namespace Src\FundRaising\Domain\Exceptions;

class FundRaisingInvalidStatusTransitionException extends FundRaisingException
{
    public static function from(string $current, string $requested): self
    {
        return new self("Cannot transition fund raising from '{$current}' to '{$requested}'.");
    }
}

<?php

namespace Src\FundRaising\Domain\Exceptions;

class FundRaisingNotFoundException extends FundRaisingException
{
    public static function withUuid(string $uuid): self
    {
        return new self("Fund raising not found with UUID: {$uuid}");
    }
}

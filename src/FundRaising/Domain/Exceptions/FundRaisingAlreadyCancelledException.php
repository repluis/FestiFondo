<?php

namespace Src\FundRaising\Domain\Exceptions;

class FundRaisingAlreadyCancelledException extends FundRaisingException
{
    public static function withUuid(string $uuid): self
    {
        return new self("Fund raising campaign is already cancelled: {$uuid}");
    }
}

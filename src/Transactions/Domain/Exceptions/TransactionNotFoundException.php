<?php

namespace Src\Transactions\Domain\Exceptions;

class TransactionNotFoundException extends TransactionException
{
    public static function withUuid(string $uuid): self
    {
        return new self("Transaction not found: {$uuid}");
    }
}

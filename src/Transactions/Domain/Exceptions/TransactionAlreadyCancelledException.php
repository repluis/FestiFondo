<?php

namespace Src\Transactions\Domain\Exceptions;

class TransactionAlreadyCancelledException extends TransactionException
{
    public static function withUuid(string $uuid): self
    {
        return new self("Transaction is already cancelled: {$uuid}");
    }
}

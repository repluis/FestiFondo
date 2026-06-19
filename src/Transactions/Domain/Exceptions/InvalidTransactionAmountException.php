<?php

namespace Src\Transactions\Domain\Exceptions;

class InvalidTransactionAmountException extends TransactionException
{
    public static function mustBePositive(): self
    {
        return new self('Transaction amount must be greater than zero.');
    }
}

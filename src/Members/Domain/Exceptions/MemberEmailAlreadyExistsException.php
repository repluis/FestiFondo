<?php

namespace Src\Members\Domain\Exceptions;

class MemberEmailAlreadyExistsException extends MembersException
{
    public static function withEmail(string $email): self
    {
        return new self("Email already registered: {$email}");
    }
}

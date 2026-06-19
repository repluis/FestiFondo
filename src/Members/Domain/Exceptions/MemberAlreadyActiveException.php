<?php

namespace Src\Members\Domain\Exceptions;

class MemberAlreadyActiveException extends MembersException
{
    public static function withUuid(string $uuid): self
    {
        return new self("Member is already active: {$uuid}");
    }
}

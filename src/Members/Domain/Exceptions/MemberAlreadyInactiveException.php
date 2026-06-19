<?php

namespace Src\Members\Domain\Exceptions;

class MemberAlreadyInactiveException extends MembersException
{
    public static function withUuid(string $uuid): self
    {
        return new self("Member is already inactive: {$uuid}");
    }
}

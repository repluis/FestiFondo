<?php

namespace Src\Members\Domain\Exceptions;

class MemberNotFoundException extends MembersException
{
    public static function withUuid(string $uuid): self
    {
        return new self("Member not found with UUID: {$uuid}");
    }
}

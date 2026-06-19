<?php

namespace Src\Members\Domain\Exceptions;

class MemberIdentificationAlreadyExistsException extends MembersException
{
    public static function withIdentification(string $identification): self
    {
        return new self("Identification already registered: {$identification}");
    }
}

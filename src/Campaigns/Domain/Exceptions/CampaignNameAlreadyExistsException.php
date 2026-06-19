<?php

namespace Src\Campaigns\Domain\Exceptions;

class CampaignNameAlreadyExistsException extends CampaignException
{
    public static function withName(string $name): self
    {
        return new self("A campaign with this name already exists: {$name}");
    }
}

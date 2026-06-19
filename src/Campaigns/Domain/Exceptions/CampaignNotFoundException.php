<?php

namespace Src\Campaigns\Domain\Exceptions;

class CampaignNotFoundException extends CampaignException
{
    public static function withUuid(string $uuid): self
    {
        return new self("Campaign not found with UUID: {$uuid}");
    }
}

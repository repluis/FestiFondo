<?php

namespace Src\Campaigns\Domain\Exceptions;

class CampaignAlreadyCancelledException extends CampaignException
{
    public static function withUuid(string $uuid): self
    {
        return new self("Campaign is already cancelled: {$uuid}");
    }
}

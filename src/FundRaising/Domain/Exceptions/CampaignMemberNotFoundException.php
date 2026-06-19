<?php

namespace Src\FundRaising\Domain\Exceptions;

class CampaignMemberNotFoundException extends CampaignMemberException
{
    public static function withUuid(string $uuid): self
    {
        return new self("Campaign member with UUID [{$uuid}] not found.");
    }
}

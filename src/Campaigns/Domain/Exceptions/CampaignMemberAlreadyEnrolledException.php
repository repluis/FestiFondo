<?php

namespace Src\Campaigns\Domain\Exceptions;

class CampaignMemberAlreadyEnrolledException extends CampaignMemberException
{
    public static function forMember(int $memberOid, int $campaignOid): self
    {
        return new self("Member [{$memberOid}] is already enrolled in campaign [{$campaignOid}].");
    }
}

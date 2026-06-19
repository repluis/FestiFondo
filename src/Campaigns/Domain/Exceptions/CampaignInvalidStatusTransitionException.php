<?php

namespace Src\Campaigns\Domain\Exceptions;

class CampaignInvalidStatusTransitionException extends CampaignException
{
    public static function from(string $current, string $requested): self
    {
        return new self("Cannot transition campaign from '{$current}' to '{$requested}'.");
    }
}

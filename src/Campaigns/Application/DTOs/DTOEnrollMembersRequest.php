<?php

namespace Src\Campaigns\Application\DTOs;

class DTOEnrollMembersRequest
{
    public function __construct(
        public readonly int   $campaignOid,
        public readonly array $memberOids,
        public readonly int   $createdByOid,
    ) {}
}

<?php

namespace Src\FundRaising\Application\DTOs;

class DTOEnrollMembersRequest
{
    public function __construct(
        public readonly int   $campaignOid,
        public readonly array $memberOids,  // int[]
        public readonly int   $createdByOid,
    ) {}
}

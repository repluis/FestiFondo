<?php

namespace Src\Reports\Application\DTOs;

class DTOTransactionReportFiltersRequest
{
    public function __construct(
        public readonly ?int    $memberOid,
        public readonly ?int    $campaignOid,
        public readonly ?string $type,
        public readonly ?string $dateFrom,
        public readonly ?string $dateTo,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            memberOid:   isset($data['member_oid'])   && $data['member_oid']   !== '' ? (int) $data['member_oid']   : null,
            campaignOid: isset($data['campaign_oid']) && $data['campaign_oid'] !== '' ? (int) $data['campaign_oid'] : null,
            type:        isset($data['type'])         && $data['type']         !== '' ? (string) $data['type']      : null,
            dateFrom:    isset($data['date_from'])    && $data['date_from']    !== '' ? (string) $data['date_from'] : null,
            dateTo:      isset($data['date_to'])      && $data['date_to']      !== '' ? (string) $data['date_to']   : null,
        );
    }
}

<?php

namespace Src\FundRaising\Application\DTOs;

use Src\FundRaising\Domain\Entities\CampaignMember;

class DTOCampaignMemberResponse
{
    public function __construct(
        public readonly ?int    $id,
        public readonly ?int    $oid,
        public readonly ?string $uuid,
        public readonly bool    $status,
        public readonly int     $campaignOid,
        public readonly int     $memberOid,
        public readonly string  $enrolledAt,
        public readonly ?int    $createdByOid,
        public readonly ?int    $updatedByOid,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
    ) {}

    public static function fromEntity(CampaignMember $e): self
    {
        return new self(
            id:           $e->id,
            oid:          $e->oid,
            uuid:         $e->uuid,
            status:       $e->status,
            campaignOid:  $e->campaignOid,
            memberOid:    $e->memberOid,
            enrolledAt:   $e->enrolledAt,
            createdByOid: $e->createdByOid,
            updatedByOid: $e->updatedByOid,
            createdAt:    $e->createdAt,
            updatedAt:    $e->updatedAt,
        );
    }

    public function toArray(): array
    {
        return [
            'id'            => $this->id,
            'oid'           => $this->oid,
            'uuid'          => $this->uuid,
            'status'        => $this->status,
            'campaign_oid'  => $this->campaignOid,
            'member_oid'    => $this->memberOid,
            'enrolled_at'   => $this->enrolledAt,
            'created_by_oid'=> $this->createdByOid,
            'updated_by_oid'=> $this->updatedByOid,
            'created_at'    => $this->createdAt,
            'updated_at'    => $this->updatedAt,
        ];
    }
}

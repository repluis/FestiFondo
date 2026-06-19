<?php

namespace Src\FundRaising\Infrastructure\Repositories;

use Illuminate\Database\Eloquent\Model;
use Src\FundRaising\Domain\Entities\CampaignMember;

class CampaignMemberModel extends Model
{
    protected $table = 'campaign_members';

    protected $fillable = [
        'campaign_oid',
        'member_oid',
        'enrolled_at',
        'status',
        'created_by_oid',
        'updated_by_oid',
    ];

    protected $casts = [
        'status'      => 'boolean',
        'enrolled_at' => 'date:Y-m-d',
    ];

    public function toEntity(): CampaignMember
    {
        return new CampaignMember(
            id:           $this->id,
            oid:          $this->oid,
            uuid:         $this->uuid,
            status:       (bool) $this->status,
            campaignOid:  (int) $this->campaign_oid,
            memberOid:    (int) $this->member_oid,
            enrolledAt:   $this->enrolled_at?->format('Y-m-d') ?? '',
            createdByOid: $this->created_by_oid,
            updatedByOid: $this->updated_by_oid,
            createdAt:    $this->created_at?->toISOString(),
            updatedAt:    $this->updated_at?->toISOString(),
        );
    }
}

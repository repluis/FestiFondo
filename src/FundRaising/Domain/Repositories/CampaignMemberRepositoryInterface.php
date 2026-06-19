<?php

namespace Src\FundRaising\Domain\Repositories;

use Src\FundRaising\Domain\Entities\CampaignMember;

interface CampaignMemberRepositoryInterface
{
    public function enroll(array $data): CampaignMember;

    public function findByUuid(string $uuid): ?CampaignMember;

    public function findActiveByCampaignAndMember(int $campaignOid, int $memberOid): ?CampaignMember;

    /** @return CampaignMember[] */
    public function listByCampaign(int $campaignOid): array;

    /** Returns members enrolled in the campaign with balance totals per member */
    public function listMembersWithBalance(int $campaignOid): array;

    public function remove(int $oid, int $updatedByOid): void;

    public function availableMembers(int $campaignOid): array;
}

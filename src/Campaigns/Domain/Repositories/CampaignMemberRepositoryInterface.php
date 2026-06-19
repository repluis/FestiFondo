<?php

namespace Src\Campaigns\Domain\Repositories;

use Src\Campaigns\Domain\Entities\CampaignMember;

interface CampaignMemberRepositoryInterface
{
    public function enroll(array $data): CampaignMember;

    public function findByUuid(string $uuid): ?CampaignMember;

    public function findActiveByCampaignAndMember(int $campaignOid, int $memberOid): ?CampaignMember;

    /** @return CampaignMember[] */
    public function listByCampaign(int $campaignOid): array;

    public function listMembersWithBalance(int $campaignOid): array;

    public function remove(int $oid, int $updatedByOid): void;

    public function availableMembers(int $campaignOid): array;

    public function memberTransactions(int $campaignOid, int $memberOid): array;
}

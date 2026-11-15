<?php

namespace Src\Games\Application\UseCases;

use Illuminate\Support\Facades\Log;
use Src\Games\Domain\Exceptions\SecretSantaException;
use Src\Games\Domain\Repositories\GamesRepositoryInterface;

/**
 * Exclusiones guardadas por campaña ("a A no le puede tocar B").
 * Se guardan al momento de agregarlas, así siguen ahí al recargar la página
 * y se usan en los siguientes sorteos de la campaña.
 */
class ManageSecretSantaExclusionsUseCase
{
    public function __construct(private GamesRepositoryInterface $repository) {}

    /** @return array<int, array{giver: int, receiver: int}> */
    public function list(string $campaignUuid): array
    {
        return $this->repository->getExclusions($this->campaignOid($campaignUuid));
    }

    /** @return array<int, array{giver: int, receiver: int}> Lista actualizada */
    public function add(string $campaignUuid, int $giverOid, int $receiverOid, bool $bothWays, ?int $createdByOid): array
    {
        $campaignOid = $this->campaignOid($campaignUuid);

        if ($giverOid === $receiverOid) {
            throw new SecretSantaException('Elige dos personas distintas.');
        }

        $memberOids = array_column($this->repository->getCampaignMembers($campaignUuid), 'oid');
        if (!in_array($giverOid, $memberOids, true) || !in_array($receiverOid, $memberOids, true)) {
            throw new SecretSantaException('Ambas personas deben pertenecer a la campaña.');
        }

        $pairs = [['giver' => $giverOid, 'receiver' => $receiverOid]];
        if ($bothWays) {
            $pairs[] = ['giver' => $receiverOid, 'receiver' => $giverOid];
        }

        Log::info('[ManageSecretSantaExclusionsUseCase] Adding exclusions', ['campaign_oid' => $campaignOid, 'pairs' => $pairs]);
        $this->repository->addExclusions($campaignOid, $pairs, $createdByOid);

        return $this->repository->getExclusions($campaignOid);
    }

    /** @return array<int, array{giver: int, receiver: int}> Lista actualizada */
    public function remove(string $campaignUuid, int $giverOid, int $receiverOid): array
    {
        $campaignOid = $this->campaignOid($campaignUuid);

        Log::info('[ManageSecretSantaExclusionsUseCase] Removing exclusion', [
            'campaign_oid' => $campaignOid, 'giver' => $giverOid, 'receiver' => $receiverOid,
        ]);
        $this->repository->removeExclusion($campaignOid, $giverOid, $receiverOid);

        return $this->repository->getExclusions($campaignOid);
    }

    private function campaignOid(string $campaignUuid): int
    {
        $oid = $this->repository->getCampaignOidByUuid($campaignUuid);
        if ($oid === null) {
            throw new SecretSantaException('La campaña seleccionada no existe.');
        }

        return $oid;
    }
}

<?php

namespace Src\Games\Application\Services;

use Src\Games\Application\DTOs\DTOSecretSantaDrawRequest;
use Src\Games\Application\UseCases\DrawSecretSantaUseCase;
use Src\Games\Application\UseCases\ManageSecretSantaExclusionsUseCase;
use Src\Games\Application\UseCases\RevealSecretSantaUseCase;
use Src\Games\Domain\Exceptions\SecretSantaNotFoundException;
use Src\Games\Domain\Repositories\GamesRepositoryInterface;

class GamesService
{
    public function __construct(
        private GamesRepositoryInterface $repository,
        private DrawSecretSantaUseCase   $drawSecretSanta,
        private RevealSecretSantaUseCase $revealSecretSanta,
        private ManageSecretSantaExclusionsUseCase $exclusions,
    ) {}

    public function exclusions(string $campaignUuid): array
    {
        return $this->exclusions->list($campaignUuid);
    }

    public function addExclusion(string $campaignUuid, int $giverOid, int $receiverOid, bool $bothWays, ?int $createdByOid): array
    {
        return $this->exclusions->add($campaignUuid, $giverOid, $receiverOid, $bothWays, $createdByOid);
    }

    public function removeExclusion(string $campaignUuid, int $giverOid, int $receiverOid): array
    {
        return $this->exclusions->remove($campaignUuid, $giverOid, $receiverOid);
    }

    public function campaigns(): array
    {
        return $this->repository->getCampaigns();
    }

    public function campaignMembers(string $campaignUuid): array
    {
        return $this->repository->getCampaignMembers($campaignUuid);
    }

    public function latestDraw(string $campaignUuid): ?array
    {
        return $this->repository->getLatestDraw($campaignUuid);
    }

    public function draw(string $drawUuid): ?array
    {
        return $this->repository->getDrawByUuid($drawUuid);
    }

    public function drawHistory(): array
    {
        return $this->repository->listDraws();
    }

    public function drawSecretSanta(DTOSecretSantaDrawRequest $dto): array
    {
        return $this->drawSecretSanta->execute($dto);
    }

    /** Datos para la página del enlace, sin revelar el nombre. */
    public function assignmentPreview(string $token): ?array
    {
        $a = $this->repository->findAssignmentByToken($token);

        return $a ? ['giver_name' => $a['giver_name'], 'viewed' => $a['viewed_at'] !== null] : null;
    }

    public function revealSecretSanta(string $token): array
    {
        return $this->revealSecretSanta->execute($token);
    }

    public function resetSecretSantaView(string $token): void
    {
        if (!$this->repository->resetAssignmentView($token)) {
            throw new SecretSantaNotFoundException();
        }
    }
}

<?php

namespace Src\Dashboard\Application\UseCases;

use Src\Dashboard\Domain\Repositories\DashboardRepositoryInterface;

/**
 * Datos públicos de transparencia financiera de una campaña:
 * solo nombres y total pagado sin mora (no muestra deudas ni mora).
 */
class GetCampaignTransparencyUseCase
{
    public function __construct(private DashboardRepositoryInterface $repository) {}

    /** @return array{campaign: string, members: array, total: float}|null */
    public function execute(string $campaignUuid): ?array
    {
        $data = $this->repository->getCampaignTransparency($campaignUuid);

        if ($data === null) {
            return null;
        }

        $data['total'] = round(array_sum(array_column($data['members'], 'total')), 2);

        return $data;
    }
}

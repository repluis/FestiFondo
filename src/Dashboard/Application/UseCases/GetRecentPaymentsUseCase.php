<?php

namespace Src\Dashboard\Application\UseCases;

use Src\Dashboard\Domain\Repositories\DashboardRepositoryInterface;

class GetRecentPaymentsUseCase
{
    public function __construct(private DashboardRepositoryInterface $repository) {}

    public function execute(int $limit = 8): array
    {
        return $this->repository->getRecentPayments($limit);
    }
}

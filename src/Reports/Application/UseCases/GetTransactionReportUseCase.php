<?php

namespace Src\Reports\Application\UseCases;

use Src\Reports\Application\DTOs\DTOTransactionReportFiltersRequest;
use Src\Reports\Application\DTOs\DTOTransactionReportResponse;
use Src\Reports\Domain\Repositories\ReportsRepositoryInterface;

class GetTransactionReportUseCase
{
    public function __construct(private ReportsRepositoryInterface $repo) {}

    /** @return DTOTransactionReportResponse[] */
    public function execute(DTOTransactionReportFiltersRequest $filters): array
    {
        $entities = $this->repo->getTransactionReport($filters);

        return array_map(
            fn($entity) => DTOTransactionReportResponse::fromEntity($entity),
            $entities
        );
    }
}

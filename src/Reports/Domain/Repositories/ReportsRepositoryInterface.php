<?php

namespace Src\Reports\Domain\Repositories;

use Src\Reports\Application\DTOs\DTOTransactionReportFiltersRequest;
use Src\Reports\Domain\Entities\TransactionReport;

interface ReportsRepositoryInterface
{
    /** @return TransactionReport[] */
    public function getTransactionReport(DTOTransactionReportFiltersRequest $filters): array;

    /** @return array<int, array{oid: int, name: string}> */
    public function getMembersDropdown(): array;

    /** @return array<int, array{oid: int, name: string}> */
    public function getCampaignsDropdown(): array;
}

<?php

namespace Src\Reports\Application\Services;

use Src\Reports\Application\DTOs\DTOTransactionReportFiltersRequest;
use Src\Reports\Application\UseCases\GetTransactionReportUseCase;
use Src\Reports\Application\UseCases\Dropdowns\GetCampaignsDropdownUseCase;
use Src\Reports\Application\UseCases\Dropdowns\GetMembersDropdownUseCase;

class ReportsService
{
    public function __construct(
        private GetTransactionReportUseCase  $getTransactionReport,
        private GetMembersDropdownUseCase    $getMembersDropdown,
        private GetCampaignsDropdownUseCase  $getCampaignsDropdown,
    ) {}

    public function transactionReport(array $filters): array
    {
        $dto = DTOTransactionReportFiltersRequest::fromArray($filters);

        return $this->getTransactionReport->execute($dto);
    }

    public function membersDropdown(): array
    {
        return $this->getMembersDropdown->execute();
    }

    public function campaignsDropdown(): array
    {
        return $this->getCampaignsDropdown->execute();
    }
}

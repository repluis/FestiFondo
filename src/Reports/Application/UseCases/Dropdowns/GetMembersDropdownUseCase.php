<?php

namespace Src\Reports\Application\UseCases\Dropdowns;

use Src\Reports\Domain\Repositories\ReportsRepositoryInterface;

class GetMembersDropdownUseCase
{
    public function __construct(private ReportsRepositoryInterface $repo) {}

    public function execute(): array
    {
        return $this->repo->getMembersDropdown();
    }
}

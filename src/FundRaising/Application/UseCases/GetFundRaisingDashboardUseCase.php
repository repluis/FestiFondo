<?php

namespace Src\FundRaising\Application\UseCases;

use Illuminate\Support\Facades\Log;
use Src\FundRaising\Domain\Repositories\FundRaisingRepositoryInterface;
use Src\FundRaising\Domain\Repositories\ProcessChargesRepositoryInterface;

class GetFundRaisingDashboardUseCase
{
    public function __construct(
        private readonly FundRaisingRepositoryInterface    $fundRaisingRepository,
        private readonly ProcessChargesRepositoryInterface $processRepository,
    ) {}

    public function execute(): array
    {
        Log::info('[GetFundRaisingDashboardUseCase] Starting');

        Log::info('[GetFundRaisingDashboardUseCase] Step 1 — Loading members with balance');
        $members = $this->fundRaisingRepository->getDashboardMembersWithBalance();

        Log::info('[GetFundRaisingDashboardUseCase] Step 2 — Loading last process execution');
        $lastExecution = $this->processRepository->getLastProcessExecution();

        Log::info('[GetFundRaisingDashboardUseCase] Completed', ['members' => count($members)]);

        return [
            'membersWithBalance' => $members,
            'lastExecution'      => $lastExecution,
        ];
    }
}

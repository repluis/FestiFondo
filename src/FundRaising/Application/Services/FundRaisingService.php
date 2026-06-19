<?php

namespace Src\FundRaising\Application\Services;

use Src\FundRaising\Application\DTOs\DTOCreateFundRaisingRequest;
use Src\FundRaising\Application\DTOs\DTOFundRaisingResponse;
use Src\FundRaising\Application\DTOs\DTOUpdateFundRaisingRequest;
use Src\FundRaising\Application\UseCases\CancelFundRaisingUseCase;
use Src\FundRaising\Application\UseCases\CreateFundRaisingUseCase;
use Src\FundRaising\Application\UseCases\GetFundRaisingDashboardUseCase;
use Src\FundRaising\Application\UseCases\ListFundRaisingsUseCase;
use Src\FundRaising\Application\UseCases\ShowFundRaisingUseCase;
use Src\FundRaising\Application\UseCases\UpdateFundRaisingUseCase;

class FundRaisingService
{
    public function __construct(
        private readonly CreateFundRaisingUseCase       $createUseCase,
        private readonly ListFundRaisingsUseCase        $listUseCase,
        private readonly ShowFundRaisingUseCase         $showUseCase,
        private readonly UpdateFundRaisingUseCase       $updateUseCase,
        private readonly CancelFundRaisingUseCase       $cancelUseCase,
        private readonly GetFundRaisingDashboardUseCase $dashboardUseCase,
    ) {}

    public function create(DTOCreateFundRaisingRequest $dto): DTOFundRaisingResponse
    {
        return $this->createUseCase->execute($dto);
    }

    /** @return DTOFundRaisingResponse[] */
    public function list(array $filters = []): array
    {
        return $this->listUseCase->execute($filters);
    }

    public function show(string $uuid): DTOFundRaisingResponse
    {
        return $this->showUseCase->execute($uuid);
    }

    public function update(DTOUpdateFundRaisingRequest $dto): DTOFundRaisingResponse
    {
        return $this->updateUseCase->execute($dto);
    }

    public function cancel(string $uuid, int $cancelledByOid): void
    {
        $this->cancelUseCase->execute($uuid, $cancelledByOid);
    }

    public function getDashboard(): array
    {
        return $this->dashboardUseCase->execute();
    }
}

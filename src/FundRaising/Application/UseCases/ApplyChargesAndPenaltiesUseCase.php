<?php

namespace Src\FundRaising\Application\UseCases;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Guarda el proceso de cobros y moras de forma atómica: todo o nada.
 * Si algo falla a mitad de camino, se revierte completo y no quedan
 * cuotas o moras a medio crear.
 */
class ApplyChargesAndPenaltiesUseCase
{
    public function __construct(
        private readonly ProcessChargesAndPenaltiesUseCase $process,
    ) {}

    public function execute(int $triggeredByOid, ?string $campaignUuid = null): array
    {
        Log::info('[ApplyChargesAndPenaltiesUseCase] Starting (single transaction)', ['campaign_uuid' => $campaignUuid]);

        return DB::transaction(fn () => $this->process->execute($triggeredByOid, $campaignUuid));
    }
}

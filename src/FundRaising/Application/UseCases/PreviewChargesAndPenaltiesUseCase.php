<?php

namespace Src\FundRaising\Application\UseCases;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Simula el proceso de cobros y moras SIN guardar nada.
 *
 * Ejecuta el proceso real dentro de una transacción y al final hace ROLLBACK,
 * así la vista previa usa exactamente la misma lógica que el guardado.
 */
class PreviewChargesAndPenaltiesUseCase
{
    public function __construct(
        private readonly ProcessChargesAndPenaltiesUseCase $process,
    ) {}

    public function execute(int $triggeredByOid, ?string $campaignUuid = null): array
    {
        Log::info('[PreviewChargesAndPenaltiesUseCase] Starting dry run', ['campaign_uuid' => $campaignUuid]);

        DB::beginTransaction();
        try {
            $result = $this->process->execute($triggeredByOid, $campaignUuid);
        } finally {
            DB::rollBack(); // nunca se guarda nada
        }

        unset($result['process_id']); // la ejecución simulada no existe

        Log::info('[PreviewChargesAndPenaltiesUseCase] Dry run finished (rolled back)', [
            'fees'      => $result['fees_generated'],
            'penalties' => $result['penalties_generated'],
        ]);

        return $result;
    }
}

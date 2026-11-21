<?php

namespace Src\FundRaising\Application\UseCases;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Src\FundRaising\Domain\Exceptions\ProcessExecutionException;
use Src\FundRaising\Domain\Repositories\ProcessChargesRepositoryInterface;

/**
 * Restablece la base a como estaba antes de una actualización de cobros y moras.
 *
 * El proceso solo AGREGA cuotas y moras (nunca modifica las existentes) y marca
 * cada fila con generated_by_process_oid, así que deshacerlo es borrar
 * exactamente esas filas. Por seguridad:
 *   - solo la última ejecución completada de ese proceso,
 *   - y solo si ninguna de sus cuotas/moras recibió un pago.
 */
class RevertChargesExecutionUseCase
{
    public function __construct(
        private readonly ProcessChargesRepositoryInterface $repository,
    ) {}

    /** @return array{fees_deleted: int, penalties_deleted: int} */
    public function execute(int $executionId, int $revertedByOid): array
    {
        Log::info('[RevertChargesExecutionUseCase] Starting', ['execution_id' => $executionId]);

        return DB::transaction(function () use ($executionId, $revertedByOid) {
            $execution = $this->repository->getProcessExecutionById($executionId);
            if (!$execution) {
                throw ProcessExecutionException::notFound();
            }

            if ($execution->execution_status !== 'completed') {
                throw ProcessExecutionException::notCompleted($execution->execution_status);
            }

            $latest = $this->repository->getLatestExecutionByName($execution->process_name);
            if (!$latest || (int) $latest->id !== (int) $execution->id) {
                throw ProcessExecutionException::notLatest();
            }

            $paid = $this->repository->countPaidGeneratedRows($executionId);
            if ($paid > 0) {
                throw ProcessExecutionException::hasPayments($paid);
            }

            $deleted = $this->repository->deleteGeneratedRows($executionId);
            $this->repository->markExecutionReverted($executionId, $revertedByOid, $deleted);

            Log::info('[RevertChargesExecutionUseCase] Reverted', ['execution_id' => $executionId] + $deleted);

            return [
                'fees_deleted'      => $deleted['fees'],
                'penalties_deleted' => $deleted['penalties'],
            ];
        });
    }
}

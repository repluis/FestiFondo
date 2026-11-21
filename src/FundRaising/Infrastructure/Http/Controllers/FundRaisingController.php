<?php

namespace Src\FundRaising\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Src\FundRaising\Application\Services\FundRaisingService;
use Src\FundRaising\Application\UseCases\ApplyChargesAndPenaltiesUseCase;
use Src\FundRaising\Application\UseCases\PreviewChargesAndPenaltiesUseCase;
use Src\FundRaising\Application\UseCases\RevertChargesExecutionUseCase;
use Src\FundRaising\Domain\Exceptions\ProcessExecutionException;
use Src\Shared\Infrastructure\Http\Controllers\BaseController;

class FundRaisingController extends BaseController
{
    public function __construct(
        private readonly FundRaisingService                 $service,
        private readonly ApplyChargesAndPenaltiesUseCase    $applyChargesUseCase,
        private readonly PreviewChargesAndPenaltiesUseCase  $previewChargesUseCase,
        private readonly RevertChargesExecutionUseCase      $revertExecutionUseCase,
    ) {}

    /** Vista previa: simula el proceso y devuelve lo que se crearía, sin guardar nada. */
    public function previewCharges(Request $request): JsonResponse
    {
        $campaignUuid = $request->input('campaign_uuid');

        try {
            $result = $this->previewChargesUseCase->execute($this->authUserOid(), $campaignUuid);

            return response()->json(['status' => true, 'data' => $result]);
        } catch (\RuntimeException $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 409);
        } catch (\Throwable $e) {
            Log::error('[FundRaisingController::previewCharges] Unexpected error', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'No se pudo generar la vista previa: ' . $e->getMessage()], 500);
        }
    }

    /** Restablece la base a como estaba antes de una actualización de cobros y moras. */
    public function revertExecution(int $id): JsonResponse
    {
        try {
            $result = $this->revertExecutionUseCase->execute($id, $this->authUserOid());

            $this->writeAuditLog('process_executions', 'revert_charges', $id, null, null, $result);

            return response()->json([
                'status'  => true,
                'message' => "Restablecido: se eliminaron {$result['fees_deleted']} cuota(s) y {$result['penalties_deleted']} mora(s).",
                'data'    => $result,
            ]);
        } catch (ProcessExecutionException $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            Log::error('[FundRaisingController::revertExecution] Unexpected error', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'No se pudo restablecer. No se modificó nada.'], 500);
        }
    }

    public function index(): View
    {
        Log::info('[FundRaisingController::index] Request received', [
            'user_oid' => $this->authUserOid(),
            'ip'       => request()->ip(),
        ]);

        try {
            $dashboard = $this->service->getDashboard();

            Log::info('[FundRaisingController::index] Success', [
                'members' => count($dashboard['membersWithBalance']),
            ]);

            return view('FundRaising.index', [
                'membersWithBalance' => $dashboard['membersWithBalance'],
                'lastExecution'      => $dashboard['lastExecution'],
            ]);

        } catch (\Throwable $e) {
            Log::error('[FundRaisingController::index] Unexpected error', ['error' => $e->getMessage()]);

            return view('FundRaising.index', [
                'membersWithBalance' => [],
                'lastExecution'      => null,
                'loadError'          => 'Could not load data. Please try again.',
            ]);
        }
    }

    public function processCharges(Request $request): JsonResponse
    {
        $campaignUuid = $request->input('campaign_uuid');

        Log::info('[FundRaisingController::processCharges] Request received', [
            'user_oid'      => $this->authUserOid(),
            'campaign_uuid' => $campaignUuid,
        ]);

        try {
            // Todo en una sola transacción: si algo falla, no se guarda nada
            $result = $this->applyChargesUseCase->execute($this->authUserOid(), $campaignUuid);
            unset($result['details']);

            $this->writeAuditLog('fund_raisings', 'process_charges', null, null, null, $result);

            Log::info('[FundRaisingController::processCharges] Success', $result);

            return response()->json([
                'status'  => true,
                'message' => 'Charges and penalties updated successfully.',
                'data'    => $result,
            ]);

        } catch (\RuntimeException $e) {
            Log::warning('[FundRaisingController::processCharges] Process locked', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => $e->getMessage()], 409);

        } catch (\Throwable $e) {
            Log::error('[FundRaisingController::processCharges] Unexpected error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['status' => false, 'message' => 'An unexpected error occurred. Please try again.'], 500);
        }
    }
}

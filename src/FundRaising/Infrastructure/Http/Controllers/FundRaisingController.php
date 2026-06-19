<?php

namespace Src\FundRaising\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Src\FundRaising\Application\Services\FundRaisingService;
use Src\FundRaising\Application\UseCases\ProcessChargesAndPenaltiesUseCase;
use Src\Shared\Infrastructure\Http\Controllers\BaseController;

class FundRaisingController extends BaseController
{
    public function __construct(
        private readonly FundRaisingService                $service,
        private readonly ProcessChargesAndPenaltiesUseCase $processChargesUseCase,
    ) {}

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
            $result = $this->processChargesUseCase->execute($this->authUserOid(), $campaignUuid);

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

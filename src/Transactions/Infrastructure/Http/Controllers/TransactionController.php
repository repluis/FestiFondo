<?php

namespace Src\Transactions\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Src\Transactions\Application\DTOs\DTOApplyPaymentRequest;
use Src\Transactions\Application\DTOs\DTOCreateTransactionRequest;
use Src\Transactions\Application\Services\TransactionService;
use Src\Transactions\Domain\Exceptions\InvalidTransactionAmountException;
use Src\Transactions\Domain\Exceptions\TransactionAlreadyCancelledException;
use Src\Transactions\Domain\Exceptions\TransactionException;
use Src\Transactions\Domain\Exceptions\TransactionNotFoundException;
use Src\Transactions\Infrastructure\Http\Requests\ApplyPaymentRequest;
use Src\Transactions\Infrastructure\Http\Requests\CreateTransactionRequest;
use Src\Shared\Infrastructure\Http\Controllers\BaseController;

class TransactionController extends BaseController
{
    public function __construct(
        private readonly TransactionService $service,
    ) {}

    public function index(): View
    {
        Log::info('[TransactionController::index] Request received', ['ip' => request()->ip()]);

        try {
            $transactions = array_map(
                fn($dto) => $dto->toArray(),
                $this->service->list(['status' => true]),
            );

            Log::info('[TransactionController::index] Success', ['total' => count($transactions)]);

            return view('Transactions.index', compact('transactions'));

        } catch (\Throwable $e) {
            $this->writeAuditLog('transactions', 'list_failed', null, null, null, null, $e->getMessage());
            Log::error('[TransactionController::index] Unexpected error', ['error' => $e->getMessage()]);

            return view('Transactions.index', [
                'transactions' => [],
                'loadError'    => 'Could not load transactions. Please try again.',
            ]);
        }
    }

    public function create(): View
    {
        return view('Transactions.create');
    }

    public function store(CreateTransactionRequest $request): JsonResponse
    {
        Log::info('[TransactionController::store] Request received', ['payload' => $request->validated()]);

        try {
            $dto    = DTOCreateTransactionRequest::fromRequest($request, $this->authUserOid());
            $result = $this->service->create($dto);

            $this->writeAuditLog('transactions', 'created', null, $result->uuid, null, $result->toArray());

            Log::info('[TransactionController::store] Success', ['uuid' => $result->uuid]);

            return response()->json([
                'status'  => true,
                'message' => 'Transaction created successfully.',
                'data'    => $result->toArray(),
            ], 201);

        } catch (TransactionException $e) {
            $this->writeAuditLog('transactions', 'create_failed', null, null, null, null, $e->getMessage());
            Log::error('[TransactionController::store] Business error', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => $e->getMessage()], 422);

        } catch (\Throwable $e) {
            $this->writeAuditLog('transactions', 'create_failed', null, null, null, null, $e->getMessage());
            Log::error('[TransactionController::store] Unexpected error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['status' => false, 'message' => 'An unexpected error occurred. Please try again.'], 500);
        }
    }

    public function show(string $uuid): View|JsonResponse
    {
        Log::info('[TransactionController::show] Request received', ['uuid' => $uuid]);

        try {
            $transaction = $this->service->show($uuid);

            return view('Transactions.show', ['transaction' => $transaction->toArray()]);

        } catch (TransactionNotFoundException $e) {
            Log::warning('[TransactionController::show] Not found', ['uuid' => $uuid]);
            return response()->json(['status' => false, 'message' => 'Transaction not found.'], 404);

        } catch (\Throwable $e) {
            $this->writeAuditLog('transactions', 'show_failed', null, $uuid, null, null, $e->getMessage());
            Log::error('[TransactionController::show] Unexpected error', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'An unexpected error occurred. Please try again.'], 500);
        }
    }

    public function applyPayment(ApplyPaymentRequest $request): JsonResponse
    {
        Log::info('[TransactionController::applyPayment] Request received', [
            'member_oid' => $request->member_oid,
            'amount'     => $request->amount,
        ]);

        try {
            $dto    = DTOApplyPaymentRequest::fromRequest($request, $this->authUserOid());
            $result = $this->service->applyPayment($dto);

            $this->writeAuditLog('transactions', 'payment_applied', null, $result['transaction_uuid'], null, $result);

            Log::info('[TransactionController::applyPayment] Success', $result);

            return response()->json([
                'status'  => true,
                'message' => 'Payment applied successfully.',
                'data'    => $result,
            ]);

        } catch (InvalidTransactionAmountException $e) {
            Log::warning('[TransactionController::applyPayment] Invalid amount', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => $e->getMessage()], 422);

        } catch (\Throwable $e) {
            $this->writeAuditLog('transactions', 'payment_failed', null, null, null, null, $e->getMessage());
            Log::error('[TransactionController::applyPayment] Unexpected error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['status' => false, 'message' => 'An unexpected error occurred. Please try again.'], 500);
        }
    }

    public function destroy(string $uuid): JsonResponse
    {
        Log::info('[TransactionController::destroy] Request received', ['uuid' => $uuid]);

        try {
            $this->service->cancel($uuid, $this->authUserOid());

            $this->writeAuditLog('transactions', 'cancelled', null, $uuid, ['status' => true], ['status' => false]);

            Log::info('[TransactionController::destroy] Success', ['uuid' => $uuid]);

            return response()->json([
                'status'  => true,
                'message' => 'Transaction cancelled successfully.',
            ]);

        } catch (TransactionNotFoundException $e) {
            Log::warning('[TransactionController::destroy] Not found', ['uuid' => $uuid]);
            return response()->json(['status' => false, 'message' => 'Transaction not found.'], 404);

        } catch (TransactionAlreadyCancelledException $e) {
            Log::warning('[TransactionController::destroy] Already cancelled', ['uuid' => $uuid]);
            return response()->json(['status' => false, 'message' => 'Transaction is already cancelled.'], 409);

        } catch (\Throwable $e) {
            $this->writeAuditLog('transactions', 'cancel_failed', null, $uuid, null, null, $e->getMessage());
            Log::error('[TransactionController::destroy] Unexpected error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['status' => false, 'message' => 'An unexpected error occurred. Please try again.'], 500);
        }
    }
}

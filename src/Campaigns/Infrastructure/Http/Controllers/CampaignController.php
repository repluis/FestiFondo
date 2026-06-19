<?php

namespace Src\Campaigns\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Src\Campaigns\Application\DTOs\DTOCreateCampaignRequest;
use Src\Campaigns\Application\DTOs\DTOEnrollMembersRequest;
use Src\Campaigns\Application\DTOs\DTOUpdateCampaignRequest;
use Src\Campaigns\Application\Services\CampaignService;
use Src\Campaigns\Domain\Exceptions\CampaignAlreadyCancelledException;
use Src\Campaigns\Domain\Exceptions\CampaignException;
use Src\Campaigns\Domain\Exceptions\CampaignInvalidStatusTransitionException;
use Src\Campaigns\Domain\Exceptions\CampaignMemberAlreadyEnrolledException;
use Src\Campaigns\Domain\Exceptions\CampaignMemberException;
use Src\Campaigns\Domain\Exceptions\CampaignMemberNotFoundException;
use Src\Campaigns\Domain\Exceptions\CampaignNameAlreadyExistsException;
use Src\Campaigns\Domain\Exceptions\CampaignNotFoundException;
use Src\Campaigns\Infrastructure\Http\Requests\CreateCampaignRequest;
use Src\Campaigns\Infrastructure\Http\Requests\EnrollMembersRequest;
use Src\Campaigns\Infrastructure\Http\Requests\UpdateCampaignRequest;
use Src\Shared\Infrastructure\Http\Controllers\BaseController;

class CampaignController extends BaseController
{
    public function __construct(
        private readonly CampaignService $service,
    ) {}

    public function index(): View
    {
        Log::info('[CampaignController::index] Request received');

        try {
            $campaigns = array_map(
                fn($dto) => $dto->toArray(),
                $this->service->list(['status' => true]),
            );

            Log::info('[CampaignController::index] Success', ['count' => count($campaigns)]);

            return view('Campaigns.index', compact('campaigns'));

        } catch (\Throwable $e) {
            Log::error('[CampaignController::index] Error', ['error' => $e->getMessage()]);

            return view('Campaigns.index', [
                'campaigns' => [],
                'loadError' => 'Could not load campaigns. Please try again.',
            ]);
        }
    }

    public function create(): View
    {
        return view('Campaigns.create');
    }

    public function store(CreateCampaignRequest $request): JsonResponse
    {
        Log::info('[CampaignController::store] Request received', ['payload' => $request->validated()]);

        try {
            $dto    = DTOCreateCampaignRequest::fromRequest($request, $this->authUserOid());
            $result = $this->service->create($dto);

            $this->writeAuditLog('fund_raisings', 'created', null, $result->uuid, null, $result->toArray());
            Log::info('[CampaignController::store] Success', ['uuid' => $result->uuid]);

            return response()->json([
                'status'  => true,
                'message' => 'Campaign created successfully.',
                'data'    => $result->toArray(),
            ], 201);

        } catch (CampaignNameAlreadyExistsException $e) {
            Log::warning('[CampaignController::store] Name already exists', ['name' => $request->validated()['name'] ?? '']);
            return response()->json(['status' => false, 'message' => 'A campaign with this name already exists.'], 409);

        } catch (CampaignException $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 422);

        } catch (\Throwable $e) {
            Log::error('[CampaignController::store] Unexpected error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['status' => false, 'message' => 'An unexpected error occurred. Please try again.'], 500);
        }
    }

    public function show(string $uuid): View|JsonResponse
    {
        Log::info('[CampaignController::show] Request received', ['uuid' => $uuid]);

        try {
            $campaign      = $this->service->show($uuid);
            $members       = $this->service->listCampaignMembers($campaign->oid);
            $lastExecution = $this->service->getLastProcessExecution($campaign->uuid);

            Log::info('[CampaignController::show] Success', ['uuid' => $uuid, 'members' => count($members)]);

            return view('Campaigns.show', [
                'campaign'      => $campaign->toArray(),
                'members'       => $members,
                'lastExecution' => $lastExecution,
            ]);

        } catch (CampaignNotFoundException $e) {
            return response()->json(['status' => false, 'message' => 'Campaign not found.'], 404);

        } catch (\Throwable $e) {
            Log::error('[CampaignController::show] Error', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'An unexpected error occurred. Please try again.'], 500);
        }
    }

    public function edit(string $uuid): View|JsonResponse
    {
        Log::info('[CampaignController::edit] Request received', ['uuid' => $uuid]);

        try {
            $campaign = $this->service->show($uuid);

            return view('Campaigns.edit', ['campaign' => $campaign->toArray()]);

        } catch (CampaignNotFoundException $e) {
            return response()->json(['status' => false, 'message' => 'Campaign not found.'], 404);

        } catch (\Throwable $e) {
            Log::error('[CampaignController::edit] Error', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'An unexpected error occurred. Please try again.'], 500);
        }
    }

    public function update(UpdateCampaignRequest $request, string $uuid): JsonResponse
    {
        Log::info('[CampaignController::update] Request received', ['uuid' => $uuid]);

        try {
            $previous = $this->service->show($uuid);
            $dto      = DTOUpdateCampaignRequest::fromRequest($request, $uuid, $this->authUserOid());
            $result   = $this->service->update($dto);

            $this->writeAuditLog('fund_raisings', 'updated', null, $uuid, $previous->toArray(), $result->toArray());
            Log::info('[CampaignController::update] Success', ['uuid' => $uuid]);

            return response()->json([
                'status'  => true,
                'message' => 'Campaign updated successfully.',
                'data'    => $result->toArray(),
            ]);

        } catch (CampaignNotFoundException $e) {
            return response()->json(['status' => false, 'message' => 'Campaign not found.'], 404);

        } catch (CampaignAlreadyCancelledException $e) {
            return response()->json(['status' => false, 'message' => 'Cannot update a cancelled campaign.'], 409);

        } catch (CampaignInvalidStatusTransitionException $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 422);

        } catch (CampaignNameAlreadyExistsException $e) {
            Log::warning('[CampaignController::update] Name already exists', ['uuid' => $uuid]);
            return response()->json(['status' => false, 'message' => 'A campaign with this name already exists.'], 409);

        } catch (CampaignException $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 422);

        } catch (\Throwable $e) {
            Log::error('[CampaignController::update] Error', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'An unexpected error occurred. Please try again.'], 500);
        }
    }

    public function destroy(string $uuid): JsonResponse
    {
        Log::info('[CampaignController::destroy] Request received', ['uuid' => $uuid]);

        try {
            $this->service->cancel($uuid, $this->authUserOid());

            $this->writeAuditLog('fund_raisings', 'cancelled', null, $uuid, null, ['campaign_status' => 'cancelled']);
            Log::info('[CampaignController::destroy] Success', ['uuid' => $uuid]);

            return response()->json([
                'status'  => true,
                'message' => 'Campaign cancelled successfully.',
            ]);

        } catch (CampaignNotFoundException $e) {
            return response()->json(['status' => false, 'message' => 'Campaign not found.'], 404);

        } catch (CampaignAlreadyCancelledException $e) {
            return response()->json(['status' => false, 'message' => 'Campaign is already cancelled.'], 409);

        } catch (CampaignException $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 422);

        } catch (\Throwable $e) {
            Log::error('[CampaignController::destroy] Error', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'An unexpected error occurred. Please try again.'], 500);
        }
    }

    public function enrollMembers(EnrollMembersRequest $request, string $uuid): JsonResponse
    {
        Log::info('[CampaignController::enrollMembers] Request received', ['uuid' => $uuid]);

        try {
            $campaign = $this->service->show($uuid);
            $dto      = new DTOEnrollMembersRequest(
                campaignOid:  $campaign->oid,
                memberOids:   array_map('intval', $request->member_oids),
                createdByOid: $this->authUserOid(),
            );
            $enrolled = $this->service->enrollMembers($dto);

            $this->writeAuditLog('campaign_members', 'enrolled', null, $uuid, null, ['count' => count($enrolled)]);
            Log::info('[CampaignController::enrollMembers] Success', ['count' => count($enrolled)]);

            return response()->json([
                'status'  => true,
                'message' => count($enrolled) . ' member(s) enrolled successfully.',
                'data'    => array_map(fn($m) => $m->toArray(), $enrolled),
            ]);

        } catch (CampaignNotFoundException $e) {
            return response()->json(['status' => false, 'message' => 'Campaign not found.'], 404);

        } catch (CampaignMemberAlreadyEnrolledException $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 409);

        } catch (CampaignMemberException $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 422);

        } catch (\Throwable $e) {
            Log::error('[CampaignController::enrollMembers] Error', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'An unexpected error occurred. Please try again.'], 500);
        }
    }

    public function removeMember(string $uuid, string $memberUuid): JsonResponse
    {
        Log::info('[CampaignController::removeMember] Request received', [
            'campaign_uuid' => $uuid,
            'member_uuid'   => $memberUuid,
        ]);

        try {
            $this->service->removeMember($memberUuid, $this->authUserOid());

            $this->writeAuditLog('campaign_members', 'removed', null, $memberUuid, null, ['status' => false]);
            Log::info('[CampaignController::removeMember] Success');

            return response()->json([
                'status'  => true,
                'message' => 'Member removed from campaign successfully.',
            ]);

        } catch (CampaignMemberNotFoundException $e) {
            return response()->json(['status' => false, 'message' => 'Campaign member not found.'], 404);

        } catch (\Throwable $e) {
            Log::error('[CampaignController::removeMember] Error', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'An unexpected error occurred. Please try again.'], 500);
        }
    }

    public function availableMembers(string $uuid): JsonResponse
    {
        Log::info('[CampaignController::availableMembers] Request received', ['uuid' => $uuid]);

        try {
            $campaign = $this->service->show($uuid);
            $members  = $this->service->availableMembers($campaign->oid);

            Log::info('[CampaignController::availableMembers] Success', ['count' => count($members)]);

            return response()->json(['status' => true, 'data' => $members]);

        } catch (\Throwable $e) {
            Log::error('[CampaignController::availableMembers] Error', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Could not load members.'], 500);
        }
    }

    public function memberTransactions(string $uuid, int $memberOid): JsonResponse
    {
        Log::info('[CampaignController::memberTransactions] Request received', [
            'uuid'       => $uuid,
            'member_oid' => $memberOid,
        ]);

        try {
            $campaign     = $this->service->show($uuid);
            $transactions = $this->service->memberTransactions($campaign->oid, $memberOid);

            Log::info('[CampaignController::memberTransactions] Success', ['count' => count($transactions)]);

            return response()->json(['status' => true, 'data' => $transactions]);

        } catch (\Throwable $e) {
            Log::error('[CampaignController::memberTransactions] Error', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Could not load transactions.'], 500);
        }
    }
}

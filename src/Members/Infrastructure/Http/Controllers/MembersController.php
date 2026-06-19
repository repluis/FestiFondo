<?php

namespace Src\Members\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Src\Members\Application\DTOs\DTOCreateMembersRequest;
use Src\Members\Application\DTOs\DTOUpdateMembersRequest;
use Src\Members\Application\Services\MembersService;
use Src\Members\Domain\Exceptions\MemberAlreadyActiveException;
use Src\Members\Domain\Exceptions\MemberAlreadyInactiveException;
use Src\Members\Domain\Exceptions\MemberEmailAlreadyExistsException;
use Src\Members\Domain\Exceptions\MemberIdentificationAlreadyExistsException;
use Src\Members\Domain\Exceptions\MemberNotFoundException;
use Src\Members\Domain\Exceptions\MembersException;
use Src\Members\Infrastructure\Http\Requests\CreateMembersRequest;
use Src\Members\Infrastructure\Http\Requests\UpdateMembersRequest;
use Src\Shared\Infrastructure\Http\Controllers\BaseController;

class MembersController extends BaseController
{
    public function __construct(
        private readonly MembersService $service,
    ) {}

    public function index(): View
    {
        Log::info('[MembersController::index] Request received', [
            'user_oid' => $this->authUserOid(),
            'ip'       => request()->ip(),
        ]);

        try {
            $members = array_map(
                fn($dto) => $dto->toArray(),
                $this->service->list([]),
            );

            Log::info('[MembersController::index] Success', ['total' => count($members)]);

            return view('Members.index', compact('members'));

        } catch (\Throwable $e) {
            $this->writeAuditLog('members', 'list_failed', null, null, null, null, $e->getMessage());
            Log::error('[MembersController::index] Unexpected error', ['error' => $e->getMessage()]);

            return view('Members.index', ['members' => [], 'loadError' => 'Could not load members. Please try again.']);
        }
    }

    public function create(): View
    {
        return view('Members.create');
    }

    public function store(CreateMembersRequest $request): JsonResponse
    {
        Log::info('[MembersController::store] Request received', [
            'user_oid' => $this->authUserOid(),
            'payload'  => $request->validated(),
        ]);

        try {
            $dto    = DTOCreateMembersRequest::fromRequest($request, $this->authUserOid());
            $result = $this->service->create($dto);

            $this->writeAuditLog('members', 'created', null, $result->uuid, null, $result->toArray());

            Log::info('[MembersController::store] Success', ['uuid' => $result->uuid]);

            return response()->json([
                'status'  => true,
                'message' => 'Member created successfully.',
                'data'    => $result->toArray(),
            ], 201);

        } catch (MemberIdentificationAlreadyExistsException $e) {
            $this->writeAuditLog('members', 'create_failed', null, null, null, ['identification' => $request->identification], $e->getMessage());
            Log::warning('[MembersController::store] Identification conflict', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'This identification is already registered.'], 409);

        } catch (MemberEmailAlreadyExistsException $e) {
            $this->writeAuditLog('members', 'create_failed', null, null, null, ['email' => $request->email], $e->getMessage());
            Log::warning('[MembersController::store] Email conflict', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'The email address is already registered.'], 409);

        } catch (MembersException $e) {
            $this->writeAuditLog('members', 'create_failed', null, null, null, null, $e->getMessage());
            Log::error('[MembersController::store] Business error', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => $e->getMessage()], 422);

        } catch (\Throwable $e) {
            $this->writeAuditLog('members', 'create_failed', null, null, null, null, $e->getMessage());
            Log::error('[MembersController::store] Unexpected error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['status' => false, 'message' => 'An unexpected error occurred. Please try again.'], 500);
        }
    }

    public function show(string $uuid): View|JsonResponse
    {
        Log::info('[MembersController::show] Request received', [
            'user_oid' => $this->authUserOid(),
            'uuid'     => $uuid,
        ]);

        try {
            $member = $this->service->show($uuid);

            Log::info('[MembersController::show] Success', ['uuid' => $uuid]);

            return view('Members.show', ['member' => $member->toArray()]);

        } catch (MemberNotFoundException $e) {
            Log::warning('[MembersController::show] Not found', ['uuid' => $uuid]);
            return response()->json(['status' => false, 'message' => 'Member not found.'], 404);

        } catch (\Throwable $e) {
            $this->writeAuditLog('members', 'show_failed', null, $uuid, null, null, $e->getMessage());
            Log::error('[MembersController::show] Unexpected error', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'An unexpected error occurred. Please try again.'], 500);
        }
    }

    public function edit(string $uuid): View|JsonResponse
    {
        Log::info('[MembersController::edit] Request received', ['uuid' => $uuid]);

        try {
            $member = $this->service->show($uuid);

            return view('Members.edit', ['member' => $member->toArray()]);

        } catch (MemberNotFoundException $e) {
            Log::warning('[MembersController::edit] Not found', ['uuid' => $uuid]);
            return response()->json(['status' => false, 'message' => 'Member not found.'], 404);

        } catch (\Throwable $e) {
            $this->writeAuditLog('members', 'edit_failed', null, $uuid, null, null, $e->getMessage());
            Log::error('[MembersController::edit] Unexpected error', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'An unexpected error occurred. Please try again.'], 500);
        }
    }

    public function update(UpdateMembersRequest $request, string $uuid): JsonResponse
    {
        Log::info('[MembersController::update] Request received', [
            'user_oid' => $this->authUserOid(),
            'uuid'     => $uuid,
            'payload'  => $request->validated(),
        ]);

        try {
            $previous = $this->service->show($uuid);
            $dto      = DTOUpdateMembersRequest::fromRequest($request, $uuid, $this->authUserOid());
            $result   = $this->service->update($dto);

            $this->writeAuditLog('members', 'updated', null, $uuid, $previous->toArray(), $result->toArray());

            Log::info('[MembersController::update] Success', ['uuid' => $uuid]);

            return response()->json([
                'status'  => true,
                'message' => 'Member updated successfully.',
                'data'    => $result->toArray(),
            ]);

        } catch (MemberNotFoundException $e) {
            Log::warning('[MembersController::update] Not found', ['uuid' => $uuid]);
            return response()->json(['status' => false, 'message' => 'Member not found.'], 404);

        } catch (MemberEmailAlreadyExistsException $e) {
            $this->writeAuditLog('members', 'update_failed', null, $uuid, null, null, $e->getMessage());
            Log::warning('[MembersController::update] Email conflict', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'The email address is already registered by another member.'], 409);

        } catch (MembersException $e) {
            $this->writeAuditLog('members', 'update_failed', null, $uuid, null, null, $e->getMessage());
            Log::error('[MembersController::update] Business error', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => $e->getMessage()], 422);

        } catch (\Throwable $e) {
            $this->writeAuditLog('members', 'update_failed', null, $uuid, null, null, $e->getMessage());
            Log::error('[MembersController::update] Unexpected error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['status' => false, 'message' => 'An unexpected error occurred. Please try again.'], 500);
        }
    }

    public function destroy(string $uuid): JsonResponse
    {
        Log::info('[MembersController::destroy] Request received', [
            'user_oid' => $this->authUserOid(),
            'uuid'     => $uuid,
        ]);

        try {
            $this->service->cancel($uuid, $this->authUserOid());

            $this->writeAuditLog('members', 'deactivated', null, $uuid, ['status' => true], ['status' => false]);

            Log::info('[MembersController::destroy] Success', ['uuid' => $uuid]);

            return response()->json([
                'status'  => true,
                'message' => 'Member deactivated successfully.',
            ]);

        } catch (MemberNotFoundException $e) {
            Log::warning('[MembersController::destroy] Not found', ['uuid' => $uuid]);
            return response()->json(['status' => false, 'message' => 'Member not found.'], 404);

        } catch (MemberAlreadyInactiveException $e) {
            Log::warning('[MembersController::destroy] Already inactive', ['uuid' => $uuid]);
            return response()->json(['status' => false, 'message' => 'Member is already inactive.'], 409);

        } catch (MembersException $e) {
            $this->writeAuditLog('members', 'deactivate_failed', null, $uuid, null, null, $e->getMessage());
            Log::error('[MembersController::destroy] Business error', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => $e->getMessage()], 422);

        } catch (\Throwable $e) {
            $this->writeAuditLog('members', 'deactivate_failed', null, $uuid, null, null, $e->getMessage());
            Log::error('[MembersController::destroy] Unexpected error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['status' => false, 'message' => 'An unexpected error occurred. Please try again.'], 500);
        }
    }

    public function activate(string $uuid): JsonResponse
    {
        Log::info('[MembersController::activate] Request received', [
            'user_oid' => $this->authUserOid(),
            'uuid'     => $uuid,
        ]);

        try {
            $this->service->activate($uuid, $this->authUserOid());

            $this->writeAuditLog('members', 'activated', null, $uuid, ['status' => false], ['status' => true]);

            Log::info('[MembersController::activate] Success', ['uuid' => $uuid]);

            return response()->json([
                'status'  => true,
                'message' => 'Member activated successfully.',
            ]);

        } catch (MemberNotFoundException $e) {
            Log::warning('[MembersController::activate] Not found', ['uuid' => $uuid]);
            return response()->json(['status' => false, 'message' => 'Member not found.'], 404);

        } catch (MemberAlreadyActiveException $e) {
            Log::warning('[MembersController::activate] Already active', ['uuid' => $uuid]);
            return response()->json(['status' => false, 'message' => 'Member is already active.'], 409);

        } catch (MembersException $e) {
            $this->writeAuditLog('members', 'activate_failed', null, $uuid, null, null, $e->getMessage());
            Log::error('[MembersController::activate] Business error', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => $e->getMessage()], 422);

        } catch (\Throwable $e) {
            $this->writeAuditLog('members', 'activate_failed', null, $uuid, null, null, $e->getMessage());
            Log::error('[MembersController::activate] Unexpected error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['status' => false, 'message' => 'An unexpected error occurred. Please try again.'], 500);
        }
    }
}

<?php

namespace Src\Shared\Infrastructure\Http\Controllers;

use App\Models\User;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

abstract class BaseController extends Controller
{
    protected function authUserOid(): int
    {
        /** @var User|null $user */
        $user = Auth::user();
        return $user?->oid ?? 0;
    }

    /**
     * Writes an entry to audit_logs. Always silent on failure
     * so an audit error never breaks the main HTTP response.
     */
    protected function writeAuditLog(
        string  $auditableType,
        string  $event,
        ?int    $auditableOid,
        ?string $auditableUuid,
        ?array  $oldValues,
        ?array  $newValues,
        ?string $reason = null,
    ): void {
        try {
            DB::table('audit_logs')->insert([
                'auditable_type' => $auditableType,
                'auditable_oid'  => $auditableOid ?? 0,
                'auditable_uuid' => $auditableUuid,
                'event'          => $event,
                'old_values'     => $oldValues ? json_encode($oldValues) : null,
                'new_values'     => $newValues ? json_encode($newValues) : null,
                'user_oid'       => $this->authUserOid(),
                'user_email'     => Auth::user()?->email,
                'ip_address'     => request()->ip(),
                'user_agent'     => request()->userAgent(),
                'reason'         => $reason,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('[BaseController::writeAuditLog] Failed to write audit entry', [
                'error'           => $e->getMessage(),
                'auditable_type'  => $auditableType,
                'event'           => $event,
            ]);
        }
    }
}

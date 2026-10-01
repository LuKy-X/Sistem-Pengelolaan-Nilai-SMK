<?php

namespace App\Http\Controllers\BK\Concerns;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait RecordsAuditTrail
{
    /**
     * Catat jejak audit untuk aksi kritis Guru BK (izin keluar, disiplin, surat peringatan).
     *
     * @param  array<string, mixed>  $newValues
     * @param  array<string, mixed>  $oldValues
     */
    protected function audit(string $action, Model $auditable, array $newValues = [], array $oldValues = []): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'auditable_type' => $auditable->getMorphClass(),
            'auditable_id' => $auditable->getKey(),
            'old_values' => $oldValues === [] ? null : $oldValues,
            'new_values' => $newValues === [] ? null : $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 250),
        ]);
    }
}

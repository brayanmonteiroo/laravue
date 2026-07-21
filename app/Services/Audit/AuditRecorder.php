<?php

declare(strict_types=1);

namespace App\Services\Audit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use OwenIt\Auditing\Models\Audit;

class AuditRecorder
{
    /**
     * Registra um audit.
     *
     * @param  array<string, mixed>  $oldValues
     * @param  array<string, mixed>  $newValues
     */
    public function record(
        Model $auditable,
        string $event,
        array $oldValues = [],
        array $newValues = [],
    ): Audit {
        $user = auth()->user();

        return Audit::query()->create([
            'user_type' => $user !== null ? $user::class : null,
            'user_id' => $user?->getKey(),
            'event' => $event,
            'auditable_type' => $auditable::class,
            'auditable_id' => $auditable->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'url' => Request::fullUrl(),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}

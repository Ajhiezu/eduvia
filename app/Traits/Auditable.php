<?php

namespace App\Traits;

use App\Models\AuditLog;

trait Auditable
{
    /**
     * Boot the trait to hook into model events and auto-generate audit logs.
     */
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            AuditLog::record(
                action: 'created',
                auditable: $model,
                newValues: $model->getAttributes(),
                description: 'Entitas baru berhasil dibuat.',
                schoolId: $model->school_id ?? null
            );
        });

        static::updated(function ($model) {
            $changes = $model->getChanges();
            $original = array_intersect_key($model->getOriginal(), $changes);

            // Don't log if only timestamps changed
            unset($changes['updated_at'], $original['updated_at']);
            if (empty($changes)) {
                return;
            }

            AuditLog::record(
                action: 'updated',
                auditable: $model,
                oldValues: $original,
                newValues: $changes,
                description: 'Pembaruan atribut entitas.',
                schoolId: $model->school_id ?? null
            );
        });

        static::deleted(function ($model) {
            AuditLog::record(
                action: 'deleted',
                auditable: $model,
                oldValues: $model->getOriginal(),
                description: 'Entitas dihapus dari sistem.',
                schoolId: $model->school_id ?? null
            );
        });
    }
}

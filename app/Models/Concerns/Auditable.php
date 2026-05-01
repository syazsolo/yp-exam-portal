<?php

namespace App\Models\Concerns;

use App\Models\AuditLog;

/**
 * @method static void created(\Closure $callback)
 * @method static void deleted(\Closure $callback)
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function (self $model) {
            AuditLog::record('created', $model);
        });

        static::deleted(function (self $model) {
            AuditLog::record('deleted', $model);
        });
    }
}

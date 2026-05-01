<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'event',
        'auditable_type',
        'auditable_id',
        'causer_id',
        'subject_id',
        'metadata',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function causer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'causer_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subject_id');
    }

    public static function record(
        string $event,
        Model $auditable,
        ?User $causer = null,
        ?User $subject = null,
        array $metadata = [],
    ): self {
        return static::create([
            'event' => $event,
            'auditable_type' => get_class($auditable),
            'auditable_id' => (string) $auditable->getKey(),
            'causer_id' => $causer?->id ?? Auth::id(),
            'subject_id' => $subject?->id,
            'metadata' => $metadata ?: null,
            'created_at' => now(),
        ]);
    }
}

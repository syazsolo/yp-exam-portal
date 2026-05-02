<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\GeneratesStringId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SchoolClass extends Model
{
    use Auditable, GeneratesStringId, HasFactory, SoftDeletes;

    protected $table = 'school_classes';

    public $incrementing = false;

    protected $keyType = 'string';

    protected static string $stringIdPrefix = 'CLS';

    protected $fillable = ['id', 'name', 'created_by'];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'class_subject', 'class_id', 'subject_id');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'class_user', 'class_id', 'user_id')
            ->using(ClassUser::class)
            ->withPivot(['assigned_at', 'unassigned_at']);
    }
}

<?php

namespace App\Models;

use App\Models\Concerns\GeneratesStringId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use GeneratesStringId, HasFactory, SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected static string $stringIdPrefix = 'SUB';

    protected $fillable = ['id', 'name', 'description', 'created_by'];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'class_subject', 'subject_id', 'class_id');
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class);
    }
}

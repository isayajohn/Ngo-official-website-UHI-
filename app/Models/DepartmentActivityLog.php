<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DepartmentActivityLog extends Model
{
    protected $fillable = ['department_id', 'user_id', 'event', 'subject_type', 'subject_id', 'properties'];
    protected function casts(): array { return ['properties' => 'array']; }
    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function subject(): MorphTo { return $this->morphTo(); }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepartmentTaskComment extends Model
{
    protected $fillable = ['task_id', 'user_id', 'comment'];
    public function task(): BelongsTo { return $this->belongsTo(DepartmentTask::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}

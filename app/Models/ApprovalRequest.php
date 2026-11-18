<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ApprovalRequest extends Model
{
    protected $fillable = ['workflow_id', 'approvable_type', 'approvable_id', 'department_id', 'requested_by_user_id', 'current_step', 'status', 'submitted_at', 'completed_at'];
    protected function casts(): array { return ['submitted_at' => 'datetime', 'completed_at' => 'datetime']; }
    public function workflow(): BelongsTo { return $this->belongsTo(ApprovalWorkflow::class, 'workflow_id'); }
    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function requestedBy(): BelongsTo { return $this->belongsTo(User::class, 'requested_by_user_id'); }
    public function approvable(): MorphTo { return $this->morphTo(); }
    public function actions(): HasMany { return $this->hasMany(ApprovalAction::class)->orderBy('created_at'); }
    public function currentStep(): BelongsTo { return $this->belongsTo(ApprovalWorkflowStep::class, 'workflow_id', 'workflow_id')->where('step_order', $this->current_step); }
}

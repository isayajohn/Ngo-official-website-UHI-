<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalWorkflowStep extends Model
{
    protected $fillable = ['workflow_id', 'step_order', 'name', 'approver_type', 'required_permission', 'required_leadership_role', 'specific_user_id', 'department_scoped', 'can_return', 'can_reject'];
    protected function casts(): array { return ['department_scoped' => 'boolean', 'can_return' => 'boolean', 'can_reject' => 'boolean']; }
    public function workflow(): BelongsTo { return $this->belongsTo(ApprovalWorkflow::class, 'workflow_id'); }
}

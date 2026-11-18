<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApprovalWorkflow extends Model
{
    protected $fillable = ['name', 'code', 'module', 'description', 'active'];
    protected function casts(): array { return ['active' => 'boolean']; }
    public function steps(): HasMany { return $this->hasMany(ApprovalWorkflowStep::class, 'workflow_id')->orderBy('step_order'); }
}

<?php

namespace App\Models;

use App\Models\Concerns\EnforcesProjectManagement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectMilestone extends Model
{
    use EnforcesProjectManagement;
    protected $fillable = ['project_id', 'title', 'description', 'due_date', 'completed_at', 'status', 'responsible_user_id'];

    protected function casts(): array
    {
        return ['due_date' => 'date', 'completed_at' => 'datetime'];
    }

    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function responsibleUser(): BelongsTo { return $this->belongsTo(User::class, 'responsible_user_id'); }
}

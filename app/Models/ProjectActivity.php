<?php

namespace App\Models;

use App\Models\Concerns\EnforcesProjectManagement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class ProjectActivity extends Model
{
    use EnforcesProjectManagement;
    protected $fillable = ['project_id', 'department_id', 'title', 'description', 'activity_type', 'start_date', 'end_date', 'status', 'responsible_team_member_id', 'budget_line_id', 'location', 'target_participants', 'actual_participants', 'completion_percentage'];

    protected function casts(): array
    {
        return ['start_date' => 'date', 'end_date' => 'date', 'target_participants' => 'integer', 'actual_participants' => 'integer', 'completion_percentage' => 'integer'];
    }

    protected static function booted(): void
    {
        static::saving(function (ProjectActivity $activity): void {
            if ($activity->completion_percentage < 0 || $activity->completion_percentage > 100) {
                throw ValidationException::withMessages(['completion_percentage' => 'Completion must be between 0 and 100 percent.']);
            }
            if ($activity->start_date && $activity->end_date && $activity->end_date->lt($activity->start_date)) {
                throw ValidationException::withMessages(['end_date' => 'The activity end date must be on or after its start date.']);
            }
        });
    }

    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function responsibleTeamMember(): BelongsTo { return $this->belongsTo(TeamMember::class, 'responsible_team_member_id'); }
}

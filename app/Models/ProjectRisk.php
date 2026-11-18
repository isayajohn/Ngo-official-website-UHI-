<?php

namespace App\Models;

use App\Models\Concerns\EnforcesProjectManagement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class ProjectRisk extends Model
{
    use EnforcesProjectManagement;
    protected $fillable = ['project_id', 'title', 'description', 'likelihood', 'impact', 'risk_level', 'mitigation', 'owner_user_id', 'status'];

    protected function casts(): array
    {
        return ['likelihood' => 'integer', 'impact' => 'integer'];
    }

    protected static function booted(): void
    {
        static::saving(function (ProjectRisk $risk): void {
            if ($risk->likelihood < 1 || $risk->likelihood > 5 || $risk->impact < 1 || $risk->impact > 5) {
                throw ValidationException::withMessages(['likelihood' => 'Likelihood and impact must each be between 1 and 5.']);
            }
            $score = (int) $risk->likelihood * (int) $risk->impact;
            $risk->risk_level = match (true) {
                $score >= 16 => 'CRITICAL',
                $score >= 10 => 'HIGH',
                $score >= 5 => 'MEDIUM',
                default => 'LOW',
            };
        });
    }

    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function owner(): BelongsTo { return $this->belongsTo(User::class, 'owner_user_id'); }
}

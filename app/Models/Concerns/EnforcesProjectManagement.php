<?php

namespace App\Models\Concerns;

use App\Models\Project;
use App\Services\DepartmentAccessService;
use Illuminate\Support\Facades\Auth;

trait EnforcesProjectManagement
{
    protected static function bootEnforcesProjectManagement(): void
    {
        static::saving(function ($record): void {
            $user = Auth::user();
            if (! $user) return;
            $projectId = $record->project_id ?: $record->project?->getKey();
            $project = $projectId ? Project::find($projectId) : null;
            abort_unless($project && app(DepartmentAccessService::class)->canManageProject($user, $project), 403);
        });

        static::deleting(function ($record): void {
            $user = Auth::user();
            if (! $user) return;
            abort_unless($record->project && app(DepartmentAccessService::class)->canManageProject($user, $record->project), 403);
        });
    }
}

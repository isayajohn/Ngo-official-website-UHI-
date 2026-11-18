<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Validation\ValidationException;

class DepartmentProject extends Pivot
{
    protected $table = 'department_project';
    public $incrementing = true;
    protected $fillable = ['department_id', 'project_id', 'is_lead'];
    protected function casts(): array { return ['is_lead' => 'boolean']; }

    protected static function booted(): void
    {
        static::saving(function (DepartmentProject $pivot) {
            if ($pivot->is_lead && static::query()->where('project_id', $pivot->project_id)->where('is_lead', true)->where('department_id', '!=', $pivot->department_id)->exists()) {
                throw ValidationException::withMessages(['departments' => 'A project can have only one lead department.']);
            }
        });
    }
}

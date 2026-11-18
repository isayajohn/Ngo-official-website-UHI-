<?php

namespace App\Services;

use App\Models\DepartmentActivityLog;
use Illuminate\Database\Eloquent\Model;

class DepartmentActivity
{
    public static function record(?int $departmentId, ?int $userId, string $event, ?Model $subject = null, array $properties = []): void
    {
        DepartmentActivityLog::create([
            'department_id' => $departmentId,
            'user_id' => $userId,
            'event' => $event,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'properties' => $properties,
        ]);
    }
}

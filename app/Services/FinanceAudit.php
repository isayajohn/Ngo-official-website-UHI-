<?php

namespace App\Services;

use App\Models\FinanceAuditLog;
use Illuminate\Database\Eloquent\Model;

class FinanceAudit
{
    public static function record(Model $subject, string $action, array $properties = []): void
    {
        FinanceAuditLog::create([
            'user_id' => auth()->id(),
            'event' => class_basename($subject).'.'.$action,
            'subject_type' => $subject->getMorphClass(),
            'subject_id' => $subject->getKey(),
            'properties' => $properties,
        ]);
    }
}

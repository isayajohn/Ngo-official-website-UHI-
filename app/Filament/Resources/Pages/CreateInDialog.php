<?php

namespace App\Filament\Resources\Pages;

use Filament\Resources\Pages\CreateRecord;

/** Shared create page for resources that also expose a modal create action on their list. */
abstract class CreateInDialog extends CreateRecord
{
    protected function afterCreate(): void
    {
        if ($this->getRecord() instanceof \App\Models\Project && (auth()->user()->can('department.assign_project') || auth()->user()->can('organization.full_access'))) {
            app(\App\Services\ProjectDepartmentService::class)->sync(
                $this->getRecord(),
                auth()->user(),
                filled($this->data['lead_department_id'] ?? null) ? (int) $this->data['lead_department_id'] : null,
                (array) ($this->data['supporting_department_ids'] ?? []),
            );
        }
    }
}

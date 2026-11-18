<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditProject extends EditRecord
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make(), RestoreAction::make()];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! auth()->user()->can('project.publish')) {
            $data['is_published'] = false;
            $data['published_at'] = null;
        }

        return $data;
    }

    protected function afterSave(): void
    {
        if (auth()->user()->can('department.assign_project') || auth()->user()->can('organization.full_access')) {
            app(\App\Services\ProjectDepartmentService::class)->sync(
                $this->getRecord(),
                auth()->user(),
                filled($this->data['lead_department_id'] ?? null) ? (int) $this->data['lead_department_id'] : null,
                (array) ($this->data['supporting_department_ids'] ?? []),
            );
        }
    }
}

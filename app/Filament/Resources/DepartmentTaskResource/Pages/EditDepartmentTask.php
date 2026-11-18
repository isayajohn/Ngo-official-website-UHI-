<?php
namespace App\Filament\Resources\DepartmentTaskResource\Pages;
use App\Filament\Resources\DepartmentTaskResource;
use Filament\Resources\Pages\EditRecord;
class EditDepartmentTask extends EditRecord
{
    protected static string $resource = DepartmentTaskResource::class;
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $access = app(\App\Services\DepartmentAccessService::class);
        $targetDepartment = \App\Models\Department::findOrFail($data['department_id']);
        abort_unless($access->canAssignTask(auth()->user(), $this->record->department) && $access->canAssignTask(auth()->user(), $targetDepartment), 403);
        return $data;
    }

    protected function afterSave(): void
    {
        app(\App\Services\DepartmentTaskAttachmentService::class)->store($this->record, (array) ($this->data['attachments_upload'] ?? []), auth()->id());
    }
}

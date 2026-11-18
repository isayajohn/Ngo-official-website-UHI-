<?php
namespace App\Filament\Resources\DepartmentTaskResource\Pages;
use App\Filament\Resources\DepartmentTaskResource;
use App\Filament\Resources\Pages\CreateInDialog;
class CreateDepartmentTask extends CreateInDialog
{
    protected static string $resource = DepartmentTaskResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $department = \App\Models\Department::findOrFail($data['department_id']);
        abort_unless(app(\App\Services\DepartmentAccessService::class)->canAssignTask(auth()->user(), $department), 403);
        $data['assigned_by_user_id'] = auth()->id();
        return $data;
    }
    protected function afterCreate(): void { app(\App\Services\DepartmentTaskAttachmentService::class)->store($this->record, (array) ($this->data['attachments_upload'] ?? []), auth()->id()); }
}

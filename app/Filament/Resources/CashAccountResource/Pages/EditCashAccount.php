<?php
namespace App\Filament\Resources\CashAccountResource\Pages;
use App\Filament\Resources\CashAccountResource;
use App\Models\Department;
use Filament\Resources\Pages\EditRecord;
class EditCashAccount extends EditRecord
{
    protected static string $resource = CashAccountResource::class;
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! empty($data['department_id']) && ! auth()->user()->can('organization.full_access')) abort_unless(app(\App\Services\DepartmentAccessService::class)->canManageDepartment(auth()->user(), Department::findOrFail($data['department_id']), 'finance.cash.manage'), 403);
        return $data;
    }
}

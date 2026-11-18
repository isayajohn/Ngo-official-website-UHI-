<?php
namespace App\Filament\Resources\CashAccountResource\Pages;
use App\Filament\Resources\CashAccountResource;
use App\Filament\Resources\Pages\CreateInDialog;
use App\Models\Department;
class CreateCashAccount extends CreateInDialog
{
    protected static string $resource = CashAccountResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! empty($data['department_id']) && ! auth()->user()->can('organization.full_access')) abort_unless(app(\App\Services\DepartmentAccessService::class)->canManageDepartment(auth()->user(), Department::findOrFail($data['department_id']), 'finance.cash.manage'), 403);
        return $data;
    }
}

<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\TeamMember;
use App\Services\DepartmentActivity;
use Illuminate\Support\Facades\DB;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected array $departmentAccess = [];
    protected ?int $primaryDepartmentId = null;
    protected array $beforeChanges = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $user = $this->getRecord();
        $active = $user->departments()->wherePivot('active', true)->pluck('departments.id')->all();
        $primary = $user->departments()->wherePivot('active', true)->wherePivot('is_primary', true)->value('departments.id');
        $data['primary_department_id'] = $primary;
        $data['additional_department_ids'] = array_values(array_diff($active, [$primary]));

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->primaryDepartmentId = filled($data['primary_department_id'] ?? null) ? (int) $data['primary_department_id'] : null;
        $this->departmentAccess = collect([$this->primaryDepartmentId, ...((array) ($data['additional_department_ids'] ?? []))])->filter()->map(fn ($id) => (int) $id)->unique()->values()->all();
        unset($data['primary_department_id'], $data['additional_department_ids'], $data['team_member_id_display']);

        $record = $this->getRecord();
        $this->beforeChanges = [
            'status' => $record->status,
            'team_member_id' => $record->team_member_id,
            'department_ids' => $record->departments()->wherePivot('active', true)->pluck('departments.id')->all(),
            'roles' => $record->roles()->pluck('name')->all(),
            'changed_fields' => array_values(array_diff(array_keys($data), ['password', 'password_confirmation', 'primary_department_id', 'additional_department_ids', 'team_member_id_display'])),
        ];
        if ($record->is(auth()->user())) {
            unset($data['status'], $data['is_active'], $data['roles'], $data['team_member_id']);
        }
        if (! auth()->user()->can('user.assign_department') && ! auth()->user()->can('organization.full_access')) {
            $this->departmentAccess = $this->beforeChanges['department_ids'];
            $this->primaryDepartmentId = $record->departments()->wherePivot('active', true)->wherePivot('is_primary', true)->value('departments.id');
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $user = $this->getRecord();
        DB::transaction(function () use ($user): void {
            if (auth()->user()->can('user.assign_department') || auth()->user()->can('organization.full_access')) {
                $ids = collect($this->departmentAccess);
                $existing = $user->departments()->get()->keyBy('id');
                $user->departments()->sync($ids->mapWithKeys(fn ($id) => [$id => ['is_primary' => $id === $this->primaryDepartmentId, 'active' => true, 'department_role' => $existing->get($id)?->pivot?->department_role]])->all());
            }

            if ($this->beforeChanges['team_member_id'] && $this->beforeChanges['team_member_id'] !== $user->team_member_id) {
                TeamMember::whereKey($this->beforeChanges['team_member_id'])->where('user_id', $user->id)->update(['user_id' => null]);
            }
            if ($member = $user->teamMember) {
                abort_if($member->user_id && $member->user_id !== $user->id, 422, 'This team member is already linked to another account.');
                TeamMember::whereKey($member->id)->update(['user_id' => $user->id]);
            }
        });

        if ($this->beforeChanges['status'] !== $user->status) {
            DepartmentActivity::record(null, auth()->id(), 'user.status_changed', $user, ['status' => $user->status]);
        }
        if ($this->beforeChanges['department_ids'] !== $this->departmentAccess) {
            DepartmentActivity::record(null, auth()->id(), 'user.departments_changed', $user, ['department_ids' => $this->departmentAccess]);
        }
        $roles = $user->roles()->pluck('name')->all();
        if ($this->beforeChanges['roles'] !== $roles) {
            DepartmentActivity::record(null, auth()->id(), 'user.roles_changed', $user, ['roles' => $roles]);
        }
        if ($this->beforeChanges['changed_fields']) {
            DepartmentActivity::record(null, auth()->id(), 'user.updated', $user, ['fields' => $this->beforeChanges['changed_fields']]);
        }

        parent::afterSave();
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}

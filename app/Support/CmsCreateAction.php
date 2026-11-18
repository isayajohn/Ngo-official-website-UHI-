<?php

namespace App\Support;

use Filament\Actions\CreateAction;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;

class CmsCreateAction
{
    /** @param class-string<Resource> $resource */
    public static function make(string $resource, bool $steps = false): CreateAction
    {
        $action = CreateAction::make()
            ->label('Add '.$resource::getModelLabel())
            ->icon('heroicon-m-plus')
            ->modal()
            ->modalHeading('Add '.$resource::getModelLabel())
            ->modalSubmitActionLabel('Save '.$resource::getModelLabel())
            ->modalWidth($steps ? Width::SevenExtraLarge : Width::TwoExtraLarge)
            ->stickyModalHeader()
            ->stickyModalFooter()
            ->closeModalByClickingAway(false)
            ->createAnother(false)
            ->databaseTransaction()
            ->mutateDataUsing(function (array $data) use ($resource): array {
                if ($resource::getModel() === \App\Models\Project::class && ! auth()->user()->can('project.publish')) {
                    $data['is_published'] = false;
                    $data['published_at'] = null;
                }
                if ($resource::getModel() === \App\Models\DepartmentTask::class) {
                    $department = \App\Models\Department::findOrFail($data['department_id'] ?? 0);
                    abort_unless(app(\App\Services\DepartmentAccessService::class)->canAssignTask(auth()->user(), $department), 403);
                    $data['assigned_by_user_id'] = auth()->id();
                }
                if ($resource::getModel() === \App\Models\DepartmentLeadership::class) {
                    $department = \App\Models\Department::findOrFail($data['department_id'] ?? 0);
                    abort_unless(auth()->user()->can('organization.full_access') || (auth()->user()->can('department.manage_leadership') && app(\App\Services\DepartmentAccessService::class)->canAccessDepartment(auth()->user(), $department)), 403);
                }
                if ($resource::getModel() === \App\Models\ImpactStatistic::class) {
                    // New indicators always begin as unverified drafts.
                    $data['is_active'] = false;
                    unset($data['verified_at'], $data['verified_by']);
                }
                if ($resource::getModel() === \Spatie\Permission\Models\Role::class) {
                    $data['guard_name'] = 'web';
                }

                return $data;
            });

        if ($steps) {
            $action->steps(function (Schema $schema) use ($resource): array {
                return array_map(
                    fn (Section $section): Step => Step::make($section->getHeading())
                        ->description($section->getDescription())
                        ->schema($section->getDefaultChildComponents())
                        ->columns($section->getColumns()),
                    $resource::form($schema)->getComponents(),
                );
            });
        }

        if ($resource::getModel() === \App\Models\DepartmentTask::class) {
            $action->after(function (CreateAction $action): void {
                $task = $action->getRecord();
                if ($task instanceof \App\Models\DepartmentTask) app(\App\Services\DepartmentTaskAttachmentService::class)->store($task, (array) ($action->getData()['attachments_upload'] ?? []), auth()->id());
            });
        }

        if ($resource::getModel() === \App\Models\Project::class) {
            $action->after(function (CreateAction $action): void {
                $project = $action->getRecord();
                $data = $action->getData();
                app(\App\Services\ProjectDepartmentService::class)->sync(
                    $project,
                    auth()->user(),
                    filled($data['lead_department_id'] ?? null) ? (int) $data['lead_department_id'] : null,
                    (array) ($data['supporting_department_ids'] ?? []),
                );
            });
        }

        return $action;
    }
}

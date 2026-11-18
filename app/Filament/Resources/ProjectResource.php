<?php

namespace App\Filament\Resources;

use App\Enums\ProjectStatus;
use App\Filament\Resources\ProjectResource\Pages;
use App\Models\District;
use App\Models\Project;
use App\Support\CmsFields;
use App\Support\CmsCreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Validation\Rule;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Our Work';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $recordTitleAttribute = 'slug';

    public static function form(Schema $schema): Schema
    {
        $translations = [];
        $narratives = [];
        foreach (['title', 'short_description', 'description', 'challenge', 'objectives', 'activities', 'target_groups'] as $field) {
            foreach (['en' => 'English', 'sw' => 'Kiswahili'] as $locale => $label) {
                $component = $field === 'title' ? TextInput::make($field.'.'.$locale)->maxLength(255) : Textarea::make($field.'.'.$locale)->rows($field === 'description' ? 6 : 3)->maxLength($field === 'short_description' ? 600 : 20000);
                $target = in_array($field, ['title', 'short_description']) ? 'translations' : 'narratives';
                ${$target}[] = $component->label(ucwords(str_replace('_', ' ', $field)).' · '.$label)->required($locale === 'en' && in_array($field, ['title', 'short_description', 'description']));
            }
        }

        return $schema->components([
            Section::make('Basics')->schema([
                Select::make('programme_id')->relationship('programme', 'slug', fn (Builder $query) => $query->where('is_active', true))->getOptionLabelFromRecordUsing(fn ($record) => $record->translated('title'))->searchable()->preload()->required()->rules(['exists:programmes,id']),
                TextInput::make('slug')->required(fn (string $operation) => $operation === 'edit')->unique(ignoreRecord: true)->maxLength(255)->rules(['regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'])->helperText('Leave blank when creating to generate a unique URL.'),
                ...$translations,
            ])->columns(2)->columnSpanFull(),
            Section::make('Details')->schema($narratives)->columns(2)->columnSpanFull(),
            Section::make('Delivery')->schema([
                Select::make('status')->options(ProjectStatus::class)->default(ProjectStatus::Planned->value)->required(),
                Select::make('region_id')->relationship('region', 'name', fn (Builder $query) => $query->published())->searchable()->preload()->live()->afterStateUpdated(fn (Set $set) => $set('district_id', null)),
                Select::make('district_id')->options(fn (Get $get) => District::published()->where('region_id', $get('region_id'))->pluck('name', 'id'))->searchable()->rules(fn (Get $get) => [Rule::exists('districts', 'id')->where('region_id', $get('region_id'))]),
                TextInput::make('location')->label('Public location description')->maxLength(255),
                DatePicker::make('start_date'), DatePicker::make('end_date')->rules(fn (Get $get) => filled($get('start_date')) ? ['after_or_equal:'.$get('start_date')] : []),
                TextInput::make('code')->label('Project code')->maxLength(80)->unique(ignoreRecord: true),
                Select::make('project_manager_team_member_id')->label('Project manager')->options(fn () => \App\Models\TeamMember::query()->where('active', true)->whereIn('department_id', app(\App\Services\ProjectDepartmentService::class)->assignableDepartments(auth()->user())->modelKeys())->orderBy('name')->pluck('name', 'id'))->searchable()->preload(),
                TextInput::make('funding_partner')->maxLength(255),
                TextInput::make('project_budget')->numeric()->minValue(0)->maxValue(9999999999999999)->helperText('Optional, internal only. Never displayed publicly.'),
                TextInput::make('people_reached')->label('Operational reach (internal)')->numeric()->integer()->minValue(0)->maxValue(1000000000000)->helperText('Public reach figures must be entered and verified in Impact Statistics.'),
                Select::make('lead_department_id')->label('Lead department')->options(fn () => app(\App\Services\ProjectDepartmentService::class)->assignableDepartments(auth()->user())->pluck('name', 'id'))->default(fn (?Project $record) => $record?->departments()->wherePivot('is_lead', true)->value('departments.id'))->searchable()->visible(fn () => auth()->user()->can('department.assign_project') || auth()->user()->can('organization.full_access'))->helperText('Assign a lead department before submitting this proposal for approval.'),
                Select::make('supporting_department_ids')->label('Supporting departments')->options(fn () => app(\App\Services\ProjectDepartmentService::class)->assignableDepartments(auth()->user())->pluck('name', 'id'))->default(fn (?Project $record) => $record?->departments()->wherePivot('is_lead', false)->pluck('departments.id')->all() ?? [])->multiple()->searchable()->visible(fn () => auth()->user()->can('department.assign_project') || auth()->user()->can('organization.full_access'))->helperText('Additional departments may view this project; operational ownership remains with the lead department.'),
            ])->columns(2)->columnSpanFull(),
            Section::make('Images')->schema([
                CmsFields::image('featured_image'), TextInput::make('image_alt')->maxLength(255)->requiredWith('featured_image'),
                Repeater::make('images')->relationship()->orderColumn('display_order')->maxItems(20)->schema([
                    CmsFields::image('image')->required(), TextInput::make('alt_text')->required()->maxLength(255), TextInput::make('caption')->maxLength(255),
                ])->defaultItems(0)->columnSpanFull(),
            ])->columns(2)->columnSpanFull(),
            Section::make('Publish')->description('Only users with project.publish may publish or schedule. Edits by other project editors return published content to draft.')->schema([
                TextInput::make('meta_title')->maxLength(70), Textarea::make('meta_description')->maxLength(180), CmsFields::image('og_image'),
                Toggle::make('is_featured')->default(false),
                Toggle::make('is_published')->label('Published')->default(false)->visible(fn () => auth()->user()->can('project.publish'))->dehydrated(fn () => auth()->user()->can('project.publish')),
                DateTimePicker::make('published_at')->label('Publish at')->visible(fn () => auth()->user()->can('project.publish'))->dehydrated(fn () => auth()->user()->can('project.publish'))->helperText('Blank publishes immediately; a future time schedules publication.'),
            ])->columnSpanFull(),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('title.en')->label('Project')->searchable()->wrap(), TextColumn::make('programme.title.en')->label('Programme')->wrap(),
            TextColumn::make('status')->badge()->sortable(), TextColumn::make('region.name')->sortable(),
            IconColumn::make('is_published')->boolean(), IconColumn::make('is_featured')->boolean(), TextColumn::make('start_date')->date()->sortable(),
        ])->defaultSort('updated_at', 'desc')->filters([
            SelectFilter::make('status')->options(ProjectStatus::class), SelectFilter::make('programme_id')->relationship('programme', 'slug'), SelectFilter::make('region_id')->relationship('region', 'name'),
            SelectFilter::make('is_published')->options(['1' => 'Published / scheduled', '0' => 'Draft']), TrashedFilter::make(),
        ])->recordActions([
            ViewAction::make(), EditAction::make(),
            Action::make('submitApproval')->label('Submit proposal')->icon('heroicon-o-paper-airplane')->requiresConfirmation()
                ->visible(fn (Project $record) => app(\App\Services\DepartmentAccessService::class)->canManageProject(auth()->user(), $record) && ! $record->approvalRequests()->whereIn('status', ['SUBMITTED', 'UNDER_REVIEW', 'APPROVED'])->exists())
                ->action(function (Project $record): void {
                    $department = $record->departments()->wherePivot('is_lead', true)->first();
                    abort_unless($department, 422, 'Assign a lead department before submitting this proposal.');
                    $workflow = \App\Models\ApprovalWorkflow::where('code', 'PROJECT_PROPOSAL')->where('active', true)->firstOrFail();
                    app(\App\Services\ApprovalWorkflowService::class)->submit($workflow, $record, auth()->user(), $department->id);
                }),
        ])->headerActions([CmsCreateAction::make(static::class, true)]);
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $query = parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
        if ($user && ! $user->can('organization.full_access')) {
            $departmentIds = $user->departments()->wherePivot('active', true)->select('departments.id');
            $leadershipIds = \App\Models\Department::query()->whereHas('activeLeadership.teamMember', fn ($q) => $q->where('user_id', $user->id))->select('departments.id');
            $query->whereHas('departments', fn ($q) => $q->whereIn('departments.id', $departmentIds)->orWhereIn('departments.id', $leadershipIds));
        }
        return $query;
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListProjects::route('/'), 'create' => Pages\CreateProject::route('/create'), 'view' => Pages\ViewProject::route('/{record}'), 'edit' => Pages\EditProject::route('/{record}/edit')];
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\ProjectResource\RelationManagers\ActivitiesRelationManager::class,
            \App\Filament\Resources\ProjectResource\RelationManagers\MilestonesRelationManager::class,
            \App\Filament\Resources\ProjectResource\RelationManagers\RisksRelationManager::class,
        ];
    }
}

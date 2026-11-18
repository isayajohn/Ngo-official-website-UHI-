<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use App\Models\Project;
use App\Services\DepartmentAccessService;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

abstract class ProjectOperationsRelationManager extends RelationManager
{
    protected static ?string $title = null;
    protected string $operationType = '';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord instanceof Project
            && auth()->user()?->can('project.view')
            && app(DepartmentAccessService::class)->canViewProject(auth()->user(), $ownerRecord);
    }

    public function form(Schema $schema): Schema
    {
        $fields = match ($this->operationType) {
            'activity' => [
                TextInput::make('title')->required()->maxLength(255),
                TextInput::make('activity_type')->maxLength(100),
                Textarea::make('description')->rows(3)->columnSpanFull(),
                Select::make('department_id')->options(fn () => $this->getOwnerRecord()->departments()->pluck('departments.name', 'departments.id'))->searchable(),
                Select::make('responsible_team_member_id')->relationship('responsibleTeamMember', 'name')->searchable()->preload(),
                DatePicker::make('start_date'), DatePicker::make('end_date')->rules(fn (Get $get) => filled($get('start_date')) ? ['after_or_equal:'.$get('start_date')] : []),
                Select::make('status')->options(['PLANNED' => 'Planned', 'IN_PROGRESS' => 'In Progress', 'COMPLETED' => 'Completed', 'DELAYED' => 'Delayed', 'CANCELLED' => 'Cancelled'])->default('PLANNED')->required(),
                TextInput::make('location')->maxLength(255), TextInput::make('target_participants')->numeric()->integer()->minValue(0),
                TextInput::make('actual_participants')->numeric()->integer()->minValue(0), TextInput::make('completion_percentage')->numeric()->integer()->minValue(0)->maxValue(100)->default(0),
            ],
            'milestone' => [
                TextInput::make('title')->required()->maxLength(255), Textarea::make('description')->rows(3)->columnSpanFull(),
                DatePicker::make('due_date'), Select::make('responsible_user_id')->relationship('responsibleUser', 'name')->searchable()->preload(),
                Select::make('status')->options(['UPCOMING' => 'Upcoming', 'IN_PROGRESS' => 'In Progress', 'COMPLETED' => 'Completed', 'OVERDUE' => 'Overdue', 'CANCELLED' => 'Cancelled'])->default('UPCOMING')->required(),
            ],
            default => [
                TextInput::make('title')->required()->maxLength(255), Textarea::make('description')->rows(3)->columnSpanFull(),
                TextInput::make('likelihood')->numeric()->integer()->minValue(1)->maxValue(5)->default(1)->required(),
                TextInput::make('impact')->numeric()->integer()->minValue(1)->maxValue(5)->default(1)->required(),
                Textarea::make('mitigation')->rows(3)->columnSpanFull(), Select::make('owner_user_id')->relationship('owner', 'name')->searchable()->preload(),
                Select::make('status')->options(['OPEN' => 'Open', 'MITIGATING' => 'Mitigating', 'CLOSED' => 'Closed', 'ACCEPTED' => 'Accepted'])->default('OPEN')->required(),
            ],
        };

        return $schema->components($fields)->columns(2);
    }

    public function table(Table $table): Table
    {
        $columns = match ($this->operationType) {
            'activity' => [TextColumn::make('title')->searchable()->wrap(), TextColumn::make('status')->badge(), TextColumn::make('start_date')->date(), TextColumn::make('end_date')->date(), TextColumn::make('completion_percentage')->suffix('%')],
            'milestone' => [TextColumn::make('title')->searchable()->wrap(), TextColumn::make('status')->badge(), TextColumn::make('due_date')->date()->sortable(), TextColumn::make('completed_at')->dateTime()],
            default => [TextColumn::make('title')->searchable()->wrap(), TextColumn::make('risk_level')->badge(), TextColumn::make('likelihood'), TextColumn::make('impact'), TextColumn::make('status')->badge()],
        };

        return $table->columns($columns)->headerActions([
            CreateAction::make()->visible(fn () => app(DepartmentAccessService::class)->canManageProject(auth()->user(), $this->getOwnerRecord())),
        ])->recordActions([
            EditAction::make()->visible(fn () => app(DepartmentAccessService::class)->canManageProject(auth()->user(), $this->getOwnerRecord())),
            DeleteAction::make()->visible(fn () => app(DepartmentAccessService::class)->canManageProject(auth()->user(), $this->getOwnerRecord())),
        ])->defaultSort('created_at', 'desc');
    }
}

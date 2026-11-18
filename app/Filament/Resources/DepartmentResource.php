<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepartmentResource\Pages;
use App\Models\Department;
use App\Models\TeamMember;
use App\Support\CmsCreateAction;
use App\Support\CmsFields;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Validation\Rule;

class DepartmentResource extends Resource
{
    protected static ?string $model = Department::class;
    protected static string|\UnitEnum|null $navigationGroup = 'Organization';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationLabel = 'Departments';
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Basic Information')->schema([
                TextInput::make('name')->required()->maxLength(160),
                TextInput::make('code')->required()->maxLength(30)->unique(ignoreRecord: true)->rules(['regex:/^[A-Za-z0-9_-]+$/']),
                TextInput::make('slug')->required(fn (string $operation) => $operation === 'edit')->maxLength(255)->unique(ignoreRecord: true)->rules(['regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'])->helperText('Leave blank on creation to generate a stable public URL.'),
                Select::make('parent_id')->label('Parent department')->relationship('parent', 'name')->searchable()->preload()->nullable(),
                TextInput::make('short_description')->maxLength(255),
                Textarea::make('description')->rows(5)->maxLength(5000)->columnSpanFull(),
            ])->columns(2)->columnSpanFull(),
            Section::make('Leadership')->schema([
                Select::make('head_staff_id')->label('Department head')->options(fn () => TeamMember::query()->where('active', true)->orderBy('name')->pluck('name', 'id'))->searchable()->preload()->nullable()->helperText('Only active team members are listed.'),
            ])->columnSpanFull(),
            Section::make('Contact')->schema([
                TextInput::make('email')->email()->maxLength(255), TextInput::make('phone')->tel()->maxLength(50), TextInput::make('office_location')->maxLength(255),
                Toggle::make('accepts_public_messages')->label('Accept public contact messages')->default(true),
            ])->columns(2)->columnSpanFull(),
            Section::make('Website')->schema([
                TextInput::make('icon')->maxLength(40)->helperText('Use a supported UGHI icon name.'), CmsFields::image('featured_image'),
                Toggle::make('show_on_website')->label('Show on website')->default(true), TextInput::make('display_order')->numeric()->integer()->minValue(0)->default(0)->required(),
            ])->columns(2)->columnSpanFull(),
            Section::make('Relationships')->schema([
                Select::make('programmes')->relationship('programmes', 'slug')->multiple()->searchable()->preload()->helperText('Programmes supported by this department.'),
                Select::make('projects')->relationship('projects', 'slug')->multiple()->searchable()->preload()->helperText('Projects supported by this department.'),
            ])->columns(2)->columnSpanFull(),
            Section::make('Status')->schema([
                Toggle::make('active')->label('Active')->default(true),
            ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->searchable()->sortable(), TextColumn::make('code')->searchable(), TextColumn::make('parent.name')->label('Parent')->placeholder('Top level'),
            TextColumn::make('head.name')->label('Department head')->placeholder('Not assigned'), TextColumn::make('team_members_count')->counts('teamMembers')->label('Staff'), TextColumn::make('projects_count')->counts('projects')->label('Projects'),
            IconColumn::make('active')->boolean(), IconColumn::make('show_on_website')->boolean()->label('Website'), TextColumn::make('display_order')->sortable(), TextColumn::make('updated_at')->dateTime()->sortable(),
        ])->defaultSort('display_order')->filters([
            TernaryFilter::make('active'), TernaryFilter::make('show_on_website')->label('Website visible'), SelectFilter::make('parent_id')->relationship('parent', 'name'), TrashedFilter::make(),
        ])->recordActions([ViewAction::make(), EditAction::make()])->headerActions([CmsCreateAction::make(static::class, true)]);
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $query = parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class])->withCount(['teamMembers', 'projects']);
        if ($user && ! $user->can('organization.full_access')) {
            $membership = $user->departments()->wherePivot('active', true)->select('departments.id');
            $leadership = Department::query()->whereHas('activeLeadership.teamMember', fn ($q) => $q->where('user_id', $user->id))->select('departments.id');
            $legacyHead = Department::query()->whereHas('head', fn ($q) => $q->where('user_id', $user->id))->select('departments.id');
            $query->where(fn ($q) => $q->whereIn('id', $membership)->orWhereIn('id', $leadership)->orWhereIn('id', $legacyHead));
        }
        return $query;
    }
    public static function getPages(): array { return ['index' => Pages\ListDepartments::route('/'), 'create' => Pages\CreateDepartment::route('/create'), 'view' => Pages\ViewDepartment::route('/{record}'), 'edit' => Pages\EditDepartment::route('/{record}/edit')]; }
}

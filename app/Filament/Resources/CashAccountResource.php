<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CashAccountResource\Pages;
use App\Models\Account;
use App\Models\CashAccount;
use App\Models\Department;
use App\Models\TeamMember;
use App\Support\CmsCreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CashAccountResource extends Resource
{
    protected static ?string $model = CashAccount::class;
    protected static string|\UnitEnum|null $navigationGroup = 'Finance';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Cash Accounts';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([Section::make('Cash account')->schema([
            TextInput::make('name')->required()->maxLength(160),
            Select::make('department_id')->options(fn () => Department::query()->where('active', true)->when(! auth()->user()->can('organization.full_access'), fn ($q) => $q->whereIn('id', auth()->user()->departments()->wherePivot('active', true)->select('departments.id')))->orderBy('name')->pluck('name', 'id'))->searchable()->nullable(),
            Select::make('custodian_team_member_id')->label('Custodian')->options(fn ($get) => TeamMember::query()->where('active', true)->when($get('department_id'), fn ($q, $id) => $q->where('department_id', $id))->orderBy('name')->pluck('name', 'id'))->searchable()->nullable(),
            Select::make('currency')->options(['TZS' => 'TZS', 'USD' => 'USD', 'EUR' => 'EUR', 'GBP' => 'GBP'])->default('TZS')->required(),
            TextInput::make('opening_balance')->numeric()->minValue(0)->default('0.00')->required(),
            Select::make('account_id')->label('Chart of accounts')->options(fn () => Account::query()->where('type', 'ASSET')->where('allow_transactions', true)->where('active', true)->orderBy('code')->get()->mapWithKeys(fn ($a) => [$a->id => $a->code.' · '.$a->name]))->searchable()->required()->unique(ignoreRecord: true),
            Toggle::make('active')->default(true),
        ])->columns(2)]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('name')->searchable()->sortable(), TextColumn::make('department.name')->label('Department')->placeholder('Organization'), TextColumn::make('custodian.name')->label('Custodian')->placeholder('Unassigned'), TextColumn::make('currency')->badge(), TextColumn::make('opening_balance')->money(fn ($record) => $record->currency), TextColumn::make('account.code')->label('Ledger account'), IconColumn::make('active')->boolean()])
            ->filters([SelectFilter::make('department_id')->relationship('department', 'name'), TernaryFilter::make('active')])->recordActions([EditAction::make()])->headerActions([CmsCreateAction::make(static::class)]);
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $query = parent::getEloquentQuery()->with(['department', 'custodian', 'account']);
        if ($user && ! $user->can('organization.full_access')) {
            $ids = $user->departments()->wherePivot('active', true)->select('departments.id');
            $leadershipIds = Department::query()->whereHas('activeLeadership.teamMember', fn ($q) => $q->where('user_id', $user->id))->select('id');
            $query->where(fn ($q) => $q->whereNull('department_id')->orWhereIn('department_id', $ids)->orWhereIn('department_id', $leadershipIds));
        }
        return $query;
    }

    public static function getPages(): array { return ['index' => Pages\ListCashAccounts::route('/'), 'create' => Pages\CreateCashAccount::route('/create'), 'edit' => Pages\EditCashAccount::route('/{record}/edit')]; }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountResource\Pages;
use App\Models\Account;
use App\Support\CmsCreateAction;
use Filament\Actions\EditAction;
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
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AccountResource extends Resource
{
    protected static ?string $model = Account::class;
    protected static string|\UnitEnum|null $navigationGroup = 'Finance';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Chart of Accounts';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([Section::make('Account')->schema([
            TextInput::make('code')->required()->maxLength(32)->unique(ignoreRecord: true), TextInput::make('name')->required()->maxLength(160),
            Select::make('type')->options(['ASSET' => 'Asset', 'LIABILITY' => 'Liability', 'EQUITY' => 'Equity', 'INCOME' => 'Income', 'EXPENSE' => 'Expense'])->required()->live(),
            Select::make('parent_id')->label('Parent account')->options(fn (Get $get, ?Account $record) => Account::query()->where('active', true)->where('type', $get('type'))->when($record, fn ($q) => $q->whereKeyNot($record->id))->orderBy('code')->get()->mapWithKeys(fn ($account) => [$account->id => $account->code.' · '.$account->name]))->searchable()->nullable(),
            Select::make('normal_balance')->options(['DEBIT' => 'Debit', 'CREDIT' => 'Credit'])->required(),
            TextInput::make('display_order')->numeric()->integer()->minValue(0)->default(0)->required(), Textarea::make('description')->rows(3)->columnSpanFull(),
            Toggle::make('allow_transactions')->default(true), Toggle::make('active')->default(true),
        ])->columns(2)]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('code')->searchable()->sortable(), TextColumn::make('name')->searchable()->sortable(), TextColumn::make('parent.name')->label('Parent')->placeholder('Top level'), TextColumn::make('type')->badge(), TextColumn::make('normal_balance')->badge(), IconColumn::make('allow_transactions')->boolean(), IconColumn::make('active')->boolean()])
            ->defaultSort('code')->filters([SelectFilter::make('type')->options(['ASSET' => 'Asset', 'LIABILITY' => 'Liability', 'EQUITY' => 'Equity', 'INCOME' => 'Income', 'EXPENSE' => 'Expense']), TernaryFilter::make('active')])->recordActions([EditAction::make()])->headerActions([CmsCreateAction::make(static::class)]);
    }

    public static function getEloquentQuery(): Builder { return parent::getEloquentQuery()->with('parent'); }
    public static function getPages(): array { return ['index' => Pages\ListAccounts::route('/'), 'create' => Pages\CreateAccount::route('/create'), 'edit' => Pages\EditAccount::route('/{record}/edit')]; }
}

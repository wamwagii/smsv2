<?php

namespace App\Filament\Resources\AcademicYears;

use App\Filament\Resources\AcademicYears\Pages\CreateAcademicYears;
use App\Filament\Resources\AcademicYears\Pages\EditAcademicYears;
use App\Filament\Resources\AcademicYears\Pages\ListAcademicYears;
use App\Filament\Resources\AcademicYears\Pages\ViewAcademicYears;
use App\Filament\Resources\AcademicYears\Schemas\AcademicYearsForm;
use App\Filament\Resources\AcademicYears\Schemas\AcademicYearsInfolist;
use App\Filament\Resources\AcademicYears\Tables\AcademicYearsTable;
use App\Models\AcademicYears;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class AcademicYearsResource extends Resource
{
    protected static ?string $model = AcademicYears::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CalendarDateRange;

    protected static string|\UnitEnum|null $navigationGroup = 'Academics';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return AcademicYearsForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AcademicYearsInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AcademicYearsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListAcademicYears::route('/'),
            'create' => CreateAcademicYears::route('/create'),
            'view'   => ViewAcademicYears::route('/{record}'),
            'edit'   => EditAcademicYears::route('/{record}/edit'),
        ];
    }

    /* -----------------------------------------------------------------
     |  Global search
     | -----------------------------------------------------------------
     */

    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->name ?? 'Academic Year';
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Status'  => ucfirst($record->status ?? '-'),
            'Current' => $record->is_current ? 'Yes' : 'No',
        ];
    }

    /* -----------------------------------------------------------------
     |  Navigation
     | -----------------------------------------------------------------
     */

    public static function getNavigationBadge(): ?string
    {
        $current = static::getModel()::where('is_current', true)->value('name');

        return $current ? (string) $current : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

/* -----------------------------------------------------------------
 |  Authorization
 | -----------------------------------------------------------------
 */

public static function canViewAny(): bool
{
    return User::currentIsAdmin();
}

public static function canView(Model $record): bool
{
    return User::currentIsAdmin();
}

public static function canCreate(): bool
{
    return User::currentIsAdmin();
}

public static function canEdit(Model $record): bool
{
    return User::currentIsAdmin();
}

public static function canDelete(Model $record): bool
{
    if (!User::currentIsAdmin()) {
        return false;
    }

    return !$record->hasRelatedData();
}

public static function canDeleteAny(): bool
{
    return User::currentIsAdmin();
}


}
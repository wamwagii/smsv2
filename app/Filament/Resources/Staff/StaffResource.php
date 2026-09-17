<?php

namespace App\Filament\Resources\Staff;

use App\Filament\Resources\Staff\Pages\CreateStaff;
use App\Filament\Resources\Staff\Pages\EditStaff;
use App\Filament\Resources\Staff\Pages\ListStaff;
use App\Filament\Resources\Staff\Pages\ViewStaff;
use App\Filament\Resources\Staff\Schemas\StaffForm;
use App\Filament\Resources\Staff\Schemas\StaffInfolist;
use App\Filament\Resources\Staff\Tables\StaffTable;
use App\Models\Staff;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StaffResource extends Resource
{
    protected static ?string $model = Staff::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::UserGroup;

    protected static string|\UnitEnum|null $navigationGroup = 'People';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'full_name';

    public static function form(Schema $schema): Schema
    {
        return StaffForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return StaffInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StaffTable::configure($table);
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
            'index'  => ListStaff::route('/'),
            'create' => CreateStaff::route('/create'),
            'view'   => ViewStaff::route('/{record}'),
            'edit'   => EditStaff::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    /* -----------------------------------------------------------------
     |  Global search
     | -----------------------------------------------------------------
     */

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'staff_number',
            'first_name',
            'middle_name',
            'last_name',
            'email',
            'phone_number',
        ];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->full_name ?? 'Staff';
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Staff No.' => $record->staff_number ?? '-',
            'Role'      => ucfirst($record->role ?? '-'),
        ];
    }

    /* -----------------------------------------------------------------
     |  Navigation
     | -----------------------------------------------------------------
     */

    public static function getNavigationBadge(): ?string
    {
        $onLeave = static::getModel()::where('status', 'on_leave')->count();

        return $onLeave > 0 ? (string) $onLeave : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
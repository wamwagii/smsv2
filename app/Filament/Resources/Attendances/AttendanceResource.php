<?php

namespace App\Filament\Resources\Attendances;

use App\Filament\Resources\Attendances\Pages\CreateAttendance;
use App\Filament\Resources\Attendances\Pages\EditAttendance;
use App\Filament\Resources\Attendances\Pages\ListAttendances;
use App\Filament\Resources\Attendances\Pages\MarkAttendance;
use App\Filament\Resources\Attendances\Pages\ViewAttendance;
use App\Filament\Resources\Attendances\Schemas\AttendanceForm;
use App\Filament\Resources\Attendances\Schemas\AttendanceInfolist;
use App\Filament\Resources\Attendances\Tables\AttendancesTable;
use App\Models\Attendance;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ClipboardDocumentCheck;

    protected static string|\UnitEnum|null $navigationGroup = 'Academics';

    protected static ?int $navigationSort = 6;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return AttendanceForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AttendanceInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AttendancesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListAttendances::route('/'),
            'create' => CreateAttendance::route('/create'),
            'mark'   => MarkAttendance::route('/mark'),     // ← static route FIRST
            'view'   => ViewAttendance::route('/{record}'), // ← wildcard AFTER
            'edit'   => EditAttendance::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::whereDate('date', today())->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }

    /* -----------------------------------------------------------------
     |  Authorization
     | -----------------------------------------------------------------
     */

    public static function canViewAny(): bool
    {
        return User::currentIsAdmin();
    }

    public static function canCreate(): bool
    {
        return User::currentIsAdmin();
    }

    public static function canView(Model $record): bool
    {
        return User::currentIsAdmin();
    }

    public static function canEdit(Model $record): bool
    {
        return User::currentIsAdmin();
    }

    public static function canDelete(Model $record): bool
    {
        return User::currentIsAdmin();
    }

    public static function canDeleteAny(): bool
    {
        return User::currentIsAdmin();
    }
}
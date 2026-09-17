<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::UserCircle;

    protected static string|\UnitEnum|null $navigationGroup = 'Administration';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
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
            'index'  => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit'   => EditUser::route('/{record}/edit'),
        ];
    }

    /* -----------------------------------------------------------------
     |  Global search
     | -----------------------------------------------------------------
     */

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->name ?? 'User';
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Email' => $record->email ?? '-',
        ];
    }

    /* -----------------------------------------------------------------
     |  Navigation
     | -----------------------------------------------------------------
     */

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
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
        if ($record->id === User::currentId()) {
            return false;
        }

        return User::currentIsAdmin();
    }

    public static function canDeleteAny(): bool
    {
        return User::currentIsAdmin();
    }
}
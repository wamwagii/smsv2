<?php

namespace App\Filament\Resources\Departments;

use App\Filament\Resources\Departments\Pages\CreateDepartment;
use App\Filament\Resources\Departments\Pages\EditDepartment;
use App\Filament\Resources\Departments\Pages\ListDepartments;
use App\Filament\Resources\Departments\Pages\ViewDepartment;
use App\Filament\Resources\Departments\Schemas\DepartmentForm;
use App\Filament\Resources\Departments\Schemas\DepartmentInfolist;
use App\Filament\Resources\Departments\Tables\DepartmentsTable;
use App\Models\Department;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class DepartmentResource extends Resource
{
    protected static ?string $model = Department::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Folder;

    protected static string|\UnitEnum|null $navigationGroup = 'Administration';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return DepartmentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DepartmentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DepartmentsTable::configure($table);
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
            'index'  => ListDepartments::route('/'),
            'create' => CreateDepartment::route('/create'),
            'view'   => ViewDepartment::route('/{record}'),
            'edit'   => EditDepartment::route('/{record}/edit'),
        ];
    }

    /* -----------------------------------------------------------------
     |  Query — eager load relationships to prevent N+1
     | -----------------------------------------------------------------
     */

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['headOfDepartment'])
            ->withCount(['staff']);
    }

    /* -----------------------------------------------------------------
     |  Global search
     | -----------------------------------------------------------------
     */

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'code'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->name ?? 'Department';
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Code' => $record->code ?? '-',
        ];
    }

    /* -----------------------------------------------------------------
     |  Navigation
     | -----------------------------------------------------------------
     */

    public static function getNavigationBadge(): ?string
    {
        $count = Cache::remember(
            'departments.active_count',
            now()->addMinutes(5),
            fn () => static::getModel()::where('is_active', true)->count(),
        );

        return $count > 0 && $count < 100 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }
}
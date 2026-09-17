<?php

namespace App\Filament\Resources\Students;

use App\Filament\Resources\Students\Pages\CreateStudent;
use App\Filament\Resources\Students\Pages\EditStudent;
use App\Filament\Resources\Students\Pages\ListStudents;
use App\Filament\Resources\Students\Pages\ViewStudent;
use App\Filament\Resources\Students\Schemas\StudentForm;
use App\Filament\Resources\Students\Schemas\StudentInfolist;
use App\Filament\Resources\Students\Tables\StudentsTable;
use App\Models\Student;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::AcademicCap;

    protected static string|\UnitEnum|null $navigationGroup = 'People';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'full_name';

    public static function form(Schema $schema): Schema
    {
        return StudentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return StudentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StudentsTable::configure($table);
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
            'index'  => ListStudents::route('/'),
            'create' => CreateStudent::route('/create'),
            'view'   => ViewStudent::route('/{record}'),
            'edit'   => EditStudent::route('/{record}/edit'),
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
            'admission_number',
            'first_name',
            'middle_name',
            'last_name',
        ];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->full_name ?? 'Student';
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Admission' => $record->admission_number ?? '-',
            'Class'     => $record->class?->class_code ?? '-',
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()
            ->with('class');
    }

    /* -----------------------------------------------------------------
     |  Navigation
     | -----------------------------------------------------------------
     */

    public static function getNavigationBadge(): ?string
    {
        // Total active students — high count, so only show if small
        $active = static::getModel()::where('status', 'active')->count();

        return $active > 0 && $active < 100 ? (string) $active : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
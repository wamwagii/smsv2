<?php

namespace App\Filament\Resources\Subjects;

use App\Filament\Resources\Subjects\Pages\CreateSubject;
use App\Filament\Resources\Subjects\Pages\EditSubject;
use App\Filament\Resources\Subjects\Pages\ListSubjects;
use App\Filament\Resources\Subjects\Pages\ViewSubject;
use App\Filament\Resources\Subjects\Schemas\SubjectForm;
use App\Filament\Resources\Subjects\Schemas\SubjectInfolist;
use App\Filament\Resources\Subjects\Tables\SubjectsTable;
use App\Models\Subject;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class SubjectResource extends Resource
{
    protected static ?string $model = Subject::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::BookOpen;

    protected static string|\UnitEnum|null $navigationGroup = 'Academics';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return SubjectForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SubjectInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SubjectsTable::configure($table);
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
            'index'  => ListSubjects::route('/'),
            'create' => CreateSubject::route('/create'),
            'view'   => ViewSubject::route('/{record}'),
            'edit'   => EditSubject::route('/{record}/edit'),
        ];
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
        return $record->name ?? 'Subject';
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Code'     => $record->code ?? '-',
            'Category' => ucfirst(str_replace('_', ' ', $record->category ?? '-')),
        ];
    }

    /* -----------------------------------------------------------------
     |  Navigation
     | -----------------------------------------------------------------
     */

    public static function getNavigationBadge(): ?string
    {
        $active = static::getModel()::where('is_active', true)->count();

        return $active > 0 ? (string) $active : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
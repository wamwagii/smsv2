<?php

namespace App\Filament\Resources\Exams;

use App\Filament\Resources\Exams\Pages\CreateExam;
use App\Filament\Resources\Exams\Pages\EditExam;
use App\Filament\Resources\Exams\Pages\ListExams;
use App\Filament\Resources\Exams\Pages\ViewExam;
use App\Filament\Resources\Exams\Schemas\ExamForm;
use App\Filament\Resources\Exams\Schemas\ExamInfolist;
use App\Filament\Resources\Exams\Tables\ExamsTable;
use App\Models\Exam;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ExamResource extends Resource
{
    protected static ?string $model = Exam::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::RectangleStack;

    protected static string|\UnitEnum|null $navigationGroup = 'Academics';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ExamForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ExamInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExamsTable::configure($table);
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
            'index'  => ListExams::route('/'),
            'create' => CreateExam::route('/create'),
            'view'   => ViewExam::route('/{record}'),
            'edit'   => EditExam::route('/{record}/edit'),
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
     |  Navigation
     | -----------------------------------------------------------------
     */

    public static function getNavigationBadge(): ?string
    {
        $ongoing = static::getModel()::where('status', 'ongoing')->count();

        return $ongoing > 0 ? (string) $ongoing : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }

    /* -----------------------------------------------------------------
     |  Global search
     | -----------------------------------------------------------------
     */

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'description'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->name;
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Term'   => ucfirst(str_replace('_', ' ', $record->term ?? '')),
            'Status' => ucfirst($record->status ?? ''),
        ];
    }
}
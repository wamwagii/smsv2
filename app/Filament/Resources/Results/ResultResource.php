<?php

namespace App\Filament\Resources\Results;

use App\Filament\Resources\Results\Pages\CreateResult;
use App\Filament\Resources\Results\Pages\EditResult;
use App\Filament\Resources\Results\Pages\ListResults;
use App\Filament\Resources\Results\Pages\ViewResult;
use App\Filament\Resources\Results\Schemas\ResultForm;
use App\Filament\Resources\Results\Schemas\ResultInfolist;
use App\Filament\Resources\Results\Tables\ResultsTable;
use App\Models\Result;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ResultResource extends Resource
{
    protected static ?string $model = Result::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ChartBarSquare;

    protected static string|\UnitEnum|null $navigationGroup = 'Academics';

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return ResultForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ResultInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ResultsTable::configure($table);
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
            'index'  => ListResults::route('/'),
            'create' => CreateResult::route('/create'),
            'view'   => ViewResult::route('/{record}'),
            'edit'   => EditResult::route('/{record}/edit'),
        ];
    }

    /* -----------------------------------------------------------------
     |  Global search
     | -----------------------------------------------------------------
     */

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'student.admission_number',
            'student.first_name',
            'student.last_name',
            'exam.name',
            'subject.name',
        ];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        $studentName = trim(
            ($record->student?->first_name ?? '') . ' ' .
            ($record->student?->last_name ?? '')
        );

        return $studentName . ' — ' . ($record->subject?->name ?? 'Result');
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Admission' => $record->student?->admission_number ?? '-',
            'Exam'      => $record->exam?->name ?? '-',
            'Grade'     => $record->grade ?? '-',
        ];
    }

    public static function getGlobalSearchEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getGlobalSearchEloquentQuery()
            ->with(['student', 'exam', 'subject']);
    }

    /* -----------------------------------------------------------------
     |  Navigation
     | -----------------------------------------------------------------
     */

    public static function getNavigationBadge(): ?string
    {
        // Badge showing how many results have missing grades (a data-health indicator)
        $missing = static::getModel()::whereNull('grade')->count();

        return $missing > 0 ? (string) $missing : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }
}
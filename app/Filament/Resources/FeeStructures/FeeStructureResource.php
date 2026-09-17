<?php

namespace App\Filament\Resources\FeeStructures;

use App\Filament\Resources\FeeStructures\Pages\CreateFeeStructure;
use App\Filament\Resources\FeeStructures\Pages\EditFeeStructure;
use App\Filament\Resources\FeeStructures\Pages\ListFeeStructures;
use App\Filament\Resources\FeeStructures\Pages\ViewFeeStructure;
use App\Filament\Resources\FeeStructures\Schemas\FeeStructureForm;
use App\Filament\Resources\FeeStructures\Schemas\FeeStructureInfolist;
use App\Filament\Resources\FeeStructures\Tables\FeeStructuresTable;
use App\Models\FeeStructure;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class FeeStructureResource extends Resource
{
    protected static ?string $model = FeeStructure::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ClipboardDocument;

    protected static string|\UnitEnum|null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return FeeStructureForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return FeeStructureInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FeeStructuresTable::configure($table);
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
            'index'  => ListFeeStructures::route('/'),
            'create' => CreateFeeStructure::route('/create'),
            'view'   => ViewFeeStructure::route('/{record}'),
            'edit'   => EditFeeStructure::route('/{record}/edit'),
        ];
    }

    /* -----------------------------------------------------------------
     |  Record title
     | -----------------------------------------------------------------
     */

    public static function getRecordTitle(?Model $record): ?string
    {
        if (!$record) {
            return null;
        }

        $class = $record->class?->class_code ?? 'N/A';
        $year  = $record->academicYear?->name ?? 'N/A';

        return "Fee Structure — {$class} ({$year})";
    }

    /* -----------------------------------------------------------------
     |  Global search
     | -----------------------------------------------------------------
     */

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'class.class_code',
            'academicYear.name',
        ];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->class?->class_code
            ? "Fee Structure — {$record->class->class_code}"
            : 'Fee Structure #' . $record->id;
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Academic Year' => $record->academicYear?->name ?? '-',
            'Total Fees'    => 'KES ' . number_format((float) ($record->total_fees ?? 0), 2),
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()
            ->with(['class', 'academicYear']);
    }

    /* -----------------------------------------------------------------
     |  Navigation
     | -----------------------------------------------------------------
     */

    public static function getNavigationBadge(): ?string
    {
        // Small table; show count only if low
        $count = static::getModel()::where('is_active', true)->count();

        return $count > 0 && $count < 100 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
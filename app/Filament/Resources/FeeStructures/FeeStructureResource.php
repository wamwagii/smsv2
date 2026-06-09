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

class FeeStructureResource extends Resource
{
    protected static ?string $model = FeeStructure::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ClipboardDocument;

    protected static ?string $recordTitleAttribute = 'name';

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
            'index' => ListFeeStructures::route('/'),
            'create' => CreateFeeStructure::route('/create'),
            'view' => ViewFeeStructure::route('/{record}'),
            'edit' => EditFeeStructure::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources\SmsBatches;

use App\Filament\Resources\SmsBatches\Pages\ListSmsBatches;
use App\Filament\Resources\SmsBatches\Pages\ViewSmsBatch;
use App\Filament\Resources\SmsBatches\Schemas\SmsBatchInfolist;
use App\Filament\Resources\SmsBatches\Tables\SmsBatchesTable;
use App\Models\SmsBatch;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SmsBatchResource extends Resource
{
    protected static ?string $model = SmsBatch::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ChatBubbleLeftRight;

    protected static string|\UnitEnum|null $navigationGroup = 'Communication';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'SMS History';

    public static function infolist(Schema $schema): Schema
    {
        return SmsBatchInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SmsBatchesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSmsBatches::route('/'),
            'view'  => ViewSmsBatch::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}

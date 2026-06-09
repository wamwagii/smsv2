<?php

namespace App\Filament\Resources\AcademicYears;

use App\Filament\Resources\AcademicYears\Pages\CreateAcademicYears;
use App\Filament\Resources\AcademicYears\Pages\EditAcademicYears;
use App\Filament\Resources\AcademicYears\Pages\ListAcademicYears;
use App\Filament\Resources\AcademicYears\Pages\ViewAcademicYears;
use App\Filament\Resources\AcademicYears\Schemas\AcademicYearsForm;
use App\Filament\Resources\AcademicYears\Schemas\AcademicYearsInfolist;
use App\Filament\Resources\AcademicYears\Tables\AcademicYearsTable;
use App\Models\AcademicYears;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AcademicYearsResource extends Resource
{
    protected static ?string $model = AcademicYears::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CalendarDateRange;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return AcademicYearsForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AcademicYearsInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AcademicYearsTable::configure($table);
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
            'index' => ListAcademicYears::route('/'),
            'create' => CreateAcademicYears::route('/create'),
            'view' => ViewAcademicYears::route('/{record}'),
            'edit' => EditAcademicYears::route('/{record}/edit'),
        ];
    }
}

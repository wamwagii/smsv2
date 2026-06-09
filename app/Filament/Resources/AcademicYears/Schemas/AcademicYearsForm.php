<?php

namespace App\Filament\Resources\AcademicYears\Schemas;


use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AcademicYearsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Academic Year Name')
                    ->required()
                    ->maxLength(255),
                DateTimePicker::make('start_date')
                    ->label('Start Date')
                    ->required(),
                DateTimePicker::make('end_date')
                    ->label('End Date')
                    ->required(),
                Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'archived' => 'Archived',
                    ])
                    ->default('active')
                    ->required(),
                Toggle::make('is_current')
                    ->label('Is Current Academic Year')
                    ->default(false),
                
                
            ]);
    }
}

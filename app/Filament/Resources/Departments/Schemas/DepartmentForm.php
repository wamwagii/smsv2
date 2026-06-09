<?php

namespace App\Filament\Resources\Departments\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use App\Models\Staff;

class DepartmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                
                TextInput::make('code')
                    ->required()
                    ->maxLength(10),
                
                Select::make('head_of_department_id')
                    ->label('Head of Department')
                    ->options(function () {
                        return Staff::whereIn('role', ['teacher', 'management'])
                            ->pluck('first_name', 'id');
                    })
                    ->searchable(),
                
                Textarea::make('description')
                    ->columnSpanFull(),
                
                Toggle::make('is_active')
                    ->default(true),
            ]);
    }
}
<?php

namespace App\Filament\Resources\Departments\Schemas;

use App\Models\Staff;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

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
                    ->maxLength(10)
                    ->unique(ignoreRecord: true),

                Select::make('head_of_department_id')
                    ->label('Head of Department')
                    ->relationship(
                        name: 'headOfDepartment',
                        titleAttribute: 'first_name',
                        modifyQueryUsing: fn ($query) => $query->whereIn('role', ['teacher', 'management']),
                    )
                    ->getOptionLabelFromRecordUsing(fn (Staff $record) => $record->full_name)
                    ->searchable(['first_name', 'last_name'])
                    ->preload(),

                Textarea::make('description')
                    ->columnSpanFull(),

                Toggle::make('is_active')
                    ->default(true),
            ]);
    }
}
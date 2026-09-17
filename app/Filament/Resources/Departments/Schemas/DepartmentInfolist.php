<?php

namespace App\Filament\Resources\Departments\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class DepartmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),

                TextEntry::make('code'),

                TextEntry::make('headOfDepartment.full_name')
                    ->label('Head of Department')
                    ->placeholder('Not assigned'),

                TextEntry::make('description')
                    ->placeholder('-')
                    ->columnSpanFull(),

                IconEntry::make('is_active')
                    ->label('Active')
                    ->boolean(),

                TextEntry::make('created_at')
                    ->label('Created')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('-'),

                TextEntry::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('-'),
            ]);
    }
}
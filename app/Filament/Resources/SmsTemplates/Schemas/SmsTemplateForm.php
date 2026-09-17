<?php

namespace App\Filament\Resources\SmsTemplates\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SmsTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(100),

                Textarea::make('body')
                    ->required()
                    ->rows(4)
                    ->maxLength(480)
                    ->helperText('Placeholders: {guardian_name}, {student_name}, {school_name}')
                    ->columnSpanFull(),

                Toggle::make('is_active')->default(true),
            ]);
    }
}

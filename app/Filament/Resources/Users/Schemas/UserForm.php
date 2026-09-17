<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('User Information')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->autocomplete('off'),

                        TextInput::make('email')
                            ->label('Email address')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->autocomplete('off'),

                        TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation) => $operation === 'create')
                            ->dehydrated(fn ($state) => filled($state))
                            ->helperText('Leave blank to keep the current password when editing.')
                            ->maxLength(255)
                            ->autocomplete('new-password'),

                        DateTimePicker::make('email_verified_at')
                            ->label('Email Verified At')
                            ->native(false)
                            ->displayFormat('d/m/Y H:i'),
                    ]),

                Section::make('Permissions')
                    ->schema([
                        Toggle::make('is_admin')
                            ->label('Administrator')
                            ->helperText(function ($record) {
                                if ($record?->is_admin && User::isLastAdmin($record)) {
                                    return 'This is the last administrator — you cannot remove this role.';
                                }
                                return 'Administrators can manage users, academic years, and other sensitive resources.';
                            })
                            ->disabled(function ($record) {
                                return $record?->is_admin && User::isLastAdmin($record);
                            })
                            ->dehydrated()
                            ->default(false),
                    ]),
            ]);
    }
}
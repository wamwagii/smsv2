<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\ActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('is_admin')
                    ->label('Admin')
                    ->boolean()
                    ->trueIcon('heroicon-o-shield-check')
                    ->falseIcon('heroicon-o-user')
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->sortable(),

                TextColumn::make('name')
                    ->searchable()
                    ->weight('semibold'),

                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Email copied'),

                TextColumn::make('email_verified_at')
                    ->label('Verified')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Not verified')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_admin')
                    ->label('Administrator')
                    ->placeholder('All users')
                    ->trueLabel('Administrators only')
                    ->falseLabel('Non-administrators only'),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('View')
                        ->color('info')
                        ->icon('heroicon-o-eye'),

                    EditAction::make()
                        ->label('Edit')
                        ->color('warning')
                        ->icon('heroicon-o-pencil'),

                    \Filament\Actions\DeleteAction::make()
                        ->label('Delete')
                        ->color('danger')
                        ->icon('heroicon-o-trash')
                        ->requiresConfirmation()
                        ->modalHeading('Delete User')
                        ->modalDescription('Are you sure you want to delete this user? This action cannot be undone.')
                        ->visible(function (User $record) {
                            // Self-deletion is already blocked by UserResource::canDelete(),
                            // but hide the action entirely for cleaner UX.
                            return $record->id !== User::currentId();
                        }),
                ])
                    ->label('Actions')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->color('gray')
                    ->tooltip('Actions'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Delete Selected'),
                ]),
            ])
            ->defaultSort('is_admin', 'desc')
            ->striped();
    }
}
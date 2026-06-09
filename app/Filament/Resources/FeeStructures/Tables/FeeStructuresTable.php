<?php

namespace App\Filament\Resources\FeeStructures\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Collection;
use Filament\Notifications\Notification;

class FeeStructuresTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('class.class_code')
                    ->label('Class')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->color('primary'),
                
                TextColumn::make('academicYear.name')
                    ->label('Academic Year')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('tuition_fees')
                    ->label('Tuition')
                    ->money('KES')
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('total_fees')
                    ->label('Total Fees')
                    ->money('KES')
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),
                
                BadgeColumn::make('payment_installments_count')
                    ->label('Installments')
                    ->getStateUsing(fn ($record) => $record->payment_plan ? count($record->payment_plan) : 0)
                    ->colors([
                        'success' => fn ($state) => $state === 3,
                        'warning' => fn ($state) => $state > 0 && $state < 3,
                        'danger' => fn ($state) => $state === 0,
                    ])
                    ->formatStateUsing(fn ($state) => $state . ' term' . ($state !== 1 ? 's' : '')),
                
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),
                
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('class_id')
                    ->label('Class')
                    ->relationship('class', 'class_code')
                    ->searchable()
                    ->preload(),
                
                SelectFilter::make('academic_year_id')
                    ->label('Academic Year')
                    ->relationship('academicYear', 'name')
                    ->searchable()
                    ->preload(),
                
                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ]),
                
                Filter::make('has_payment_plan')
                    ->label('Has Payment Plan')
                    ->query(fn ($query) => $query->whereNotNull('payment_plan'))
                    ->toggle(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('View')
                    ->color('info')
                    ->icon('heroicon-o-eye'),
                
                EditAction::make()
                    ->label('Edit')
                    ->color('warning')
                    ->icon('heroicon-o-pencil'),
                
                Action::make('duplicate')
                    ->label('Duplicate')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalHeading('Duplicate Fee Structure')
                    ->modalDescription('Create a new fee structure based on this one for a different class or year?')
                    ->action(function ($record) {
                        $newStructure = $record->replicate();
                        $newStructure->save();
                        
                        Notification::make()
                            ->title('Fee structure duplicated')
                            ->success()
                            ->send();
                    }),
                
                \Filament\Actions\DeleteAction::make()
                    ->label('Delete')
                    ->color('danger')
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Delete Selected')
                        ->requiresConfirmation(),
                    
                    BulkAction::make('activate_selected')
                        ->label('Activate Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records) {
                            $records->each->update(['is_active' => true]);
                            Notification::make()
                                ->title($records->count() . ' fee structures activated')
                                ->success()
                                ->send();
                        }),
                    
                    BulkAction::make('deactivate_selected')
                        ->label('Deactivate Selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function (Collection $records) {
                            $records->each->update(['is_active' => false]);
                            Notification::make()
                                ->title($records->count() . ' fee structures deactivated')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->headerActions([
                Action::make('create')
                    ->label('New Fee Structure')
                    ->icon('heroicon-o-plus')
                    ->color('primary')
                    ->url(route('filament.admin.resources.fee-structures.create')),
            ])
            ->defaultSort('academic_year_id', 'desc')
            ->searchable()
            ->persistFiltersInSession()
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25);
    }
}
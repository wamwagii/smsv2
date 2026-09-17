<?php

namespace App\Filament\Resources\Departments\Tables;

use App\Models\Staff;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class DepartmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('code')
                    ->searchable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('headOfDepartment.full_name')
                    ->label('Head of Department')
                    ->state(fn ($record) => $record->headOfDepartment?->full_name)
                    ->placeholder('Not assigned')
                    ->searchable(query: function ($query, $search) {
                        return $query->whereHas('headOfDepartment', function ($q) use ($search) {
                            $q->where('first_name', 'like', "%{$search}%")
                              ->orWhere('last_name', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(query: function ($query, $direction) {
                        return $query->orderBy(
                            Staff::select('first_name')
                                ->whereColumn('staff.id', 'departments.head_of_department_id')
                                ->limit(1),
                            $direction,
                        )->orderBy(
                            Staff::select('last_name')
                                ->whereColumn('staff.id', 'departments.head_of_department_id')
                                ->limit(1),
                            $direction,
                        );
                    }),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                TextColumn::make('staff_count')
                    ->label('Staff')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

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
                TernaryFilter::make('is_active')
                    ->label('Active')
                    ->placeholder('All departments')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),

                SelectFilter::make('head_of_department_id')
                    ->label('Head of Department')
                    ->relationship('headOfDepartment', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn (Staff $record) => $record->full_name)
                    ->searchable(['first_name', 'last_name'])
                    ->preload(),
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

                    DeleteAction::make()
                        ->label('Delete')
                        ->color('danger')
                        ->icon('heroicon-o-trash')
                        ->requiresConfirmation()
                        ->modalHeading('Delete Department')
                        ->modalDescription('Are you sure you want to delete this department? This action cannot be undone.')
                        ->visible(fn ($record) => ! $record->staff()->exists()),
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
            ->defaultSort('name', 'asc')
            ->striped();
    }
}
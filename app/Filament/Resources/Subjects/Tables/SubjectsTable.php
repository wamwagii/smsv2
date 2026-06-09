<?php

namespace App\Filament\Resources\Subjects\Tables;

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

class SubjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->color('primary')
                    ->copyable()
                    ->copyMessage('Subject code copied'),
                
                TextColumn::make('name')
                    ->label('Subject Name')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),
                
                TextColumn::make('department.name')
                    ->label('Department')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                
                BadgeColumn::make('category')
                    ->label('Category')
                    ->colors([
                        'success' => 'core',
                        'warning' => 'elective',
                        'info' => 'extra_curricular',
                    ])
                    ->formatStateUsing(fn ($state) => ucfirst(str_replace('_', ' ', $state)))
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('theory_hours_per_week')
                    ->label('Theory Hrs')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('practical_hours_per_week')
                    ->label('Practical Hrs')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('total_hours')
                    ->label('Total Hours')
                    ->getStateUsing(fn ($record) => ($record->theory_hours_per_week ?? 0) + ($record->practical_hours_per_week ?? 0))
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                
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
                SelectFilter::make('department_id')
                    ->label('Department')
                    ->relationship('department', 'name')
                    ->searchable()
                    ->preload(),
                
                SelectFilter::make('category')
                    ->label('Category')
                    ->options([
                        'core' => 'Core Subject',
                        'elective' => 'Elective',
                        'extra_curricular' => 'Extra Curricular',
                    ]),
                
                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ]),
                
                Filter::make('has_hours')
                    ->label('Has Teaching Hours')
                    ->query(fn ($query) => $query->where('theory_hours_per_week', '>', 0)->orWhere('practical_hours_per_week', '>', 0))
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
                    ->modalHeading('Duplicate Subject')
                    ->modalDescription('Create a new subject based on this one?')
                    ->action(function ($record) {
                        $newSubject = $record->replicate();
                        $newSubject->code = $newSubject->code . '_COPY';
                        $newSubject->name = $newSubject->name . ' (Copy)';
                        $newSubject->save();
                        
                        Notification::make()
                            ->title('Subject duplicated successfully')
                            ->success()
                            ->send();
                    }),
                
                \Filament\Actions\DeleteAction::make()
                    ->label('Delete')
                    ->color('danger')
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->modalHeading('Delete Subject')
                    ->modalDescription('Are you sure you want to delete this subject? This action cannot be undone.')
                    ->modalSubmitActionLabel('Yes, delete it'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Delete Selected')
                        ->requiresConfirmation()
                        ->modalHeading('Delete Subjects')
                        ->modalDescription('Are you sure you want to delete the selected subjects? This action cannot be undone.'),
                    
                    BulkAction::make('activate_selected')
                        ->label('Activate Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records) {
                            $count = $records->each->update(['is_active' => true])->count();
                            Notification::make()
                                ->title($count . ' subjects activated')
                                ->success()
                                ->send();
                        }),
                    
                    BulkAction::make('deactivate_selected')
                        ->label('Deactivate Selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function (Collection $records) {
                            $count = $records->each->update(['is_active' => false])->count();
                            Notification::make()
                                ->title($count . ' subjects deactivated')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->headerActions([
                Action::make('create')
                    ->label('New Subject')
                    ->icon('heroicon-o-plus')
                    ->color('primary')
                    ->url(route('filament.admin.resources.subjects.create')),
            ])
            ->defaultSort('name', 'asc')
            ->searchable()
            ->persistFiltersInSession()
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25);
    }
}
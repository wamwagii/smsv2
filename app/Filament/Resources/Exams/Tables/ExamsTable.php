<?php

namespace App\Filament\Resources\Exams\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;
use App\Models\Exam;
use App\Models\AcademicYears;

class ExamsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Exam Name')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('term')
                    ->label('Term')
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'term_1' => 'primary',
                        'term_2' => 'warning',
                        'term_3' => 'success',
                        default  => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state) => ucfirst(str_replace('_', ' ', $state ?? '-'))),

                TextColumn::make('academicYear.name')
                    ->label('Academic Year')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('start_date')
                    ->label('Start Date')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label('End Date')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('total_marks')
                    ->label('Total')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('passing_marks')
                    ->label('Pass')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'upcoming'  => 'gray',
                        'ongoing'   => 'info',
                        'completed' => 'warning',
                        'published' => 'success',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state) => ucfirst($state ?? '-'))
                    ->sortable(),

                TextColumn::make('results_count')
                    ->label('Results')
                    ->counts('results')
                    ->badge()
                    ->color('primary')
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

                TextColumn::make('deleted_at')
                    ->label('Deleted')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->color('danger'),
            ])
            ->filters([
                SelectFilter::make('academic_year_id')
                    ->label('Academic Year')
                    ->relationship('academicYear', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('term')
                    ->label('Term')
                    ->options([
                        'term_1' => 'Term 1',
                        'term_2' => 'Term 2',
                        'term_3' => 'Term 3',
                    ]),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'upcoming'  => 'Upcoming',
                        'ongoing'   => 'Ongoing',
                        'completed' => 'Completed',
                        'published' => 'Published',
                    ]),

                Filter::make('current_year')
                    ->label('Current Academic Year')
                    ->query(fn (Builder $query) => $query->whereHas(
                        'academicYear',
                        fn ($q) => $q->where('is_current', true)
                    ))
                    ->toggle(),

                Filter::make('has_results')
                    ->label('Has Results')
                    ->query(fn (Builder $query) => $query->has('results'))
                    ->toggle(),

                TrashedFilter::make(),
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

                    Action::make('view_results')
                        ->label('View Results')
                        ->icon('heroicon-o-document-chart-bar')
                        ->color('primary')
                        ->url(fn (Exam $record) => route('filament.admin.resources.results.index', [
                            'tableFilters' => [
                                'exam_id' => ['value' => $record->id],
                            ],
                        ]))
                        ->visible(fn (Exam $record) => $record->results()->exists()),

                    Action::make('mark_completed')
                        ->label('Mark Completed')
                        ->icon('heroicon-o-check-circle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Mark Exam as Completed')
                        ->modalDescription('Once completed, results can be published to students.')
                        ->action(function (Exam $record) {
                            $record->update(['status' => 'completed']);
                            Notification::make()
                                ->title('Exam marked as completed')
                                ->success()
                                ->send();
                        })
                        ->visible(fn (Exam $record) => in_array($record->status, ['upcoming', 'ongoing'])),

                    Action::make('publish')
                        ->label('Publish')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Publish Exam')
                        ->modalDescription('Once published, results become visible to students. Continue?')
                        ->action(function (Exam $record) {
                            $record->update(['status' => 'published']);
                            Notification::make()
                                ->title('Exam published')
                                ->success()
                                ->send();
                        })
                        ->visible(fn (Exam $record) => $record->status === 'completed'),

                    Action::make('restore')
                        ->label('Restore')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn (Exam $record) => $record->restore())
                        ->visible(fn (Exam $record) => $record->trashed()),

                    Action::make('force_delete')
                        ->label('Delete Permanently')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Permanently Delete Exam')
                        ->modalDescription('This will permanently delete the exam and cannot be undone.')
                        ->action(fn (Exam $record) => $record->forceDelete())
                        ->visible(fn (Exam $record) => $record->trashed()),

                    \Filament\Actions\DeleteAction::make()
                        ->label('Delete')
                        ->color('danger')
                        ->icon('heroicon-o-trash')
                        ->requiresConfirmation()
                        ->visible(fn (Exam $record) => !$record->trashed()),
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

                    RestoreBulkAction::make()
                        ->label('Restore Selected'),

                    ForceDeleteBulkAction::make()
                        ->label('Permanently Delete Selected'),

                    BulkAction::make('publish_selected')
                        ->label('Publish Selected')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->each->update(['status' => 'published']);
                            Notification::make()
                                ->title($records->count() . ' exams published')
                                ->success()
                                ->send();
                        }),

                    BulkAction::make('complete_selected')
                        ->label('Mark Completed')
                        ->icon('heroicon-o-check-circle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->each->update(['status' => 'completed']);
                            Notification::make()
                                ->title($records->count() . ' exams marked completed')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('start_date', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25);
    }
}
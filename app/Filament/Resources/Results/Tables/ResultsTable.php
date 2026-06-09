<?php

namespace App\Filament\Resources\Results\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Collection;
use Filament\Notifications\Notification;
use App\Models\Result;

class ResultsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student.admission_number')
                    ->label('Admission No.')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->toggleable(),
                
                TextColumn::make('student_full_name')
                    ->label('Student Name')
                    ->getStateUsing(fn ($record) => $record->student->first_name . ' ' . $record->student->last_name)
                    ->searchable(query: function ($query, $search) {
                        return $query->whereHas('student', function ($q) use ($search) {
                            $q->where('first_name', 'like', "%{$search}%")
                              ->orWhere('last_name', 'like', "%{$search}%");
                        });
                    })
                    ->sortable()
                    ->weight('semibold'),
                
                TextColumn::make('student.class.class_code')
                    ->label('Class')
                    ->getStateUsing(fn ($record) => $record->student->class->class_code ?? 'N/A')
                    ->searchable(query: function ($query, $search) {
                        return $query->whereHas('student.class', function ($q) use ($search) {
                            $q->where('class_code', 'like', "%{$search}%");
                        });
                    })
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('exam.name')
                    ->label('Exam')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('exam.term')
                    ->label('Term')
                    ->getStateUsing(fn ($record) => ucfirst(str_replace('_', ' ', $record->exam->term)))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('subject.code')
                    ->label('Subject Code')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('subject.name')
                    ->label('Subject')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                
                TextColumn::make('marks_obtained')
                    ->label('Marks')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('total_marks')
                    ->label('Total')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('percentage')
                    ->label('%')
                    ->numeric(decimalPlaces: 2)
                    ->suffix('%')
                    ->sortable()
                    ->color(fn ($state) => 
                        $state >= 80 ? 'success' : 
                        ($state >= 60 ? 'warning' : 
                        ($state >= 40 ? 'info' : 'danger'))
                    )
                    ->weight('bold')
                    ->toggleable(),
                
                BadgeColumn::make('grade')
                    ->label('Grade')
                    ->colors([
                        'success' => 'A',
                        'success' => 'A-',
                        'info' => 'B+',
                        'info' => 'B',
                        'info' => 'B-',
                        'warning' => 'C+',
                        'warning' => 'C',
                        'warning' => 'C-',
                        'danger' => 'D+',
                        'danger' => 'D',
                        'danger' => 'D-',
                        'danger' => 'E',
                    ])
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('teacher_comments')
                    ->label('Comments')
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->teacher_comments)
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('created_at')
                    ->label('Recorded')
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
                SelectFilter::make('exam_id')
                    ->label('Exam')
                    ->relationship('exam', 'name')
                    ->searchable()
                    ->preload(),
                
                SelectFilter::make('subject_id')
                    ->label('Subject')
                    ->relationship('subject', 'name')
                    ->searchable()
                    ->preload(),
                
                SelectFilter::make('class_id')
                    ->label('Class')
                    ->options(function () {
                        return \App\Models\Classes::pluck('class_code', 'id')->toArray();
                    })
                    ->searchable()
                    ->preload(),
                
                SelectFilter::make('grade')
                    ->label('Grade')
                    ->options([
                        'A' => 'A',
                        'A-' => 'A-',
                        'B+' => 'B+',
                        'B' => 'B',
                        'B-' => 'B-',
                        'C+' => 'C+',
                        'C' => 'C',
                        'C-' => 'C-',
                        'D+' => 'D+',
                        'D' => 'D',
                        'D-' => 'D-',
                        'E' => 'E',
                    ])
                    ->searchable(),
                
                Filter::make('percentage_range')
                    ->label('Percentage Range')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('min_percentage')
                            ->label('Min %')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100),
                        \Filament\Forms\Components\TextInput::make('max_percentage')
                            ->label('Max %')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['min_percentage'],
                                fn ($query) => $query->where('percentage', '>=', $data['min_percentage']),
                            )
                            ->when(
                                $data['max_percentage'],
                                fn ($query) => $query->where('percentage', '<=', $data['max_percentage']),
                            );
                    }),
                
                Filter::make('passing')
                    ->label('Passing Students')
                    ->query(fn ($query) => $query->where('percentage', '>=', 50))
                    ->toggle(),
                
                Filter::make('failing')
                    ->label('Failing Students')
                    ->query(fn ($query) => $query->where('percentage', '<', 50))
                    ->toggle(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('View Details')
                    ->color('info')
                    ->icon('heroicon-o-eye'),
                
                EditAction::make()
                    ->label('Edit Result')
                    ->color('warning')
                    ->icon('heroicon-o-pencil'),
                
                Action::make('print_result_slip')
                    ->label('Print Slip')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->url(fn ($record) => route('results.print', $record), shouldOpenInNewTab: true)
                    ->openUrlInNewTab(),
                
                \Filament\Actions\DeleteAction::make()
                    ->label('Delete')
                    ->color('danger')
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->modalHeading('Delete Result')
                    ->modalDescription('Are you sure you want to delete this result? This action cannot be undone.')
                    ->modalSubmitActionLabel('Yes, delete it'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Delete Selected')
                        ->requiresConfirmation()
                        ->modalHeading('Delete Results')
                        ->modalDescription('Are you sure you want to delete the selected results?'),
                    
                    BulkAction::make('export_results')
                        ->label('Export Selected')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('success')
                        ->action(function (Collection $records) {
                            // Create export data
                            $data = $records->map(function ($record) {
                                return [
                                    'Admission No' => $record->student->admission_number,
                                    'Student Name' => $record->student->first_name . ' ' . $record->student->last_name,
                                    'Exam' => $record->exam->name,
                                    'Subject' => $record->subject->name,
                                    'Marks' => $record->marks_obtained,
                                    'Total' => $record->total_marks,
                                    'Percentage' => $record->percentage,
                                    'Grade' => $record->grade,
                                    'Comments' => $record->teacher_comments,
                                ];
                            });
                            
                            // Store as JSON or CSV
                            \Illuminate\Support\Facades\Storage::put('exports/results_' . now()->timestamp . '.json', json_encode($data));
                            
                            Notification::make()
                                ->title($records->count() . ' results exported successfully')
                                ->success()
                                ->send();
                        }),
                    
                    BulkAction::make('bulk_print')
                        ->label('Bulk Print')
                        ->icon('heroicon-o-printer')
                        ->color('info')
                        ->action(function (Collection $records) {
                            // Generate bulk PDF
                            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.bulk_results', ['results' => $records]);
                            return response()->streamDownload(function () use ($pdf) {
                                echo $pdf->output();
                            }, 'bulk_results.pdf');
                        }),
                ]),
            ])
            ->headerActions([
                Action::make('create')
                    ->label('New Result')
                    ->icon('heroicon-o-plus')
                    ->color('primary')
                    ->url(route('filament.admin.resources.results.create')),
                
                Action::make('bulk_upload')
                    ->label('Bulk Upload')
                    ->icon('heroicon-o-document-arrow-up')
                    ->color('gray')
                    ->form([
                        \Filament\Forms\Components\FileUpload::make('csv_file')
                            ->label('CSV File')
                            ->required()
                            ->acceptedFileTypes(['text/csv', 'application/csv'])
                            ->helperText('Upload a CSV file with columns: admission_number, exam_id, subject_id, marks_obtained, total_marks, teacher_comments'),
                    ])
                    ->action(function (array $data) {
                        // Process bulk upload
                        Notification::make()
                            ->title('Bulk upload initiated')
                            ->info()
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->searchable()
            ->persistFiltersInSession()
            ->striped()
            ->paginated([10, 25, 50, 100, 'all'])
            ->defaultPaginationPageOption(25)
            ->recordUrl(null); // Disable clicking on rows
    }
}
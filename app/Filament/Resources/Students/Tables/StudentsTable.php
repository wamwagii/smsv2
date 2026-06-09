<?php

namespace App\Filament\Resources\Students\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Table;
use App\Models\Classes;
use App\Models\AcademicYears;

class StudentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // Photo column - shows actual image instead of text
                ImageColumn::make('photo')
                    ->label('Photo')
                    ->circular()
                    ->width(40)
                    ->height(40)
                    ->defaultImageUrl(function ($record) {
                        return 'https://ui-avatars.com/api/?background=4F46E5&color=fff&name=' . urlencode($record->first_name . ' ' . $record->last_name);
                    }),
                
                // Admission Number - made more prominent
                TextColumn::make('admission_number')
                    ->label('Admission No.')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Admission number copied')
                    ->weight('bold')
                    ->color('primary'),
                
                // Full name combined for better display
                TextColumn::make('full_name')
                    ->label('Full Name')
                    ->getStateUsing(fn ($record) => trim($record->first_name . ' ' . ($record->middle_name ? $record->middle_name . ' ' : '') . $record->last_name))
                    ->searchable(['first_name', 'middle_name', 'last_name'])
                    ->sortable(['first_name', 'last_name'])
                    ->weight('semibold'),
                
                // Individual name fields (toggleable)
                TextColumn::make('first_name')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('middle_name')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('last_name')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                // Date of Birth with age calculation
                TextColumn::make('date_of_birth')
                    ->label('DOB')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable()
                    ->description(fn ($record) => $record->date_of_birth ? \Carbon\Carbon::parse($record->date_of_birth)->age . ' yrs' : ''),
                
                // Gender with badge
                BadgeColumn::make('gender')
                    ->label('Gender')
                    ->colors([
                        'primary' => 'male',
                        'danger' => 'female',
                        'gray' => 'other',
                    ])
                    ->icons([
                        'heroicon-o-mars' => 'male',
                        'heroicon-o-venus' => 'female',
                    ])
                    ->sortable()
                    ->toggleable(),
                
                // Class relationship (shows class code instead of ID)
                TextColumn::make('class.class_code')
                    ->label('Class')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('success')
                    ->toggleable(),
                
                // Academic Year relationship
                TextColumn::make('academicYear.name')
                    ->label('Academic Year')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                
                // Contact information group
                TextColumn::make('phone_number')
                    ->label('Phone')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->copyable(),
                
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->copyable(),
                
                // Status with badge
                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'alumni',
                        'danger' => 'suspended',
                        'gray' => 'transferred',
                        'dark' => 'expelled',
                    ])
                    ->icons([
                        'heroicon-o-check-circle' => 'active',
                        'heroicon-o-academic-cap' => 'alumni',
                        'heroicon-o-exclamation-circle' => 'suspended',
                    ])
                    ->sortable()
                    ->toggleable(),
                
                // Roll number
                TextColumn::make('roll_number')
                    ->label('Roll No.')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                // Parent/Guardian information (toggleable by default)
                TextColumn::make('father_name')
                    ->label('Father')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('father_phone')
                    ->label('Father\'s Phone')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('mother_name')
                    ->label('Mother')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('mother_phone')
                    ->label('Mother\'s Phone')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('guardian_name')
                    ->label('Guardian')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                // KCSE/KCPE information (toggleable)
                TextColumn::make('kcpse_index_number')
                    ->label('KCPSE Index')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                BadgeColumn::make('kcpe_grade')
                    ->label('KCPE Grade')
                    ->colors([
                        'success' => ['A', 'A-'],
                        'warning' => ['B+', 'B', 'B-'],
                        'danger' => ['C+', 'C', 'C-'],
                        'gray' => ['D+', 'D', 'D-', 'E'],
                    ])
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('kcpe_score')
                    ->label('KCPE Score')
                    ->numeric(decimalPlaces: 0)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                // Enrollment dates
                TextColumn::make('enrollment_date')
                    ->label('Enrolled')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('graduation_date')
                    ->label('Graduated')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                // Timestamps
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
                // Filter by status
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Active',
                        'alumni' => 'Alumni',
                        'transferred' => 'Transferred',
                        'suspended' => 'Suspended',
                        'expelled' => 'Expelled',
                    ])
                    ->placeholder('All Students'),
                
                // Filter by gender
                SelectFilter::make('gender')
                    ->options([
                        'male' => 'Male',
                        'female' => 'Female',
                        'other' => 'Other',
                    ]),
                
                // Filter by class
                SelectFilter::make('class_id')
                    ->label('Class')
                    ->relationship('class', 'class_code')
                    ->searchable()
                    ->preload(),
                
                // Filter by academic year
                SelectFilter::make('academic_year_id')
                    ->label('Academic Year')
                    ->relationship('academicYear', 'name')
                    ->searchable()
                    ->preload(),
                
                // Filter by enrollment date range
                Filter::make('enrollment_date_range')
                    ->form([
                        DatePicker::make('enrolled_from')
                            ->label('Enrolled From'),
                        DatePicker::make('enrolled_until')
                            ->label('Enrolled Until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['enrolled_from'],
                                fn ($query) => $query->whereDate('enrollment_date', '>=', $data['enrolled_from']),
                            )
                            ->when(
                                $data['enrolled_until'],
                                fn ($query) => $query->whereDate('enrollment_date', '<=', $data['enrolled_until']),
                            );
                    }),
                
                // Filter by date of birth range
                Filter::make('dob_range')
                    ->label('Date of Birth Range')
                    ->form([
                        DatePicker::make('dob_from')
                            ->label('Born After'),
                        DatePicker::make('dob_until')
                            ->label('Born Before'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['dob_from'],
                                fn ($query) => $query->whereDate('date_of_birth', '>=', $data['dob_from']),
                            )
                            ->when(
                                $data['dob_until'],
                                fn ($query) => $query->whereDate('date_of_birth', '<=', $data['dob_until']),
                            );
                    }),
                
                // Filter for deleted records (soft deletes)
                Filter::make('trashed')
                    ->label('Deleted Records')
                    ->toggle()
                    ->query(fn ($query) => $query->onlyTrashed()),
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
                        ->requiresConfirmation()
                        ->modalHeading('Delete Students')
                        ->modalDescription('Are you sure you want to delete these students? This action cannot be undone.'),
                    
                    // Bulk action to change status
                    \Filament\Actions\BulkAction::make('change_status')
                        ->label('Change Status')
                        ->icon('heroicon-o-tag')
                        ->color('warning')
                        ->form([
                            \Filament\Forms\Components\Select::make('status')
                                ->label('New Status')
                                ->options([
                                    'active' => 'Active',
                                    'alumni' => 'Alumni',
                                    'transferred' => 'Transferred',
                                    'suspended' => 'Suspended',
                                    'expelled' => 'Expelled',
                                ])
                                ->required(),
                        ])
                        ->action(function (array $data, \Illuminate\Database\Eloquent\Collection $records) {
                            $records->each->update(['status' => $data['status']]);
                            \Filament\Notifications\Notification::make()
                                ->title('Status updated for ' . $records->count() . ' students')
                                ->success()
                                ->send();
                        }),
                    
                    // Bulk action to change class
                    \Filament\Actions\BulkAction::make('change_class')
                        ->label('Change Class')
                        ->icon('heroicon-o-academic-cap')
                        ->color('info')
                        ->form([
                            \Filament\Forms\Components\Select::make('class_id')
                                ->label('New Class')
                                ->relationship('class', 'class_code')
                                ->required()
                                ->searchable(),
                        ])
                        ->action(function (array $data, \Illuminate\Database\Eloquent\Collection $records) {
                            $records->each->update(['class_id' => $data['class_id']]);
                            \Filament\Notifications\Notification::make()
                                ->title('Class changed for ' . $records->count() . ' students')
                                ->success()
                                ->send();
                        }),
                    
                    // Export bulk action
                    \Filament\Actions\ExportBulkAction::make()
                        ->label('Export Selected')
                        ->exporter(\App\Filament\Exports\StudentExporter::class),
                ]),
            ])
            ->headerActions([
                \Filament\Actions\Action::make('create')
                    ->label('New Student')
                    ->icon('heroicon-o-plus')
                    ->color('primary')
                    ->url(route('filament.admin.resources.students.create')),
                
                \Filament\Actions\ExportAction::make()
                    ->label('Export All')
                    ->exporter(\App\Filament\Exports\StudentExporter::class)
                    ->icon('heroicon-o-document-arrow-down'),
            ])
            ->defaultSort('admission_number', 'desc')
            ->searchable()
            ->persistFiltersInSession()
            ->persistSearchInSession()
            ->striped()
            ->paginated([10, 25, 50, 100, 'all'])
            ->defaultPaginationPageOption(25);
    }
}
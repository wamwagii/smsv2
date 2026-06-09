<?php

namespace App\Filament\Resources\Staff\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Table;
use App\Models\Department;

class StaffTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // Staff Photo
                ImageColumn::make('photo')
                    ->label('Photo')
                    ->circular()
                    ->width(40)
                    ->height(40)
                    ->defaultImageUrl(function ($record) {
                        return 'https://ui-avatars.com/api/?background=4F46E5&color=fff&name=' . urlencode($record->first_name . ' ' . $record->last_name);
                    }),
                
                // Staff Identification
                TextColumn::make('staff_number')
                    ->label('Staff No.')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Staff number copied')
                    ->weight('bold')
                    ->color('primary'),
                
                // Full Name (combined)
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
                
                // Contact Information
                TextColumn::make('phone_number')
                    ->label('Phone')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),
                
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                // Employment Information
                BadgeColumn::make('employment_type')
                    ->label('Employment')
                    ->colors([
                        'success' => 'full_time',
                        'warning' => 'part_time',
                        'info' => 'contract',
                        'gray' => 'temporary',
                    ])
                    ->formatStateUsing(fn ($state) => ucfirst(str_replace('_', ' ', $state)))
                    ->sortable()
                    ->toggleable(),
                
                BadgeColumn::make('role')
                    ->label('Role')
                    ->colors([
                        'primary' => 'teacher',
                        'success' => 'admin',
                        'warning' => 'accountant',
                        'info' => 'librarian',
                        'gray' => 'support',
                        'danger' => 'management',
                    ])
                    ->sortable()
                    ->toggleable(),
                
                // Department (relationship)
                TextColumn::make('department.name')
                    ->label('Department')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('position')
                    ->label('Position')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                // Status Badge
                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'on_leave',
                        'danger' => 'suspended',
                        'gray' => 'resigned',
                        'dark' => 'terminated',
                    ])
                    ->icons([
                        'heroicon-o-check-circle' => 'active',
                        'heroicon-o-clock' => 'on_leave',
                        'heroicon-o-exclamation-circle' => 'suspended',
                    ])
                    ->sortable(),
                
                // Dates
                TextColumn::make('hire_date')
                    ->label('Hired')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('contract_end_date')
                    ->label('Contract End')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->color(fn ($state) => $state && $state->isPast() ? 'danger' : null),
                
                // Professional Details
                TextColumn::make('tsc_number')
                    ->label('TSC No.')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('national_id')
                    ->label('ID No.')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->copyable(),
                
                TextColumn::make('qualification')
                    ->label('Qualification')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                // Statutory Information
                TextColumn::make('kra_pin')
                    ->label('KRA PIN')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('nhif_number')
                    ->label('NHIF')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('nssf_number')
                    ->label('NSSF')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                // Bank Details (toggleable by default)
                TextColumn::make('bank_name')
                    ->label('Bank')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('account_number')
                    ->label('Account No.')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                // Emergency Contact (toggleable by default)
                TextColumn::make('emergency_contact_name')
                    ->label('Emergency Contact')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('emergency_contact_phone')
                    ->label('Emergency Phone')
                    ->searchable()
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
                        'on_leave' => 'On Leave',
                        'suspended' => 'Suspended',
                        'resigned' => 'Resigned',
                        'terminated' => 'Terminated',
                    ])
                    ->placeholder('All Staff'),
                
                // Filter by role
                SelectFilter::make('role')
                    ->label('Role')
                    ->options([
                        'teacher' => 'Teacher',
                        'admin' => 'Administrator',
                        'accountant' => 'Accountant',
                        'librarian' => 'Librarian',
                        'support' => 'Support Staff',
                        'management' => 'Management',
                    ]),
                
                // Filter by employment type
                SelectFilter::make('employment_type')
                    ->label('Employment Type')
                    ->options([
                        'full_time' => 'Full Time',
                        'part_time' => 'Part Time',
                        'contract' => 'Contract',
                        'temporary' => 'Temporary',
                    ]),
                
                // Filter by department
                SelectFilter::make('department_id')
                    ->label('Department')
                    ->relationship('department', 'name')
                    ->searchable()
                    ->preload(),
                
                // Filter by gender
                SelectFilter::make('gender')
                    ->options([
                        'male' => 'Male',
                        'female' => 'Female',
                        'other' => 'Other',
                    ]),
                
                // Filter by hire date range
                Filter::make('hire_date_range')
                    ->label('Hire Date Range')
                    ->form([
                        DatePicker::make('hired_from')
                            ->label('Hired From'),
                        DatePicker::make('hired_until')
                            ->label('Hired Until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['hired_from'],
                                fn ($query) => $query->whereDate('hire_date', '>=', $data['hired_from']),
                            )
                            ->when(
                                $data['hired_until'],
                                fn ($query) => $query->whereDate('hire_date', '<=', $data['hired_until']),
                            );
                    }),
                
                // Filter for contract expiry
                Filter::make('contract_expiring')
                    ->label('Contract Expiring Soon')
                    ->query(fn ($query) => $query->where('contract_end_date', '<=', now()->addMonths(3))->where('contract_end_date', '>=', now()))
                    ->toggle(),
                
                // Filter for deleted records
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
                        ->requiresConfirmation(),
                    
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
                                    'on_leave' => 'On Leave',
                                    'suspended' => 'Suspended',
                                    'resigned' => 'Resigned',
                                    'terminated' => 'Terminated',
                                ])
                                ->required(),
                        ])
                        ->action(function (array $data, \Illuminate\Database\Eloquent\Collection $records) {
                            $records->each->update(['status' => $data['status']]);
                            \Filament\Notifications\Notification::make()
                                ->title('Status updated for ' . $records->count() . ' staff members')
                                ->success()
                                ->send();
                        }),
                    
                    // Bulk action to change department
                    \Filament\Actions\BulkAction::make('change_department')
                        ->label('Change Department')
                        ->icon('heroicon-o-building-library')
                        ->color('info')
                        ->form([
                            \Filament\Forms\Components\Select::make('department_id')
                                ->label('New Department')
                                ->relationship('department', 'name')
                                ->required()
                                ->searchable(),
                        ])
                        ->action(function (array $data, \Illuminate\Database\Eloquent\Collection $records) {
                            $records->each->update(['department_id' => $data['department_id']]);
                            \Filament\Notifications\Notification::make()
                                ->title('Department changed for ' . $records->count() . ' staff members')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->headerActions([
                \Filament\Actions\Action::make('create')
                    ->label('New Staff')
                    ->icon('heroicon-o-plus')
                    ->color('primary')
                    ->url(route('filament.admin.resources.staff.create')),
            ])
            ->defaultSort('staff_number', 'desc')
            ->searchable()
            ->persistFiltersInSession()
            ->persistSearchInSession()
            ->striped()
            ->paginated([10, 25, 50, 100, 'all'])
            ->defaultPaginationPageOption(25);
    }
}
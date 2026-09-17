<?php

namespace App\Filament\Resources\Staff\Schemas;

use App\Models\Staff;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StaffInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Personal Information')
                    ->columns(3)
                    ->schema([
                        ImageEntry::make('photo')
                            ->label('Photo')
                            ->circular()
                            ->imageHeight(100)
                            ->defaultImageUrl(fn (Staff $record) =>
                                'https://ui-avatars.com/api/?background=4F46E5&color=fff&name='
                                . urlencode($record->first_name . ' ' . $record->last_name)
                            )
                            ->columnSpan(1),

                        TextEntry::make('staff_number')
                            ->label('Staff No.')
                            ->weight('bold')
                            ->color('primary')
                            ->copyable()
                            ->copyMessage('Staff number copied'),

                        TextEntry::make('full_name')
                            ->label('Full Name')
                            ->state(fn (Staff $record) => trim(
                                $record->first_name
                                . ' ' . ($record->middle_name ? $record->middle_name . ' ' : '')
                                . $record->last_name
                            ))
                            ->weight('semibold'),

                        TextEntry::make('date_of_birth')
                            ->label('Date of Birth')
                            ->date('d/m/Y')
                            ->belowContent(fn (Staff $record) =>
                                $record->date_of_birth
                                    ? \Carbon\Carbon::parse($record->date_of_birth)->age . ' years old'
                                    : null
                            ),

                        TextEntry::make('gender')
                            ->badge()
                            ->color(fn (?string $state) => match ($state) {
                                'male'   => 'primary',
                                'female' => 'danger',
                                default  => 'gray',
                            })
                            ->formatStateUsing(fn (?string $state) => ucfirst($state ?? '-')),

                        TextEntry::make('national_id')
                            ->label('National ID')
                            ->placeholder('-')
                            ->copyable(),

                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (?string $state) => match ($state) {
                                'active'     => 'success',
                                'on_leave'   => 'warning',
                                'suspended'  => 'danger',
                                'resigned'   => 'gray',
                                'terminated' => 'dark',
                                default      => 'gray',
                            })
                            ->formatStateUsing(fn (?string $state) => ucfirst(str_replace('_', ' ', $state ?? '-'))),
                    ]),

                Section::make('Employment Information')
                    ->columns(3)
                    ->collapsible()
                    ->schema([
                        TextEntry::make('role')
                            ->badge()
                            ->color(fn (?string $state) => match ($state) {
                                'teacher'    => 'primary',
                                'admin'      => 'success',
                                'accountant' => 'warning',
                                'librarian'  => 'info',
                                'support'    => 'gray',
                                'management' => 'danger',
                                default      => 'gray',
                            })
                            ->formatStateUsing(fn (?string $state) => ucfirst($state ?? '-')),

                        TextEntry::make('employment_type')
                            ->label('Employment Type')
                            ->badge()
                            ->color(fn (?string $state) => match ($state) {
                                'full_time' => 'success',
                                'part_time' => 'warning',
                                'contract'  => 'info',
                                'temporary' => 'gray',
                                default     => 'gray',
                            })
                            ->formatStateUsing(fn (?string $state) => ucfirst(str_replace('_', ' ', $state ?? '-'))),

                        TextEntry::make('department.name')
                            ->label('Department')
                            ->badge()
                            ->color('success')
                            ->placeholder('-'),

                        TextEntry::make('position')
                            ->placeholder('-'),

                        TextEntry::make('hire_date')
                            ->label('Hire Date')
                            ->date('d/m/Y'),

                        TextEntry::make('contract_end_date')
                            ->label('Contract End Date')
                            ->date('d/m/Y')
                            ->placeholder('-')
                            ->color(fn ($state) => $state && $state->isPast() ? 'danger' : null)
                            ->belowContent(fn (Staff $record) => 
                                $record->contract_end_date && $record->contract_end_date->isFuture()
                                    ? 'Expires in ' . now()->diffInDays($record->contract_end_date) . ' days'
                                    : ($record->contract_end_date && $record->contract_end_date->isPast() ? '⚠️ Expired' : null)
                            ),
                    ]),

                Section::make('Contact Information')
                    ->columns(2)
                    ->collapsible()
                    ->schema([
                        TextEntry::make('phone_number')
                            ->label('Phone')
                            ->icon('heroicon-o-phone')
                            ->copyable(),

                        TextEntry::make('email')
                            ->label('Email Address')
                            ->icon('heroicon-o-envelope')
                            ->copyable(),

                        TextEntry::make('physical_address')
                            ->label('Physical Address')
                            ->icon('heroicon-o-map-pin')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),

                Section::make('Professional Qualifications')
                    ->columns(2)
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextEntry::make('tsc_number')
                            ->label('TSC Number')
                            ->placeholder('-')
                            ->copyable(),

                        TextEntry::make('qualification')
                            ->placeholder('-'),

                        TextEntry::make('subjects_taught')
                            ->label('Subjects Taught')
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('certifications')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),

                Section::make('Statutory Information')
                    ->columns(3)
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextEntry::make('kra_pin')
                            ->label('KRA PIN')
                            ->placeholder('-')
                            ->copyable(),

                        TextEntry::make('nhif_number')
                            ->label('NHIF Number')
                            ->placeholder('-')
                            ->copyable(),

                        TextEntry::make('nssf_number')
                            ->label('NSSF Number')
                            ->placeholder('-')
                            ->copyable(),
                    ]),

                Section::make('Bank Details')
                    ->columns(3)
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextEntry::make('bank_name')
                            ->label('Bank')
                            ->placeholder('-'),

                        TextEntry::make('bank_branch')
                            ->label('Branch')
                            ->placeholder('-'),

                        TextEntry::make('account_number')
                            ->label('Account Number')
                            ->placeholder('-')
                            ->copyable(),
                    ]),

                Section::make('Emergency Contact')
                    ->columns(3)
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextEntry::make('emergency_contact_name')
                            ->label('Name')
                            ->placeholder('-'),

                        TextEntry::make('emergency_contact_phone')
                            ->label('Phone')
                            ->placeholder('-')
                            ->copyable(),

                        TextEntry::make('emergency_contact_relation')
                            ->label('Relationship')
                            ->placeholder('-'),
                    ]),

                Section::make('Record Metadata')
                    ->columns(3)
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('-'),

                        TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('-'),

                        TextEntry::make('deleted_at')
                            ->label('Deleted')
                            ->dateTime('d/m/Y H:i')
                            ->color('danger')
                            ->visible(fn (Staff $record): bool => $record->trashed()),
                    ]),
            ]);
    }
}
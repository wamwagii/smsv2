<?php

namespace App\Filament\Resources\Staff\Schemas;

use App\Models\Staff;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class StaffInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('staff_number'),
                TextEntry::make('first_name'),
                TextEntry::make('middle_name')
                    ->placeholder('-'),
                TextEntry::make('last_name'),
                TextEntry::make('date_of_birth')
                    ->date(),
                TextEntry::make('gender'),
                TextEntry::make('photo')
                    ->placeholder('-'),
                TextEntry::make('phone_number'),
                TextEntry::make('email')
                    ->label('Email address'),
                TextEntry::make('physical_address')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('employment_type'),
                TextEntry::make('hire_date')
                    ->date(),
                TextEntry::make('contract_end_date')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('tsc_number')
                    ->placeholder('-'),
                TextEntry::make('national_id'),
                TextEntry::make('kra_pin')
                    ->placeholder('-'),
                TextEntry::make('nhif_number')
                    ->placeholder('-'),
                TextEntry::make('nssf_number')
                    ->placeholder('-'),
                TextEntry::make('qualification')
                    ->placeholder('-'),
                TextEntry::make('subjects_taught')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('certifications')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('bank_name')
                    ->placeholder('-'),
                TextEntry::make('bank_branch')
                    ->placeholder('-'),
                TextEntry::make('account_number')
                    ->placeholder('-'),
                TextEntry::make('position')
                    ->placeholder('-'),
                TextEntry::make('department_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('role'),
                TextEntry::make('status'),
                TextEntry::make('emergency_contact_name')
                    ->placeholder('-'),
                TextEntry::make('emergency_contact_phone')
                    ->placeholder('-'),
                TextEntry::make('emergency_contact_relation')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Staff $record): bool => $record->trashed()),
            ]);
    }
}

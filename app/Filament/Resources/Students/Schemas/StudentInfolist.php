<?php

namespace App\Filament\Resources\Students\Schemas;

use App\Models\Student;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class StudentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('admission_number'),
                TextEntry::make('first_name'),
                TextEntry::make('middle_name')
                    ->placeholder('-'),
                TextEntry::make('last_name'),
                TextEntry::make('date_of_birth')
                    ->date(),
                TextEntry::make('gender'),
                TextEntry::make('photo')
                    ->placeholder('-'),
                TextEntry::make('birth_certificate_number')
                    ->placeholder('-'),
                TextEntry::make('phone_number')
                    ->placeholder('-'),
                TextEntry::make('email')
                    ->label('Email address')
                    ->placeholder('-'),
                TextEntry::make('physical_address')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('class_id')
                    ->numeric(),
                TextEntry::make('academic_year_id')
                    ->numeric(),
                TextEntry::make('roll_number')
                    ->placeholder('-'),
                TextEntry::make('kcpse_index_number')
                    ->placeholder('-'),
                TextEntry::make('kcpe_grade')
                    ->placeholder('-'),
                TextEntry::make('kcpe_score')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('father_name')
                    ->placeholder('-'),
                TextEntry::make('father_phone')
                    ->placeholder('-'),
                TextEntry::make('mother_name')
                    ->placeholder('-'),
                TextEntry::make('mother_phone')
                    ->placeholder('-'),
                TextEntry::make('guardian_name')
                    ->placeholder('-'),
                TextEntry::make('guardian_phone')
                    ->placeholder('-'),
                TextEntry::make('guardian_relation')
                    ->placeholder('-'),
                TextEntry::make('status'),
                TextEntry::make('enrollment_date')
                    ->date(),
                TextEntry::make('graduation_date')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('medical_notes')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Student $record): bool => $record->trashed()),
            ]);
    }
}

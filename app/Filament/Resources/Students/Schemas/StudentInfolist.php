<?php

namespace App\Filament\Resources\Students\Schemas;

use App\Models\Student;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Schemas\Components\Section;      // ← moved in v4
use Filament\Schemas\Schema;

class StudentInfolist
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
                            ->imageHeight(100)               // ← renamed from height()
                            ->defaultImageUrl(fn (Student $record) =>
                                'https://ui-avatars.com/api/?background=4F46E5&color=fff&name='
                                . urlencode($record->first_name . ' ' . $record->last_name)
                            )
                            ->columnSpan(1),

                        TextEntry::make('admission_number')
                            ->label('Admission No.')
                            ->weight('bold')
                            ->color('primary')
                            ->copyable()
                            ->copyMessage('Admission number copied'),

                        TextEntry::make('full_name')
                            ->label('Full Name')
                            ->state(fn (Student $record) => $record->full_name)
                            ->weight('semibold'),

                        TextEntry::make('date_of_birth')
                            ->label('Date of Birth')
                            ->date('d/m/Y')
                            ->belowContent(fn (Student $record) =>
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

                        TextEntry::make('birth_certificate_number')
                            ->label('Birth Certificate No.')
                            ->placeholder('-')
                            ->copyable(),

                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (?string $state) => match ($state) {
                                'active'      => 'success',
                                'alumni'      => 'warning',
                                'transferred' => 'gray',
                                'suspended'   => 'danger',
                                'expelled'    => 'dark',
                                default       => 'gray',
                            })
                            ->formatStateUsing(fn (?string $state) => ucfirst($state ?? '-')),
                    ]),

                Section::make('Contact Information')
                    ->columns(2)
                    ->collapsible()
                    ->schema([
                        TextEntry::make('phone_number')
                            ->label('Phone')
                            ->placeholder('-')
                            ->icon('heroicon-o-phone')
                            ->copyable(),

                        TextEntry::make('email')
                            ->label('Email Address')
                            ->placeholder('-')
                            ->icon('heroicon-o-envelope')
                            ->copyable(),

                        TextEntry::make('physical_address')
                            ->label('Physical Address')
                            ->placeholder('-')
                            ->icon('heroicon-o-map-pin')
                            ->columnSpanFull(),
                    ]),

                Section::make('Academic Information')
                    ->columns(3)
                    ->collapsible()
                    ->schema([
                        TextEntry::make('class.class_code')
                            ->label('Class')
                            ->badge()
                            ->color('success')
                            ->placeholder('-'),

                        TextEntry::make('academicYear.name')
                            ->label('Academic Year')
                            ->placeholder('-'),

                        TextEntry::make('roll_number')
                            ->label('Roll No.')
                            ->placeholder('-'),

                        TextEntry::make('enrollment_date')
                            ->label('Enrollment Date')
                            ->date('d/m/Y'),

                        TextEntry::make('graduation_date')
                            ->label('Graduation Date')
                            ->date('d/m/Y')
                            ->placeholder('-'),

                        TextEntry::make('kcpse_index_number')
                            ->label('KCPSE Index No.')
                            ->placeholder('-'),
                    ]),

                Section::make('KCPE Results')
                    ->columns(2)
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextEntry::make('kcpe_grade')
                            ->label('KCPE Grade')
                            ->badge()
                            ->color(fn (?string $state) => match (true) {
                                in_array($state, ['A', 'A-'])            => 'success',
                                in_array($state, ['B+', 'B', 'B-'])      => 'warning',
                                in_array($state, ['C+', 'C', 'C-'])      => 'danger',
                                in_array($state, ['D+', 'D', 'D-', 'E']) => 'gray',
                                default                                  => 'gray',
                            })
                            ->placeholder('-'),

                        TextEntry::make('kcpe_score')
                            ->label('KCPE Score')
                            ->numeric()
                            ->placeholder('-'),
                    ]),

                Section::make('Parent / Guardian (Legacy Fields)')
                    ->description('Static contact info captured at admission.')
                    ->columns(2)
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextEntry::make('father_name')
                            ->label('Father\'s Name')
                            ->placeholder('-'),
                        TextEntry::make('father_phone')
                            ->label('Father\'s Phone')
                            ->placeholder('-')
                            ->copyable(),

                        TextEntry::make('mother_name')
                            ->label('Mother\'s Name')
                            ->placeholder('-'),
                        TextEntry::make('mother_phone')
                            ->label('Mother\'s Phone')
                            ->placeholder('-')
                            ->copyable(),

                        TextEntry::make('guardian_name')
                            ->label('Guardian Name')
                            ->placeholder('-'),
                        TextEntry::make('guardian_phone')
                            ->label('Guardian Phone')
                            ->placeholder('-')
                            ->copyable(),

                        TextEntry::make('guardian_relation')
                            ->label('Relationship')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),

                Section::make('Linked Parents / Guardians')
                    ->description('Guardians connected through the portal.')
                    ->columns(1)
                    ->collapsible()
                    ->schema([
                        TextEntry::make('parents_list')
                            ->hiddenLabel()
                            ->state(function (Student $record) {
                                return $record->parents->map(function ($parent) {
                                    $primary = $parent->pivot->is_primary_contact ? ' ⭐' : '';
                                    return "{$parent->full_name}{$primary} — {$parent->phone_number}";
                                })->all();
                            })
                            ->listWithLineBreaks()
                            ->bulleted()
                            ->placeholder('No linked parents/guardians'),
                    ]),

                Section::make('Additional Notes')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextEntry::make('medical_notes')
                            ->label('Medical Notes')
                            ->placeholder('-')
                            ->columnSpanFull(),
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
                            ->visible(fn (Student $record): bool => $record->trashed()),
                    ]),
            ]);
    }
}
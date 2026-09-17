<?php

namespace App\Filament\Resources\Attendances\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AttendanceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Attendance Details')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('date')
                            ->label('Date')
                            ->date('d/m/Y')
                            ->weight('bold'),

                        TextEntry::make('class.class_code')
                            ->label('Class')
                            ->badge()
                            ->color('primary'),

                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (?string $state) => match ($state) {
                                'present' => 'success',
                                'absent'  => 'danger',
                                'late'    => 'warning',
                                'excused' => 'info',
                                'holiday' => 'gray',
                                default   => 'gray',
                            })
                            ->formatStateUsing(fn (?string $state) => ucfirst($state ?? '-')),

                        TextEntry::make('student.admission_number')
                            ->label('Admission No.'),

                        TextEntry::make('student.full_name')
                            ->label('Student Name')
                            ->state(fn ($record) => $record->student?->full_name),

                        TextEntry::make('student.roll_number')
                            ->label('Roll No.')
                            ->placeholder('-'),
                    ]),

                Section::make('Timing')
                    ->description('Arrival and departure details')
                    ->icon('heroicon-o-clock')
                    ->columns(3)
                    ->visible(fn ($record) => filled($record->arrival_time) || filled($record->departure_time))
                    ->schema([
                        TextEntry::make('arrival_time')
                            ->label('Arrival Time')
                            ->time('H:i')
                            ->placeholder('-'),

                        TextEntry::make('departure_time')
                            ->label('Departure Time')
                            ->time('H:i')
                            ->placeholder('-'),

                        TextEntry::make('time_at_school')
                            ->label('Time at School')
                            ->state(function ($record) {
                                if (!$record->arrival_time || !$record->departure_time) {
                                    return null;
                                }

                                $arrival   = \Carbon\Carbon::parse($record->arrival_time);
                                $departure = \Carbon\Carbon::parse($record->departure_time);

                                $minutes = $arrival->diffInMinutes($departure);
                                $hours   = floor($minutes / 60);
                                $mins    = $minutes % 60;

                                return trim(
                                    ($hours > 0 ? "{$hours}h " : '')
                                    . ($mins > 0 ? "{$mins}m" : '')
                                ) ?: '0m';
                            })
                            ->badge()
                            ->color('success')
                            ->placeholder('-'),
                    ]),

                Section::make('Reason')
                    ->description('Explanation for the absence or excuse')
                    ->icon('heroicon-o-document-text')
                    ->visible(fn ($record) => filled($record->reason))
                    ->schema([
                        TextEntry::make('reason')
                            ->hiddenLabel()
                            ->prose(),
                    ]),

                Section::make('Record Metadata')
                    ->columns(3)
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextEntry::make('markedBy.full_name')
                            ->label('Marked By')
                            ->state(fn ($record) => $record->markedBy?->full_name)
                            ->placeholder('System'),

                        TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime('d/m/Y H:i'),

                        TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime('d/m/Y H:i'),
                    ]),
            ]);
    }
}
<?php

namespace App\Filament\Resources\Attendances\Schemas;

use App\Models\Student;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class AttendanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Attendance Record')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('class_id')
                                ->label('Class')
                                ->relationship('class', 'class_code')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->live()
                                ->afterStateUpdated(fn ($set) => $set('student_id', null)),

                            DatePicker::make('date')
                                ->label('Date')
                                ->required()
                                ->native(false)
                                ->displayFormat('d/m/Y')
                                ->default(today())
                                ->maxDate(today()),
                        ]),

                        Select::make('student_id')
                            ->label('Student')
                            ->options(function ($get) {
                                $classId = $get('class_id');
                                if (!$classId) return [];

                                return Student::where('class_id', $classId)
                                    ->where('status', 'active')
                                    ->orderBy('roll_number')
                                    ->get()
                                    ->mapWithKeys(fn ($s) => [
                                        $s->id => trim(
                                            ($s->roll_number ? "{$s->roll_number}. " : '')
                                            . "{$s->admission_number} - {$s->full_name}"
                                        ),
                                    ]);
                            })
                            ->required()
                            ->searchable()
                            ->preload(),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'present' => 'Present',
                                'absent'  => 'Absent',
                                'late'    => 'Late',
                                'excused' => 'Excused',
                                'holiday' => 'Holiday',
                            ])
                            ->required()
                            ->default('present')
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function ($set, $state) {
                                // Auto-fill arrival time when status is "late"
                                if ($state === 'late') {
                                    $set('arrival_time', now()->format('H:i'));
                                }
                            }),
                    ]),

                Section::make('Timing')
                    ->description('Only relevant for late arrivals and early departures')
                    ->icon('heroicon-o-clock')
                    ->columns(2)
                    ->collapsible()
                    ->collapsed()
                    ->visible(fn ($get) => in_array($get('status'), ['late', 'present'], true))
                    ->schema([
                        TimePicker::make('arrival_time')
                            ->label('Arrival Time')
                            ->native(false)
                            ->seconds(false)
                            ->displayFormat('H:i'),

                        TimePicker::make('departure_time')
                            ->label('Departure Time')
                            ->native(false)
                            ->seconds(false)
                            ->displayFormat('H:i'),
                    ]),

                Section::make('Reason')
                    ->description('Required for absences and excuses')
                    ->icon('heroicon-o-document-text')
                    ->collapsible()
                    ->collapsed()
                    ->visible(fn ($get) => in_array($get('status'), ['absent', 'excused'], true))
                    ->schema([
                        Textarea::make('reason')
                            ->label('Reason')
                            ->rows(3)
                            ->maxLength(500)
                            ->placeholder('e.g., Sick leave, family emergency, medical appointment'),
                    ]),
            ]);
    }
}
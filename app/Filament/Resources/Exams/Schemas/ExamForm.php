<?php

namespace App\Filament\Resources\Exams\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use App\Models\AcademicYears;

class ExamForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Exam Information')
                    ->description('Basic details about the exam')
                    ->icon('heroicon-o-document-check')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')
                                ->label('Exam Name')
                                ->required()
                                ->maxLength(255)
                                ->placeholder('e.g., End of Term Exam Term 2'),

                            Select::make('term')
                                ->label('Term')
                                ->options([
                                    'term_1' => 'Term 1 (January - March)',
                                    'term_2' => 'Term 2 (April - July)',
                                    'term_3' => 'Term 3 (August - November)',
                                ])
                                ->required()
                                ->native(false),
                        ]),

                        Grid::make(2)->schema([
                            Select::make('academic_year_id')
                                ->label('Academic Year')
                                ->relationship('academicYear', 'name')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->default(fn () => AcademicYears::where('is_current', true)->value('id'))
                                ->helperText('Which academic year this exam belongs to'),

                            Select::make('status')
                                ->label('Status')
                                ->options([
                                    'upcoming'  => 'Upcoming',
                                    'ongoing'   => 'Ongoing',
                                    'completed' => 'Completed',
                                    'published' => 'Published',
                                ])
                                ->required()
                                ->default('upcoming')
                                ->native(false)
                                ->helperText('Students only see results when the exam is published'),
                        ]),
                    ]),

                Section::make('Schedule')
                    ->description('When the exam takes place')
                    ->icon('heroicon-o-calendar')
                    ->schema([
                        Grid::make(2)->schema([
                            DatePicker::make('start_date')
                                ->label('Start Date')
                                ->required()
                                ->native(false)
                                ->displayFormat('d/m/Y'),

                            DatePicker::make('end_date')
                                ->label('End Date')
                                ->required()
                                ->native(false)
                                ->displayFormat('d/m/Y')
                                ->afterOrEqual('start_date'),
                        ]),
                    ]),

                Section::make('Marking Scheme')
                    ->description('Marks configuration for this exam')
                    ->icon('heroicon-o-calculator')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('total_marks')
                                ->label('Total Marks')
                                ->numeric()
                                ->required()
                                ->minValue(1)
                                ->default(100)
                                ->helperText('Maximum marks per subject'),

                            TextInput::make('passing_marks')
                                ->label('Passing Marks')
                                ->numeric()
                                ->required()
                                ->minValue(0)
                                ->default(50)
                                ->maxValue(fn ($get) => (int) $get('total_marks') ?: 100)
                                ->helperText('Minimum marks to pass a subject'),
                        ]),
                    ]),

                Section::make('Additional Information')
                    ->icon('heroicon-o-information-circle')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Textarea::make('description')
                            ->label('Description')
                            ->maxLength(65535)
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Optional notes about this exam'),
                    ]),
            ]);
    }
}
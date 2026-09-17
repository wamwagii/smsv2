<?php

namespace App\Filament\Resources\Results\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use App\Models\Student;
use App\Models\Exam;
use App\Models\Subject;
use App\Models\Result;

class ResultForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Result Information')
                    ->description('Record student exam results')
                    ->icon('heroicon-o-document-chart-bar')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('exam_id')
                                ->label('Exam')
                                ->relationship('exam', 'name')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->live()
                                ->afterStateUpdated(fn ($set, $get) => static::loadExamDetails($set, $get))
                                ->helperText('Select the exam'),

                            Select::make('class_id')
                                ->label('Class')
                                ->relationship('class', 'class_code')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->live()
                                ->afterStateUpdated(function ($set, $get) {
                                    // Reset dependent selections when class changes
                                    $set('student_id', null);
                                    $set('subject_id', null);
                                })
                                ->helperText('Select the class'),

                            Select::make('student_id')
                                ->label('Student')
                                ->options(function ($get) {
                                    $classId = $get('class_id');
                                    if (!$classId) return [];

                                    return Student::where('class_id', $classId)
                                        ->where('status', 'active')
                                        ->orderBy('roll_number')
                                        ->select('id', 'admission_number', 'first_name', 'last_name', 'roll_number')
                                        ->get()
                                        ->mapWithKeys(fn ($s) => [
                                            $s->id => trim(($s->roll_number ? "{$s->roll_number}. " : '')
                                                . "{$s->admission_number} - {$s->first_name} {$s->last_name}"),
                                        ]);
                                })
                                ->required()
                                ->searchable()
                                ->preload()
                                ->live()
                                ->helperText('Select the student'),

                            Select::make('subject_id')
                                ->label('Subject')
                                ->options(function ($get) {
                                    $classId = $get('class_id');
                                    if (!$classId) return [];

                                    return Subject::whereHas('classes', function ($query) use ($classId) {
                                            $query->where('class_id', $classId);
                                        })
                                        ->where('is_active', true)
                                        ->orderBy('name')
                                        ->select('id', 'code', 'name')
                                        ->get()
                                        ->mapWithKeys(fn ($s) => [
                                            $s->id => "{$s->code} - {$s->name}",
                                        ]);
                                })
                                ->required()
                                ->searchable()
                                ->preload()
                                ->live()
                                ->helperText('Select the subject'),
                        ]),

                        Grid::make(2)->schema([
                            TextInput::make('marks_obtained')
                                ->label('Marks Obtained')
                                ->numeric()
                                ->required()
                                ->minValue(0)
                                ->maxValue(fn ($get) => (float) ($get('total_marks') ?: 100))
                                ->rules([
                                    fn ($get) => function ($attribute, $value, $fail) use ($get) {
                                        $total = (float) $get('total_marks');
                                        if ($total > 0 && (float) $value > $total) {
                                            $fail("Marks cannot exceed total marks ({$total}).");
                                        }
                                    },
                                ])
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn ($set, $get) => static::calculateResults($set, $get))
                                ->helperText('Marks obtained by the student'),

                            TextInput::make('total_marks')
                                ->label('Total Marks')
                                ->numeric()
                                ->required()
                                ->minValue(1)
                                ->default(100)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn ($set, $get) => static::calculateResults($set, $get))
                                ->helperText('Maximum possible marks'),

                            TextInput::make('percentage')
                                ->label('Percentage')
                                ->numeric()
                                ->prefix('%')
                                ->readOnly()
                                ->dehydrated(true)
                                ->helperText('Auto-calculated percentage'),

                            TextInput::make('grade')
                                ->label('Grade')
                                ->maxLength(2)
                                ->readOnly()
                                ->dehydrated(true)
                                ->helperText('Auto-calculated grade'),
                        ]),

                        Textarea::make('teacher_comments')
                            ->label("Teacher's Comments")
                            ->maxLength(65535)
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText('Additional comments or feedback from the teacher'),

                        Repeater::make('assessment_breakdown')
                            ->label('Assessment Breakdown')
                            ->schema([
                                Grid::make(3)->schema([
                                    TextInput::make('assessment_type')
                                        ->label('Assessment Type')
                                        ->required()
                                        ->placeholder('e.g., CAT 1, CAT 2, Assignment, Final Exam'),

                                    TextInput::make('marks')
                                        ->label('Marks')
                                        ->numeric()
                                        ->required()
                                        ->minValue(0),

                                    TextInput::make('weight')
                                        ->label('Weight (%)')
                                        ->numeric()
                                        ->minValue(0)
                                        ->maxValue(100)
                                        ->helperText('Optional'),
                                ]),
                            ])
                            ->columnSpanFull()
                            ->defaultItems(0)
                            ->addActionLabel('Add Assessment Component')
                            ->reorderable(true)
                            ->helperText('Break down marks by assessment type (optional)'),
                    ]),

                Section::make('Record Metadata')
                    ->description('System information')
                    ->icon('heroicon-o-information-circle')
                    ->columns(2)
                    ->collapsible()
                    ->collapsed()
                    ->visible(fn ($record) => $record !== null)
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('-'),

                        TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('-'),
                    ]),
            ]);
    }

    /* -----------------------------------------------------------------
     |  Reactive helpers
     | -----------------------------------------------------------------
     */

    protected static function loadExamDetails($set, $get): void
    {
        $examId = $get('exam_id');
        if (!$examId) return;

        $exam = Exam::select('id', 'total_marks')->find($examId);
        if ($exam) {
            $set('total_marks', $exam->total_marks ?? 100);
        }
    }

    protected static function calculateResults($set, $get): void
    {
        $marksObtained = (float) ($get('marks_obtained') ?? 0);
        $totalMarks = (float) ($get('total_marks') ?? 100);

        if ($totalMarks > 0) {
            $percentage = round(($marksObtained / $totalMarks) * 100, 2);
            $set('percentage', $percentage);
            $set('grade', Result::calculateGrade($percentage));
        } else {
            $set('percentage', null);
            $set('grade', null);
        }
    }
}
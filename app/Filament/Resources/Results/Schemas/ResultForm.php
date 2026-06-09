<?php

namespace App\Filament\Resources\Results\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Schema;
use App\Models\Student;
use App\Models\Exam;
use App\Models\Subject;
use App\Models\Classes;
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
                        Grid::make(2)
                            ->schema([
                                Select::make('exam_id')
                                    ->label('Exam')
                                    ->relationship('exam', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function ($set, $get) {
                                        static::loadExamDetails($set, $get);
                                    })
                                    ->helperText('Select the exam'),
                                
                                Select::make('class_id')
                                    ->label('Class')
                                    ->relationship('class', 'class_code')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function ($set, $get) {
                                        static::loadClassStudents($set, $get);
                                    })
                                    ->helperText('Select the class'),
                                
                                Select::make('student_id')
                                    ->label('Student')
                                    ->options(function ($get) {
                                        $classId = $get('class_id');
                                        if ($classId) {
                                            return Student::where('class_id', $classId)
                                                ->where('status', 'active')
                                                ->get()
                                                ->mapWithKeys(function ($student) {
                                                    return [$student->id => $student->admission_number . ' - ' . $student->first_name . ' ' . $student->last_name];
                                                });
                                        }
                                        return [];
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
                                        if ($classId) {
                                            return Subject::whereHas('classes', function ($query) use ($classId) {
                                                $query->where('class_id', $classId);
                                            })
                                            ->where('is_active', true)
                                            ->get()
                                            ->mapWithKeys(function ($subject) {
                                                return [$subject->id => $subject->code . ' - ' . $subject->name];
                                            });
                                        }
                                        return [];
                                    })
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->helperText('Select the subject'),
                            ]),
                        
                        Grid::make(3)
                            ->schema([
                                TextInput::make('marks_obtained')
                                    ->label('Marks Obtained')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->live()
                                    ->afterStateUpdated(function ($set, $get) {
                                        static::calculateResults($set, $get);
                                    })
                                    ->helperText('Marks obtained by the student'),
                                
                                TextInput::make('total_marks')
                                    ->label('Total Marks')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->default(100)
                                    ->live()
                                    ->afterStateUpdated(function ($set, $get) {
                                        static::calculateResults($set, $get);
                                    })
                                    ->helperText('Maximum possible marks'),
                                
                                TextInput::make('percentage')
                                    ->label('Percentage')
                                    ->numeric()
                                    ->prefix('%')
                                    ->disabled()
                                    ->dehydrated(true)
                                    ->helperText('Auto-calculated percentage'),
                                
                                TextInput::make('grade')
                                    ->label('Grade')
                                    ->maxLength(2)
                                    ->disabled()
                                    ->dehydrated(true)
                                    ->helperText('Auto-calculated grade'),
                            ]),
                        
                        Textarea::make('teacher_comments')
                            ->label('Teacher\'s Comments')
                            ->maxLength(65535)
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText('Additional comments or feedback from the teacher'),
                        
                        Repeater::make('assessment_breakdown')
                            ->label('Assessment Breakdown')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
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
                                            ->helperText('Optional: Weight percentage'),
                                    ]),
                            ])
                            ->columnSpanFull()
                            ->defaultItems(0)
                            ->addActionLabel('Add Assessment Component')
                            ->reorderable(true)
                            ->helperText('Break down marks by assessment type (optional)'),
                    ]),
                
                // Display Additional Info
                Section::make('Additional Information')
                    ->description('System information')
                    ->icon('heroicon-o-information-circle')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Placeholder::make('created_at')
                            ->label('Created')
                            ->content(fn ($record) => $record ? $record->created_at->format('d/m/Y H:i') : '-'),
                        
                        Placeholder::make('updated_at')
                            ->label('Last Updated')
                            ->content(fn ($record) => $record ? $record->updated_at->format('d/m/Y H:i') : '-'),
                    ]),
            ]);
    }
    
    protected static function loadExamDetails($set, $get)
    {
        $examId = $get('exam_id');
        if ($examId) {
            $exam = Exam::find($examId);
            if ($exam) {
                // Optionally set total marks from exam default
                $set('total_marks', $exam->total_marks ?? 100);
            }
        }
    }
    
    protected static function loadClassStudents($set, $get)
    {
        $classId = $get('class_id');
        if ($classId) {
            // Reset student selection when class changes
            $set('student_id', null);
        }
    }
    
    protected static function calculateResults($set, $get)
    {
        $marksObtained = floatval($get('marks_obtained') ?? 0);
        $totalMarks = floatval($get('total_marks') ?? 100);
        
        if ($totalMarks > 0) {
            $percentage = ($marksObtained / $totalMarks) * 100;
            $set('percentage', round($percentage, 2));
            $set('grade', Result::calculateGrade($percentage));
        }
    }
}
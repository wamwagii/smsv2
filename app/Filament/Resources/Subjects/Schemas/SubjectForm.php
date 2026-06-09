<?php

namespace App\Filament\Resources\Subjects\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Schema;
use App\Models\Department;

class SubjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Subject Information')
                    ->description('Basic subject details')
                    ->icon('heroicon-o-book-open')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Subject Name')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->placeholder('e.g., Mathematics, English, Physics')
                                    ->helperText('Full name of the subject'),
                                
                                TextInput::make('code')
                                    ->label('Subject Code')
                                    ->required()
                                    ->maxLength(10)
                                    ->unique(ignoreRecord: true)
                                    ->placeholder('e.g., MAT, ENG, PHY')
                                    ->helperText('Short code for the subject (max 10 characters)')
                                    ->regex('/^[A-Z0-9]+$/')
                                    ->validationAttribute('subject code'),
                            ]),
                        
                        Grid::make(2)
                            ->schema([
                                Select::make('department_id')
                                    ->label('Department')
                                    ->relationship('department', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Select the department this subject belongs to'),
                                
                                Select::make('category')
                                    ->label('Subject Category')
                                    ->options([
                                        'core' => 'Core Subject',
                                        'elective' => 'Elective',
                                        'extra_curricular' => 'Extra Curricular',
                                    ])
                                    ->required()
                                    ->default('core')
                                    ->native(false)
                                    ->helperText('Core subjects are mandatory for all students'),
                            ]),
                        
                        Grid::make(2)
                            ->schema([
                                TextInput::make('theory_hours_per_week')
                                    ->label('Theory Hours/Week')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(40)
                                    ->default(0)
                                    ->helperText('Number of theory hours per week'),
                                
                                TextInput::make('practical_hours_per_week')
                                    ->label('Practical Hours/Week')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(40)
                                    ->default(0)
                                    ->helperText('Number of practical/lab hours per week'),
                            ]),
                        
                        Textarea::make('description')
                            ->label('Description')
                            ->maxLength(65535)
                            ->rows(3)
                            ->placeholder('Brief description of the subject syllabus and objectives...')
                            ->columnSpanFull(),
                        
                        ToggleButtons::make('is_active')
                            ->label('Status')
                            ->options([
                                true => 'Active',
                                false => 'Inactive',
                            ])
                            ->colors([
                                true => 'success',
                                false => 'danger',
                            ])
                            ->icons([
                                true => 'heroicon-o-check-circle',
                                false => 'heroicon-o-x-circle',
                            ])
                            ->required()
                            ->default(true)
                            ->inline()
                            ->helperText('Inactive subjects will not appear in selection lists'),
                    ]),
            ]);
    }
}
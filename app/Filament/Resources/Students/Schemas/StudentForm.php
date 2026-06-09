<?php

namespace App\Filament\Resources\Students\Schemas;

use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Fieldset;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Schema;
use App\Models\AcademicYears;

class StudentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema

            ->columns(1) // Full width layout
            ->extraAttributes(['class' => 'w-full']) // Ensure full width

            ->components([
                Wizard::make([
                    Step::make('Personal Information')
                        ->schema([
                            Section::make('Student Photo')
                                ->schema([
                                    FileUpload::make('photo')
                                        ->image()
                                        ->avatar()
                                        ->directory('students/photos')
                                        ->imageEditor()
                                        ->circleCropper()
                                        ->maxSize(2048),
                                ]),
                            
                            Section::make('Basic Information')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            TextInput::make('admission_number')
                                                ->label('Admission Number')
                                                ->required()
                                                ->unique(ignoreRecord: true)
                                                ->maxLength(50),
                                            
                                            TextInput::make('roll_number')
                                                ->label('Roll Number')
                                                ->maxLength(50),
                                        ]),
                                    
                                    Grid::make(3)
                                        ->schema([
                                            TextInput::make('first_name')
                                                ->label('First Name')
                                                ->required()
                                                ->maxLength(100),
                                            
                                            TextInput::make('middle_name')
                                                ->label('Middle Name')
                                                ->maxLength(100),
                                            
                                            TextInput::make('last_name')
                                                ->label('Last Name')
                                                ->required()
                                                ->maxLength(100),
                                        ]),
                                    
                                    Grid::make(2)
                                        ->schema([
                                            DatePicker::make('date_of_birth')
                                                ->label('Date of Birth')
                                                ->required()
                                                ->native(false)
                                                ->displayFormat('d/m/Y'),
                                            
                                            Select::make('gender')
                                                ->label('Gender')
                                                ->options([
                                                    'male' => 'Male',
                                                    'female' => 'Female',
                                                    'other' => 'Other',
                                                ])
                                                ->required(),
                                        ]),
                                    
                                    Grid::make(2)
                                        ->schema([
                                            TextInput::make('birth_certificate_number')
                                                ->label('Birth Certificate Number')
                                                ->maxLength(50),
                                            
                                            Select::make('status')
                                                ->label('Student Status')
                                                ->options([
                                                    'active' => 'Active',
                                                    'alumni' => 'Alumni',
                                                    'transferred' => 'Transferred',
                                                    'suspended' => 'Suspended',
                                                    'expelled' => 'Expelled',
                                                ])
                                                ->required()
                                                ->default('active'),
                                        ]),
                                ]),
                        ]),
                    
                    Step::make('Academic Information')
                        ->schema([
                            Section::make('Current Placement')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            Select::make('class_id')
                                                ->label('Current Class')
                                                ->relationship('class', 'class_code')
                                                ->required()
                                                ->searchable()
                                                ->preload(),
                                            
                                            Select::make('academic_year_id')
                                                ->label('Academic Year')
                                                ->relationship('academicYear', 'name')
                                                ->required()
                                                ->searchable()
                                                ->preload()
                                                ->default(function () {
                                                    return AcademicYears::where('is_current', true)->first()?->id;
                                                }),
                                        ]),
                                    
                                    Grid::make(2)
                                        ->schema([
                                            DatePicker::make('enrollment_date')
                                                ->label('Enrollment Date')
                                                ->required()
                                                ->native(false)
                                                ->displayFormat('d/m/Y')
                                                ->default(now()),
                                            
                                            DatePicker::make('graduation_date')
                                                ->label('Graduation Date')
                                                ->native(false)
                                                ->displayFormat('d/m/Y'),
                                        ]),
                                ]),
                            
                            Section::make('KCSE/KCPE Information')
                                ->schema([
                                    Grid::make(3)
                                        ->schema([
                                            TextInput::make('kcpse_index_number')
                                                ->label('KCPSE Index Number')
                                                ->maxLength(20),
                                            
                                            Select::make('kcpe_grade')
                                                ->label('KCPE Grade')
                                                ->options([
                                                    'A' => 'A', 
                                                    'A-' => 'A-', 
                                                    'B+' => 'B+', 
                                                    'B' => 'B',
                                                    'B-' => 'B-', 
                                                    'C+' => 'C+', 
                                                    'C' => 'C', 
                                                    'C-' => 'C-',
                                                    'D+' => 'D+', 
                                                    'D' => 'D', 
                                                    'D-' => 'D-', 
                                                    'E' => 'E',
                                                ]),
                                            
                                            TextInput::make('kcpe_score')
                                                ->label('KCPE Score')
                                                ->numeric()
                                                ->minValue(0)
                                                ->maxValue(500),
                                        ]),
                                ]),
                        ]),
                    
                    Step::make('Contact Information')
                        ->schema([
                            Section::make('Student Contact')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            TextInput::make('phone_number')
                                                ->label('Phone Number')
                                                ->tel()
                                                ->maxLength(15),
                                            
                                            TextInput::make('email')
                                                ->label('Email Address')
                                                ->email()
                                                ->maxLength(100)
                                                ->unique(ignoreRecord: true),
                                        ]),
                                    
                                    Textarea::make('physical_address')
                                        ->label('Physical Address')
                                        ->maxLength(65535)
                                        ->rows(2),
                                ]),
                        ]),
                    
                    Step::make('Parents/Guardians')
                        ->schema([
                            Fieldset::make('Father\'s Information')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            TextInput::make('father_name')
                                                ->label('Father\'s Full Name')
                                                ->maxLength(100),
                                            
                                            TextInput::make('father_phone')
                                                ->label('Father\'s Phone Number')
                                                ->tel()
                                                ->maxLength(15),
                                        ]),
                                ]),
                            
                            Fieldset::make('Mother\'s Information')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            TextInput::make('mother_name')
                                                ->label('Mother\'s Full Name')
                                                ->maxLength(100),
                                            
                                            TextInput::make('mother_phone')
                                                ->label('Mother\'s Phone Number')
                                                ->tel()
                                                ->maxLength(15),
                                        ]),
                                ]),
                            
                            Fieldset::make('Guardian Information')
                                ->schema([
                                    Grid::make(3)
                                        ->schema([
                                            TextInput::make('guardian_name')
                                                ->label('Guardian\'s Full Name')
                                                ->maxLength(100),
                                            
                                            TextInput::make('guardian_phone')
                                                ->label('Guardian\'s Phone Number')
                                                ->tel()
                                                ->maxLength(15),
                                            
                                            TextInput::make('guardian_relation')
                                                ->label('Relationship')
                                                ->maxLength(50),
                                        ]),
                                ]),
                        ]),
                    
                    Step::make('Medical Information')
                        ->schema([
                            Section::make('Medical Notes')
                                ->schema([
                                    Textarea::make('medical_notes')
                                        ->label('Medical Conditions / Allergies')
                                        ->maxLength(65535)
                                        ->rows(4)
                                        ->helperText('List any medical conditions, allergies, or special needs'),
                                ]),
                        ]),
                ]),
            ]);
    }
}
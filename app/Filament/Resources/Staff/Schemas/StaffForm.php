<?php

namespace App\Filament\Resources\Staff\Schemas;

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
use App\Models\Department;

class StaffForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Wizard::make([
                    // Step 1: Personal Information
                    Step::make('Personal Information')
                        ->icon('heroicon-o-user')
                        ->description('Basic personal details')
                        ->schema([
                            Section::make('Staff Photo')
                                ->schema([
                                    FileUpload::make('photo')
                                        ->label('Staff Photo')
                                        ->image()
                                        ->avatar()
                                        ->directory('staff/photos')
                                        ->imageEditor()
                                        ->circleCropper()
                                        ->maxSize(2048)
                                        ->helperText('Upload a passport size photo (max 2MB)'),
                                ]),
                            
                            Section::make('Basic Information')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            TextInput::make('staff_number')
                                                ->label('Staff Number')
                                                ->required()
                                                ->unique(ignoreRecord: true)
                                                ->maxLength(50)
                                                ->helperText('Format: TCH/YYYY/XXX')
                                                ->placeholder('e.g., TCH/2024/001'),
                                            
                                            TextInput::make('national_id')
                                                ->label('National ID Number')
                                                ->required()
                                                ->unique(ignoreRecord: true)
                                                ->maxLength(20)
                                                ->helperText('Valid Kenyan National ID'),
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
                                                ->displayFormat('d/m/Y')
                                                ->helperText('Age will be calculated automatically'),
                                            
                                            Select::make('gender')
                                                ->label('Gender')
                                                ->options([
                                                    'male' => 'Male',
                                                    'female' => 'Female',
                                                    'other' => 'Other',
                                                ])
                                                ->required()
                                                ->native(false),
                                        ]),
                                ]),
                        ]),
                    
                    // Step 2: Employment Information
                    Step::make('Employment Information')
                        ->icon('heroicon-o-briefcase')
                        ->description('Employment and position details')
                        ->schema([
                            Section::make('Employment Details')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            Select::make('employment_type')
                                                ->label('Employment Type')
                                                ->options([
                                                    'full_time' => 'Full Time',
                                                    'part_time' => 'Part Time',
                                                    'contract' => 'Contract',
                                                    'temporary' => 'Temporary',
                                                ])
                                                ->required()
                                                ->native(false),
                                            
                                            Select::make('role')
                                                ->label('Role')
                                                ->options([
                                                    'teacher' => 'Teacher',
                                                    'admin' => 'Administrator',
                                                    'accountant' => 'Accountant',
                                                    'librarian' => 'Librarian',
                                                    'support' => 'Support Staff',
                                                    'management' => 'Management',
                                                ])
                                                ->required()
                                                ->native(false),
                                        ]),
                                    
                                    Grid::make(2)
                                        ->schema([
                                            Select::make('department_id')
                                                ->label('Department')
                                                ->relationship('department', 'name')
                                                ->searchable()
                                                ->preload()
                                                ->helperText('Select the department'),
                                            
                                            TextInput::make('position')
                                                ->label('Position Title')
                                                ->maxLength(100)
                                                ->helperText('e.g., Head of Mathematics, Senior Teacher'),
                                        ]),
                                    
                                    Grid::make(2)
                                        ->schema([
                                            DatePicker::make('hire_date')
                                                ->label('Hire Date')
                                                ->required()
                                                ->native(false)
                                                ->displayFormat('d/m/Y')
                                                ->default(now()),
                                            
                                            DatePicker::make('contract_end_date')
                                                ->label('Contract End Date')
                                                ->native(false)
                                                ->displayFormat('d/m/Y')
                                                ->helperText('For contract employees only')
                                                ->visible(fn ($get) => $get('employment_type') === 'contract'),
                                        ]),
                                    
                                    Select::make('status')
                                        ->label('Employment Status')
                                        ->options([
                                            'active' => 'Active',
                                            'on_leave' => 'On Leave',
                                            'suspended' => 'Suspended',
                                            'resigned' => 'Resigned',
                                            'terminated' => 'Terminated',
                                        ])
                                        ->required()
                                        ->default('active'),
                                ]),
                        ]),
                    
                    // Step 3: Professional Information
                    Step::make('Professional Information')
                        ->icon('heroicon-o-academic-cap')
                        ->description('Qualifications and certifications')
                        ->schema([
                            Section::make('Professional Qualifications')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            TextInput::make('tsc_number')
                                                ->label('TSC Number')
                                                ->maxLength(50)
                                                ->helperText('Teachers Service Commission Number'),
                                            
                                            TextInput::make('qualification')
                                                ->label('Highest Qualification')
                                                ->maxLength(100)
                                                ->helperText('e.g., B.Ed, MSc, Diploma'),
                                        ]),
                                    
                                    Textarea::make('subjects_taught')
                                        ->label('Subjects Taught')
                                        ->rows(3)
                                        ->helperText('List subjects separated by commas')
                                        ->placeholder('e.g., Mathematics, English, Physics'),
                                    
                                    Textarea::make('certifications')
                                        ->label('Certifications & Training')
                                        ->rows(3)
                                        ->helperText('List any professional certifications or training completed'),
                                ]),
                        ]),
                    
                    // Step 4: Contact Information
                    Step::make('Contact Information')
                        ->icon('heroicon-o-phone')
                        ->description('Contact details')
                        ->schema([
                            Section::make('Contact Details')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            TextInput::make('phone_number')
                                                ->label('Phone Number')
                                                ->tel()
                                                ->required()
                                                ->maxLength(15)
                                                ->unique(ignoreRecord: true)
                                                ->regex('/^07[0-9]{8}$/')
                                                ->helperText('Valid Kenyan phone number (e.g., 0712345678)'),
                                            
                                            TextInput::make('email')
                                                ->label('Email Address')
                                                ->email()
                                                ->required()
                                                ->maxLength(100)
                                                ->unique(ignoreRecord: true),
                                        ]),
                                    
                                    Textarea::make('physical_address')
                                        ->label('Physical Address')
                                        ->maxLength(65535)
                                        ->rows(2)
                                        ->helperText('Home/Residential address'),
                                ]),
                        ]),
                    
                    // Step 5: Financial Information
                    Step::make('Financial Information')
                        ->icon('heroicon-o-currency-dollar')
                        ->description('Bank and statutory details')
                        ->schema([
                            Section::make('Statutory Information')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            TextInput::make('kra_pin')
                                                ->label('KRA PIN')
                                                ->maxLength(20)
                                                ->helperText('Kenya Revenue Authority PIN'),
                                            
                                            TextInput::make('nhif_number')
                                                ->label('NHIF Number')
                                                ->maxLength(20)
                                                ->helperText('National Hospital Insurance Fund Number'),
                                            
                                            TextInput::make('nssf_number')
                                                ->label('NSSF Number')
                                                ->maxLength(20)
                                                ->helperText('National Social Security Fund Number'),
                                        ]),
                                ]),
                            
                            Section::make('Bank Details')
                                ->schema([
                                    Grid::make(3)
                                        ->schema([
                                            TextInput::make('bank_name')
                                                ->label('Bank Name')
                                                ->maxLength(100),
                                            
                                            TextInput::make('bank_branch')
                                                ->label('Branch')
                                                ->maxLength(100),
                                            
                                            TextInput::make('account_number')
                                                ->label('Account Number')
                                                ->maxLength(50),
                                        ]),
                                ]),
                        ]),
                    
                    // Step 6: Emergency Contact
                    Step::make('Emergency Contact')
                        ->icon('heroicon-o-exclamation-triangle')
                        ->description('Emergency contact information')
                        ->schema([
                            Section::make('Emergency Contact Details')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            TextInput::make('emergency_contact_name')
                                                ->label('Emergency Contact Name')
                                                ->maxLength(100),
                                            
                                            TextInput::make('emergency_contact_relation')
                                                ->label('Relationship')
                                                ->maxLength(50)
                                                ->placeholder('e.g., Spouse, Parent, Sibling'),
                                            
                                            TextInput::make('emergency_contact_phone')
                                                ->label('Emergency Phone Number')
                                                ->tel()
                                                ->maxLength(15)
                                                ->regex('/^07[0-9]{8}$/')
                                                ->helperText('Valid Kenyan phone number'),
                                        ]),
                                ]),
                        ]),
                ])
                ->columnSpanFull()
                ->columns(2),
            ]);
    }
}
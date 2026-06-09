<?php

namespace App\Filament\Resources\Invoices\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Schema;
use App\Models\Student;
use App\Models\FeeStructure;
use App\Models\Invoice;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Invoice Information')
                    ->description('Create or edit student fee invoice')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('invoice_number')
                                    ->label('Invoice Number')
                                    ->required()
                                    ->maxLength(50)
                                    ->default(function () {
                                        return static::generateInvoiceNumber();
                                    })
                                    ->disabled()
                                    ->dehydrated(true)
                                    ->helperText('Auto-generated invoice number (format: INV/YYYY/XXXX)')
                                    ->extraAttributes(['class' => 'bg-gray-100 font-mono']),
                                
                                Select::make('student_id')
                                    ->label('Student')
                                    ->relationship('student', 'admission_number')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function ($set, $get) {
                                        static::loadStudentDetails($set, $get);
                                    })
                                    ->helperText('Select the student'),
                                
                                Select::make('fee_structure_id')
                                    ->label('Fee Structure')
                                    ->options(function () {
                                        return FeeStructure::where('is_active', true)
                                            ->with(['class', 'academicYear'])
                                            ->get()
                                            ->mapWithKeys(function ($fee) {
                                                return [$fee->id => $fee->class->class_code . ' - ' . $fee->academicYear->name . ' (KES ' . number_format($fee->total_fees, 2) . ')'];
                                            });
                                    })
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function ($set, $get) {
                                        static::calculateInvoiceAmount($set, $get);
                                    })
                                    ->helperText('Select the fee structure'),
                                
                                Select::make('term')
                                    ->label('Term')
                                    ->options([
                                        'term_1' => 'Term 1 (January - March)',
                                        'term_2' => 'Term 2 (April - July)',
                                        'term_3' => 'Term 3 (August - November)',
                                    ])
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($set, $get) {
                                        static::calculateInvoiceAmount($set, $get);
                                    }),
                            ]),
                        
                        Grid::make(3)
                            ->schema([
                                TextInput::make('amount')
                                    ->label('Invoice Amount')
                                    ->numeric()
                                    ->prefix('KES')
                                    ->required()
                                    ->live()
                                    ->helperText('Amount to be paid'),
                                
                                TextInput::make('amount_paid')
                                    ->label('Amount Paid')
                                    ->numeric()
                                    ->prefix('KES')
                                    ->default(0)
                                    ->disabled()
                                    ->dehydrated(true)
                                    ->helperText('Auto-updates from payments'),
                                
                                Placeholder::make('balance_display')
                                    ->label('Balance')
                                    ->content(function ($get) {
                                        $amount = floatval($get('amount') ?? 0);
                                        $paid = floatval($get('amount_paid') ?? 0);
                                        $balance = $amount - $paid;
                                        return 'KES ' . number_format($balance, 2);
                                    }),
                            ]),
                        
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('due_date')
                                    ->label('Due Date')
                                    ->required()
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->default(now()->addDays(30))
                                    ->helperText('Payment due date'),
                                
                                Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'pending' => 'Pending',
                                        'partially_paid' => 'Partially Paid',
                                        'paid' => 'Paid',
                                        'overdue' => 'Overdue',
                                        'waived' => 'Waived',
                                    ])
                                    ->required()
                                    ->default('pending')
                                    ->native(false),
                            ]),
                        
                        Textarea::make('notes')
                            ->label('Notes')
                            ->maxLength(65535)
                            ->rows(2)
                            ->columnSpanFull()
                            ->helperText('Additional notes or comments about this invoice'),
                    ]),
                
                Section::make('Student Information')
                    ->description('Student details for reference')
                    ->icon('heroicon-o-user')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Placeholder::make('student_name')
                            ->label('Student Name')
                            ->content(function ($get) {
                                $studentId = $get('student_id');
                                if ($studentId) {
                                    $student = Student::find($studentId);
                                    return $student ? $student->first_name . ' ' . $student->last_name : '-';
                                }
                                return 'Select a student';
                            }),
                        
                        Placeholder::make('student_class')
                            ->label('Class')
                            ->content(function ($get) {
                                $studentId = $get('student_id');
                                if ($studentId) {
                                    $student = Student::with('class')->find($studentId);
                                    return $student && $student->class ? $student->class->class_code : '-';
                                }
                                return 'Select a student';
                            }),
                    ]),
            ]);
    }
    
    protected static function generateInvoiceNumber(): string
    {
        $year = date('Y');
        
        // Get the last invoice number for this year
        $lastInvoice = Invoice::where('invoice_number', 'like', "INV/{$year}/%")
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastInvoice && $lastInvoice->invoice_number) {
            // Extract the number from the last invoice
            preg_match('/INV\/' . $year . '\/(\d+)/', $lastInvoice->invoice_number, $matches);
            if (isset($matches[1])) {
                $lastNumber = (int)$matches[1];
                $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
            } else {
                $newNumber = '0001';
            }
        } else {
            $newNumber = '0001';
        }
        
        // Ensure uniqueness (just in case)
        $invoiceNumber = "INV/{$year}/{$newNumber}";
        
        // Check if this invoice number already exists and increment if needed
        while (Invoice::where('invoice_number', $invoiceNumber)->exists()) {
            $newNumber = str_pad((int)$newNumber + 1, 4, '0', STR_PAD_LEFT);
            $invoiceNumber = "INV/{$year}/{$newNumber}";
        }
        
        return $invoiceNumber;
    }
    
    protected static function loadStudentDetails($set, $get)
    {
        $studentId = $get('student_id');
        if ($studentId) {
            $student = Student::with('class')->find($studentId);
            if ($student && $student->class) {
                $feeStructure = FeeStructure::where('class_id', $student->class_id)
                    ->where('is_active', true)
                    ->first();
                
                if ($feeStructure) {
                    $set('fee_structure_id', $feeStructure->id);
                    static::calculateInvoiceAmount($set, $get);
                }
            }
        }
    }
    
    protected static function calculateInvoiceAmount($set, $get)
    {
        $feeStructureId = $get('fee_structure_id');
        $term = $get('term');
        
        if ($feeStructureId && $term) {
            $feeStructure = FeeStructure::find($feeStructureId);
            if ($feeStructure) {
                if ($feeStructure->payment_plan && is_array($feeStructure->payment_plan) && count($feeStructure->payment_plan) > 0) {
                    foreach ($feeStructure->payment_plan as $plan) {
                        if (isset($plan['term']) && $plan['term'] === $term) {
                            $set('amount', $plan['amount']);
                            return;
                        }
                    }
                }
                
                $totalFees = $feeStructure->tuition_fees + $feeStructure->activity_fees + 
                             $feeStructure->library_fees + $feeStructure->sports_fees + 
                             $feeStructure->medical_fees + $feeStructure->transport_fees + 
                             $feeStructure->boarding_fees + $feeStructure->uniform_fees + 
                             $feeStructure->other_fees;
                
                $set('amount', round($totalFees / 3, 2));
            }
        }
    }
}
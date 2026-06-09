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
                                // Auto-generated Invoice Number - Not Editable
                                TextInput::make('invoice_number')
                                    ->label('Invoice Number')
                                    ->required()
                                    ->maxLength(50)
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
                                        static::calculateInvoiceAmount($set, $get);
                                    })
                                    ->helperText('Select the student'),
                                
                                Select::make('fees_structure_id')
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
                                        'term_1' => 'Term 1',
                                        'term_2' => 'Term 2',
                                        'term_3' => 'Term 3',
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
                                    ->afterStateUpdated(function ($set, $get) {
                                        static::updateBalance($set, $get);
                                    }),
                                
                                TextInput::make('amount_paid')
                                    ->label('Amount Paid')
                                    ->numeric()
                                    ->prefix('KES')
                                    ->default(0)
                                    ->disabled()
                                    ->dehydrated(true)
                                    ->helperText('Auto-updates from payments'),
                                
                                TextInput::make('balance')
                                    ->label('Balance')
                                    ->numeric()
                                    ->prefix('KES')
                                    ->disabled()
                                    ->dehydrated(true)
                                    ->default(0),
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
            ]);
    }
    
    protected static function calculateInvoiceAmount($set, $get)
    {
        $feeStructureId = $get('fees_structure_id');
        $term = $get('term');
        
        if ($feeStructureId && $term) {
            $feeStructure = FeeStructure::find($feeStructureId);
            if ($feeStructure && $feeStructure->payment_plan) {
                foreach ($feeStructure->payment_plan as $installment) {
                    if ($installment['term'] === $term) {
                        $set('amount', $installment['amount']);
                        static::updateBalance($set, $get);
                        break;
                    }
                }
            } elseif ($feeStructure) {
                $set('amount', round($feeStructure->total_fees / 3, 2));
                static::updateBalance($set, $get);
            }
        }
    }
    
    protected static function updateBalance($set, $get)
    {
        $amount = floatval($get('amount') ?? 0);
        $paid = floatval($get('amount_paid') ?? 0);
        $set('balance', $amount - $paid);
    }
}
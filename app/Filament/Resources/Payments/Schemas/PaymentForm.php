<?php

namespace App\Filament\Resources\Payments\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Schema;
use App\Models\Invoice;
use App\Models\Student;
use App\Models\Guardian;
use App\Models\Payment;
use Illuminate\Support\Str;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Payment Information')
                    ->description('Record student fee payment')
                    ->icon('heroicon-o-credit-card')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Hidden::make('student_id')
                                    ->default(null),
                                
                                Select::make('student_id_select')
                                    ->label('Student')
                                    ->options(function () {
                                        return Student::where('status', 'active')
                                            ->get()
                                            ->mapWithKeys(function ($student) {
                                                return [$student->id => $student->admission_number . ' - ' . $student->first_name . ' ' . $student->last_name];
                                            });
                                    })
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function ($set, $get) {
                                        static::loadStudentData($set, $get);
                                    })
                                    ->helperText('Select the student making payment'),
                                
                                Select::make('invoice_id')
                                    ->label('Invoice (Optional)')
                                    ->options(function ($get) {
                                        $studentId = $get('student_id_select');
                                        if ($studentId) {
                                            return Invoice::with('student')
                                                ->where('student_id', $studentId)
                                                ->where('status', '!=', 'paid')
                                                ->get()
                                                ->mapWithKeys(function ($invoice) {
                                                    $balance = $invoice->amount - $invoice->amount_paid;
                                                    return [$invoice->id => $invoice->invoice_number . ' (Balance: KES ' . number_format($balance, 2) . ')'];
                                                });
                                        }
                                        return [];
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function ($set, $get) {
                                        static::loadInvoiceAmount($set, $get);
                                    })
                                    ->helperText('Optional: Select an invoice to pay against'),
                                
                                Select::make('parent_id')
                                    ->label('Parent/Guardian')
                                    ->options(function ($get) {
                                        $studentId = $get('student_id_select');
                                        if ($studentId) {
                                            // Get the student with their parents
                                            $student = Student::with('parents')->find($studentId);
                                            if ($student && $student->parents && $student->parents->count() > 0) {
                                                return $student->parents->mapWithKeys(function ($parent) {
                                                    return [$parent->id => $parent->first_name . ' ' . $parent->last_name . ' (' . $parent->phone_number . ')'];
                                                })->toArray();
                                            }
                                        }
                                        return [];
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->helperText('Select parent/guardian making the payment'),
                            ]),
                        
                        Grid::make(3)
                            ->schema([
                                TextInput::make('amount')
                                    ->label('Amount')
                                    ->numeric()
                                    ->prefix('KES')
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($set, $get) {
                                        static::validateAmount($set, $get);
                                    })
                                    ->helperText('Payment amount in Kenyan Shillings'),
                                
                                Select::make('payment_method')
                                    ->label('Payment Method')
                                    ->options([
                                        'mpesa' => 'M-Pesa',
                                        'bank_transfer' => 'Bank Transfer',
                                        'cash' => 'Cash',
                                        'cheque' => 'Cheque',
                                        'card' => 'Card',
                                    ])
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($set, $get) {
                                        static::handlePaymentMethodChange($set, $get);
                                    })
                                    ->native(false),
                                
                                Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'pending' => 'Pending',
                                        'processing' => 'Processing',
                                        'completed' => 'Completed',
                                        'failed' => 'Failed',
                                        'refunded' => 'Refunded',
                                    ])
                                    ->required()
                                    ->default('completed')
                                    ->native(false)
                                    ->helperText('Payment status'),
                            ]),
                        
                        DatePicker::make('payment_date')
                            ->label('Payment Date')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->default(now()),
                        
                        TimePicker::make('payment_time')
                            ->label('Payment Time')
                            ->native(false)
                            ->seconds(false)
                            ->default(now()),
                    ]),
                
                // M-Pesa Details Section (conditional)
                Section::make('M-Pesa Details')
                    ->description('M-Pesa transaction details')
                    ->icon('heroicon-o-phone')
                    ->collapsible()
                    ->visible(function ($get) {
                        return $get('payment_method') === 'mpesa';
                    })
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('mpesa_receipt')
                                    ->label('M-Pesa Receipt Number')
                                    ->maxLength(50)
                                    ->placeholder('e.g., QWER456TYK')
                                    ->helperText('M-Pesa confirmation code')
                                    ->live(onBlur: true),
                                
                                TextInput::make('checkout_request_id')
                                    ->label('Checkout Request ID')
                                    ->maxLength(100)
                                    ->helperText('M-Pesa checkout request ID'),
                                
                                TextInput::make('merchant_request_id')
                                    ->label('Merchant Request ID')
                                    ->maxLength(100)
                                    ->helperText('M-Pesa merchant request ID'),
                            ]),
                    ]),
                
                // Bank/Card Details Section (conditional)
                Section::make('Bank/Card Details')
                    ->description('Bank transfer or card payment details')
                    ->icon('heroicon-o-building-library')
                    ->collapsible()
                    ->visible(function ($get) {
                        return in_array($get('payment_method'), ['bank_transfer', 'card']);
                    })
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('bank_name')
                                    ->label('Bank Name')
                                    ->maxLength(100)
                                    ->placeholder('e.g., Equity Bank, KCB, Co-operative Bank'),
                                
                                TextInput::make('transaction_reference')
                                    ->label('Transaction Reference')
                                    ->maxLength(100)
                                    ->placeholder('e.g., TRX-2024-001234'),
                                
                                TextInput::make('card_last_four')
                                    ->label('Card Last 4 Digits')
                                    ->maxLength(4)
                                    ->placeholder('e.g., 1234')
                                    ->regex('/^\d{4}$/'),
                            ]),
                    ]),
                
                // Receipt Information Section
                Section::make('Receipt Information')
                    ->description('Receipt generation details')
                    ->icon('heroicon-o-document-text')
                    ->collapsible()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('receipt_number')
                                    ->label('Receipt Number')
                                    ->maxLength(50)
                                    ->disabled()
                                    ->dehydrated(true)
                                    ->default(function () {
                                        return static::generateReceiptNumber();
                                    })
                                    ->helperText('Auto-generated receipt number'),
                                
                                TextInput::make('receipt_path')
                                    ->label('Receipt Path')
                                    ->maxLength(255)
                                    ->disabled()
                                    ->dehydrated(true)
                                    ->helperText('Auto-generated after payment completion'),
                            ]),
                    ]),
                
                // Additional Information
                Section::make('Additional Information')
                    ->description('Notes and gateway responses')
                    ->icon('heroicon-o-information-circle')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Textarea::make('notes')
                            ->label('Payment Notes')
                            ->maxLength(65535)
                            ->rows(2)
                            ->helperText('Any additional notes about this payment'),
                        
                        Textarea::make('gateway_response')
                            ->label('Gateway Response')
                            ->maxLength(65535)
                            ->rows(3)
                            ->helperText('Raw response from payment gateway (M-Pesa/Bank API)')
                            ->extraAttributes(['class' => 'font-mono text-sm']),
                    ]),
                
                // Display Invoice Summary (only if invoice is selected)
                Section::make('Invoice Summary')
                    ->description('Current invoice status')
                    ->icon('heroicon-o-document-chart-bar')
                    ->collapsible()
                    ->collapsed()
                    ->visible(function ($get) {
                        return !empty($get('invoice_id'));
                    })
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Placeholder::make('invoice_amount')
                                    ->label('Invoice Amount')
                                    ->content(function ($get) {
                                        $invoice = Invoice::find($get('invoice_id'));
                                        return $invoice ? 'KES ' . number_format($invoice->amount, 2) : '-';
                                    }),
                                
                                Placeholder::make('amount_paid_so_far')
                                    ->label('Amount Paid So Far')
                                    ->content(function ($get) {
                                        $invoice = Invoice::find($get('invoice_id'));
                                        return $invoice ? 'KES ' . number_format($invoice->amount_paid, 2) : '-';
                                    }),
                                
                                Placeholder::make('current_balance')
                                    ->label('Current Balance')
                                    ->content(function ($get) {
                                        $invoice = Invoice::find($get('invoice_id'));
                                        if ($invoice) {
                                            $balance = $invoice->amount - $invoice->amount_paid;
                                            return 'KES ' . number_format($balance, 2);
                                        }
                                        return '-';
                                    }),
                            ]),
                        
                        Placeholder::make('after_payment_balance')
                            ->label('After This Payment')
                            ->content(function ($get) {
                                $invoice = Invoice::find($get('invoice_id'));
                                $amount = floatval($get('amount') ?? 0);
                                if ($invoice) {
                                    $newBalance = ($invoice->amount - $invoice->amount_paid) - $amount;
                                    return 'KES ' . number_format($newBalance, 2);
                                }
                                return '-';
                            })
                            ->visible(function ($get) {
                                return !empty($get('amount')) && floatval($get('amount')) > 0;
                            }),
                        
                        Placeholder::make('payment_status_warning')
                            ->label('Note')
                            ->content(function ($get) {
                                $invoice = Invoice::find($get('invoice_id'));
                                $amount = floatval($get('amount') ?? 0);
                                if ($invoice) {
                                    $currentBalance = $invoice->amount - $invoice->amount_paid;
                                    if ($amount > $currentBalance) {
                                        return '⚠️ Warning: Payment amount exceeds current balance!';
                                    }
                                }
                                return null;
                            })
                            ->visible(function ($get) {
                                $invoice = Invoice::find($get('invoice_id'));
                                $amount = floatval($get('amount') ?? 0);
                                if ($invoice) {
                                    $currentBalance = $invoice->amount - $invoice->amount_paid;
                                    return $amount > $currentBalance;
                                }
                                return false;
                            }),
                    ]),
                
                // Payment Note for non-invoice payments
                Section::make('Payment Note')
                    ->description('For payments without an invoice')
                    ->icon('heroicon-o-information-circle')
                    ->collapsible()
                    ->visible(function ($get) {
                        return empty($get('invoice_id'));
                    })
                    ->schema([
                        Placeholder::make('payment_info')
                            ->label('Note')
                            ->content('This payment is not linked to a specific invoice. It will be recorded as a general payment for the student.'),
                    ]),
            ]);
    }
    
    protected static function generateReceiptNumber(): string
    {
        $year = date('Y');
        $lastPayment = Payment::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastPayment && $lastPayment->receipt_number) {
            preg_match('/RCT\/' . $year . '\/(\d+)/', $lastPayment->receipt_number, $matches);
            if (isset($matches[1])) {
                $lastNumber = (int)$matches[1];
                $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
            } else {
                $newNumber = '0001';
            }
        } else {
            $newNumber = '0001';
        }
        
        $receiptNumber = "RCT/{$year}/{$newNumber}";
        
        while (Payment::where('receipt_number', $receiptNumber)->exists()) {
            $newNumber = str_pad((int)$newNumber + 1, 4, '0', STR_PAD_LEFT);
            $receiptNumber = "RCT/{$year}/{$newNumber}";
        }
        
        return $receiptNumber;
    }
    
    protected static function loadStudentData($set, $get)
    {
        $studentId = $get('student_id_select');
        if ($studentId) {
            $set('student_id', $studentId);
            
            // Reset invoice and parent when student changes
            $set('invoice_id', null);
            $set('parent_id', null);
        }
    }
    
    protected static function loadInvoiceAmount($set, $get)
    {
        $invoiceId = $get('invoice_id');
        if ($invoiceId) {
            $invoice = Invoice::find($invoiceId);
            if ($invoice) {
                $currentBalance = $invoice->amount - $invoice->amount_paid;
                $set('amount', $currentBalance);
            }
        }
    }
    
    protected static function validateAmount($set, $get)
    {
        $amount = floatval($get('amount') ?? 0);
        $invoiceId = $get('invoice_id');
        
        if ($invoiceId && $amount > 0) {
            $invoice = Invoice::find($invoiceId);
            if ($invoice) {
                $currentBalance = $invoice->amount - $invoice->amount_paid;
                if ($amount > $currentBalance) {
                    $set('amount', $currentBalance);
                }
                
                if ($amount >= $currentBalance) {
                    $set('status', 'completed');
                }
            }
        }
    }
    
    protected static function handlePaymentMethodChange($set, $get)
    {
        $method = $get('payment_method');
        
        if ($method !== 'mpesa') {
            $set('mpesa_receipt', null);
            $set('checkout_request_id', null);
            $set('merchant_request_id', null);
        }
        
        if (!in_array($method, ['bank_transfer', 'card'])) {
            $set('bank_name', null);
            $set('transaction_reference', null);
            $set('card_last_four', null);
        }
    }
}
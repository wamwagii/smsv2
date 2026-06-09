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
use Filament\Schemas\Schema;
use App\Models\Invoice;
use App\Models\Student;
use App\Models\Guardian;
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
                                // Remove the default value, let the model handle it
                                TextInput::make('idempotency_key')
                                    ->label('Transaction Key')
                                    ->required()
                                    ->maxLength(255)
                                    ->disabled()
                                    ->dehydrated(true)
                                    ->helperText('Unique transaction identifier (auto-generated)')
                                    ->extraAttributes(['class' => 'bg-gray-100 font-mono']),
                                
                                Select::make('invoice_id')
                                    ->label('Invoice')
                                    ->options(function () {
                                        return Invoice::with('student')
                                            ->where('status', '!=', 'paid')
                                            ->get()
                                            ->mapWithKeys(function ($invoice) {
                                                return [$invoice->id => $invoice->invoice_number . ' - ' . ($invoice->student->first_name ?? '') . ' ' . ($invoice->student->last_name ?? '') . ' (Balance: KES ' . number_format($invoice->balance, 2) . ')'];
                                            });
                                    })
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function ($set, $get) {
                                        static::loadInvoiceDetails($set, $get);
                                    })
                                    ->helperText('Select the invoice being paid'),
                                
                                Select::make('student_id')
                                    ->label('Student')
                                    ->relationship('student', 'admission_number')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function ($set, $get) {
                                        static::loadStudentInvoices($set, $get);
                                    })
                                    ->helperText('Select the student'),
                                
                                Select::make('parent_id')
                                    ->label('Parent/Guardian')
                                    ->relationship('parent', 'first_name')
                                    ->options(function () {
                                        return Guardian::where('status', 'active')
                                            ->get()
                                            ->mapWithKeys(function ($guardian) {
                                                return [$guardian->id => $guardian->first_name . ' ' . $guardian->last_name . ' (' . $guardian->phone_number . ')'];
                                            });
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Select parent making the payment (optional)'),
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
                                    ->default('pending')
                                    ->native(false),
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
                    ->visible(fn ($get) => $get('payment_method') === 'mpesa')
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
                    ->visible(fn ($get) => in_array($get('payment_method'), ['bank_transfer', 'card']))
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
                                    ->default(function () {
                                        return 'RCT/' . date('Y') . '/' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
                                    })
                                    ->helperText('Auto-generated receipt number'),
                                
                                TextInput::make('receipt_path')
                                    ->label('Receipt Path')
                                    ->maxLength(255)
                                    ->helperText('Path to generated receipt PDF'),
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
                
                // Display Invoice Summary
                Section::make('Invoice Summary')
                    ->description('Current invoice status')
                    ->icon('heroicon-o-document-chart-bar')
                    ->collapsible()
                    ->collapsed()
                    ->visible(fn ($get) => !empty($get('invoice_id')))
                    ->schema([
                        Placeholder::make('invoice_amount')
                            ->label('Invoice Amount')
                            ->content(function ($get) {
                                $invoice = Invoice::find($get('invoice_id'));
                                return $invoice ? 'KES ' . number_format($invoice->amount, 2) : '-';
                            }),
                        
                        Placeholder::make('amount_paid')
                            ->label('Amount Paid So Far')
                            ->content(function ($get) {
                                $invoice = Invoice::find($get('invoice_id'));
                                return $invoice ? 'KES ' . number_format($invoice->amount_paid, 2) : '-';
                            }),
                        
                        Placeholder::make('remaining_balance')
                            ->label('Remaining Balance')
                            ->content(function ($get) {
                                $invoice = Invoice::find($get('invoice_id'));
                                if ($invoice) {
                                    $balance = $invoice->balance;
                                    return 'KES ' . number_format($balance, 2);
                                }
                                return '-';
                            }),
                    ]),
            ]);
    }
    
    protected static function loadInvoiceDetails($set, $get)
    {
        $invoiceId = $get('invoice_id');
        if ($invoiceId) {
            $invoice = Invoice::find($invoiceId);
            if ($invoice) {
                $set('student_id', $invoice->student_id);
                $set('amount', $invoice->balance);
            }
        }
    }
    
    protected static function loadStudentInvoices($set, $get)
    {
        $studentId = $get('student_id');
        if ($studentId) {
            $invoices = Invoice::where('student_id', $studentId)
                ->where('status', '!=', 'paid')
                ->get();
            
            if ($invoices->count() === 1) {
                $set('invoice_id', $invoices->first()->id);
                $set('amount', $invoices->first()->balance);
            }
        }
    }
    
    protected static function validateAmount($set, $get)
    {
        $amount = floatval($get('amount') ?? 0);
        $invoiceId = $get('invoice_id');
        
        if ($invoiceId && $amount > 0) {
            $invoice = Invoice::find($invoiceId);
            if ($invoice && $amount > $invoice->balance) {
                $set('amount', $invoice->balance);
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
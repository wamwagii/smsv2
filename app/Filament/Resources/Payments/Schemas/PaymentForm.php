<?php

namespace App\Filament\Resources\Payments\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use App\Models\Invoice;
use App\Models\Student;

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
                        Grid::make(2)->schema([
                            Select::make('student_id')
                                ->label('Student')
                                ->relationship('student', 'admission_number',
                                    modifyQueryUsing: fn ($query) => $query->where('status', 'active')
                                )
                                ->getOptionLabelFromRecordUsing(fn (Student $record) =>
                                    "{$record->admission_number} - {$record->first_name} {$record->last_name}"
                                )
                                ->searchable(['admission_number', 'first_name', 'last_name'])
                                ->preload()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($set) {
                                    $set('invoice_id', null);
                                    $set('parent_id', null);
                                })
                                ->helperText('Select the student making payment'),

                            Select::make('invoice_id')
                                ->label('Invoice (Optional)')
                                ->options(function ($get) {
                                    $studentId = $get('student_id');
                                    if (!$studentId) return [];
                                    return Invoice::where('student_id', $studentId)
                                        ->where('status', '!=', 'paid')
                                        ->get()
                                        ->mapWithKeys(function ($invoice) {
                                            $balance = $invoice->amount - $invoice->amount_paid;
                                            return [$invoice->id => "{$invoice->invoice_number} (Balance: KES " . number_format($balance, 2) . ")"];
                                        });
                                })
                                ->searchable()
                                ->preload()
                                ->live()
                                ->afterStateUpdated(function ($set, $get, $state) {
                                    if ($state && !$get('amount')) {
                                        $invoice = Invoice::find($state);
                                        if ($invoice) {
                                            $set('amount', $invoice->amount - $invoice->amount_paid);
                                        }
                                    }
                                })
                                ->helperText('Optional: Select an invoice to pay against'),

                            Select::make('parent_id')
                                ->label('Parent/Guardian')
                                ->options(function ($get) {
                                    $studentId = $get('student_id');
                                    if (!$studentId) return [];
                                    $student = Student::with('parents')->find($studentId);
                                    if (!$student || $student->parents->isEmpty()) return [];
                                    return $student->parents->mapWithKeys(fn ($parent) => [
                                        $parent->id => "{$parent->first_name} {$parent->last_name} ({$parent->phone_number})",
                                    ]);
                                })
                                ->searchable()
                                ->preload()
                                ->required()
                                ->helperText('Select parent/guardian making the payment'),
                        ]),

                        Grid::make(3)->schema([
                            TextInput::make('amount')
                                ->label('Amount')
                                ->numeric()
                                ->prefix('KES')
                                ->required()
                                ->live(onBlur: true)
                                ->rules([
                                    fn ($get) => function ($attribute, $value, $fail) use ($get) {
                                        $invoiceId = $get('invoice_id');
                                        if (!$invoiceId || !$value) return;
                                        $invoice = Invoice::find($invoiceId);
                                        if (!$invoice) return;
                                        $balance = $invoice->amount - $invoice->amount_paid;
                                        if ((float) $value > $balance) {
                                            $fail('Amount exceeds invoice balance of KES ' . number_format($balance, 2));
                                        }
                                    },
                                ])
                                ->helperText('Payment amount in Kenyan Shillings'),

                            Select::make('payment_method')
                                ->label('Payment Method')
                                ->options([
                                    'mpesa'         => 'M-Pesa',
                                    'bank_transfer' => 'Bank Transfer',
                                    'cash'          => 'Cash',
                                    'cheque'        => 'Cheque',
                                    'card'          => 'Card',
                                ])
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($set, $get) {
                                    static::resetMethodSpecificFields($set, $get('payment_method'));
                                })
                                ->native(false),

                            Select::make('status')
                                ->label('Status')
                                ->options([
                                    'pending'    => 'Pending',
                                    'processing' => 'Processing',
                                    'completed'  => 'Completed',
                                    'failed'     => 'Failed',
                                    'refunded'   => 'Refunded',
                                ])
                                ->required()
                                ->default('completed')
                                ->native(false),
                        ]),

                        Grid::make(2)->schema([
                            DatePicker::make('payment_date')
                                ->label('Payment Date')
                                ->required()
                                ->native(false)
                                ->displayFormat('d/m/Y')
                                ->default(today()),

                            TimePicker::make('payment_time')
                                ->label('Payment Time')
                                ->native(false)
                                ->seconds(false)
                                ->default(now()->format('H:i')),
                        ]),
                    ]),

                /* -------------------- M-Pesa -------------------- */
                Section::make('M-Pesa Details')
                    ->description('M-Pesa transaction details')
                    ->icon('heroicon-o-phone')
                    ->collapsible()
                    ->visible(fn ($get) => $get('payment_method') === 'mpesa')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('mpesa_receipt')
                                ->label('M-Pesa Receipt Number')
                                ->maxLength(50)
                                ->placeholder('e.g., QWER456TYK'),

                            TextInput::make('checkout_request_id')
                                ->label('Checkout Request ID')
                                ->maxLength(100),

                            TextInput::make('merchant_request_id')
                                ->label('Merchant Request ID')
                                ->maxLength(100),
                        ]),
                    ]),

                /* -------------------- Bank / Card -------------------- */
                Section::make('Bank/Card Details')
                    ->description('Bank transfer or card payment details')
                    ->icon('heroicon-o-building-library')
                    ->collapsible()
                    ->visible(fn ($get) => in_array($get('payment_method'), ['bank_transfer', 'card', 'cheque']))
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('bank_name')
                                ->label('Bank Name')
                                ->maxLength(100),

                            TextInput::make('transaction_reference')
                                ->label('Transaction Reference')
                                ->maxLength(100),

                            TextInput::make('card_last_four')
                                ->label('Card Last 4 Digits')
                                ->maxLength(4)
                                ->rule('regex:/^\d{4}$/')
                                ->visible(fn ($get) => $get('payment_method') === 'card'),
                        ]),
                    ]),

                /* -------------------- Receipt -------------------- */
                Section::make('Receipt Information')
                    ->description('Receipt will be auto-generated on save')
                    ->icon('heroicon-o-document-text')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('receipt_number')
                                ->label('Receipt Number')
                                ->disabled()
                                ->dehydrated(false)
                                ->placeholder('Auto-generated')
                                ->hiddenOn('create'),

                            TextInput::make('receipt_path')
                                ->label('Receipt Path')
                                ->disabled()
                                ->dehydrated(false)
                                ->hiddenOn('create'),
                        ]),
                    ]),

                /* -------------------- Notes -------------------- */
                Section::make('Additional Information')
                    ->icon('heroicon-o-information-circle')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Textarea::make('notes')
                            ->label('Payment Notes')
                            ->maxLength(65535)
                            ->rows(2),

                        Textarea::make('gateway_response')
                            ->label('Gateway Response')
                            ->maxLength(65535)
                            ->rows(3)
                            ->extraAttributes(['class' => 'font-mono text-sm']),
                    ]),

                /* -------------------- Invoice Summary -------------------- */
                Section::make('Invoice Summary')
                    ->description('Current invoice status')
                    ->icon('heroicon-o-document-chart-bar')
                    ->collapsible()
                    ->visible(fn ($get) => !empty($get('invoice_id')))
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('invoice_amount')
                                ->label('Invoice Amount')
                                ->state(function ($get) {
                                    $invoice = Invoice::find($get('invoice_id'));
                                    return $invoice ? 'KES ' . number_format($invoice->amount, 2) : '-';
                                }),

                            TextEntry::make('amount_paid_so_far')
                                ->label('Paid So Far')
                                ->state(function ($get) {
                                    $invoice = Invoice::find($get('invoice_id'));
                                    return $invoice ? 'KES ' . number_format($invoice->amount_paid, 2) : '-';
                                }),

                            TextEntry::make('current_balance')
                                ->label('Current Balance')
                                ->weight('bold')
                                ->state(function ($get) {
                                    $invoice = Invoice::find($get('invoice_id'));
                                    return $invoice
                                        ? 'KES ' . number_format($invoice->amount - $invoice->amount_paid, 2)
                                        : '-';
                                }),
                        ]),

                        TextEntry::make('after_payment_balance')
                            ->label('After This Payment')
                            ->weight('bold')
                            ->color(fn ($get) => 
                                (float) $get('amount') > 0 ? 'success' : 'gray'
                            )
                            ->state(function ($get) {
                                $invoice = Invoice::find($get('invoice_id'));
                                $amount = (float) ($get('amount') ?? 0);
                                if (!$invoice) return '-';
                                $newBalance = ($invoice->amount - $invoice->amount_paid) - $amount;
                                return 'KES ' . number_format($newBalance, 2);
                            })
                            ->visible(fn ($get) => !empty($get('amount')) && (float) $get('amount') > 0),

                        TextEntry::make('payment_status_warning')
                            ->hiddenLabel()
                            ->color('danger')
                            ->icon('heroicon-o-exclamation-triangle')
                            ->state(function ($get) {
                                $invoice = Invoice::find($get('invoice_id'));
                                $amount = (float) ($get('amount') ?? 0);
                                if (!$invoice) return null;
                                $balance = $invoice->amount - $invoice->amount_paid;
                                return $amount > $balance
                                    ? 'Payment exceeds current balance by KES ' . number_format($amount - $balance, 2)
                                    : null;
                            })
                            ->visible(function ($get) {
                                $invoice = Invoice::find($get('invoice_id'));
                                $amount = (float) ($get('amount') ?? 0);
                                if (!$invoice) return false;
                                return $amount > ($invoice->amount - $invoice->amount_paid);
                            }),
                    ]),

                /* -------------------- No Invoice Note -------------------- */
                Section::make('Payment Note')
                    ->icon('heroicon-o-information-circle')
                    ->visible(fn ($get) => empty($get('invoice_id')))
                    ->schema([
                        TextEntry::make('payment_info')
                            ->hiddenLabel()
                            ->state('This payment is not linked to a specific invoice. It will be recorded as a general payment for the student.'),
                    ]),
            ]);
    }

    protected static function resetMethodSpecificFields($set, ?string $method): void
    {
        if ($method !== 'mpesa') {
            $set('mpesa_receipt', null);
            $set('checkout_request_id', null);
            $set('merchant_request_id', null);
        }
        if (!in_array($method, ['bank_transfer', 'card', 'cheque'])) {
            $set('bank_name', null);
            $set('transaction_reference', null);
            $set('card_last_four', null);
        }
    }
}
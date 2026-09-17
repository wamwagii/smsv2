<?php

namespace App\Filament\Resources\Payments\Schemas;

use App\Models\Payment;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PaymentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Payment Summary')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('receipt_number')
                            ->label('Receipt No.')
                            ->weight('bold')
                            ->color('primary')
                            ->copyable()
                            ->copyMessage('Receipt number copied')
                            ->placeholder('-'),

                        TextEntry::make('amount')
                            ->label('Amount')
                            ->money('KES')
                            ->weight('bold')
                            ->color('success'),

                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (?string $state) => match ($state) {
                                'completed'  => 'success',
                                'pending'    => 'warning',
                                'processing' => 'info',
                                'failed'     => 'danger',
                                'refunded'   => 'gray',
                                default      => 'gray',
                            })
                            ->icon(fn (?string $state) => match ($state) {
                                'completed'  => 'heroicon-o-check-circle',
                                'pending'    => 'heroicon-o-clock',
                                'processing' => 'heroicon-o-arrow-path',
                                'failed'     => 'heroicon-o-x-circle',
                                default      => null,
                            })
                            ->formatStateUsing(fn (?string $state) => ucfirst($state ?? '-')),

                        TextEntry::make('payment_method')
                            ->label('Method')
                            ->badge()
                            ->color(fn (?string $state) => match ($state) {
                                'mpesa'         => 'success',
                                'bank_transfer' => 'primary',
                                'cash'          => 'warning',
                                'card'          => 'info',
                                'cheque'        => 'gray',
                                default         => 'gray',
                            })
                            ->formatStateUsing(fn (?string $state) => ucfirst(str_replace('_', ' ', $state ?? '-'))),

                        TextEntry::make('payment_date')
                            ->label('Payment Date')
                            ->date('d/m/Y'),

                        TextEntry::make('payment_time')
                            ->label('Time')
                            ->time('H:i')
                            ->placeholder('-'),
                    ]),

                Section::make('Invoice & Student')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('invoice.invoice_number')
                            ->label('Invoice No.')
                            ->badge()
                            ->color('primary')
                            ->placeholder('-'),

                        TextEntry::make('student.admission_number')
                            ->label('Admission No.')
                            ->copyable()
                            ->placeholder('-'),

                        TextEntry::make('student.full_name')
                            ->label('Student Name')
                            ->state(fn (Payment $record) => $record->student?->full_name)
                            ->weight('semibold')
                            ->placeholder('-'),

                        TextEntry::make('student.class.class_code')
                            ->label('Class')
                            ->badge()
                            ->color('success')
                            ->placeholder('-'),

                        TextEntry::make('parent.full_name')
                            ->label('Paid By')
                            ->state(fn (Payment $record) => $record->parent?->full_name)
                            ->placeholder('-'),

                        TextEntry::make('parent.phone_number')
                            ->label('Payer Phone')
                            ->icon('heroicon-o-phone')
                            ->copyable()
                            ->placeholder('-'),
                    ]),

                Section::make('Transaction Details')
                    ->columns(2)
                    ->collapsible()
                    ->schema([
                        TextEntry::make('mpesa_receipt')
                            ->label('M-Pesa Receipt')
                            ->copyable()
                            ->placeholder('-')
                            ->visible(fn (Payment $record) => $record->payment_method === 'mpesa'),

                        TextEntry::make('checkout_request_id')
                            ->label('Checkout Request ID')
                            ->copyable()
                            ->placeholder('-')
                            ->visible(fn (Payment $record) => $record->payment_method === 'mpesa'),

                        TextEntry::make('merchant_request_id')
                            ->label('Merchant Request ID')
                            ->copyable()
                            ->placeholder('-')
                            ->visible(fn (Payment $record) => $record->payment_method === 'mpesa'),

                        TextEntry::make('transaction_reference')
                            ->label('Transaction Reference')
                            ->copyable()
                            ->placeholder('-'),

                        TextEntry::make('bank_name')
                            ->label('Bank')
                            ->placeholder('-')
                            ->visible(fn (Payment $record) => in_array($record->payment_method, ['bank_transfer', 'cheque'])),

                        TextEntry::make('card_last_four')
                            ->label('Card Last 4 Digits')
                            ->formatStateUsing(fn (?string $state) => $state ? '•••• ' . $state : '-')
                            ->placeholder('-')
                            ->visible(fn (Payment $record) => $record->payment_method === 'card'),

                        TextEntry::make('idempotency_key')
                            ->label('Idempotency Key')
                            ->copyable()
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),

                Section::make('Notes')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextEntry::make('notes')
                            ->label('Notes')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),

                Section::make('Receipt')
                    ->columns(2)
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextEntry::make('receipt_path')
                            ->label('Receipt File')
                            ->placeholder('-')
                            ->url(fn (Payment $record) => $record->receipt_path
                                ? \Illuminate\Support\Facades\Storage::url($record->receipt_path)
                                : null
                            )
                            ->openUrlInNewTab()
                            ->formatStateUsing(fn ($state) => $state ? basename($state) : '-'),
                    ]),

                Section::make('Gateway Response')
                    ->description('Raw response from the payment gateway.')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextEntry::make('gateway_response')
                            ->hiddenLabel()
                            ->state(fn (Payment $record) => $record->gateway_response)
                            ->formatStateUsing(function ($state) {
                                if (is_array($state)) {
                                    return json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                                }
                                if (is_string($state)) {
                                    $decoded = json_decode($state, true);
                                    return $decoded
                                        ? json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
                                        : $state;
                                }
                                return '-';
                            })
                            ->fontFamily('mono')
                            ->copyable()
                            ->columnSpanFull(),
                    ]),

                Section::make('Record Metadata')
                    ->columns(3)
                    ->collapsible()
                    ->collapsed()
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
}
<?php

namespace App\Filament\Resources\Payments\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PaymentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('idempotency_key'),
                TextEntry::make('invoice_id')
                    ->numeric(),
                TextEntry::make('student_id')
                    ->numeric(),
                TextEntry::make('parent_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('amount')
                    ->numeric(),
                TextEntry::make('payment_method'),
                TextEntry::make('status'),
                TextEntry::make('mpesa_receipt')
                    ->placeholder('-'),
                TextEntry::make('checkout_request_id')
                    ->placeholder('-'),
                TextEntry::make('merchant_request_id')
                    ->placeholder('-'),
                TextEntry::make('transaction_reference')
                    ->placeholder('-'),
                TextEntry::make('bank_name')
                    ->placeholder('-'),
                TextEntry::make('card_last_four')
                    ->placeholder('-'),
                TextEntry::make('payment_date')
                    ->date(),
                TextEntry::make('payment_time')
                    ->time()
                    ->placeholder('-'),
                TextEntry::make('notes')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('gateway_response')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('receipt_number')
                    ->placeholder('-'),
                TextEntry::make('receipt_path')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}

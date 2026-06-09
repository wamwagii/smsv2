<?php

namespace App\Filament\Resources\Payments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Collection;
use Filament\Notifications\Notification;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('receipt_number')
                    ->label('Receipt No.')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->color('primary')
                    ->copyable(),
                
                TextColumn::make('invoice.invoice_number')
                    ->label('Invoice No.')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('student.admission_number')
                    ->label('Admission No.')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('student.first_name')
                    ->label('Student Name')
                    ->getStateUsing(fn ($record) => $record->student ? $record->student->first_name . ' ' . $record->student->last_name : '-')
                    ->searchable(query: function ($query, $search) {
                        return $query->whereHas('student', function ($q) use ($search) {
                            $q->where('first_name', 'like', "%{$search}%")
                              ->orWhere('last_name', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(),
                
                TextColumn::make('student.class.class_code')
                    ->label('Class')
                    ->getStateUsing(fn ($record) => $record->student && $record->student->class ? $record->student->class->class_code : '-')
                    ->searchable(query: function ($query, $search) {
                        return $query->whereHas('student.class', function ($q) use ($search) {
                            $q->where('class_code', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(),
                
                TextColumn::make('amount')
                    ->label('Amount')
                    ->money('KES')
                    ->sortable()
                    ->weight('bold'),
                
                BadgeColumn::make('payment_method')
                    ->label('Method')
                    ->colors([
                        'success' => 'mpesa',
                        'primary' => 'bank_transfer',
                        'warning' => 'cash',
                        'info' => 'card',
                        'gray' => 'cheque',
                    ])
                    ->icons([
                        'heroicon-o-phone' => 'mpesa',
                        'heroicon-o-building-library' => 'bank_transfer',
                        'heroicon-o-banknotes' => 'cash',
                        'heroicon-o-credit-card' => 'card',
                    ])
                    ->formatStateUsing(fn ($state) => ucfirst(str_replace('_', ' ', $state))),
                
                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'completed',
                        'warning' => 'pending',
                        'info' => 'processing',
                        'danger' => 'failed',
                        'gray' => 'refunded',
                    ])
                    ->icons([
                        'heroicon-o-check-circle' => 'completed',
                        'heroicon-o-clock' => 'pending',
                        'heroicon-o-arrow-path' => 'processing',
                        'heroicon-o-x-circle' => 'failed',
                    ]),
                
                TextColumn::make('mpesa_receipt')
                    ->label('M-Pesa Receipt')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->copyable(),
                
                TextColumn::make('transaction_reference')
                    ->label('Transaction Ref')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('payment_date')
                    ->label('Payment Date')
                    ->date('d/m/Y')
                    ->sortable(),
                
                TextColumn::make('payment_time')
                    ->label('Time')
                    ->time('H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('parent.first_name')
                    ->label('Paid By')
                    ->getStateUsing(fn ($record) => $record->parent ? $record->parent->first_name . ' ' . $record->parent->last_name : '-')
                    ->searchable(query: function ($query, $search) {
                        return $query->whereHas('parent', function ($q) use ($search) {
                            $q->where('first_name', 'like', "%{$search}%")
                              ->orWhere('last_name', 'like', "%{$search}%");
                        });
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('payment_method')
                    ->label('Payment Method')
                    ->options([
                        'mpesa' => 'M-Pesa',
                        'bank_transfer' => 'Bank Transfer',
                        'cash' => 'Cash',
                        'cheque' => 'Cheque',
                        'card' => 'Card',
                    ]),
                
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                        'refunded' => 'Refunded',
                    ]),
                
                SelectFilter::make('student.class_id')
                    ->label('Class')
                    ->options(function () {
                        return \App\Models\Classes::pluck('class_code', 'id')->toArray();
                    })
                    ->searchable()
                    ->preload(),
                
                Filter::make('payment_date_range')
                    ->label('Payment Date Range')
                    ->form([
                        DatePicker::make('payment_from')
                            ->label('From'),
                        DatePicker::make('payment_until')
                            ->label('To'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['payment_from'],
                                fn ($query) => $query->whereDate('payment_date', '>=', $data['payment_from']),
                            )
                            ->when(
                                $data['payment_until'],
                                fn ($query) => $query->whereDate('payment_date', '<=', $data['payment_until']),
                            );
                    }),
                
                Filter::make('today')
                    ->label('Today\'s Payments')
                    ->query(fn ($query) => $query->whereDate('payment_date', today()))
                    ->toggle(),
                
                Filter::make('this_week')
                    ->label('This Week')
                    ->query(fn ($query) => $query->whereBetween('payment_date', [now()->startOfWeek(), now()->endOfWeek()]))
                    ->toggle(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('View')
                    ->color('info')
                    ->icon('heroicon-o-eye'),
                
                EditAction::make()
                    ->label('Edit')
                    ->color('warning')
                    ->icon('heroicon-o-pencil'),
                
                Action::make('print_receipt')
                    ->label('Print Receipt')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->url(fn ($record) => route('payments.receipt', $record), shouldOpenInNewTab: true),
                
                Action::make('mark_completed')
                    ->label('Mark as Completed')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->action(function ($record) {
                        $record->update(['status' => 'completed']);
                        // Update invoice balance
                        if ($record->invoice) {
                            $record->invoice->updateBalance();
                        }
                        Notification::make()
                            ->title('Payment marked as completed')
                            ->success()
                            ->send();
                    }),
                
                \Filament\Actions\DeleteAction::make()
                    ->label('Delete')
                    ->color('danger')
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Delete Selected')
                        ->requiresConfirmation(),
                    
                    BulkAction::make('mark_completed_bulk')
                        ->label('Mark as Completed')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records) {
                            $count = 0;
                            foreach ($records as $record) {
                                if ($record->status === 'pending') {
                                    $record->update(['status' => 'completed']);
                                    if ($record->invoice) {
                                        $record->invoice->updateBalance();
                                    }
                                    $count++;
                                }
                            }
                            Notification::make()
                                ->title($count . ' payments marked as completed')
                                ->success()
                                ->send();
                        }),
                    
                    BulkAction::make('generate_receipts')
                        ->label('Generate Receipts')
                        ->icon('heroicon-o-document-text')
                        ->color('info')
                        ->action(function (Collection $records) {
                            // Logic to generate PDF receipts
                            Notification::make()
                                ->title('Receipt generation started for ' . $records->count() . ' payments')
                                ->info()
                                ->send();
                        }),
                ]),
            ])
            ->headerActions([
                Action::make('create')
                    ->label('New Payment')
                    ->icon('heroicon-o-plus')
                    ->color('primary')
                    ->url(route('filament.admin.resources.payments.create')),
            ])
            ->defaultSort('payment_date', 'desc')
            ->searchable()
            ->persistFiltersInSession()
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25);
    }
}
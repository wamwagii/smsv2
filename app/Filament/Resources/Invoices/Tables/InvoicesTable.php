<?php

namespace App\Filament\Resources\Invoices\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Collection;
use Filament\Notifications\Notification;

class InvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('Invoice No.')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->color('primary')
                    ->copyable()
                    ->copyMessage('Invoice number copied'),
                
                TextColumn::make('student.admission_number')
                    ->label('Admission No.')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('student.full_name')
                    ->label('Student Name')
                    ->getStateUsing(fn ($record) => $record->student->first_name . ' ' . $record->student->last_name)
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('student.class.class_code')
                    ->label('Class')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('amount')
                    ->label('Amount')
                    ->money('KES')
                    ->sortable(),
                
                TextColumn::make('amount_paid')
                    ->label('Paid')
                    ->money('KES')
                    ->sortable()
                    ->color('success'),
                
                TextColumn::make('balance')
                    ->label('Balance')
                    ->money('KES')
                    ->sortable()
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'success')
                    ->weight('bold'),
                
                BadgeColumn::make('term')
                    ->label('Term')
                    ->formatStateUsing(fn ($state) => ucfirst(str_replace('_', ' ', $state)))
                    ->colors([
                        'primary' => 'term_1',
                        'warning' => 'term_2',
                        'success' => 'term_3',
                    ]),
                
                TextColumn::make('due_date')
                    ->label('Due Date')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn ($state) => $state->isPast() ? 'danger' : null),
                
                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'danger' => 'pending',
                        'warning' => 'partially_paid',
                        'success' => 'paid',
                        'gray' => 'overdue',
                        'info' => 'waived',
                    ])
                    ->icons([
                        'heroicon-o-clock' => 'pending',
                        'heroicon-o-check-circle' => 'paid',
                        'heroicon-o-exclamation-circle' => 'overdue',
                    ])
                    ->sortable(),
                
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'partially_paid' => 'Partially Paid',
                        'paid' => 'Paid',
                        'overdue' => 'Overdue',
                        'waived' => 'Waived',
                    ]),
                
                SelectFilter::make('term')
                    ->label('Term')
                    ->options([
                        'term_1' => 'Term 1',
                        'term_2' => 'Term 2',
                        'term_3' => 'Term 3',
                    ]),
                
                SelectFilter::make('student.class_id')
                    ->label('Class')
                    ->relationship('student.class', 'class_code')
                    ->searchable()
                    ->preload(),
                
                Filter::make('due_date_range')
                    ->label('Due Date Range')
                    ->form([
                        DatePicker::make('due_from')
                            ->label('Due From'),
                        DatePicker::make('due_until')
                            ->label('Due Until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['due_from'],
                                fn ($query) => $query->whereDate('due_date', '>=', $data['due_from']),
                            )
                            ->when(
                                $data['due_until'],
                                fn ($query) => $query->whereDate('due_date', '<=', $data['due_until']),
                            );
                    }),
                
                Filter::make('overdue')
                    ->label('Overdue Invoices')
                    ->query(fn ($query) => $query->where('due_date', '<', now())->where('status', '!=', 'paid'))
                    ->toggle(),
                
                Filter::make('has_balance')
                    ->label('Has Balance')
                    ->query(fn ($query) => $query->whereColumn('amount', '>', 'amount_paid'))
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
                
                Action::make('mark_as_paid')
                    ->label('Mark as Paid')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Mark Invoice as Paid')
                    ->modalDescription('This will mark the invoice as fully paid. Are you sure?')
                    ->action(function ($record) {
                        $record->update([
                            'amount_paid' => $record->amount,
                            'status' => 'paid',
                        ]);
                        Notification::make()
                            ->title('Invoice marked as paid')
                            ->success()
                            ->send();
                    })
                    ->visible(fn ($record) => $record->status !== 'paid'),
                
                Action::make('print_invoice')
                    ->label('Print')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->url(fn ($record) => route('invoices.print', $record), shouldOpenInNewTab: true),
                
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
                    
                    BulkAction::make('mark_as_paid_bulk')
                        ->label('Mark as Paid')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records) {
                            $count = 0;
                            foreach ($records as $record) {
                                if ($record->status !== 'paid') {
                                    $record->update([
                                        'amount_paid' => $record->amount,
                                        'status' => 'paid',
                                    ]);
                                    $count++;
                                }
                            }
                            Notification::make()
                                ->title($count . ' invoices marked as paid')
                                ->success()
                                ->send();
                        }),
                    
                    BulkAction::make('send_reminders')
                        ->label('Send Reminders')
                        ->icon('heroicon-o-envelope')
                        ->color('warning')
                        ->action(function (Collection $records) {
                            // Logic to send SMS/Email reminders
                            Notification::make()
                                ->title('Reminders sent to ' . $records->count() . ' students')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->headerActions([
                Action::make('create')
                    ->label('New Invoice')
                    ->icon('heroicon-o-plus')
                    ->color('primary')
                    ->url(route('filament.admin.resources.invoices.create')),
            ])
            ->defaultSort('created_at', 'desc')
            ->searchable()
            ->persistFiltersInSession()
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25);
    }
}
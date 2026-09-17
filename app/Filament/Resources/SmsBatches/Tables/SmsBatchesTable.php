<?php

namespace App\Filament\Resources\SmsBatches\Tables;

use App\Jobs\SendSmsMessage;
use App\Models\SmsMessage;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class SmsBatchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('#')->sortable(),
                TextColumn::make('body')->limit(60)->tooltip(fn ($state) => $state)->searchable(),
                TextColumn::make('audience')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'all'    => 'primary',
                        'class'  => 'info',
                        'custom' => 'warning',
                        default  => 'gray',
                    }),
                TextColumn::make('class.class_code')->label('Class')->placeholder('—'),
                TextColumn::make('total_recipients')->label('Recipients')->badge()->color('gray'),
                TextColumn::make('sent_count')->label('Sent')->badge()->color('success'),
                TextColumn::make('failed_count')
                    ->label('Failed')
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'gray'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'completed'  => 'success',
                        'processing' => 'warning',
                        'failed'     => 'danger',
                        default      => 'gray',
                    }),
                TextColumn::make('user.name')->label('By')->placeholder('System')->toggleable(),
                TextColumn::make('created_at')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('audience')->options([
                    'all'    => 'All',
                    'class'  => 'By class',
                    'custom' => 'Custom',
                ]),
                SelectFilter::make('status')->options([
                    'pending'    => 'Pending',
                    'processing' => 'Processing',
                    'completed'  => 'Completed',
                    'failed'     => 'Failed',
                ]),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),

                    \Filament\Actions\Action::make('retry_failed')
                        ->label('Retry Failed')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Retry failed messages')
                        ->modalDescription(fn ($record) =>
                            "Re-queue all failed messages in batch #{$record->id}? " .
                            "Currently {$record->failed_count} failed."
                        )
                        ->visible(fn ($record) => $record->failed_count > 0)
                        ->action(function ($record) {
                            $count = static::retryFailedInBatch($record);

                            Notification::make()
                                ->title($count > 0
                                    ? "{$count} message(s) re-queued"
                                    : 'No failed messages to retry')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('retry_failed_selected')
                        ->label('Retry Failed in Selected')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Retry failed messages')
                        ->modalDescription(function (Collection $records) {
                            $total = $records->sum('failed_count');
                            return "Re-queue all failed messages across {$records->count()} batch(es)? " .
                                   "Total failed messages: {$total}.";
                        })
                        ->action(function (Collection $records) {
                            $total = 0;

                            foreach ($records as $batch) {
                                $total += static::retryFailedInBatch($batch);
                            }

                            Notification::make()
                                ->title($total > 0
                                    ? "{$total} message(s) re-queued across {$records->count()} batch(es)"
                                    : 'No failed messages to retry')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped();
    }

    /**
     * Re-queue every failed message in a batch.
     * Returns the number of messages re-queued.
     */
    protected static function retryFailedInBatch($batch): int
    {
        $count = 0;

        SmsMessage::where('batch_id', $batch->id)
            ->where('status', 'failed')
            ->each(function (SmsMessage $message) use (&$count) {
                $message->update([
                    'status'  => 'pending',
                    'error'   => null,
                    'sent_at' => null,
                ]);

                SendSmsMessage::dispatch($message->id);
                $count++;
            });

        if ($count > 0) {
            // Reset batch to processing so the worker updates it correctly
            $batch->update(['status' => 'processing']);
        }

        return $count;
    }
}
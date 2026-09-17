<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentPaymentsWidget extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Payment::query()
                    ->with(['student'])
                    ->where('status', 'completed')
                    ->latest('payment_date')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('student.full_name')
                    ->label('Student')
                    ->default('—')
                    ->searchable(),

                Tables\Columns\TextColumn::make('amount')
                    ->money('KES')
                    ->weight('semibold')
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Method')
                    ->badge()
                    ->color(fn ($state) => match (strtolower((string) $state)) {
                        'mpesa'  => 'success',
                        'bank'   => 'info',
                        'card'   => 'warning',
                        'cash'   => 'gray',
                        default  => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => ucfirst((string) $state)),

                Tables\Columns\TextColumn::make('mpesa_receipt')
                    ->label('Receipt')
                    ->placeholder('—')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('payment_date')
                    ->label('Paid')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_time')
                    ->label('Time')
                    ->time('H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('payment_date', 'desc')
            ->paginated(false)
            ->heading('Recent Payments');
    }
}
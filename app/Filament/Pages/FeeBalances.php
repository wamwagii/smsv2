<?php

namespace App\Filament\Pages;

use App\Models\Student;
use BackedEnum;
use App\Filament\Widgets\FeesStatsWidget;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class FeeBalances extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-s-banknotes';

    protected static string|UnitEnum|null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Fee Balances';

    protected static ?string $title = 'Fee Balances';

    protected string $view = 'filament.pages.fee-balances';

    protected function getHeaderWidgets(): array
    {
        return [
            FeesStatsWidget::class,
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Student::query()
                    ->where('status', 'active')
                    ->withSum('invoices as billed_sum', 'amount')
                    ->withSum('invoices as paid_sum', 'amount_paid')
            )
            ->columns([
                TextColumn::make('admission_number')
                    ->label('Adm No.')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('full_name')
                    ->label('Student')
                    ->state(fn($record) => $record->full_name)
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(['first_name', 'last_name']),

                TextColumn::make('class.class_code')
                    ->label('Class')
                    ->badge()
                    ->color('success'),

                TextColumn::make('billed_sum')
                    ->label('Billed')
                    ->money('KES')
                    ->default(0)
                    ->sortable(),

                TextColumn::make('paid_sum')
                    ->label('Paid')
                    ->money('KES')
                    ->default(0)
                    ->sortable(),

                TextColumn::make('balance')
                    ->label('Balance')
                    ->state(fn($record) => round(($record->billed_sum ?? 0) - ($record->paid_sum ?? 0), 2))
                    ->money('KES')
                    ->weight('bold')
                    ->color(fn($state) => $state > 0.01 ? 'danger' : 'success')
                    ->sortable(query: function (Builder $query, string $direction) {
                        return $query->orderByRaw(
                            'COALESCE(billed_sum, 0) - COALESCE(paid_sum, 0) ' . $direction
                        );
                    }),
            ])
            ->filters([
                SelectFilter::make('class_id')
                    ->label('Class')
                    ->relationship('class', 'class_code')
                    ->searchable()
                    ->preload(),
            ])
            ->defaultSort('admission_number', 'asc')
            ->striped()
            ->paginated([25, 50, 100])
            ->defaultPaginationPageOption(50);
    }
}
<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use App\Models\Student;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FeesStatsWidget extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '60s';

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $totalBilled    = (float) Invoice::sum('amount');
        $totalCollected = (float) Invoice::sum('amount_paid');
        $outstanding    = round($totalBilled - $totalCollected, 2);

        $collectionRate = $totalBilled > 0
            ? round(($totalCollected / $totalBilled) * 100, 1)
            : 0;

        $overdueCount = Invoice::query()
            ->whereDate('due_date', '<', today())
            ->whereNotIn('status', ['paid', 'waived'])
            ->count();

       $overdueAmount = (float) Invoice::query()
    ->whereDate('due_date', '<', today())
    ->whereNotIn('status', ['paid', 'waived'])
    ->selectRaw('SUM(amount - amount_paid) as total')
    ->value('total') ?? 0;

        $studentsWithBalance = Student::query()
            ->where('status', 'active')
            ->whereHas('invoices', fn ($q) => $q->where('balance', '>', 0.01))
            ->count();

        return [
            Stat::make('Billed', 'KES ' . number_format($totalBilled, 0))
                ->description('Total across all invoices')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),

            Stat::make('Collected', 'KES ' . number_format($totalCollected, 0))
                ->description("{$collectionRate}% collection rate")
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Outstanding', 'KES ' . number_format($outstanding, 0))
                ->description('Still owed by parents')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($outstanding > 0 ? 'danger' : 'success'),

            Stat::make('Overdue', 'KES ' . number_format($overdueAmount, 0))
                ->description("{$overdueCount} overdue invoice(s)")
                ->descriptionIcon('heroicon-m-clock')
                ->color($overdueAmount > 0 ? 'danger' : 'gray'),

            Stat::make('Students Owing', $studentsWithBalance)
                ->description('Active students with a balance')
                ->descriptionIcon('heroicon-m-user-group')
                ->color($studentsWithBalance > 0 ? 'warning' : 'success'),
        ];
    }
}
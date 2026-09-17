<?php

namespace App\Filament\Widgets;

use App\Models\SmsBatch;
use App\Models\SmsMessage;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SmsStatsWidget extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '30s';

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $totalBatches  = SmsBatch::count();
        $totalMessages = SmsMessage::count();
        $totalSent     = SmsMessage::where('status', 'sent')->count();
        $totalFailed   = SmsMessage::where('status', 'failed')->count();
        $totalPending  = SmsMessage::where('status', 'pending')->count();

        $deliveryRate = $totalMessages > 0
            ? round(($totalSent / $totalMessages) * 100, 1)
            : 0;

        // Cost: AT charges ~KES 0.80 per SMS. Adjust if your rate differs.
        $costPerSms = 0.80;
        $estimatedCost = round($totalSent * $costPerSms, 2);

        // Last 7 days volume
        $last7Days = SmsMessage::where('created_at', '>=', now()->subDays(7))->count();
        $previous7Days = SmsMessage::whereBetween('created_at', [
            now()->subDays(14),
            now()->subDays(7),
        ])->count();

        $trend = $previous7Days > 0
            ? round((($last7Days - $previous7Days) / $previous7Days) * 100, 1)
            : ($last7Days > 0 ? 100 : 0);

        return [
            Stat::make('Total Batches', number_format($totalBatches))
                ->description('All-time send batches')
                ->descriptionIcon('heroicon-m-paper-airplane')
                ->color('primary'),

            Stat::make('Messages Sent', number_format($totalSent))
                ->description("{$deliveryRate}% delivery rate")
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Failed', number_format($totalFailed))
                ->description($totalFailed > 0 ? 'Needs attention' : 'No failures')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color($totalFailed > 0 ? 'danger' : 'gray'),

            Stat::make('Pending', number_format($totalPending))
                ->description($totalPending > 0 ? 'In queue' : 'Queue empty')
                ->descriptionIcon('heroicon-m-clock')
                ->color($totalPending > 0 ? 'warning' : 'gray'),

            Stat::make('Last 7 Days', number_format($last7Days))
                ->description(
                    $trend > 0
                        ? "↑ {$trend}% vs previous week"
                        : ($trend < 0
                            ? "↓ " . abs($trend) . '% vs previous week'
                            : 'No change vs previous week')
                )
                ->descriptionIcon($trend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($trend >= 0 ? 'success' : 'danger'),

            Stat::make('Estimated Cost', 'KES ' . number_format($estimatedCost, 2))
                ->description('@ KES ' . number_format($costPerSms, 2) . ' per SMS')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('info'),
        ];
    }
}
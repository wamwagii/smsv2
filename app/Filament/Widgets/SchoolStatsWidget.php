<?php

namespace App\Filament\Widgets;

use App\Models\Department;
use App\Models\FeeStructure;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Staff;
use App\Models\Student;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SchoolStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalReceived = (float) Payment::query()
            ->where('status', 'completed')
            ->sum('amount');

        return [
            Stat::make('Students', Student::count())
                ->description('Enrolled')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),

            Stat::make('Staff', Staff::count())
                ->description('All staff')
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('info'),

            Stat::make('Departments', Department::count())
                ->description('Active: ' . Department::where('is_active', true)->count())
                ->descriptionIcon('heroicon-m-building-office')
                ->color('success'),

            Stat::make('Fee Structures', FeeStructure::where('is_active', true)->count())
                ->description('Active')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('warning'),

            Stat::make('Invoices', Invoice::count())
                ->description('All time')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('gray'),

            Stat::make('Payments', 'KES ' . number_format($totalReceived, 0))
                ->description('Completed only')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
        ];
    }
}
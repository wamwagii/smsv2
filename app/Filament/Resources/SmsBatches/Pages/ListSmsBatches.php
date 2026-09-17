<?php

namespace App\Filament\Resources\SmsBatches\Pages;

use App\Filament\Pages\SendSms;
use App\Filament\Resources\SmsBatches\SmsBatchResource;
use App\Filament\Widgets\SmsStatsWidget;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListSmsBatches extends ListRecords
{
    protected static string $resource = SmsBatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('send_sms')
                ->label('Send SMS')
                ->icon('heroicon-o-paper-airplane')
                ->url(SendSms::getUrl()),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            SmsStatsWidget::class,
        ];
    }
}
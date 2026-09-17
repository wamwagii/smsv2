<?php

namespace App\Filament\Resources\Attendances\Pages;

use App\Filament\Resources\Attendances\AttendanceResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAttendances extends ListRecords
{
    protected static string $resource = AttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('mark_attendance')
                ->label('Mark Attendance')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('primary')
                ->url(fn () => AttendanceResource::getUrl('mark')),

            //CreateAction::make()
            //    ->label('New Record')
             //   ->icon('heroicon-o-plus'),
        ];
    }
}
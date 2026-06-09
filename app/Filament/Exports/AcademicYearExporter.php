<?php

namespace App\Filament\Exports;

use App\Models\AcademicYears;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Filament\Notifications\Notification;

class AcademicYearExporter extends Exporter
{
    protected static ?string $model = AcademicYears::class;
    
    // Define which columns can be exported
    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID')
                ->enabledByDefault(false), // Not enabled by default, user can choose
            
            ExportColumn::make('name')
                ->label('Academic Year')
                ->enabledByDefault(true), // Enabled by default
            
            ExportColumn::make('start_date')
                ->label('Start Date')
                ->formatStateUsing(fn ($state) => $state ? $state->format('d/m/Y') : '')
                ->enabledByDefault(true),
            
            ExportColumn::make('end_date')
                ->label('End Date')
                ->formatStateUsing(fn ($state) => $state ? $state->format('d/m/Y') : '')
                ->enabledByDefault(true),
            
            ExportColumn::make('status')
                ->label('Status')
                ->formatStateUsing(fn ($state) => ucfirst($state))
                ->enabledByDefault(true),
            
            ExportColumn::make('is_current')
                ->label('Current Year')
                ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No')
                ->enabledByDefault(true),
            
            ExportColumn::make('students_count')
                ->label('Total Students')
                ->counts('students')
                ->enabledByDefault(false), // Relationship count
            
            ExportColumn::make('created_at')
                ->label('Created At')
                ->formatStateUsing(fn ($state) => $state ? $state->format('d/m/Y H:i') : '')
                ->enabledByDefault(false),
            
            ExportColumn::make('updated_at')
                ->label('Last Updated')
                ->formatStateUsing(fn ($state) => $state ? $state->format('d/m/Y H:i') : '')
                ->enabledByDefault(false),
        ];
    }
    
    // Customize the completion notification
    public static function getCompletedNotificationTitle(Export $export): string
    {
        return 'Academic Years Export Completed';
    }
    
    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = "Successfully exported {$export->successful_rows} academic years.";
        
        if ($export->failed_rows > 0) {
            $body .= " Failed to export {$export->failed_rows} records.";
        }
        
        return $body;
    }
    
    // Modify the query before export (optional)
    public static function modifyQuery($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->withCount('students'); // Eager load student counts
    }
    
    // Customize the export file name
    public function getFileName(Export $export): string
    {
        return "academic-years-" . now()->format('Y-m-d-His');
    }
    
    // Set custom notification
    public static function modifyCompletedNotification(Notification $notification, Export $export): Notification
    {
        return $notification
            ->icon('heroicon-o-calendar')
            ->color('success')
            ->duration(10000) // Show for 10 seconds
            ->actions([
                \Filament\Actions\Action::make('download')
                    ->label('Download')
                    ->url($export->getUrl())
                    ->button(),
            ]);
    }
}
<?php

namespace App\Filament\Resources\AcademicYears\Tables;

use Filament\Actions\ExportAction;
use Filament\Actions\ExportBulkAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use App\Filament\Exports\AcademicYearExporter;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use App\Models\AcademicYears;
use Filament\Notifications\Notification;

class AcademicYearsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Academic Year')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                
                TextColumn::make('start_date')
                    ->label('Start Date')
                    ->date('d/m/Y')
                    ->sortable(),
                
                TextColumn::make('end_date')
                    ->label('End Date')
                    ->date('d/m/Y')
                    ->sortable(),
                
                \Filament\Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'active',
                        'danger' => 'archived',
                    ]),
                
                IconColumn::make('is_current')
                    ->label('Current Year')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('warning')
                    ->falseColor('gray'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'archived' => 'Archived',
                    ]),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                    
                    // Custom action: Set as current
                    \Filament\Actions\Action::make('set_as_current')
                        ->label('Set as Current')
                        ->icon('heroicon-o-star')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function (AcademicYears $record) {
                            AcademicYears::query()->update(['is_current' => false]);
                            $record->update(['is_current' => true]);
                            
                            Notification::make()
                                ->title('Current Year Updated')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->toolbarActions([
                // Export bulk action (exports selected rows only)
                ExportBulkAction::make()
                    ->label('Export Selected')
                    ->exporter(AcademicYearExporter::class)
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->modalHeading('Export Academic Years')
                    ->modalDescription('Select the columns you want to export.')
                    ->chunkSize(100), // Export in chunks of 100 records
                
                // Additional custom export action for all filtered data
                \Filament\Actions\Action::make('export_all_filtered')
                    ->label('Export All Filtered')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('info')
                    ->action(function ($livewire) {
                        // Get all filtered and sorted records
                        $records = $livewire->getFilteredTableQuery()->get();
                        
                        // Trigger export
                        return ExportAction::make()
                            ->exporter(AcademicYearExporter::class)
                            ->modifyQueryUsing(fn ($query) => $query->whereIn('id', $records->pluck('id')))
                            ->run();
                    }),
            ])
            ->headerActions([
                // Header export action (exports all records)
                ExportAction::make()
                    ->label('Export All')
                    ->exporter(AcademicYearExporter::class)
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('primary')
                    ->modalHeading('Export All Academic Years')
                    ->modalDescription('This will export all academic years. You can select which columns to include.')
                    ->columnMappingColumns(2) // 2 columns in the modal for better layout
                    ->maxRows(10000) // Limit to 10,000 rows max
                    ->chunkSize(250), // Process in chunks of 250 records
            ])
            ->selectable() // Required for bulk actions
            ->defaultSort('start_date', 'desc')
            ->searchable()
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
}
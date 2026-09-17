<?php

namespace App\Filament\Resources\Exams\Schemas;

use App\Models\Exam;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class ExamInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Exam Details')
                    ->icon('heroicon-o-document-check')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('name')
                            ->label('Exam Name')
                            ->weight('bold')
                            ->columnSpan(2),

                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (?string $state) => match ($state) {
                                'upcoming'  => 'gray',
                                'ongoing'   => 'info',
                                'completed' => 'warning',
                                'published' => 'success',
                                default     => 'gray',
                            })
                            ->formatStateUsing(fn (?string $state) => ucfirst($state ?? '-')),

                        TextEntry::make('term')
                            ->badge()
                            ->color(fn (?string $state) => match ($state) {
                                'term_1' => 'primary',
                                'term_2' => 'warning',
                                'term_3' => 'success',
                                default  => 'gray',
                            })
                            ->formatStateUsing(fn (?string $state) => ucfirst(str_replace('_', ' ', $state ?? '-'))),

                        TextEntry::make('academicYear.name')
                            ->label('Academic Year'),

                        TextEntry::make('results_count')
                            ->label('Results Recorded')
                            ->state(fn (Exam $record) => $record->results()->count())
                            ->badge()
                            ->color('primary'),

                        TextEntry::make('start_date')
                            ->label('Start Date')
                            ->date('d/m/Y'),

                        TextEntry::make('end_date')
                            ->label('End Date')
                            ->date('d/m/Y')
                            ->belowContent(function (Exam $record) {
                                if (!$record->start_date || !$record->end_date) return null;
                                $days = $record->start_date->diffInDays($record->end_date) + 1;
                                return $days . ' day' . ($days !== 1 ? 's' : '');
                            }),

                        TextEntry::make('total_marks')
                            ->label('Total Marks')
                            ->numeric(),

                        TextEntry::make('passing_marks')
                            ->label('Passing Marks')
                            ->numeric(),

                        TextEntry::make('passing_percentage')
                            ->label('Passing Percentage')
                            ->state(function (Exam $record) {
                                if (!$record->total_marks) return 'N/A';
                                return round(($record->passing_marks / $record->total_marks) * 100, 2) . '%';
                            }),
                    ]),

                Section::make('Description')
                    ->collapsible()
                    ->visible(fn (Exam $record) => filled($record->description))
                    ->schema([
                        TextEntry::make('description')
                            ->hiddenLabel()
                            ->columnSpanFull(),
                    ]),

                Section::make('Record Metadata')
                    ->columns(3)
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('-'),

                        TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('-'),

                        TextEntry::make('deleted_at')
                            ->label('Deleted')
                            ->dateTime('d/m/Y H:i')
                            ->color('danger')
                            ->visible(fn (Exam $record): bool => $record->trashed()),
                    ]),
            ]);
    }
}
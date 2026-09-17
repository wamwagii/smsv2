<?php

namespace App\Filament\Resources\Attendances\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AttendancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('class.class_code')
                    ->label('Class')
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('student.admission_number')
                    ->label('Admission No.')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),

                TextColumn::make('student.full_name')
                    ->label('Student')
                    ->state(fn ($record) => $record->student?->full_name)
                    ->searchable(query: fn (Builder $query, string $search) =>
                        $query->whereHas('student', function ($q) use ($search) {
                            $q->where('first_name', 'like', "%{$search}%")
                              ->orWhere('last_name', 'like', "%{$search}%");
                        })
                    )
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'present' => 'success',
                        'absent'  => 'danger',
                        'late'    => 'warning',
                        'excused' => 'info',
                        'holiday' => 'gray',
                        default   => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state) => ucfirst($state ?? '-'))
                    ->sortable(),

                TextColumn::make('arrival_time')
                    ->label('Arrived')
                    ->time('H:i')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('departure_time')
                    ->label('Left')
                    ->time('H:i')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('reason')
                    ->label('Reason')
                    ->limit(40)
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('markedBy.full_name')
                    ->label('Marked By')
                    ->state(fn ($record) => $record->markedBy?->full_name)
                    ->placeholder('System')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Recorded')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('class_id')
                    ->label('Class')
                    ->relationship('class', 'class_code')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'present' => 'Present',
                        'absent'  => 'Absent',
                        'late'    => 'Late',
                        'excused' => 'Excused',
                        'holiday' => 'Holiday',
                    ]),

                Filter::make('date')
                    ->label('Date')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('date')
                            ->label('Date')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                    ])
                    ->query(fn (Builder $query, array $data) =>
                        $query->when(
                            $data['date'] ?? null,
                            fn ($q, $date) => $q->whereDate('date', $date)
                        )
                    ),

                Filter::make('today')
                    ->label("Today's Attendance")
                    ->query(fn (Builder $query) => $query->whereDate('date', today()))
                    ->toggle(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('View')
                        ->color('info')
                        ->icon('heroicon-o-eye'),

                    EditAction::make()
                        ->label('Edit')
                        ->color('warning')
                        ->icon('heroicon-o-pencil'),

                    DeleteAction::make()
                        ->label('Delete')
                        ->color('danger')
                        ->icon('heroicon-o-trash')
                        ->requiresConfirmation(),
                ])
                    ->label('Actions')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->color('gray'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Delete Selected'),
                ]),
            ])
            ->defaultSort('date', 'desc')
            ->striped();
    }
}
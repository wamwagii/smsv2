<?php

namespace App\Filament\Resources\FeeStructures\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Fieldset;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Schema;
use App\Models\Classes;
use App\Models\AcademicYears;

class FeeStructureForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Fee Structure Information')
                    ->description('Set fees for a specific class and academic year')
                    ->icon('heroicon-o-currency-dollar')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('class_id')
                                    ->label('Class')
                                    ->relationship('class', 'class_code')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Select the class for this fee structure'),
                                
                                Select::make('academic_year_id')
                                    ->label('Academic Year')
                                    ->relationship('academicYear', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->default(function () {
                                        return AcademicYears::where('is_current', true)->first()?->id;
                                    })
                                    ->helperText('Select the academic year'),
                            ]),
                        
                        Fieldset::make('Fee Components')
                            ->label('Fee Breakdown')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('tuition_fees')
                                            ->label('Tuition Fees')
                                            ->numeric()
                                            ->prefix('KES')
                                            ->required()
                                            ->default(0)
                                            ->minValue(0)
                                            ->live()
                                            ->afterStateUpdated(function ($set, $get) {
                                                static::calculateTotal($set, $get);
                                            }),
                                        
                                        TextInput::make('activity_fees')
                                            ->label('Activity Fees')
                                            ->numeric()
                                            ->prefix('KES')
                                            ->default(0)
                                            ->minValue(0)
                                            ->live()
                                            ->afterStateUpdated(function ($set, $get) {
                                                static::calculateTotal($set, $get);
                                            }),
                                        
                                        TextInput::make('library_fees')
                                            ->label('Library Fees')
                                            ->numeric()
                                            ->prefix('KES')
                                            ->default(0)
                                            ->minValue(0)
                                            ->live()
                                            ->afterStateUpdated(function ($set, $get) {
                                                static::calculateTotal($set, $get);
                                            }),
                                        
                                        TextInput::make('sports_fees')
                                            ->label('Sports Fees')
                                            ->numeric()
                                            ->prefix('KES')
                                            ->default(0)
                                            ->minValue(0)
                                            ->live()
                                            ->afterStateUpdated(function ($set, $get) {
                                                static::calculateTotal($set, $get);
                                            }),
                                        
                                        TextInput::make('medical_fees')
                                            ->label('Medical Fees')
                                            ->numeric()
                                            ->prefix('KES')
                                            ->default(0)
                                            ->minValue(0)
                                            ->live()
                                            ->afterStateUpdated(function ($set, $get) {
                                                static::calculateTotal($set, $get);
                                            }),
                                        
                                        TextInput::make('transport_fees')
                                            ->label('Transport Fees')
                                            ->numeric()
                                            ->prefix('KES')
                                            ->default(0)
                                            ->minValue(0)
                                            ->live()
                                            ->afterStateUpdated(function ($set, $get) {
                                                static::calculateTotal($set, $get);
                                            }),
                                        
                                        TextInput::make('boarding_fees')
                                            ->label('Boarding Fees')
                                            ->numeric()
                                            ->prefix('KES')
                                            ->default(0)
                                            ->minValue(0)
                                            ->live()
                                            ->afterStateUpdated(function ($set, $get) {
                                                static::calculateTotal($set, $get);
                                            }),
                                        
                                        TextInput::make('uniform_fees')
                                            ->label('Uniform Fees')
                                            ->numeric()
                                            ->prefix('KES')
                                            ->default(0)
                                            ->minValue(0)
                                            ->live()
                                            ->afterStateUpdated(function ($set, $get) {
                                                static::calculateTotal($set, $get);
                                            }),
                                        
                                        TextInput::make('other_fees')
                                            ->label('Other Fees')
                                            ->numeric()
                                            ->prefix('KES')
                                            ->default(0)
                                            ->minValue(0)
                                            ->live()
                                            ->afterStateUpdated(function ($set, $get) {
                                                static::calculateTotal($set, $get);
                                            }),
                                    ]),
                            ]),
                        
                        Grid::make(2)
                            ->schema([
                                TextInput::make('total_fees')
                                    ->label('Total Fees')
                                    ->numeric()
                                    ->prefix('KES')
                                    ->disabled()
                                    ->dehydrated(true)
                                    ->helperText('Automatically calculated from all fee components'),
                                
                                Toggle::make('is_active')
                                    ->label('Active')
                                    ->default(true)
                                    ->helperText('Inactive fee structures will not be applied to new invoices'),
                            ]),
                    ]),
                
                Section::make('Payment Plan')
                    ->description('Optional: Set up payment schedule (e.g., termly payments)')
                    ->icon('heroicon-o-calendar')
                    ->collapsible()
                    ->schema([
                        Repeater::make('payment_plan')
                            ->label('Payment Installments')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        Select::make('term')
                                            ->label('Term')
                                            ->options([
                                                'term_1' => 'Term 1',
                                                'term_2' => 'Term 2',
                                                'term_3' => 'Term 3',
                                            ])
                                            ->required(),
                                        
                                        DatePicker::make('due_date')
                                            ->label('Due Date')
                                            ->required()
                                            ->native(false)
                                            ->displayFormat('d/m/Y'),
                                        
                                        TextInput::make('amount')
                                            ->label('Amount')
                                            ->numeric()
                                            ->prefix('KES')
                                            ->required()
                                            ->minValue(0),
                                    ]),
                            ])
                            ->columnSpanFull()
                            ->defaultItems(3)
                            ->maxItems(6)
                            ->addActionLabel('Add Installment')
                            ->reorderable(false)
                            ->helperText('Set up payment installments for the academic year (optional)'),
                    ]),
            ]);
    }
    
    protected static function calculateTotal($set, $get)
    {
        $tuition = floatval($get('tuition_fees') ?? 0);
        $activity = floatval($get('activity_fees') ?? 0);
        $library = floatval($get('library_fees') ?? 0);
        $sports = floatval($get('sports_fees') ?? 0);
        $medical = floatval($get('medical_fees') ?? 0);
        $transport = floatval($get('transport_fees') ?? 0);
        $boarding = floatval($get('boarding_fees') ?? 0);
        $uniform = floatval($get('uniform_fees') ?? 0);
        $other = floatval($get('other_fees') ?? 0);
        
        $total = $tuition + $activity + $library + $sports + $medical + $transport + $boarding + $uniform + $other;
        
        $set('total_fees', $total);
    }
}
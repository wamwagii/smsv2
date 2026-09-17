<?php

namespace App\Filament\Resources\FeeStructures\Schemas;

use App\Models\AcademicYears;
use App\Models\Classes;
use App\Models\FeeStructure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class FeeStructureForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Fee Structure Information')
                    ->description('Set fees for a specific grade and academic year')
                    ->icon('heroicon-o-currency-dollar')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('class_id')
                                    ->label('Grade')
                                    ->options(function () {
                                        return Classes::orderBy('level')->get()
                                            ->mapWithKeys(fn ($class) => [$class->id => 'Grade ' . $class->level]);
                                    })
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Select the grade level for this fee structure'),

                                Select::make('academic_year_id')
                                    ->label('Academic Year')
                                    ->relationship('academicYear', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->default(fn () => static::defaultAcademicYearId())
                                    ->afterStateUpdated(function (Set $set, Get $get) {
                                        // Re-seed the term due dates whenever the academic year changes
                                        $set('payment_plan', static::defaultSplit(
                                            static::sumFeeComponents($get),
                                            $get('academic_year_id'),
                                        ));
                                    })
                                    ->helperText('Defaults to next year after 1 September'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('tuition_fees')
                                    ->label('Tuition Fees')
                                    ->numeric()
                                    ->prefix('KES')
                                    ->required()
                                    ->default(0)
                                    ->minValue(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Set $set, Get $get) => static::calculateTotal($set, $get)),

                                TextInput::make('activity_fees')
                                    ->label('Activity Fees')
                                    ->numeric()
                                    ->prefix('KES')
                                    ->default(0)
                                    ->minValue(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Set $set, Get $get) => static::calculateTotal($set, $get)),

                                TextInput::make('library_fees')
                                    ->label('Library Fees')
                                    ->numeric()
                                    ->prefix('KES')
                                    ->default(0)
                                    ->minValue(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Set $set, Get $get) => static::calculateTotal($set, $get)),

                                TextInput::make('sports_fees')
                                    ->label('Sports Fees')
                                    ->numeric()
                                    ->prefix('KES')
                                    ->default(0)
                                    ->minValue(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Set $set, Get $get) => static::calculateTotal($set, $get)),

                                TextInput::make('medical_fees')
                                    ->label('Medical Fees')
                                    ->numeric()
                                    ->prefix('KES')
                                    ->default(0)
                                    ->minValue(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Set $set, Get $get) => static::calculateTotal($set, $get)),

                                TextInput::make('transport_fees')
                                    ->label('Transport Fees')
                                    ->numeric()
                                    ->prefix('KES')
                                    ->default(0)
                                    ->minValue(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Set $set, Get $get) => static::calculateTotal($set, $get)),

                                TextInput::make('boarding_fees')
                                    ->label('Boarding Fees')
                                    ->numeric()
                                    ->prefix('KES')
                                    ->default(0)
                                    ->minValue(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Set $set, Get $get) => static::calculateTotal($set, $get)),

                                TextInput::make('uniform_fees')
                                    ->label('Uniform Fees')
                                    ->numeric()
                                    ->prefix('KES')
                                    ->default(0)
                                    ->minValue(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Set $set, Get $get) => static::calculateTotal($set, $get)),

                                TextInput::make('other_fees')
                                    ->label('Other Fees')
                                    ->numeric()
                                    ->prefix('KES')
                                    ->default(0)
                                    ->minValue(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Set $set, Get $get) => static::calculateTotal($set, $get)),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('total_fees')
                                    ->label('Total Fees')
                                    ->numeric()
                                    ->prefix('KES')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->helperText('Automatically calculated from all fee components'),

                                Toggle::make('is_active')
                                    ->label('Active')
                                    ->default(true)
                                    ->helperText('Inactive fee structures will not be applied to new invoices'),
                            ]),
                    ]),

                Section::make('Payment Plan')
                    ->description('Set up payment schedule. Term 1 must be the highest, Term 3 the lowest.')
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
                                                'term_1' => 'Term 1 (highest)',
                                                'term_2' => 'Term 2',
                                                'term_3' => 'Term 3 (lowest)',
                                            ])
                                            ->required()
                                            ->distinct()
                                            ->disableOptionsWhenSelectedInSiblingRepeaterItems(),

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
                                            ->minValue(0)
                                            ->live(onBlur: true),
                                    ]),
                            ])
                            ->defaultItems(3)
                            ->maxItems(3)
                            ->minItems(3)
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->columnSpanFull()
                            ->helperText('Exactly 3 installments: Term 1 ≥ Term 2 ≥ Term 3, summing to the total fees.')
                            ->afterStateHydrated(function (Repeater $component, $state, Get $get) {
                                if (blank($state)) {
                                    $component->state(static::defaultSplit(
                                        static::sumFeeComponents($get),
                                        $get('academic_year_id'),
                                    ));
                                }
                            })
                            ->rules([
                                function (Get $get) {
                                    return function (string $attribute, $value, $fail) use ($get) {
                                        $plan = collect($value ?? []);

                                        if ($plan->count() !== 3) {
                                            $fail('Exactly 3 installments are required.');
                                            return;
                                        }

                                        $byTerm = $plan->keyBy('term');
                                        $t1 = (float) ($byTerm['term_1']['amount'] ?? 0);
                                        $t2 = (float) ($byTerm['term_2']['amount'] ?? 0);
                                        $t3 = (float) ($byTerm['term_3']['amount'] ?? 0);

                                        if ($t1 < $t2 || $t2 < $t3) {
                                            $fail('Term 1 must be ≥ Term 2 ≥ Term 3.');
                                        }

                                        $total = static::sumFeeComponents($get);
                                        $sum = $t1 + $t2 + $t3;

                                        if (abs($total - $sum) > 0.01) {
                                            $fail(
                                                'Installments (KES ' . number_format($sum, 2) .
                                                ') must equal the total fees (KES ' . number_format($total, 2) . ').'
                                            );
                                        }
                                    };
                                },
                            ]),

                        Placeholder::make('installments_total')
                            ->label('Installments Total')
                            ->content(function (Get $get) {
                                $plan = collect($get('payment_plan') ?? []);
                                $sum = $plan->sum(fn ($row) => (float) ($row['amount'] ?? 0));
                                $total = static::sumFeeComponents($get);
                                $diff = $total - $sum;

                                $formatted = 'KES ' . number_format($sum, 2);

                                if (abs($diff) < 0.01) {
                                    return new HtmlString(
                                        "<span style='color:#16a34a;font-weight:600'>{$formatted} ✓ matches total</span>"
                                    );
                                }

                                $sign = $diff > 0 ? 'short by' : 'over by';

                                return new HtmlString(
                                    "<span style='color:#dc2626;font-weight:600'>{$formatted} — {$sign} KES " .
                                    number_format(abs($diff), 2) . "</span>"
                                );
                            }),

                        Placeholder::make('past_due_warning')
                            ->hiddenLabel()
                            ->visible(function (Get $get) {
                                $plan = collect($get('payment_plan') ?? []);

                                return $plan->contains(fn ($row) =>
                                    ! empty($row['due_date']) &&
                                    \Carbon\Carbon::parse($row['due_date'])->isPast()
                                );
                            })
                            ->content(function (Get $get) {
                                $past = collect($get('payment_plan') ?? [])
                                    ->filter(fn ($row) =>
                                        ! empty($row['due_date']) &&
                                        \Carbon\Carbon::parse($row['due_date'])->isPast()
                                    )
                                    ->map(fn ($row) =>
                                        str_replace('_', ' ', ucfirst($row['term'] ?? '')) .
                                        ' (' . \Carbon\Carbon::parse($row['due_date'])->format('d/m/Y') . ')'
                                    )
                                    ->implode(', ');

                                return new HtmlString(
                                    "<span style='color:#d97706;font-weight:600'>⚠ Some terms are already past: {$past}. " .
                                    "This is expected for historical plans, but double-check if you're creating a new one.</span>"
                                );
                            }),
                    ]),
            ]);
    }

    /* -----------------------------------------------------------------
     |  Helpers
     | -----------------------------------------------------------------
     */

    /**
     * Sum every individual fee component (excludes total_fees itself).
     */
    protected static function sumFeeComponents(Get $get): float
    {
        $fields = [
            'tuition_fees',
            'activity_fees',
            'library_fees',
            'sports_fees',
            'medical_fees',
            'transport_fees',
            'boarding_fees',
            'uniform_fees',
            'other_fees',
        ];

        return collect($fields)->sum(fn ($f) => (float) ($get($f) ?? 0));
    }

    /**
     * Build a fresh payment plan for the given total and academic year.
     * Delegates to FeeStructure::buildDefaultPaymentPlan() so the form,
     * the seeder, and any Tinker script share one source of truth.
     */
    protected static function defaultSplit(float $total, ?int $academicYearId = null): array
    {
        $yearName = null;

        if ($academicYearId) {
            $yearName = AcademicYears::find($academicYearId)?->name;
        }

        $yearName ??= AcademicYears::where('is_current', true)->first()?->name;

        $year = FeeStructure::yearFromAcademicYearName($yearName);

        return FeeStructure::buildDefaultPaymentPlan($total, $year);
    }

    /**
     * Update total_fees. If the user hasn't entered any installment amounts yet,
     * seed the split so the numbers stay consistent.
     */
    protected static function calculateTotal(Set $set, Get $get): void
    {
        $total = static::sumFeeComponents($get);
        $set('total_fees', $total);

        $existing = collect($get('payment_plan') ?? [])
            ->pluck('amount')
            ->filter(fn ($v) => (float) $v > 0);

        if ($existing->isEmpty() && $total > 0) {
            $set('payment_plan', static::defaultSplit($total, $get('academic_year_id')));
        }
    }

    /**
     * Pick the default academic year for a new fee structure.
     *
     * - Before 1 September: current academic year.
     * - On or after 1 September: next academic year if one exists, else current.
     *
     * Only affects the Create form. Editing an existing record keeps its
     * stored academic_year_id because the form value is already populated.
     */
    protected static function defaultAcademicYearId(): ?int
    {
        $current = AcademicYears::where('is_current', true)->first();

        // Before September, just use the current year
        if (now()->month < 9) {
            return $current?->id;
        }

        // Past mid-year — prefer next year's plan if it exists
        $thisYear = (int) now()->year;
        $nextYear = $thisYear + 1;

        $next = AcademicYears::query()
            ->where(function ($q) use ($nextYear, $thisYear) {
                $q->where('name', 'like', "%{$nextYear}%")
                  ->orWhere('name', 'like', "{$thisYear}/{$nextYear}%")
                  ->orWhere('name', 'like', "{$thisYear}-{$nextYear}%");
            })
            ->orderBy('name')
            ->first();

        return $next?->id ?? $current?->id;
    }
}
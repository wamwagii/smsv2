<?php

namespace App\Filament\Resources\Invoices\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use App\Models\Student;
use App\Models\FeeStructure;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Invoice Information')
                    ->description('Create or edit student fee invoice')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('invoice_number')
                                ->label('Invoice Number')
                                ->maxLength(50)
                                ->disabled()
                                ->dehydrated(false)
                                ->placeholder('Auto-generated on save')
                                ->hiddenOn('create')
                                ->extraAttributes(['class' => 'bg-gray-100 font-mono'])
                                ->helperText('Auto-generated (format: INV/YYYY/XXXX)'),

                            Select::make('student_id')
                                ->label('Student')
                                ->relationship(
                                    'student',
                                    'admission_number',
                                    modifyQueryUsing: fn ($query) => $query->where('status', 'active')
                                )
                                ->getOptionLabelFromRecordUsing(fn (Student $record) =>
                                    "{$record->admission_number} - {$record->first_name} {$record->last_name}"
                                )
                                ->searchable(['admission_number', 'first_name', 'last_name'])
                                ->preload()
                                ->required()
                                ->live()
                                ->afterStateUpdated(fn ($set, $get) => static::loadStudentDetails($set, $get))
                                ->helperText('Select the student'),

                            Select::make('fee_structure_id')
                                ->label('Fee Structure')
                                ->relationship(
                                    'feeStructure',
                                    'id',
                                    modifyQueryUsing: fn ($query) => $query->where('is_active', true)->with(['class', 'academicYear'])
                                )
                                ->getOptionLabelFromRecordUsing(fn (FeeStructure $record) =>
                                    ($record->class?->class_code ?? 'N/A')
                                    . ' - ' . ($record->academicYear?->name ?? 'N/A')
                                    . ' (KES ' . number_format($record->total_fees ?? 0, 2) . ')'
                                )
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live()
                                ->afterStateUpdated(fn ($set, $get) => static::calculateInvoiceAmount($set, $get))
                                ->rules([
                                    fn ($get) => function ($attribute, $value, $fail) use ($get) {
                                        $studentId = $get('student_id');
                                        if (!$studentId || !$value) return;
                                        $student = Student::find($studentId);
                                        $fee = FeeStructure::find($value);
                                        if ($student && $fee && $student->class_id !== $fee->class_id) {
                                            $fail('Selected fee structure does not match the student\'s class.');
                                        }
                                    },
                                ])
                                ->helperText('Select the fee structure'),

                            Select::make('term')
                                ->label('Term')
                                ->options([
                                    'term_1' => 'Term 1 (January - March)',
                                    'term_2' => 'Term 2 (April - July)',
                                    'term_3' => 'Term 3 (August - November)',
                                ])
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($set, $get, $state) {
                                    static::calculateInvoiceAmount($set, $get);
                                    static::setDueDateForTerm($set, $state);
                                }),
                        ]),

                        Grid::make(3)->schema([
                            TextInput::make('amount')
                                ->label('Invoice Amount')
                                ->numeric()
                                ->prefix('KES')
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn ($set, $get) => static::syncStatus($set, $get))
                                ->helperText('Amount to be paid'),

                            TextInput::make('amount_paid')
                                ->label('Amount Paid')
                                ->numeric()
                                ->prefix('KES')
                                ->default(0)
                                ->disabled()
                                ->dehydrated(false)
                                ->helperText('Auto-updates from payments'),

                            TextEntry::make('balance_display')
                                ->label('Balance')
                                ->weight('bold')
                                ->color(function ($get) {
                                    $balance = (float) ($get('amount') ?? 0) - (float) ($get('amount_paid') ?? 0);
                                    return $balance > 0 ? 'danger' : 'success';
                                })
                                ->state(function ($get) {
                                    $amount = (float) ($get('amount') ?? 0);
                                    $paid = (float) ($get('amount_paid') ?? 0);
                                    return 'KES ' . number_format($amount - $paid, 2);
                                }),
                        ]),

                        Grid::make(2)->schema([
                            DatePicker::make('due_date')
                                ->label('Due Date')
                                ->required()
                                ->native(false)
                                ->displayFormat('d/m/Y')
                                ->default(now()->addDays(30))
                                ->helperText('Payment due date'),

                            Select::make('status')
                                ->label('Status')
                                ->options([
                                    'pending'        => 'Pending',
                                    'partially_paid' => 'Partially Paid',
                                    'paid'           => 'Paid',
                                    'overdue'        => 'Overdue',
                                    'waived'         => 'Waived',
                                ])
                                ->required()
                                ->default('pending')
                                ->native(false),
                        ]),

                        Textarea::make('notes')
                            ->label('Notes')
                            ->maxLength(65535)
                            ->rows(2)
                            ->columnSpanFull()
                            ->helperText('Additional notes or comments about this invoice'),
                    ]),

                Section::make('Student Information')
                    ->description('Student details for reference')
                    ->icon('heroicon-o-user')
                    ->collapsible()
                    ->collapsed()
                    ->visible(fn ($get) => !empty($get('student_id')))
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('student_name')
                                ->label('Student Name')
                                ->state(fn ($get) => Student::find($get('student_id'))?->full_name ?? '-'),

                            TextEntry::make('student_class')
                                ->label('Class')
                                ->badge()
                                ->color('success')
                                ->state(fn ($get) => Student::with('class')->find($get('student_id'))?->class?->class_code ?? '-'),

                            TextEntry::make('student_admission')
                                ->label('Admission No.')
                                ->state(fn ($get) => Student::find($get('student_id'))?->admission_number ?? '-'),
                        ]),
                    ]),
            ]);
    }

    protected static function loadStudentDetails($set, $get): void
    {
        $studentId = $get('student_id');
        if (!$studentId) return;

        $student = Student::with('class')->find($studentId);
        if (!$student || !$student->class) return;

        $feeStructure = FeeStructure::where('class_id', $student->class_id)
            ->where('academic_year_id', $student->academic_year_id)
            ->where('is_active', true)
            ->first();

        if ($feeStructure) {
            $set('fee_structure_id', $feeStructure->id);
            static::calculateInvoiceAmount($set, $get);
        }
    }

    protected static function calculateInvoiceAmount($set, $get): void
    {
        $feeStructureId = $get('fee_structure_id');
        $term = $get('term');

        if (!$feeStructureId || !$term) return;

        $feeStructure = FeeStructure::find($feeStructureId);
        if (!$feeStructure) return;

        if (is_array($feeStructure->payment_plan) && count($feeStructure->payment_plan) > 0) {
            foreach ($feeStructure->payment_plan as $plan) {
                if (($plan['term'] ?? null) === $term) {
                    $set('amount', $plan['amount'] ?? 0);
                    return;
                }
            }
        }

        $totalFees = (float) (
            ($feeStructure->tuition_fees   ?? 0) +
            ($feeStructure->activity_fees  ?? 0) +
            ($feeStructure->library_fees   ?? 0) +
            ($feeStructure->sports_fees    ?? 0) +
            ($feeStructure->medical_fees   ?? 0) +
            ($feeStructure->transport_fees ?? 0) +
            ($feeStructure->boarding_fees  ?? 0) +
            ($feeStructure->uniform_fees   ?? 0) +
            ($feeStructure->other_fees     ?? 0)
        );

        $set('amount', round($totalFees / 3, 2));
    }

    protected static function setDueDateForTerm($set, ?string $term): void
    {
        $year = now()->year;
        $dueDate = match ($term) {
            'term_1' => "{$year}-03-15",
            'term_2' => "{$year}-07-15",
            'term_3' => "{$year}-11-15",
            default  => null,
        };
        if ($dueDate) {
            $set('due_date', $dueDate);
        }
    }

    protected static function syncStatus($set, $get): void
    {
        $amount = (float) ($get('amount') ?? 0);
        $paid = (float) ($get('amount_paid') ?? 0);

        if ($paid >= $amount && $amount > 0) {
            $set('status', 'paid');
        } elseif ($paid > 0) {
            $set('status', 'partially_paid');
        } elseif ($amount > 0) {
            $set('status', 'pending');
        }
    }
}
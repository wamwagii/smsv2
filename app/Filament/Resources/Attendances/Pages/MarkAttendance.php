<?php

namespace App\Filament\Resources\Attendances\Pages;

use App\Filament\Resources\Attendances\AttendanceResource;
use App\Models\Attendance;
use App\Models\Classes;
use App\Models\Student;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class MarkAttendance extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static string $resource = AttendanceResource::class;

    protected string $view = 'filament.resources.attendances.pages.mark-attendance';

    public ?int $classId = null;

    public ?string $date = null;

    public function mount(): void
    {
        $this->date = now()->toDateString();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('classId')
                    ->label('Class')
                    ->options(fn () => Classes::query()
                        ->where('is_active', true)
                        ->orderBy('level')
                        ->get()
                        ->mapWithKeys(fn ($c) => [$c->id => $c->class_code]))
                    ->required()
                    ->searchable()
                    ->live(),

                DatePicker::make('date')
                    ->label('Date')
                    ->required()
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->maxDate(today())
                    ->default(today())
                    ->live(),
            ])
            ->statePath('')
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                if (!$this->classId || !$this->date) {
                    return Student::query()->whereRaw('1 = 0');
                }

                return Student::query()
                    ->where('class_id', $this->classId)
                    ->where('status', 'active')
                    ->orderBy('roll_number');
            })
            ->columns([
                TextColumn::make('roll_number')
                    ->label('Roll')
                    ->default('-'),

                TextColumn::make('admission_number')
                    ->label('Admission No.'),

                TextColumn::make('full_name')
                    ->label('Student'),

                SelectColumn::make('attendance_status')
                    ->label('Status')
                    ->options([
                        'present' => 'Present',
                        'absent'  => 'Absent',
                        'late'    => 'Late',
                        'excused' => 'Excused',
                        'holiday' => 'Holiday',
                    ])
                    ->state(fn (Student $record) =>
                        Attendance::where('student_id', $record->id)
                            ->whereDate('date', $this->date)
                            ->value('status') ?? 'present'
                    )
                    ->updateStateUsing(function (Student $record, $state) {
                        Attendance::updateOrCreate(
                            [
                                'student_id' => $record->id,
                                'date'       => $this->date,
                            ],
                            [
                                'class_id' => $this->classId,
                                'status'   => $state,
                            ]
                        );

                        Notification::make()
                            ->title("Marked {$record->full_name} as {$state}")
                            ->success()
                            ->duration(1500)
                            ->send();
                    })
                    ->rules(['required', 'in:present,absent,late,excused,holiday']),
            ])
            ->paginated(false)
            ->striped();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('mark_all_present')
                ->label('Mark All Present')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Mark all students present?')
                ->modalDescription('This will mark every active student in this class as "present".')
                ->action(function () {
                    if (!$this->classId || !$this->date) return;

                    $studentIds = Student::where('class_id', $this->classId)
                        ->where('status', 'active')
                        ->pluck('id');

                    foreach ($studentIds as $studentId) {
                        Attendance::updateOrCreate(
                            [
                                'student_id' => $studentId,
                                'date'       => $this->date,
                            ],
                            [
                                'class_id' => $this->classId,
                                'status'   => 'present',
                            ]
                        );
                    }

                    Notification::make()
                        ->title('All students marked present')
                        ->success()
                        ->send();
                })
                ->visible(fn () => (bool) $this->classId),

            Action::make('back_to_list')
                ->label('All Records')
                ->icon('heroicon-o-list-bullet')
                ->color('gray')
                ->url(fn () => AttendanceResource::getUrl('index')),
        ];
    }

    public function getTitle(): string
    {
        return 'Mark Attendance';
    }
}
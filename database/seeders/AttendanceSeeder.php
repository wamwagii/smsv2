<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Classes;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttendanceSeeder extends Seeder
{
    /**
     * Generate attendance for the last N school days.
     */
    private const SCHOOL_DAYS_BACK = 30;

    /**
     * Status distribution (weighted).
     * 85% present, 5% late, 5% absent, 5% excused.
     */
    private const STATUS_POOL = [
        'present', 'present', 'present', 'present', 'present',
        'present', 'present', 'present', 'present', 'present',
        'present', 'present', 'present', 'present', 'present',
        'present', 'present',
        'late',
        'absent',
        'excused',
    ];

    public function run(): void
    {
        $classes = Classes::with('students')->where('is_active', true)->get();

        if ($classes->isEmpty()) {
            $this->command->error('No active classes found. Run ClassSeeder first.');
            return;
        }

        // Clear existing attendance for idempotency
        Attendance::query()->delete();

        $created = 0;
        $skipped = 0;

        DB::transaction(function () use ($classes, &$created, &$skipped) {
            foreach ($classes as $class) {
                if ($class->students->isEmpty()) {
                    $skipped++;
                    continue;
                }

                // Iterate over the last N days
                for ($daysAgo = self::SCHOOL_DAYS_BACK; $daysAgo >= 0; $daysAgo--) {
                    $date = today()->subDays($daysAgo);

                    // Skip weekends (Saturday = 6, Sunday = 0 in Carbon)
                    if ($date->isWeekend()) {
                        continue;
                    }

                    foreach ($class->students as $student) {
                        $status = self::STATUS_POOL[array_rand(self::STATUS_POOL)];

                        $record = [
                            'student_id'     => $student->id,
                            'class_id'       => $class->id,
                            'date'           => $date->toDateString(),
                            'status'         => $status,
                            'arrival_time'   => null,
                            'departure_time' => null,
                            'reason'         => null,
                            'marked_by'      => null,
                            'created_at'     => now(),
                            'updated_at'     => now(),
                        ];

                        // Late arrivals have an arrival time after 8:00 AM
                        if ($status === 'late') {
                            $record['arrival_time'] = sprintf(
                                '%02d:%02d:00',
                                rand(8, 9),
                                rand(0, 59)
                            );
                        }

                        // Some present students have early departures
                        if ($status === 'present' && rand(1, 20) === 1) {
                            $record['departure_time'] = sprintf(
                                '%02d:%02d:00',
                                rand(12, 15),
                                rand(0, 59)
                            );
                        }

                        // Absent and excused have reasons
                        if ($status === 'absent') {
                            $record['reason'] = $this->randomAbsenceReason();
                        }

                        if ($status === 'excused') {
                            $record['reason'] = $this->randomExcuseReason();
                        }

                        Attendance::insert($record);
                        $created++;
                    }
                }
            }
        });

        $this->command->info("Attendance seeded: {$created} records created, {$skipped} classes skipped.");
    }

    private function randomAbsenceReason(): string
    {
        $reasons = [
            'Sick — reported by parent',
            'Family emergency',
            'Medical appointment',
            'Unexplained absence',
            'Reported by guardian',
        ];

        return $reasons[array_rand($reasons)];
    }

    private function randomExcuseReason(): string
    {
        $reasons = [
            'Approved school event',
            'Sports competition',
            'Medical leave (documented)',
            'Family bereavement',
            'Religious observance',
        ];

        return $reasons[array_rand($reasons)];
    }
}
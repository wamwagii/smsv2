<?php

namespace Database\Seeders;

use App\Models\Classes;
use App\Models\Subject;
use App\Models\Staff;
use App\Models\AcademicYears;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClassSubjectTeacherSeeder extends Seeder
{
    public function run(): void
    {
        $classes = Classes::all();
        $subjects = Subject::where('is_active', true)->get();
        $teachers = Staff::where('role', 'teacher')->get();

        if ($classes->isEmpty()) {
            $this->command->error('No classes found. Seed ClassSeeder first.');
            return;
        }

        if ($subjects->isEmpty()) {
            $this->command->error('No active subjects found. Seed SubjectSeeder first.');
            return;
        }

        // Pivot requires an academic year — use the current one
        $academicYear = AcademicYears::where('is_current', true)->first()
            ?? AcademicYears::first();

        if (!$academicYear) {
            $this->command->error('No academic year found. Seed AcademicYearSeeder first.');
            return;
        }

        $created = 0;
        $updated = 0;

        /* -------------------------------------------------
         |  Class ↔ Subject ↔ Staff assignments
         ------------------------------------------------- */
        foreach ($classes as $class) {
            // Assign 6–10 subjects per class
            $subjectCount = rand(6, min(10, $subjects->count()));
            $classSubjects = $subjects->random($subjectCount);

            foreach ($classSubjects as $subject) {
                $staffId = $teachers->isNotEmpty()
                    ? $teachers->random()->id
                    : null;

                if (!$staffId) {
                    // Can't seed a row without staff_id (it's NOT NULL)
                    continue;
                }

                $existing = DB::table('class_subject_teacher')
                    ->where('class_id', $class->id)
                    ->where('subject_id', $subject->id)
                    ->where('academic_year_id', $academicYear->id)
                    ->first();

                if ($existing) {
                    DB::table('class_subject_teacher')
                        ->where('id', $existing->id)
                        ->update([
                            'staff_id'         => $staffId,
                            'updated_at'       => now(),
                        ]);
                    $updated++;
                } else {
                    DB::table('class_subject_teacher')->insert([
                        'class_id'         => $class->id,
                        'subject_id'       => $subject->id,
                        'staff_id'         => $staffId,
                        'academic_year_id' => $academicYear->id,
                        'is_class_teacher' => false,
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ]);
                    $created++;
                }
            }
        }

        $this->command->info("Class-Subject-Teacher rows: {$created} created, {$updated} updated.");

        /* -------------------------------------------------
         |  Assign one class teacher per class
         ------------------------------------------------- */
        $assignedClassTeachers = 0;

        foreach ($classes as $class) {
            $pivotRow = DB::table('class_subject_teacher')
                ->where('class_id', $class->id)
                ->where('academic_year_id', $academicYear->id)
                ->inRandomOrder()
                ->first();

            if ($pivotRow) {
                DB::table('class_subject_teacher')
                    ->where('id', $pivotRow->id)
                    ->update(['is_class_teacher' => true]);
                $assignedClassTeachers++;
            }
        }

        $this->command->info("Class teachers assigned: {$assignedClassTeachers}");
    }
}
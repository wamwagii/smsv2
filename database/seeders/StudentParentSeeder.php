<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\Guardian;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StudentParentSeeder extends Seeder
{
    public function run(): void
    {
        $guardianCount = Guardian::count();

        if ($guardianCount === 0) {
            $this->command->error('No guardians found. Seed guardians first.');
            return;
        }

        $created = 0;

        DB::transaction(function () use ($guardianCount, &$created) {
            // Chunk students so we don't load the whole table into memory
            Student::query()->chunkById(500, function ($students) use ($guardianCount, &$created) {
                foreach ($students as $student) {
                    // Reset existing pivots for deterministic re-seeding
                    DB::table('student_parent')
                        ->where('student_id', $student->id)
                        ->delete();

                    $numParents = rand(1, 2);

                    // Fetch fresh random guardians per student — avoids loading all into memory
                    $assignedParents = Guardian::inRandomOrder()
                        ->limit($numParents)
                        ->get();

                    // Safety: ensure we don't try to assign more than exist
                    $assignedParents = $assignedParents->take(min($numParents, $guardianCount));

                    foreach ($assignedParents->values() as $parentIndex => $parent) {
                        DB::table('student_parent')->updateOrInsert(
                            [
                                'student_id' => $student->id,
                                'parent_id'  => $parent->id,
                            ],
                            [
                                'is_primary_contact'     => $parentIndex === 0,
                                'receives_notifications' => true,
                                'created_at'             => now(),
                                'updated_at'             => now(),
                            ]
                        );
                        $created++;
                    }
                }
            });
        });

        $this->command->info($created . ' student-parent relationships created/updated.');
    }
}
<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\Guardian;
use Illuminate\Database\Seeder;

class StudentParentSeeder extends Seeder
{
    public function run(): void
    {
        $students = Student::all();
        $parents = Guardian::all();
        
        $created = 0;
        
        foreach ($students as $index => $student) {
            // Assign 1-2 parents per student
            $numParents = rand(1, 2);
            $assignedParents = $parents->random(min($numParents, $parents->count()));
            
            foreach ($assignedParents as $parentIndex => $parent) {
                \DB::table('student_parent')->updateOrInsert(
                    [
                        'student_id' => $student->id,
                        'parent_id' => $parent->id,
                    ],
                    [
                        'is_primary_contact' => $parentIndex === 0,
                        'receives_notifications' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
                $created++;
            }
        }
        
        $this->command->info($created . ' student-parent relationships created/updated.');
    }
}
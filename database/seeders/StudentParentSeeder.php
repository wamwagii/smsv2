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
        
        foreach ($students as $index => $student) {
            // Assign 1-2 parents per student
            $numParents = rand(1, 2);
            $assignedParents = $parents->random($numParents);
            
            foreach ($assignedParents as $parentIndex => $parent) {
                \DB::table('student_parent')->insert([
                    'student_id' => $student->id,
                    'parent_id' => $parent->id,
                    'is_primary_contact' => $parentIndex === 0,
                    'receives_notifications' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
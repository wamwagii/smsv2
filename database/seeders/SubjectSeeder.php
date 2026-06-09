<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            // Mathematics Department
            ['name' => 'Mathematics', 'code' => 'MAT', 'department_id' => 1, 'category' => 'core', 'theory_hours_per_week' => 6, 'is_active' => true],
            ['name' => 'Additional Mathematics', 'code' => 'ADD-MAT', 'department_id' => 1, 'category' => 'elective', 'theory_hours_per_week' => 4, 'is_active' => true],
            
            // English Department
            ['name' => 'English', 'code' => 'ENG', 'department_id' => 2, 'category' => 'core', 'theory_hours_per_week' => 5, 'is_active' => true],
            ['name' => 'Literature', 'code' => 'LIT', 'department_id' => 2, 'category' => 'elective', 'theory_hours_per_week' => 4, 'is_active' => true],
            
            // Kiswahili Department
            ['name' => 'Kiswahili', 'code' => 'KIS', 'department_id' => 3, 'category' => 'core', 'theory_hours_per_week' => 5, 'is_active' => true],
            
            // Science Department
            ['name' => 'Biology', 'code' => 'BIO', 'department_id' => 4, 'category' => 'core', 'theory_hours_per_week' => 4, 'practical_hours_per_week' => 2, 'is_active' => true],
            ['name' => 'Chemistry', 'code' => 'CHE', 'department_id' => 4, 'category' => 'core', 'theory_hours_per_week' => 4, 'practical_hours_per_week' => 2, 'is_active' => true],
            ['name' => 'Physics', 'code' => 'PHY', 'department_id' => 4, 'category' => 'core', 'theory_hours_per_week' => 4, 'practical_hours_per_week' => 2, 'is_active' => true],
            
            // Humanities Department
            ['name' => 'History', 'code' => 'HIS', 'department_id' => 5, 'category' => 'core', 'theory_hours_per_week' => 4, 'is_active' => true],
            ['name' => 'Geography', 'code' => 'GEO', 'department_id' => 5, 'category' => 'core', 'theory_hours_per_week' => 4, 'is_active' => true],
            ['name' => 'Religious Education', 'code' => 'RE', 'department_id' => 5, 'category' => 'core', 'theory_hours_per_week' => 3, 'is_active' => true],
            
            // Business Department
            ['name' => 'Business Studies', 'code' => 'BUS', 'department_id' => 8, 'category' => 'elective', 'theory_hours_per_week' => 4, 'is_active' => true],
            
            // Technical Department
            ['name' => 'Computer Studies', 'code' => 'COM', 'department_id' => 6, 'category' => 'elective', 'theory_hours_per_week' => 2, 'practical_hours_per_week' => 2, 'is_active' => true],
        ];

        foreach ($subjects as $subject) {
            Subject::updateOrCreate(
                ['code' => $subject['code']],
                $subject
            );
        }
        
        $this->command->info('Subjects seeded successfully.');
    }
}
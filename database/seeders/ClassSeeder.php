<?php

namespace Database\Seeders;

use App\Models\Classes;
use Illuminate\Database\Seeder;

class ClassSeeder extends Seeder
{
    public function run(): void
    {
        // Create only grades 1-12, no streams
        for ($grade = 1; $grade <= 12; $grade++) {
            Classes::updateOrCreate(
                ['level' => $grade, 'stream' => null],
                [
                    'name' => "Grade {$grade}",
                    'level' => $grade,
                    'stream' => null,
                    'class_code' => "Grade-{$grade}",
                    'capacity' => 45,
                    'current_enrollment' => 0,
                    'description' => "Grade {$grade}",
                    'is_active' => true,
                ]
            );
        }
        
        $this->command->info('✓ 12 classes (Grades 1-12) created successfully.');
    }
}
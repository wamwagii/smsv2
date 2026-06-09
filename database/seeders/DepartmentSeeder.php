<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Mathematics Department', 'code' => 'MAT', 'is_active' => true],
            ['name' => 'English Department', 'code' => 'ENG', 'is_active' => true],
            ['name' => 'Kiswahili Department', 'code' => 'KIS', 'is_active' => true],
            ['name' => 'Science Department', 'code' => 'SCI', 'is_active' => true],
            ['name' => 'Humanities Department', 'code' => 'HUM', 'is_active' => true],
            ['name' => 'Technical Department', 'code' => 'TEC', 'is_active' => true],
            ['name' => 'Languages Department', 'code' => 'LAN', 'is_active' => true],
            ['name' => 'Business Department', 'code' => 'BUS', 'is_active' => true],
        ];

        foreach ($departments as $department) {
            Department::updateOrCreate(
                ['code' => $department['code']],
                $department
            );
        }
        
        $this->command->info('Departments seeded successfully.');
    }
}
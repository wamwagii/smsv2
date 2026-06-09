<?php

namespace Database\Seeders;

use App\Models\AcademicYears;
use Illuminate\Database\Seeder;

class AcademicYearSeeder extends Seeder
{
    public function run(): void
    {
        $years = [
            [
                'name' => '2024',
                'start_date' => '2024-01-15',
                'end_date' => '2024-12-20',
                'status' => 'archived',
                'is_current' => false,
            ],
            [
                'name' => '2025',
                'start_date' => '2025-01-15',
                'end_date' => '2025-12-20',
                'status' => 'archived',
                'is_current' => false,
            ],
            [
                'name' => '2026',
                'start_date' => '2026-01-15',
                'end_date' => '2026-12-20',
                'status' => 'active',
                'is_current' => true,
            ],
        ];

        foreach ($years as $year) {
            AcademicYears::create($year);
        }
    }
}
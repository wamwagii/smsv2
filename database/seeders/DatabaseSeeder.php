<?php

namespace Database\Seeders;

//use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    //use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            // Reference data
            AcademicYearSeeder::class,
            DepartmentSeeder::class,
            ClassSeeder::class,
            SubjectSeeder::class,

            // Staff (needed by class-subject pivot)
            StaffSeeder::class,

            // Class ↔ Subject ↔ Teacher pivot
            ClassSubjectTeacherSeeder::class,

            // People
            StudentSeeder::class,
            ParentSeeder::class,
            StudentParentSeeder::class,

            // Exams
            ExamSeeder::class,

            // Results (needs students + exams + subjects + pivot)
            ResultSeeder::class,

            // Attendance (needs students + classes)
            AttendanceSeeder::class,

            // Financial
            FeeStructuresSeeder::class,
            InvoiceSeeder::class,
            PaymentSeeder::class,
        ]);
    }
}
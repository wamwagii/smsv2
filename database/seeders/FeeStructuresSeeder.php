<?php

namespace Database\Seeders;

use App\Models\AcademicYears;
use App\Models\Classes;
use App\Models\FeeStructure;
use Illuminate\Database\Seeder;

class FeeStructuresSeeder extends Seeder
{
    public function run(): void
    {
        $classes = Classes::all();
        $academicYear = AcademicYears::where('is_current', true)->first()
            ?? AcademicYears::first();

        if (! $academicYear) {
            $this->command->error('No academic year found.');
            return;
        }

        $year = FeeStructure::yearFromAcademicYearName($academicYear->name);

        $created = 0;

        foreach ($classes as $class) {
            // Set fees based on grade level
            if ($class->level <= 3) {
                $tuition = 5000;
                $activity = 1000;
                $transport = 2000;
                $boarding = 0;
            } elseif ($class->level <= 6) {
                $tuition = 6000;
                $activity = 1500;
                $transport = 2500;
                $boarding = 0;
            } elseif ($class->level <= 8) {
                $tuition = 8000;
                $activity = 2000;
                $transport = 3000;
                $boarding = 15000;
            } else {
                $tuition = 10000;
                $activity = 2500;
                $transport = 3500;
                $boarding = 20000;
            }

            $library = 500;
            $sports = 500;
            $medical = 1000;
            $uniform = 3000;
            $other = 1000;

            $total = $tuition + $activity + $library + $sports + $medical
                   + $transport + $boarding + $uniform + $other;

            FeeStructure::updateOrCreate(
                [
                    'class_id'         => $class->id,
                    'academic_year_id' => $academicYear->id,
                ],
                [
                    'tuition_fees'   => $tuition,
                    'activity_fees'  => $activity,
                    'library_fees'   => $library,
                    'sports_fees'    => $sports,
                    'medical_fees'   => $medical,
                    'transport_fees' => $transport,
                    'boarding_fees'  => $boarding,
                    'uniform_fees'   => $uniform,
                    'other_fees'     => $other,
                    'is_active'      => true,
                    'payment_plan'   => FeeStructure::buildDefaultPaymentPlan($total, $year),
                ]
            );
            $created++;
        }

        $this->command->info("Fee structures seeded: {$created} updated/created.");
    }
}
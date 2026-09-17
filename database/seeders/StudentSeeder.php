<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\Guardian;
use App\Models\Classes;
use App\Models\AcademicYears;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $classes = Classes::all();
        $academicYear = AcademicYears::where('is_current', true)->first()
            ?? AcademicYears::first();

        if (!$academicYear) {
            $this->command->error('No academic year found. Seed academic years first.');
            return;
        }

        if ($classes->isEmpty()) {
            $this->command->error('No classes found. Seed classes first.');
            return;
        }

        $guardians = Guardian::all();

        if ($guardians->isEmpty()) {
            $this->command->warn('No guardians found — students will be seeded without parent links.');
        }

        $created = 0;
        $counter = 1;

        DB::transaction(function () use ($faker, $classes, $academicYear, $guardians, &$created, &$counter) {
            foreach ($classes as $class) {
                $numStudents = rand(10, 20);

                for ($i = 1; $i <= $numStudents; $i++) {
                    $gender = $faker->randomElement(['male', 'female']);
                    $firstName = $gender === 'male'
                        ? $faker->firstNameMale
                        : $faker->firstNameFemale;

                    $admissionNumber = 'ADM/' . date('Y') . '/' . str_pad($counter, 4, '0', STR_PAD_LEFT);

                    $student = Student::updateOrCreate(
                        ['admission_number' => $admissionNumber],
                        [
                            'first_name'       => $firstName,
                            'middle_name'      => $faker->optional(0.7)->firstName,
                            'last_name'        => $faker->lastName,
                            'date_of_birth'    => $faker->dateTimeBetween('-18 years', '-5 years'),
                            'gender'           => $gender,
                            'phone_number'     => $faker->optional(0.8)->phoneNumber,
                            'email'            => $faker->optional(0.7)->safeEmail,
                            'class_id'         => $class->id,
                            'academic_year_id' => $academicYear->id,
                            'roll_number'      => str_pad($i, 2, '0', STR_PAD_LEFT),
                            'status'           => 'active',
                            'enrollment_date'  => $faker->dateTimeBetween('-2 years', 'now'),
                        ]
                    );

                    // Link 1–3 guardians to this student via the pivot
                    if ($guardians->isNotEmpty()) {
                        $assigned = $guardians->random(min(rand(1, 3), $guardians->count()));

                        $attach = [];
                        foreach ($assigned as $index => $guardian) {
                            $attach[$guardian->id] = [
                                'is_primary_contact'     => $index === 0,
                                'receives_notifications' => $index === 0,
                            ];
                        }

                        $student->parents()->syncWithoutDetaching($attach);
                    }

                    $created++;
                    $counter++;
                }
            }
        });

        $this->command->info($created . ' students created/updated successfully.');
    }
}
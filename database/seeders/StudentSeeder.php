<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\Classes;
use App\Models\AcademicYears;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $classes = Classes::all();
        $academicYear = AcademicYears::where('is_current', true)->first();
        
        $students = [];
        $counter = 1;
        
        foreach ($classes as $class) {
            // Generate 10-20 students per class
            $numStudents = rand(10, 20);
            
            for ($i = 1; $i <= $numStudents; $i++) {
                $gender = $faker->randomElement(['male', 'female']);
                $firstName = $gender === 'male' ? $faker->firstNameMale : $faker->firstNameFemale;
                
                $students[] = [
                    'admission_number' => 'ADM/' . date('Y') . '/' . str_pad($counter, 4, '0', STR_PAD_LEFT),
                    'first_name' => $firstName,
                    'middle_name' => rand(0, 1) ? $faker->optional()->firstName : null,
                    'last_name' => $faker->lastName,
                    'date_of_birth' => $faker->dateTimeBetween('-18 years', '-12 years'),
                    'gender' => $gender,
                    'phone_number' => $faker->optional()->phoneNumber,
                    'email' => $faker->optional()->email,
                    'class_id' => $class->id,
                    'academic_year_id' => $academicYear->id,
                    'roll_number' => str_pad($i, 2, '0', STR_PAD_LEFT),
                    'father_name' => $faker->optional()->name('male'),
                    'father_phone' => $faker->optional()->phoneNumber,
                    'mother_name' => $faker->optional()->name('female'),
                    'mother_phone' => $faker->optional()->phoneNumber,
                    'guardian_name' => $faker->optional()->name,
                    'guardian_phone' => $faker->optional()->phoneNumber,
                    'status' => 'active',
                    'enrollment_date' => $faker->dateTimeBetween('-2 years', 'now'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                
                $counter++;
            }
        }
        
        // Insert in chunks to avoid memory issues
        foreach (array_chunk($students, 50) as $chunk) {
            Student::insert($chunk);
        }
    }
}
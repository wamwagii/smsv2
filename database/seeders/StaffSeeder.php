<?php

namespace Database\Seeders;

use App\Models\Staff;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StaffSeeder extends Seeder
{
    public function run(): void
    {
        $staff = [
            // Admin
            [
                'staff_number' => 'ADM/2024/001',
                'first_name' => 'John',
                'last_name' => 'Maina',
                'date_of_birth' => '1975-05-15',
                'gender' => 'male',
                'phone_number' => '0712345678',
                'email' => 'principal@school.com',
                'employment_type' => 'full_time',
                'hire_date' => '2010-01-10',
                'national_id' => '12345678',
                'kra_pin' => 'A123456789',
                'role' => 'management',
                'position' => 'Principal',
                'status' => 'active',
                'qualification' => 'PhD in Education Management',
            ],
            [
                'staff_number' => 'ADM/2024/002',
                'first_name' => 'Jane',
                'last_name' => 'Wanjiku',
                'date_of_birth' => '1980-08-20',
                'gender' => 'female',
                'phone_number' => '0723456789',
                'email' => 'deputy@school.com',
                'employment_type' => 'full_time',
                'hire_date' => '2012-03-15',
                'national_id' => '23456789',
                'kra_pin' => 'B234567890',
                'role' => 'management',
                'position' => 'Deputy Principal',
                'status' => 'active',
                'qualification' => 'Master\'s in Education',
            ],
            
            // Mathematics Department
            [
                'staff_number' => 'TCH/2024/001',
                'first_name' => 'Peter',
                'last_name' => 'Odhiambo',
                'date_of_birth' => '1985-03-10',
                'gender' => 'male',
                'phone_number' => '0734567890',
                'email' => 'peter.odhiambo@school.com',
                'employment_type' => 'full_time',
                'hire_date' => '2015-01-15',
                'national_id' => '34567890',
                'kra_pin' => 'C345678901',
                'tsc_number' => 'TSC123456',
                'role' => 'teacher',
                'department_id' => 1,
                'position' => 'Head of Mathematics',
                'status' => 'active',
                'qualification' => 'B.Ed Mathematics',
                'subjects_taught' => json_encode(['Mathematics', 'Additional Mathematics']),
            ],
            [
                'staff_number' => 'TCH/2024/002',
                'first_name' => 'Mary',
                'last_name' => 'Mutua',
                'date_of_birth' => '1988-07-22',
                'gender' => 'female',
                'phone_number' => '0745678901',
                'email' => 'mary.mutua@school.com',
                'employment_type' => 'full_time',
                'hire_date' => '2016-06-01',
                'national_id' => '45678901',
                'kra_pin' => 'D456789012',
                'tsc_number' => 'TSC234567',
                'role' => 'teacher',
                'department_id' => 1,
                'position' => 'Mathematics Teacher',
                'status' => 'active',
                'qualification' => 'B.Sc Mathematics',
                'subjects_taught' => json_encode(['Mathematics']),
            ],
            
            // English Department
            [
                'staff_number' => 'TCH/2024/003',
                'first_name' => 'James',
                'last_name' => 'Kariuki',
                'date_of_birth' => '1982-11-05',
                'gender' => 'male',
                'phone_number' => '0756789012',
                'email' => 'james.kariuki@school.com',
                'employment_type' => 'full_time',
                'hire_date' => '2014-08-20',
                'national_id' => '56789012',
                'kra_pin' => 'E567890123',
                'tsc_number' => 'TSC345678',
                'role' => 'teacher',
                'department_id' => 2,
                'position' => 'Head of English',
                'status' => 'active',
                'qualification' => 'B.Ed English',
                'subjects_taught' => json_encode(['English', 'Literature']),
            ],
            [
                'staff_number' => 'TCH/2024/004',
                'first_name' => 'Grace',
                'last_name' => 'Atieno',
                'date_of_birth' => '1990-04-18',
                'gender' => 'female',
                'phone_number' => '0767890123',
                'email' => 'grace.atieno@school.com',
                'employment_type' => 'full_time',
                'hire_date' => '2018-01-10',
                'national_id' => '67890123',
                'kra_pin' => 'F678901234',
                'tsc_number' => 'TSC456789',
                'role' => 'teacher',
                'department_id' => 2,
                'position' => 'English Teacher',
                'status' => 'active',
                'qualification' => 'B.Ed English',
                'subjects_taught' => json_encode(['English']),
            ],
            
            // Science Department
            [
                'staff_number' => 'TCH/2024/005',
                'first_name' => 'David',
                'last_name' => 'Mwangi',
                'date_of_birth' => '1986-09-12',
                'gender' => 'male',
                'phone_number' => '0778901234',
                'email' => 'david.mwangi@school.com',
                'employment_type' => 'full_time',
                'hire_date' => '2015-05-15',
                'national_id' => '78901234',
                'kra_pin' => 'G789012345',
                'tsc_number' => 'TSC567890',
                'role' => 'teacher',
                'department_id' => 4,
                'position' => 'Head of Science',
                'status' => 'active',
                'qualification' => 'B.Ed Science',
                'subjects_taught' => json_encode(['Biology', 'Chemistry']),
            ],
            [
                'staff_number' => 'TCH/2024/006',
                'first_name' => 'Susan',
                'last_name' => 'Njeri',
                'date_of_birth' => '1992-01-25',
                'gender' => 'female',
                'phone_number' => '0789012345',
                'email' => 'susan.njeri@school.com',
                'employment_type' => 'full_time',
                'hire_date' => '2019-06-01',
                'national_id' => '89012345',
                'kra_pin' => 'H890123456',
                'tsc_number' => 'TSC678901',
                'role' => 'teacher',
                'department_id' => 4,
                'position' => 'Physics Teacher',
                'status' => 'active',
                'qualification' => 'B.Ed Physics',
                'subjects_taught' => json_encode(['Physics']),
            ],
        ];

        foreach ($staff as $member) {
            Staff::updateOrCreate(
                ['staff_number' => $member['staff_number']],
                $member
            );
        }
        
        $this->command->info('Staff seeded successfully.');
    }
}
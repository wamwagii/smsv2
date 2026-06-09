<?php

namespace Database\Seeders;

use App\Models\Guardian;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ParentSeeder extends Seeder
{
    public function run(): void
    {
        $parents = [
            [
                'first_name' => 'Michael',
                'last_name' => 'Omondi',
                'phone_number' => '0712345601',
                'email' => 'michael.omondi@example.com',
                'password' => Hash::make('password123'),
                'national_id' => '12345601',
                'relationship' => 'father',
                'occupation' => 'Engineer',
                'status' => 'active',
            ],
            [
                'first_name' => 'Esther',
                'last_name' => 'Wanjiru',
                'phone_number' => '0712345602',
                'email' => 'esther.wanjiru@example.com',
                'password' => Hash::make('password123'),
                'national_id' => '12345602',
                'relationship' => 'mother',
                'occupation' => 'Teacher',
                'status' => 'active',
            ],
            [
                'first_name' => 'Joseph',
                'last_name' => 'Ndirangu',
                'phone_number' => '0712345603',
                'email' => 'joseph.ndirangu@example.com',
                'password' => Hash::make('password123'),
                'national_id' => '12345603',
                'relationship' => 'father',
                'occupation' => 'Doctor',
                'status' => 'active',
            ],
            [
                'first_name' => 'Mary',
                'last_name' => 'Akinyi',
                'phone_number' => '0712345604',
                'email' => 'mary.akinyi@example.com',
                'password' => Hash::make('password123'),
                'national_id' => '12345604',
                'relationship' => 'mother',
                'occupation' => 'Accountant',
                'status' => 'active',
            ],
            [
                'first_name' => 'Peter',
                'last_name' => 'Kamau',
                'phone_number' => '0712345605',
                'email' => 'peter.kamau@example.com',
                'password' => Hash::make('password123'),
                'national_id' => '12345605',
                'relationship' => 'father',
                'occupation' => 'Businessman',
                'status' => 'active',
            ],
            [
                'first_name' => 'Jane',
                'last_name' => 'Muthoni',
                'phone_number' => '0712345606',
                'email' => 'jane.muthoni@example.com',
                'password' => Hash::make('password123'),
                'national_id' => '12345606',
                'relationship' => 'mother',
                'occupation' => 'Lawyer',
                'status' => 'active',
            ],
            [
                'first_name' => 'John',
                'last_name' => 'Otieno',
                'phone_number' => '0712345607',
                'email' => 'john.otieno@example.com',
                'password' => Hash::make('password123'),
                'national_id' => '12345607',
                'relationship' => 'father',
                'occupation' => 'Farmer',
                'status' => 'active',
            ],
            [
                'first_name' => 'Lucy',
                'last_name' => 'Chebet',
                'phone_number' => '0712345608',
                'email' => 'lucy.chebet@example.com',
                'password' => Hash::make('password123'),
                'national_id' => '12345608',
                'relationship' => 'mother',
                'occupation' => 'Nurse',
                'status' => 'active',
            ],
        ];

        foreach ($parents as $parent) {
            Guardian::create($parent);
        }
    }
}
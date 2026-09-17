<?php

namespace Database\Seeders;

use App\Models\SmsTemplate;
use Illuminate\Database\Seeder;

class SmsTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Fee Reminder',
                'body' => 'Dear {guardian_name}, this is a reminder that fees for {student_name} are due. Kindly clear the balance to avoid interruption. - {school_name}',
            ],
            [
                'name' => 'Fee Reminder with Balance',
                'body' => 'Dear {guardian_name}, our records show {student_name} has an outstanding balance of {balance_formatted}. Kindly clear this by the end of the week to avoid interruption. - {school_name}',
            ],
            [
                'name' => 'Exam Schedule',
                'body' => 'Dear {guardian_name}, the end-of-term exams for {student_name} begin on Monday. Ensure they attend all papers. - {school_name}',
            ],
            [
                'name' => 'Holiday Notice',
                'body' => 'Dear {guardian_name}, {student_name} will close for the holidays on Friday. Please arrange for pickup. - {school_name}',
            ],
            [
                'name' => 'General Announcement',
                'body' => 'Dear {guardian_name}, this is a notice from {school_name} regarding {student_name}.',
            ],
        ];

        foreach ($templates as $t) {
            SmsTemplate::updateOrCreate(['name' => $t['name']], $t);
        }

        $this->command->info(count($templates) . ' SMS templates seeded.');
    }
}
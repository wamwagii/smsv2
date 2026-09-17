<?php

namespace Database\Seeders;

use App\Models\AcademicYears;
use App\Models\Exam;
use Illuminate\Database\Seeder;

class ExamSeeder extends Seeder
{
    public function run(): void
    {
        $academicYear = AcademicYears::where('is_current', true)->first()
            ?? AcademicYears::first();

        if (!$academicYear) {
            $this->command->error('No academic year found. Seed AcademicYearSeeder first.');
            return;
        }

        $year = (int) date('Y', strtotime($academicYear->start_date ?? now()));

        $exams = [
            [
                'name'          => 'Opener Exam Term 1',
                'term'          => 'term_1',
                'start_date'    => "{$year}-01-15",
                'end_date'      => "{$year}-01-25",
                'total_marks'   => 100,
                'passing_marks' => 50,
                'status'        => 'completed',
                'description'   => 'Opening assessment for Term 1.',
            ],
            [
                'name'          => 'Mid Term Exam Term 1',
                'term'          => 'term_1',
                'start_date'    => "{$year}-02-20",
                'end_date'      => "{$year}-03-01",
                'total_marks'   => 100,
                'passing_marks' => 50,
                'status'        => 'completed',
                'description'   => 'Mid-term assessment for Term 1.',
            ],
            [
                'name'          => 'End of Term Exam Term 1',
                'term'          => 'term_1',
                'start_date'    => "{$year}-03-25",
                'end_date'      => "{$year}-04-05",
                'total_marks'   => 100,
                'passing_marks' => 50,
                'status'        => 'completed',
                'description'   => 'End-of-term assessment for Term 1.',
            ],
            [
                'name'          => 'Opener Exam Term 2',
                'term'          => 'term_2',
                'start_date'    => "{$year}-05-05",
                'end_date'      => "{$year}-05-15",
                'total_marks'   => 100,
                'passing_marks' => 50,
                'status'        => 'completed',
                'description'   => 'Opening assessment for Term 2.',
            ],
            [
                'name'          => 'Mid Term Exam Term 2',
                'term'          => 'term_2',
                'start_date'    => "{$year}-06-15",
                'end_date'      => "{$year}-06-25",
                'total_marks'   => 100,
                'passing_marks' => 50,
                'status'        => 'completed',
                'description'   => 'Mid-term assessment for Term 2.',
            ],
            [
                'name'          => 'End of Term Exam Term 2',
                'term'          => 'term_2',
                'start_date'    => "{$year}-07-20",
                'end_date'      => "{$year}-07-30",
                'total_marks'   => 100,
                'passing_marks' => 50,
                'status'        => 'published',
                'description'   => 'End-of-term assessment for Term 2.',
            ],
        ];

        $created = 0;
        $updated = 0;

        foreach ($exams as $data) {
            $existing = Exam::where('name', $data['name'])
                ->where('academic_year_id', $academicYear->id)
                ->first();

            if ($existing) {
                $existing->update($data);
                $updated++;
            } else {
                Exam::create(array_merge($data, [
                    'academic_year_id' => $academicYear->id,
                ]));
                $created++;
            }
        }

        $this->command->info("Exams seeded: {$created} created, {$updated} updated.");
    }
}
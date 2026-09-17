<?php

namespace Database\Seeders;

use App\Models\Result;
use App\Models\Student;
use App\Models\Exam;
use App\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ResultSeeder extends Seeder
{
    /**
     * Score bands: [min %, max %, weight]
     * Higher weight → more results fall in that band (bell-curve-ish).
     */
    private const SCORE_BANDS = [
        [85, 100, 5],
        [75, 84,  10],
        [65, 74,  20],
        [50, 64,  25],
        [30, 49,  15],
        [0,  29,  5],
    ];

    public function run(): void
    {
        $students = Student::with('class')->get();

        if ($students->isEmpty()) {
            $this->command->error('No students found. Run StudentSeeder first.');
            return;
        }

        // Seed results only for exams that have been completed or published
        $exams = Exam::whereIn('status', ['completed', 'published'])->get();

        if ($exams->isEmpty()) {
            $this->command->error('No completed/published exams found. Run ExamSeeder first.');
            return;
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;

        DB::transaction(function () use ($exams, $students, &$created, &$updated, &$skipped) {
            foreach ($exams as $exam) {
                foreach ($students as $student) {
                    if (!$student->class_id) {
                        $skipped++;
                        continue;
                    }

                    $subjects = $this->subjectsForClass($student->class_id);

                    if ($subjects->isEmpty()) {
                        $skipped++;
                        continue;
                    }

                    foreach ($subjects as $subject) {
                        $totalMarks = $exam->total_marks ?? 100;
                        $marksObtained = $this->randomMarks($totalMarks);

                        $existing = Result::where('student_id', $student->id)
                            ->where('exam_id', $exam->id)
                            ->where('subject_id', $subject->id)
                            ->first();

                        $payload = [
                            'class_id'             => $student->class_id,
                            'marks_obtained'       => $marksObtained,
                            'total_marks'          => $totalMarks,
                            'teacher_comments'     => $this->randomComment($marksObtained, $totalMarks),
                            'assessment_breakdown' => $this->randomBreakdown($marksObtained),
                        ];

                        if ($existing) {
                            $existing->update($payload);
                            $updated++;
                        } else {
                            Result::create(array_merge($payload, [
                                'student_id' => $student->id,
                                'exam_id'    => $exam->id,
                                'subject_id' => $subject->id,
                            ]));
                            $created++;
                        }
                    }
                }
            }
        });

        $this->command->info("Results seeded: {$created} created, {$updated} updated, {$skipped} skipped.");
    }

    private function subjectsForClass(?int $classId)
    {
        if (!$classId) {
            return collect();
        }

        return Subject::whereHas('classes', function ($query) use ($classId) {
                $query->where('class_id', $classId);
            })
            ->where('is_active', true)
            ->get();
    }

    private function randomMarks(int $totalMarks): float
    {
        $pool = [];
        foreach (self::SCORE_BANDS as [$min, $max, $weight]) {
            for ($i = 0; $i < $weight; $i++) {
                $pool[] = [$min, $max];
            }
        }

        [$minPercent, $maxPercent] = $pool[array_rand($pool)];
        $percent = rand($minPercent * 10, $maxPercent * 10) / 10;

        return round(($percent / 100) * $totalMarks, 2);
    }

    private function randomComment(float $marks, int $totalMarks): string
    {
        $percent = $totalMarks > 0 ? ($marks / $totalMarks) * 100 : 0;

        return match (true) {
            $percent >= 85 => $this->pick([
                'Excellent performance. Keep it up!',
                'Outstanding work — well done.',
                'Top of the class material.',
            ]),
            $percent >= 75 => $this->pick([
                'Very good performance.',
                'Strong result — keep pushing.',
                'Consistent effort, well done.',
            ]),
            $percent >= 65 => $this->pick([
                'Good work, but there is room to improve.',
                'Satisfactory performance.',
                'Solid effort — aim higher next time.',
            ]),
            $percent >= 50 => $this->pick([
                'Average performance. More effort needed.',
                'Fair result — focus on weak areas.',
                'Can do much better with more practice.',
            ]),
            default => $this->pick([
                'Needs significant improvement.',
                'Below expectations — requires extra support.',
                'Must put in more effort.',
            ]),
        };
    }

    private function randomBreakdown(float $marksObtained): ?array
    {
        if (rand(1, 10) > 7) {
            return null; // 30% chance no breakdown
        }

        $cat1 = round($marksObtained * (rand(20, 30) / 100), 2);
        $cat2 = round($marksObtained * (rand(20, 30) / 100), 2);
        $final = round($marksObtained - $cat1 - $cat2, 2);

        return [
            ['assessment_type' => 'CAT 1',      'marks' => $cat1,  'weight' => 20],
            ['assessment_type' => 'CAT 2',      'marks' => $cat2,  'weight' => 20],
            ['assessment_type' => 'Final Exam', 'marks' => $final, 'weight' => 60],
        ];
    }

    private function pick(array $options): string
    {
        return $options[array_rand($options)];
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Result extends Model
{
    protected $table = 'results';

    protected $fillable = [
        'student_id',
        'exam_id',
        'subject_id',
        'class_id',
        'marks_obtained',
        'total_marks',
        'percentage',
        'grade',
        'teacher_comments',
        'assessment_breakdown',
    ];

    protected $casts = [
        'marks_obtained'       => 'decimal:2',
        'total_marks'          => 'integer',
        'percentage'           => 'decimal:2',
        'assessment_breakdown' => 'array',
    ];

    protected static function booted(): void
    {
        static::saving(function (Result $result) {
            if ($result->total_marks > 0) {
                $result->percentage = round(
                    ($result->marks_obtained / $result->total_marks) * 100,
                    2
                );
                $result->grade = self::calculateGrade((float) $result->percentage);
            }
        });
    }

    public static function calculateGrade(float $percentage): string
    {
        return match (true) {
            $percentage >= 80 => 'A',
            $percentage >= 75 => 'A-',
            $percentage >= 70 => 'B+',
            $percentage >= 65 => 'B',
            $percentage >= 60 => 'B-',
            $percentage >= 55 => 'C+',
            $percentage >= 50 => 'C',
            $percentage >= 45 => 'C-',
            $percentage >= 40 => 'D+',
            $percentage >= 35 => 'D',
            $percentage >= 30 => 'D-',
            default           => 'E',
        };
    }

    public static function getGradePoint(string $grade): int
    {
        return match ($grade) {
            'A'  => 12,
            'A-' => 11,
            'B+' => 10,
            'B'  => 9,
            'B-' => 8,
            'C+' => 7,
            'C'  => 6,
            'C-' => 5,
            'D+' => 4,
            'D'  => 3,
            'D-' => 2,
            'E'  => 1,
            default => 0,
        };
    }

    /* -------- Relationships -------- */

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    /* -------- Scopes -------- */

    public function scopeForExam($query, $examId)
    {
        return $query->where('exam_id', $examId);
    }

    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeForClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'marks_obtained' => 'decimal:2',
        'total_marks' => 'integer',
        'percentage' => 'decimal:2',
        'assessment_breakdown' => 'array',
    ];
    
    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($result) {
            // Auto-calculate percentage
            if ($result->marks_obtained && $result->total_marks) {
                $result->percentage = ($result->marks_obtained / $result->total_marks) * 100;
                $result->grade = self::calculateGrade($result->percentage);
            }
        });
    }
    
    /**
     * Calculate grade based on percentage
     */
    public static function calculateGrade($percentage)
    {
        return match(true) {
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
            default => 'E',
        };
    }
    
    /**
     * Get the grade point
     */
    public static function getGradePoint($grade)
    {
        return match($grade) {
            'A' => 12,
            'A-' => 11,
            'B+' => 10,
            'B' => 9,
            'B-' => 8,
            'C+' => 7,
            'C' => 6,
            'C-' => 5,
            'D+' => 4,
            'D' => 3,
            'D-' => 2,
            'E' => 1,
            default => 0,
        };
    }
    
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
    
    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }
    
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
    
    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }
    
    // Scope for a specific exam
    public function scopeForExam($query, $examId)
    {
        return $query->where('exam_id', $examId);
    }
    
    // Scope for a specific student
    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }
    
    // Scope for a specific class
    public function scopeForClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    protected $table = 'exams';
    
    protected $fillable = [
        'name',
        'term',
        'academic_year_id',
        'start_date',
        'end_date',
        'total_marks',
        'passing_marks',
        'description',
        'status',
    ];
    
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];
    
    public function academicYear()
    {
        return $this->belongsTo(AcademicYears::class);
    }
    
    public function results()
    {
        return $this->hasMany(Result::class);
    }
    
    // Scope for active exams
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
    
    // Scope for upcoming exams
    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', now())->where('status', 'upcoming');
    }
}
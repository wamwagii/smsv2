<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $table = 'subjects';
    
    protected $fillable = [
        'name',
        'code',
        'department_id',
        'category',
        'theory_hours_per_week',
        'practical_hours_per_week',
        'description',
        'is_active',
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
        'theory_hours_per_week' => 'integer',
        'practical_hours_per_week' => 'integer',
    ];
    
    /**
     * Get the department that the subject belongs to
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    
    /**
     * Get the teachers assigned to this subject
     */
    public function teachers()
    {
        return $this->belongsToMany(Staff::class, 'class_subject_teacher', 'subject_id', 'staff_id')
                    ->withPivot('class_id', 'academic_year_id')
                    ->withTimestamps();
    }
    
    /**
     * Get the classes that take this subject
     */
    public function classes()
    {
        return $this->belongsToMany(Classes::class, 'class_subject_teacher', 'subject_id', 'class_id')
                    ->withPivot('staff_id', 'academic_year_id')
                    ->withTimestamps();
    }
    
    /**
     * Get the results for this subject
     */
    public function results()
    {
        return $this->hasMany(Result::class);
    }
    
    /**
     * Scope a query to only include active subjects
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    /**
     * Scope a query to only include core subjects
     */
    public function scopeCore($query)
    {
        return $query->where('category', 'core');
    }
    
    /**
     * Scope a query to only include electives
     */
    public function scopeElective($query)
    {
        return $query->where('category', 'elective');
    }
    
    /**
     * Get total hours per week
     */
    public function getTotalHoursAttribute()
    {
        return ($this->theory_hours_per_week ?? 0) + ($this->practical_hours_per_week ?? 0);
    }
}
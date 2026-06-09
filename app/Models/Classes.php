<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classes extends Model
{
    protected $table = 'classes';
    
    protected $fillable = [
        'name',
        'level',
        'stream',
        'class_code',
        'capacity',
        'current_enrollment',
        'class_teacher_id',
        'description',
        'is_active',
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
    ];
    
    public function students()
    {
        return $this->hasMany(Student::class);
    }
    
    public function feeStructure()
    {
        return $this->hasOne(FeeStructure::class);
    }
    
    // Helper method to get grade level
    public function getGradeLevelAttribute()
    {
        return $this->level;
    }
    
    // Scope for active classes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    // Scope for a specific grade
    public function scopeGrade($query, $level)
    {
        return $query->where('level', $level);
    }
}
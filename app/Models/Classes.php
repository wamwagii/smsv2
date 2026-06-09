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
    
    public function classTeacher()
    {
        return $this->belongsTo(Staff::class, 'class_teacher_id');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $table = 'departments';
    
    protected $fillable = [
        'name',
        'code',
        'head_of_department_id',
        'description',
        'is_active',
    ];
    
    public function staff()
    {
        return $this->hasMany(Staff::class);
    }
    
    public function headOfDepartment()
    {
        return $this->belongsTo(Staff::class, 'head_of_department_id');
    }
}
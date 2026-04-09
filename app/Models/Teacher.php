<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    protected $table = 'teacher';
    
    protected $fillable = ['user_id', 'employee_no', 'phone', 'name', 'created_at', 'updated_at', 'created_user_id', 'updated_user_id'];

    public function courses()
    {
        return $this->hasMany(Course::class, 'teacher_id');
    }

    public function bills()
    {
        return $this->hasMany(CourseBill::class, 'teacher_id');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $table = 'students';
    
    protected $fillable = ['user_id', 'teacher_id', 'student_no', 'grade', 'name'];
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }
    
    public function bills()
    {
        return $this->hasMany(CourseBill::class, 'student_id');
    }

    public function courses()
    {
        return $this->hasMany(Course::class, 'student_id');
    }
}
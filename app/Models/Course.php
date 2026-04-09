<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\CourseBill;

class Course extends Model
{
    protected $table = 'courses';

    protected $fillable = [
        'course_name',
        'course_month',
        'fee',
        'teacher_id',
        'student_id',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function bills()
    {
        return $this->hasMany(CourseBill::class, 'course_id');
    }
}

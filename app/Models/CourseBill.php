<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseBill extends Model
{
    public const STATUS_PENDING = 1;
    public const STATUS_PAID = 2;
    public const STATUS_FAILED = 3;

    protected $table = 'course_bills';

    protected $fillable = [
        'bill_no',
        'course_id',
        'teacher_id',
        'student_id',
        'amount',
        'status',
        'payment_provider',
        'payment_reference',
        'paid_at',
        'payment_meta',
    ];

    protected $casts = [
        'status' => 'integer',
        'paid_at' => 'datetime',
        'payment_meta' => 'array',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}

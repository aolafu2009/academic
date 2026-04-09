<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    // 指定表名
    protected $table = 'admin_users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type',  // 加上 user_type 字段
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // 用户类型常量
    const TYPE_ADMIN = 1;
    const TYPE_TEACHER = 2;
    const TYPE_STUDENT = 3;
    // 获取用户类型文本
    public function getUserTypeTextAttribute()
    {
        $map = [
            self::TYPE_ADMIN => '管理员',
            self::TYPE_TEACHER => '教师',
            self::TYPE_STUDENT => '学生',
        ];
        return $map[$this->user_type] ?? '未知';
    }

    // 判断是否是教师
    public function isTeacher()
    {
        return $this->user_type === self::TYPE_TEACHER;
    }

    // 判断是否是学生
    public function isStudent()
    {
        return $this->user_type === self::TYPE_STUDENT;
    }

    // 判断是否是管理员
    public function isAdmin()
    {
        return $this->user_type === self::TYPE_ADMIN;
    }

    public function teacher()
    {
        return $this->hasOne(Teacher::class, 'user_id');
    }

    public function student()
    {
        return $this->hasOne(Student::class, 'user_id');
    }

    // 模型事件：自动同步 teacher/student 表
    protected static function booted()
    {
        static::created(function ($user) {
            if ($user->user_type === self::TYPE_TEACHER) {
                Teacher::create(['user_id' => $user->id]);
            } elseif ($user->user_type === self::TYPE_STUDENT) {
                Student::create(['user_id' => $user->id]);
            }
        });
        
        static::updated(function ($user) {
            if ($user->isDirty('user_type')) {
                $oldType = $user->getOriginal('user_type');
                $newType = $user->user_type;
                
                if ($oldType === self::TYPE_TEACHER && $user->teacher) {
                    $user->teacher->delete();
                } elseif ($oldType === self::TYPE_STUDENT && $user->student) {
                    $user->student->delete();
                }
                
                if ($newType === self::TYPE_TEACHER) {
                    Teacher::create(['user_id' => $user->id]);
                } elseif ($newType === self::TYPE_STUDENT) {
                    Student::create(['user_id' => $user->id]);
                }
            }
        });
        
        static::deleting(function ($user) {
            if ($user->teacher) {
                $user->teacher->delete();
            }
            if ($user->student) {
                $user->student->delete();
            }
        });
    }
    
}

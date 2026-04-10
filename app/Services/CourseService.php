<?php

namespace App\Services;

use App\Exceptions\ApiBusinessException;
use App\Models\Course;
use App\Models\Teacher;
use App\Models\User;

class CourseService
{
    /**
     * 课程列表（按用户身份过滤），默认分页 20，最大 100。
     */
    public function listByUser(User $user, array $params): array
    {
        $perPage = max(1, min((int) ($params['per_page'] ?? 20), 100));
        $query = Course::query()->with(['student:id,name', 'teacher:id,name']);

        if ((int) $user->user_type === User::TYPE_TEACHER) {
            $teacher = Teacher::query()->where('user_id', $user->id)->first();
            if (!$teacher) {
                throw new ApiBusinessException('教师资料不存在', 422);
            }
            $query->where('teacher_id', $teacher->id);
        }

        if ((int) $user->user_type === User::TYPE_STUDENT && $user->student) {
            $query->where('student_id', $user->student->id);
        }

        $courses = $query->orderByDesc('id')->paginate($perPage);

        return [
            'data' => $courses->items(),
            'meta' => [
                'current_page' => $courses->currentPage(),
                'per_page' => $courses->perPage(),
                'total' => $courses->total(),
                'last_page' => $courses->lastPage(),
            ],
        ];
    }

    /**
     * 教师创建课程。
     */
    public function createByTeacher(User $user, array $validated): Course
    {
        if ((int) $user->user_type !== User::TYPE_TEACHER) {
            throw new ApiBusinessException('仅教师可创建课程', 403);
        }

        $teacher = Teacher::query()->where('user_id', $user->id)->first();
        if (!$teacher) {
            throw new ApiBusinessException('教师资料不存在', 422);
        }

        $course = Course::query()->create([
            'course_name' => $validated['course_name'],
            'course_month' => $validated['course_month'],
            'fee' => $validated['fee'],
            'teacher_id' => $teacher->id,
            'student_id' => $validated['student_id'],
        ]);

        return $course->load(['student:id,name', 'teacher:id,name']);
    }
}

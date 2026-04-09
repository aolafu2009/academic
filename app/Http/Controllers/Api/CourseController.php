<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreCourseRequest;
use App\Models\Course;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * 课程列表（按用户身份返回可见范围）。
     */
    public function index(Request $request)
    {
        // 预加载学生/教师，避免列表渲染时出现 N+1 查询。
        $query = Course::query()->with(['student:id,name', 'teacher:id,name']);
        $user = $request->user();

        if ((int) $user->user_type === User::TYPE_TEACHER) {
            // 教师仅查看自己创建的课程。
            $teacher = Teacher::where('user_id', $user->id)->first();
            if ($teacher) {
                $query->where('teacher_id', $teacher->id);
            } else {
                return response()->json(['message' => '教师资料不存在'], 422);
            }
        }

        if ((int) $user->user_type === User::TYPE_STUDENT && $user->student) {
            // 学生仅查看自己的课程。
            $query->where('student_id', $user->student->id);
        }

        $courses = $query->orderByDesc('id')->get();

        return response()->json([
            'code' => 200,
            'data' => $courses,
        ]);
    }

    /**
     * 教师创建课程。
     */
    public function store(StoreCourseRequest $request)
    {
        $user = $request->user();
        if ((int) $user->user_type !== User::TYPE_TEACHER) {
            return response()->json(['message' => '仅教师可创建课程'], 403);
        }

        $teacher = Teacher::where('user_id', $user->id)->first();        
        if (!$teacher) {
            return response()->json(['message' => '教师资料不存在'], 422);
        }    
        
        // 校验规则与错误文案由 FormRequest 统一管理，控制器只取净化后的参数。
        $validated = $request->validated();

        $course = Course::create([
            'course_name' => $validated['course_name'],
            'course_month' => $validated['course_month'],
            'fee' => $validated['fee'],
            'teacher_id' => $teacher->id,
            'student_id' => $validated['student_id'],
        ]);

        $course->load(['student:id,name', 'teacher:id,name']);

        return response()->json([
            'code' => 200,
            'message' => '课程创建成功',
            'data' => $course,
        ]);
    }
}

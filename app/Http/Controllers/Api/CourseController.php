<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ApiBusinessException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreCourseRequest;
use App\Services\CourseService;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    private CourseService $courseService;

    public function __construct(CourseService $courseService)
    {
        $this->courseService = $courseService;
    }

    /**
     * 课程列表（按用户身份返回可见范围）。
     */
    public function index(Request $request)
    {
        try {
            $result = $this->courseService->listByUser($request->user(), $request->only(['per_page']));
        } catch (ApiBusinessException $e) {
            return response()->json([
                'code' => $e->getStatus(),
                'message' => $e->getMessage(),
                'errors' => $e->getErrors(),
            ], $e->getStatus());
        }

        return response()->json([
            'code' => 200,
            'data' => $result['data'],
            'meta' => $result['meta'],
        ]);
    }

    /**
     * 教师创建课程。
     */
    public function store(StoreCourseRequest $request)
    {
        try {
            $course = $this->courseService->createByTeacher($request->user(), $request->validated());
        } catch (ApiBusinessException $e) {
            return response()->json([
                'code' => $e->getStatus(),
                'message' => $e->getMessage(),
                'errors' => $e->getErrors(),
            ], $e->getStatus());
        }

        return response()->json([
            'code' => 200,
            'message' => '课程创建成功',
            'data' => $course,
        ]);
    }
}

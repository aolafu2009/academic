<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\StudentService;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function __construct(private readonly StudentService $studentService)
    {
    }

    /**
     * 学生列表接口，请求编排与响应输出由控制器负责。
     */
    public function index(Request $request)
    {
        if ((int) $request->user()->user_type !== User::TYPE_TEACHER) {
            return response()->json([
                'code' => 403,
                'message' => '仅教师可查看学生列表',
            ], 403);
        }

        $result = $this->studentService->list($request->only(['per_page', 'name']));

        return response()->json([
            'code' => 200,
            'data' => $result['data'],
            'meta' => $result['meta'],
        ]);
    }
}

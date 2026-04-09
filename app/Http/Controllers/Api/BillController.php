<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ApiBusinessException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PayBillRequest;
use App\Http\Requests\Api\StoreBillRequest;
use App\Models\CourseBill;
use App\Services\BillService;
use Illuminate\Http\Request;

class BillController extends Controller
{
    /**
     * 通过依赖注入接入账单业务服务层。
     * 控制器仅负责请求编排与响应格式，核心业务由 Service 统一处理。
     */
    public function __construct(private readonly BillService $billService)
    {
    }

    /**
     * 账单列表（按当前登录用户身份自动过滤）。
     */
    public function index(Request $request)
    {
        // Service 内部会根据用户角色（教师/学生）构建对应可见数据范围。
        $query = $this->billService->queryByUser($request->user());

        return response()->json([
            'code' => 200,
            'data' => $query->orderByDesc('id')->get(),
        ]);
    }

    /**
     * 教师创建账单。
     */
    public function store(StoreBillRequest $request)
    {
        try {
            $bill = $this->billService->createByTeacher($request->user(), $request->validated());
        } catch (ApiBusinessException $e) {
            // 业务异常统一转换为标准 JSON 返回，避免控制器出现大量判断分支。
            return response()->json([
                'code' => $e->getStatus(),
                'message' => $e->getMessage(),
                'errors' => $e->getErrors(),
            ], $e->getStatus());
        }

        return response()->json([
            'code' => 200,
            'message' => '账单创建成功',
            'data' => $bill,
        ]);
    }

    /**
     * 学生支付账单（通过 bill_no 对外编号定位账单）。
     */
    public function pay(PayBillRequest $request, string $billNo)
    {
                
        $bill = CourseBill::query()->where('bill_no', $billNo)->first();
        if (!$bill) {
            return response()->json([
                'code' => 404,
                'message' => '账单不存在',
            ], 404);
        }

        try {
            $paidBill = $this->billService->payByStudent($request->user(), $bill, $request->validated());
        } catch (ApiBusinessException $e) {
            // 支付失败、归属不匹配等业务错误统一在 Service 抛出并在此收敛返回。
            return response()->json([
                'code' => $e->getStatus(),
                'message' => $e->getMessage(),
                'errors' => $e->getErrors(),
            ], $e->getStatus());
        }

        return response()->json([
            'code' => 200,
            'message' => '支付成功',
            'data' => $paidBill,
        ]);
    }
}

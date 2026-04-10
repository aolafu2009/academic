<?php

namespace App\Services;

use App\Exceptions\ApiBusinessException;
use App\Models\Course;
use App\Models\CourseBill;
use App\Models\Teacher;
use App\Models\User;
use App\Services\Payments\PaymentGatewayFactory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class BillService
{
    private PaymentGatewayFactory $gatewayFactory;

    public function __construct(PaymentGatewayFactory $gatewayFactory)
    {
        $this->gatewayFactory = $gatewayFactory;
    }

    public function queryByUser(User $user): Builder
    {
        // 预加载关联，避免列表接口出现 N+1 查询
        $query = CourseBill::query()->with([
            'course:id,course_name,course_month,fee',
            'student:id,name',
            'teacher:id,name',
        ]);

        // 学生仅查看自己的账单
        if ((int) $user->user_type === User::TYPE_STUDENT && $user->student) {
            $query->where('student_id', $user->student->id);
        }

        // 教师仅查看自己名下课程产生的账单
        if ((int) $user->user_type === User::TYPE_TEACHER) {
            $teacher = Teacher::query()->where('user_id', $user->id)->first();
            if ($teacher) {
                $query->where('teacher_id', $teacher->id);
            }
        }

        return $query;
    }

    /**
     * 账单列表（按用户身份过滤），默认分页 20，最大 100。
     */
    public function listByUser(User $user, array $params): array
    {
        $perPage = max(1, min((int) ($params['per_page'] ?? 20), 100));
        $bills = $this->queryByUser($user)->orderByDesc('id')->paginate($perPage);

        return [
            'data' => $bills->items(),
            'meta' => [
                'current_page' => $bills->currentPage(),
                'per_page' => $bills->perPage(),
                'total' => $bills->total(),
                'last_page' => $bills->lastPage(),
            ],
        ];
    }

    public function createByTeacher(User $user, array $validated): CourseBill
    {
        // 创建账单入口仅允许教师调用
        if ((int) $user->user_type !== User::TYPE_TEACHER) {
            throw new ApiBusinessException('仅教师可创建账单', 403);
        }

        // 根据登录用户反查教师资料
        $teacher = Teacher::query()->where('user_id', $user->id)->first();
        if (!$teacher) {
            throw new ApiBusinessException('教师资料不存在', 422);
        }

        // 课程必须存在且归属当前教师，避免越权创建账单
        $course = Course::query()
            ->where('id', $validated['course_id'])
            ->where('teacher_id', $teacher->id)
            ->first();

        if (!$course) {
            throw new ApiBusinessException('课程不存在或不属于当前教师', 422);
        }

        if ((int) $validated['student_id'] !== (int) $course->student_id) {
            throw new ApiBusinessException('账单学生必须与课程学生一致', 422);
        }

        // 账单金额优先使用请求值，未传则回退到课程费用
        $bill = CourseBill::query()->create([
            'bill_no' => $this->generateBillNo(),
            'course_id' => $course->id,
            'teacher_id' => $teacher->id,
            'student_id' => $validated['student_id'],
            'amount' => $validated['amount'] ?? $course->fee,
            'status' => CourseBill::STATUS_PENDING,
        ]);

        return $bill->load(['course:id,course_name,course_month,fee', 'student:id,name', 'teacher:id,name']);
    }

    public function payByStudent(User $user, CourseBill $bill, array $validated): CourseBill
    {
        // 支付入口仅允许学生调用
        if ((int) $user->user_type !== User::TYPE_STUDENT) {
            throw new ApiBusinessException('仅学生可支付账单', 403);
        }

        if (!$user->student) {
            throw new ApiBusinessException('学生资料不存在', 422);
        }

        // 只能支付自己对应的账单
        if ((int) $bill->student_id !== (int) $user->student->id) {
            throw new ApiBusinessException('只能支付自己的账单', 403);
        }

        // 已支付账单直接拦截，防止重复扣款
        if ($bill->status === CourseBill::STATUS_PAID) {
            throw new ApiBusinessException('账单已支付，请勿重复支付', 422);
        }

        // 通过工厂选择具体支付网关，便于后续扩展多渠道
        $provider = $validated['payment_provider'] ?? 'omise';
        $gateway = $this->gatewayFactory->make($provider);

        $result = $gateway->charge([
            'amount' => (float) $bill->amount,
            'currency' => (string) config('services.omise.currency', 'thb'),
            'omise_token' => $validated['omise_token'] ?? '',
            'description' => 'Bill #'.$bill->id.' payment',
            'bill_id' => $bill->id,
        ]);

        // 将第三方支付结果回写到账单，保留渠道与原始回包便于追踪
        $bill->status = $result->success ? CourseBill::STATUS_PAID : CourseBill::STATUS_FAILED;
        $bill->payment_provider = $provider;
        $bill->payment_reference = $result->reference;
        $bill->payment_meta = $result->raw;
        $bill->paid_at = $result->success ? Carbon::now() : null;
        $bill->save();

        return $bill->load(['course:id,course_name,course_month,fee', 'student:id,name', 'teacher:id,name']);
    }

    // 生成唯一账单号：时间戳 + 6 位随机数，冲突时最多重试 10 次
    private function generateBillNo(): string
    {
        for ($i = 0; $i < 10; $i++) {
            $billNo = 'P'.now()->format('YmdHis').str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $exists = CourseBill::query()->where('bill_no', $billNo)->exists();
            if (!$exists) {
                return $billNo;
            }
        }

        throw new ApiBusinessException('生成账单号失败，请稍后重试', 500);
    }
}

<?php

namespace App\Services\Payments\Contracts;

use App\Services\Payments\PaymentResult;

interface PaymentGatewayInterface
{
    // 统一支付网关入口：输入渠道所需参数，返回标准化支付结果
    public function charge(array $payload): PaymentResult;
}

<?php

namespace App\Services\Payments;

class PaymentResult
{
    public function __construct(
        // 是否支付成功（由具体网关映射）
        public readonly bool $success,
        // 第三方返回的交易引用号
        public readonly string $reference,
        // 原始返回数据，便于问题排查或对账
        public readonly array $raw = []
    ) {
    }
}

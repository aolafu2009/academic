<?php

namespace App\Services\Payments;

use App\Exceptions\ApiBusinessException;
use App\Services\Payments\Contracts\PaymentGatewayInterface;
use GuzzleHttp\Client;

class PaymentGatewayFactory
{
    public function make(string $provider): PaymentGatewayInterface
    {
        // 统一在工厂内做渠道分发，业务层只关心 provider 名称
        return match ($provider) {
            'omise' => new OmisePaymentGateway(new Client(['timeout' => 10])),
            default => throw new ApiBusinessException('不支持的支付渠道: '.$provider, 422),
        };
    }
}

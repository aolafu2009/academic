<?php

namespace App\Services\Payments;

use App\Exceptions\ApiBusinessException;
use App\Services\Payments\Contracts\PaymentGatewayInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use Throwable;

class OmisePaymentGateway implements PaymentGatewayInterface
{
    public function __construct(private readonly Client $client)
    {
    }

    public function charge(array $payload): PaymentResult
    {
        // 运行时读取密钥，避免硬编码并支持环境切换
        $secretKey = (string) config('services.omise.secret_key');
        if ($secretKey === '') {
            throw new ApiBusinessException('未配置 Omise 密钥，请检查 OMISE_SECRET_KEY', 500);
        }

        // Omise 的 amount 直接使用最小货币单位（如分/satang）
        // 约束为非负整数字符串或整数，避免将主单位金额误当作最小单位。
        $amount = $payload['amount'] ?? 0;
        $smallestUnitAmount = filter_var($amount, FILTER_VALIDATE_INT);
        if ($smallestUnitAmount === false) {
            throw new ApiBusinessException('支付金额格式错误，amount 必须是最小货币单位整数', 422);
        }

        if ($smallestUnitAmount <= 0) {
            throw new ApiBusinessException('支付金额必须大于0', 422);
        }

        // 前端应先创建 token，再由服务端发起扣款
        $token = (string) ($payload['omise_token'] ?? '');
        if ($token === '') {
            throw new ApiBusinessException('缺少 Omise Token', 422);
        }

        try {
            // 使用密钥发起服务端扣款，metadata 里保留业务 bill_id 便于对账
            $response = $this->client->post('https://api.omise.co/charges', [
                'auth' => [$secretKey, ''],
                'connect_timeout' => 10,
                'timeout' => 20,
                'http_errors' => false,
                'form_params' => [
                    'amount' => $smallestUnitAmount,
                    'currency' => (string) ($payload['currency'] ?? 'thb'),
                    'card' => $token,
                    'description' => (string) ($payload['description'] ?? 'Academic bill payment'),
                    'metadata' => [
                        'bill_id' => (string) ($payload['bill_id'] ?? ''),
                    ],
                ],
            ]);

            $statusCode = $response->getStatusCode();
            $body = json_decode((string) $response->getBody(), true);

            if ($statusCode >= 400) {
                $errorMessage = (string) ($body['message'] ?? '');
                $errorCode = (string) ($body['code'] ?? '');

                throw new ApiBusinessException(
                    'Omise 支付失败'
                    .($errorCode !== '' ? " [{$errorCode}]" : '')
                    .($errorMessage !== '' ? ': '.$errorMessage : '，请检查支付参数后重试'),
                    422,
                    is_array($body) ? ['omise' => $body] : []
                );
            }
        } catch (ConnectException $e) {
            $message = $e->getMessage();
            $hint = str_contains(strtolower($message), 'ssl')
                || str_contains(strtolower($message), 'tls')
                || str_contains(strtolower($message), 'certificate')
                ? '疑似 TLS/证书链问题，请检查服务器 CA 证书与网络策略'
                : '请检查后端服务器到 api.omise.co 的网络连通性';

            throw new ApiBusinessException(
                '支付网关连接失败：'.$hint,
                503,
                ['gateway_error' => $message]
            );
        } catch (Throwable $e) {
            // 将底层异常转换为业务异常，避免实现细节泄漏到控制层
            throw new ApiBusinessException('Omise 支付请求失败: '.$e->getMessage(), 422);
        }

        // 返回体至少要有支付流水 id
        if (!is_array($body) || empty($body['id'])) {
            throw new ApiBusinessException('Omise 返回结果异常', 422);
        }

        // paid 或 status=successful 任一满足即视为成功
        $isPaid = (bool) ($body['paid'] ?? false);
        $isSuccessful = (string) ($body['status'] ?? '') === 'successful';
        if (!$isPaid && !$isSuccessful) {
            throw new ApiBusinessException('支付未成功，请更换支付方式或稍后重试', 422);
        }

        // 统一封装支付结果，供上层服务写入账单状态
        return new PaymentResult(
            success: true,
            reference: (string) $body['id'],
            raw: $body
        );
    }
}

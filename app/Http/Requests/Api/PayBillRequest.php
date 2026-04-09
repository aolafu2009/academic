<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class PayBillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_provider' => 'required|string|in:omise',
            'omise_token' => 'required_if:payment_provider,omise|string',
        ];
    }

    public function messages(): array
    {
        return [
            'payment_provider.required' => '支付渠道不能为空',
            'payment_provider.in' => '支付渠道不支持',
            'omise_token.required_if' => 'Omise 支付 Token 不能为空',
        ];
    }

    protected function prepareForValidation(): void
    {
        $paymentProvider = $this->input('payment_provider', 'omise');
        $omiseToken = $this->input('omise_token');

        // 兼容前端常见字段命名，避免 token 字段名不一致导致 422。
        if (!is_string($omiseToken) || trim($omiseToken) === '') {
            $tokenFromAlias = $this->input('token', $this->input('omiseToken'));
            if (is_string($tokenFromAlias) && trim($tokenFromAlias) !== '') {
                $omiseToken = $tokenFromAlias;
            }
        }

        $this->merge([
            'payment_provider' => is_string($paymentProvider) && trim($paymentProvider) !== ''
                ? trim($paymentProvider)
                : 'omise',
            'omise_token' => is_string($omiseToken) ? trim($omiseToken) : $omiseToken,
        ]);
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'code' => 422,
            'message' => '参数验证失败',
            'errors' => $validator->errors(),
        ], 422));
    }
}

<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreBillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'course_id' => 'required|integer|exists:courses,id',
            'student_id' => 'required|integer|exists:students,id',
            'amount' => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'course_id.required' => '课程不能为空',
            'course_id.exists' => '课程不存在',
            'student_id.required' => '学生不能为空',
            'student_id.exists' => '学生不存在',
            'amount.numeric' => '账单金额必须是数字',
            'amount.min' => '账单金额不能小于0',
        ];
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

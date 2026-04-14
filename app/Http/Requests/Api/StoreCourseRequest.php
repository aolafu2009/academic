<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'course_name' => 'required|string|max:100',
            'course_month' => ['required', 'regex:/^\d{6}$/'],
            'fee' => 'required|numeric|min:1',
            'student_id' => 'required|integer|exists:students,id',
        ];
    }

    public function messages(): array
    {
        return [
            'course_name.required' => '课程名不能为空',
            'course_name.max' => '课程名不能超过100个字符',
            'course_month.required' => '课程年月不能为空',
            'course_month.regex' => '课程年月日错误',
            'fee.required' => '课程费用不能为空',
            'fee.numeric' => '课程费用必须是数字',
            'fee.min' => '课程费用不能小于1',
            'student_id.required' => '学生不能为空',
            'student_id.exists' => '学生不存在',
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

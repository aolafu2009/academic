<?php

namespace App\Services;

use App\Models\Student;
use Illuminate\Support\Collection;

class StudentService
{
    /**
     * 学生列表（默认每页 20 条，支持姓名模糊查询）。
     */
    public function list(array $params): array
    {
        $perPage = max(1, min((int) ($params['per_page'] ?? 20), 100));
        $nameKeyword = trim((string) ($params['name'] ?? ''));

        $query = Student::query()->select(['id', 'name', 'student_no', 'grade']);

        if ($nameKeyword !== '') {
            $query->where('name', 'like', '%'.$nameKeyword.'%');
        }

        $students = $query->orderBy('id')->paginate($perPage);

        return [
            'data' => $this->formatStudents(collect($students->items())),
            'meta' => [
                'current_page' => $students->currentPage(),
                'per_page' => $students->perPage(),
                'total' => $students->total(),
                'last_page' => $students->lastPage(),
            ],
        ];
    }

    private function formatStudents(Collection $students): Collection
    {
        return $students->map(function (Student $student) {
            $name = (string) ($student->name ?? '');
            $studentNo = (string) ($student->student_no ?? '');
            $grade = (string) ($student->grade ?? '');

            return [
                'id' => $student->id,
                'name' => $name,
                'student_no' => $studentNo,
                'grade' => $grade,
                'display_text' => $name.'-'.$studentNo.'-'.$grade,
            ];
        })->values();
    }
}

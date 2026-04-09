<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Laravel\Passport\Passport;
use Tests\TestCase;

class StudentControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]);

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $this->prepareSchema();
    }

    private function prepareSchema(): void
    {
        Schema::dropAllTables();

        Schema::create('admin_users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('username')->nullable();
            $table->string('email')->nullable();
            $table->string('password');
            $table->unsignedTinyInteger('user_type')->default(User::TYPE_STUDENT);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('teacher_id')->nullable();
            $table->string('student_no')->nullable();
            $table->string('grade')->nullable();
            $table->string('name')->nullable();
            $table->timestamps();
        });

        Schema::create('teacher', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('employee_no')->nullable();
            $table->string('phone')->nullable();
            $table->string('name')->nullable();
            $table->unsignedBigInteger('created_user_id')->nullable();
            $table->unsignedBigInteger('updated_user_id')->nullable();
            $table->timestamps();
        });
    }

    private function actingAsTeacher(string $username = 'teacher-student-list-user'): void
    {
        $user = User::query()->create([
            'name' => 'Teacher User',
            'username' => $username,
            'email' => $username.'@example.com',
            'password' => Hash::make('secret123'),
            'user_type' => User::TYPE_TEACHER,
        ]);

        Passport::actingAs($user);
    }

    private function actingAsStudent(string $username = 'student-student-list-user'): void
    {
        $user = User::query()->create([
            'name' => 'Student User',
            'username' => $username,
            'email' => $username.'@example.com',
            'password' => Hash::make('secret123'),
            'user_type' => User::TYPE_STUDENT,
        ]);

        Passport::actingAs($user);
    }

    public function test_students_index_returns_20_items_by_default(): void
    {
        $this->actingAsTeacher();

        for ($i = 1; $i <= 25; $i++) {
            Student::query()->create([
                'name' => '学生'.$i,
                'student_no' => (string) (20000 + $i),
                'grade' => '1年级',
            ]);
        }

        $response = $this->getJson('/api/students');

        $response->assertOk()
            ->assertJsonPath('code', 200)
            ->assertJsonCount(20, 'data')
            ->assertJsonPath('meta.per_page', 20)
            ->assertJsonPath('meta.total', 25);
    }

    public function test_students_index_supports_name_fuzzy_search(): void
    {
        $this->actingAsTeacher('teacher-fuzzy-search-user');

        Student::query()->create([
            'name' => '王明',
            'student_no' => '20001',
            'grade' => '1年级',
        ]);

        Student::query()->create([
            'name' => '李雷',
            'student_no' => '20002',
            'grade' => '1年级',
        ]);

        $response = $this->getJson('/api/students?name=王');

        $response->assertOk()
            ->assertJsonPath('code', 200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', '王明');
    }

    public function test_students_index_returns_display_text(): void
    {
        $this->actingAsTeacher('teacher-display-text-user');

        Student::query()->create([
            'name' => '王明',
            'student_no' => '20001',
            'grade' => '1年级',
        ]);

        $response = $this->getJson('/api/students');

        $response->assertOk()
            ->assertJsonPath('code', 200)
            ->assertJsonPath('data.0.display_text', '王明-20001-1年级');
    }

    public function test_students_index_forbids_non_teacher_user(): void
    {
        $this->actingAsStudent();

        $response = $this->getJson('/api/students');

        $response->assertStatus(403)
            ->assertJsonPath('code', 403)
            ->assertJsonPath('message', '仅教师可查看学生列表');
    }
}

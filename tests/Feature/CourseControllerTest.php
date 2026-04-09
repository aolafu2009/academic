<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Laravel\Passport\Passport;
use Tests\TestCase;

class CourseControllerTest extends TestCase
{
    use WithFaker;

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

        Schema::create('teacher', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name')->nullable();
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

        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('course_name', 100);
            $table->string('course_month', 6);
            $table->decimal('fee', 10, 2)->default(0);
            $table->unsignedBigInteger('teacher_id');
            $table->unsignedBigInteger('student_id');
            $table->timestamps();
        });
    }

    private function createTeacherUser(string $username = 'teacher01'): array
    {
        $user = User::query()->create([
            'name' => 'Teacher User',
            'username' => $username,
            'email' => $username.'@example.com',
            'password' => Hash::make('secret123'),
            'user_type' => User::TYPE_TEACHER,
        ]);

        $teacher = Teacher::query()->firstOrCreate(
            ['user_id' => $user->id],
            ['name' => 'Teacher A']
        );

        return [$user, $teacher];
    }

    public function test_teacher_can_create_course_successfully(): void
    {
        [$user, $teacher] = $this->createTeacherUser();

        $student = Student::query()->create([
            'name' => '小王',
            'teacher_id' => $teacher->id,
        ]);

        Passport::actingAs($user);

        $response = $this->postJson('/api/courses', [
            'course_name' => '语文',
            'course_month' => '202310',
            'fee' => 200,
            'student_id' => $student->id,
        ]);

        $response->assertOk()
            ->assertJsonPath('code', 200)
            ->assertJsonPath('data.course_name', '语文')
            ->assertJsonPath('data.course_month', '202310')
            ->assertJsonPath('data.student_id', $student->id);

        $this->assertDatabaseHas('courses', [
            'course_name' => '语文',
            'course_month' => '202310',
            'teacher_id' => $teacher->id,
            'student_id' => $student->id,
        ]);
    }

    public function test_course_month_validation_fails_when_not_six_digits(): void
    {
        [$user, $teacher] = $this->createTeacherUser('teacher02');

        $student = Student::query()->create([
            'name' => '小王',
            'teacher_id' => $teacher->id,
        ]);

        Passport::actingAs($user);

        $response = $this->postJson('/api/courses', [
            'course_name' => '数学',
            'course_month' => 20260,
            'fee' => 39900,
            'student_id' => $student->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['course_month']);
    }

    public function test_student_cannot_create_course(): void
    {
        $studentUser = User::query()->create([
            'name' => 'Student User',
            'username' => 'student01',
            'email' => 'student01@example.com',
            'password' => Hash::make('secret123'),
            'user_type' => User::TYPE_STUDENT,
        ]);

        $student = Student::query()->create([
            'user_id' => $studentUser->id,
            'name' => '小王',
        ]);

        Passport::actingAs($studentUser);

        $response = $this->postJson('/api/courses', [
            'course_name' => '英语',
            'course_month' => '202311',
            'fee' => 300,
            'student_id' => $student->id,
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('message', '仅教师可创建课程');
    }

    public function test_create_course_fails_when_student_not_exists(): void
    {
        [$user] = $this->createTeacherUser('teacher03');
        Passport::actingAs($user);

        $response = $this->postJson('/api/courses', [
            'course_name' => '物理',
            'course_month' => '202312',
            'fee' => 300,
            'student_id' => 999999,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['student_id']);
    }
}

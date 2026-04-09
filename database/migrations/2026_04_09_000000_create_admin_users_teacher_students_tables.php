<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('admin_users')) {
            Schema::create('admin_users', function (Blueprint $table) {
                $table->id();
                $table->string('username', 100)->unique();
                $table->string('name', 100)->nullable();
                $table->string('email')->nullable()->unique();
                $table->string('password');
                $table->tinyInteger('user_type')->default(1)->comment('1管理员 2教师 3学生');
                $table->rememberToken();
                $table->timestamps();

                $table->index('user_type', 'admin_users_user_type_idx');
            });
        }

        if (!Schema::hasTable('teacher')) {
            Schema::create('teacher', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->unique();
                $table->string('employee_no', 50)->nullable();
                $table->string('phone', 30)->nullable();
                $table->string('name', 100)->nullable();
                $table->unsignedBigInteger('created_user_id')->nullable();
                $table->unsignedBigInteger('updated_user_id')->nullable();
                $table->timestamps();

                $table->index('employee_no', 'teacher_employee_no_idx');
            });
        }

        if (!Schema::hasTable('students')) {
            Schema::create('students', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->unique();
                $table->unsignedBigInteger('teacher_id')->nullable();
                $table->string('student_no', 50)->nullable();
                $table->string('grade', 20)->nullable();
                $table->string('name', 100)->nullable();
                $table->timestamps();

                $table->index('teacher_id', 'students_teacher_id_idx');
                $table->index('student_no', 'students_student_no_idx');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
        Schema::dropIfExists('teacher');
        Schema::dropIfExists('admin_users');
    }
};

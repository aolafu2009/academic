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
        Schema::create('courses', function (Blueprint $table) {
            $table->id()->comment('主键ID');
            $table->string('course_name', 100)->comment('课程名');
            $table->string('course_month', 6)->comment('年月，如 202310');
            $table->decimal('fee', 10, 2)->default(0)->comment('课程费用');
            $table->unsignedBigInteger('teacher_id')->comment('老师ID');
            $table->unsignedBigInteger('student_id')->comment('学生ID');
            $table->timestamp('created_at')->nullable()->comment('创建时间');
            $table->timestamp('updated_at')->nullable()->comment('更新时间');

            $table->index('teacher_id');
            $table->index('student_id');
            $table->index('course_month');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};

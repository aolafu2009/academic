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
        Schema::create('course_bills', function (Blueprint $table) {
            $table->id()->comment('主键ID');
            $table->unsignedBigInteger('course_id')->comment('课程ID');
            $table->unsignedBigInteger('teacher_id')->comment('老师ID');
            $table->unsignedBigInteger('student_id')->comment('学生ID');
            $table->decimal('amount', 10, 2)->default(0)->comment('账单金额');
            $table->timestamp('created_at')->nullable()->comment('创建时间');
            $table->timestamp('updated_at')->nullable()->comment('更新时间');

            $table->index('course_id');
            $table->index('teacher_id');
            $table->index('student_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_bills');
    }
};

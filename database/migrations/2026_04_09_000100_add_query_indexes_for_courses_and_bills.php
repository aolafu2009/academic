<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('courses')) {
            Schema::table('courses', function (Blueprint $table) {
                if (!$this->hasIndex('courses', 'courses_teacher_id_id_idx')) {
                    $table->index(['teacher_id', 'id'], 'courses_teacher_id_id_idx');
                }

                if (!$this->hasIndex('courses', 'courses_student_id_id_idx')) {
                    $table->index(['student_id', 'id'], 'courses_student_id_id_idx');
                }
            });
        }

        if (Schema::hasTable('course_bills')) {
            Schema::table('course_bills', function (Blueprint $table) {
                if (!$this->hasIndex('course_bills', 'course_bills_teacher_id_id_idx')) {
                    $table->index(['teacher_id', 'id'], 'course_bills_teacher_id_id_idx');
                }

                if (!$this->hasIndex('course_bills', 'course_bills_student_id_id_idx')) {
                    $table->index(['student_id', 'id'], 'course_bills_student_id_id_idx');
                }

                if (!$this->hasIndex('course_bills', 'course_bills_status_id_idx')) {
                    $table->index(['status', 'id'], 'course_bills_status_id_idx');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('courses')) {
            Schema::table('courses', function (Blueprint $table) {
                if ($this->hasIndex('courses', 'courses_teacher_id_id_idx')) {
                    $table->dropIndex('courses_teacher_id_id_idx');
                }

                if ($this->hasIndex('courses', 'courses_student_id_id_idx')) {
                    $table->dropIndex('courses_student_id_id_idx');
                }
            });
        }

        if (Schema::hasTable('course_bills')) {
            Schema::table('course_bills', function (Blueprint $table) {
                if ($this->hasIndex('course_bills', 'course_bills_teacher_id_id_idx')) {
                    $table->dropIndex('course_bills_teacher_id_id_idx');
                }

                if ($this->hasIndex('course_bills', 'course_bills_student_id_id_idx')) {
                    $table->dropIndex('course_bills_student_id_id_idx');
                }

                if ($this->hasIndex('course_bills', 'course_bills_status_id_idx')) {
                    $table->dropIndex('course_bills_status_id_idx');
                }
            });
        }
    }

    private function hasIndex(string $tableName, string $indexName): bool
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            $rows = DB::select(
                'SELECT 1 FROM pg_indexes WHERE schemaname = current_schema() AND tablename = ? AND indexname = ? LIMIT 1',
                [$tableName, $indexName]
            );

            return !empty($rows);
        }

        if ($driver === 'mysql') {
            $rows = DB::select(
                'SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ? LIMIT 1',
                [$tableName, $indexName]
            );

            return !empty($rows);
        }

        return false;
    }
};

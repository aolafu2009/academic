<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement("COMMENT ON COLUMN courses.id IS '主键ID'");
            DB::statement("COMMENT ON COLUMN courses.course_name IS '课程名'");
            DB::statement("COMMENT ON COLUMN courses.course_month IS '年月，如 202310'");
            DB::statement("COMMENT ON COLUMN courses.fee IS '课程费用'");
            DB::statement("COMMENT ON COLUMN courses.teacher_id IS '老师ID'");
            DB::statement("COMMENT ON COLUMN courses.student_id IS '学生ID'");
            DB::statement("COMMENT ON COLUMN courses.created_at IS '创建时间'");
            DB::statement("COMMENT ON COLUMN courses.updated_at IS '更新时间'");

            DB::statement("COMMENT ON COLUMN course_bills.id IS '主键ID'");
            DB::statement("COMMENT ON COLUMN course_bills.bill_no IS '账单号'");
            DB::statement("COMMENT ON COLUMN course_bills.course_id IS '课程ID'");
            DB::statement("COMMENT ON COLUMN course_bills.teacher_id IS '老师ID'");
            DB::statement("COMMENT ON COLUMN course_bills.student_id IS '学生ID'");
            DB::statement("COMMENT ON COLUMN course_bills.amount IS '账单金额'");
            DB::statement("COMMENT ON COLUMN course_bills.status IS '支付状态：1待支付 2已支付 3支付失败'");
            DB::statement("COMMENT ON COLUMN course_bills.payment_provider IS '支付渠道'");
            DB::statement("COMMENT ON COLUMN course_bills.payment_reference IS '第三方支付流水号'");
            DB::statement("COMMENT ON COLUMN course_bills.paid_at IS '支付时间'");
            DB::statement("COMMENT ON COLUMN course_bills.payment_meta IS '支付扩展信息'");
            DB::statement("COMMENT ON COLUMN course_bills.created_at IS '创建时间'");
            DB::statement("COMMENT ON COLUMN course_bills.updated_at IS '更新时间'");
            return;
        }

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE courses MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID'");
            DB::statement("ALTER TABLE courses MODIFY course_name VARCHAR(100) NOT NULL COMMENT '课程名'");
            DB::statement("ALTER TABLE courses MODIFY course_month VARCHAR(6) NOT NULL COMMENT '年月，如 202310'");
            DB::statement("ALTER TABLE courses MODIFY fee DECIMAL(10,2) NOT NULL DEFAULT 0 COMMENT '课程费用'");
            DB::statement("ALTER TABLE courses MODIFY teacher_id BIGINT UNSIGNED NOT NULL COMMENT '老师ID'");
            DB::statement("ALTER TABLE courses MODIFY student_id BIGINT UNSIGNED NOT NULL COMMENT '学生ID'");
            DB::statement("ALTER TABLE courses MODIFY created_at TIMESTAMP NULL COMMENT '创建时间'");
            DB::statement("ALTER TABLE courses MODIFY updated_at TIMESTAMP NULL COMMENT '更新时间'");

            DB::statement("ALTER TABLE course_bills MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID'");
            DB::statement("ALTER TABLE course_bills MODIFY bill_no VARCHAR(32) NULL COMMENT '账单号'");
            DB::statement("ALTER TABLE course_bills MODIFY course_id BIGINT UNSIGNED NOT NULL COMMENT '课程ID'");
            DB::statement("ALTER TABLE course_bills MODIFY teacher_id BIGINT UNSIGNED NOT NULL COMMENT '老师ID'");
            DB::statement("ALTER TABLE course_bills MODIFY student_id BIGINT UNSIGNED NOT NULL COMMENT '学生ID'");
            DB::statement("ALTER TABLE course_bills MODIFY amount DECIMAL(10,2) NOT NULL DEFAULT 0 COMMENT '账单金额'");
            DB::statement("ALTER TABLE course_bills MODIFY status TINYINT NOT NULL DEFAULT 1 COMMENT '支付状态：1待支付 2已支付 3支付失败'");
            DB::statement("ALTER TABLE course_bills MODIFY payment_provider VARCHAR(50) NULL COMMENT '支付渠道'");
            DB::statement("ALTER TABLE course_bills MODIFY payment_reference VARCHAR(120) NULL COMMENT '第三方支付流水号'");
            DB::statement("ALTER TABLE course_bills MODIFY paid_at TIMESTAMP NULL COMMENT '支付时间'");
            DB::statement("ALTER TABLE course_bills MODIFY payment_meta JSON NULL COMMENT '支付扩展信息'");
            DB::statement("ALTER TABLE course_bills MODIFY created_at TIMESTAMP NULL COMMENT '创建时间'");
            DB::statement("ALTER TABLE course_bills MODIFY updated_at TIMESTAMP NULL COMMENT '更新时间'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement("COMMENT ON COLUMN courses.id IS NULL");
            DB::statement("COMMENT ON COLUMN courses.course_name IS NULL");
            DB::statement("COMMENT ON COLUMN courses.course_month IS NULL");
            DB::statement("COMMENT ON COLUMN courses.fee IS NULL");
            DB::statement("COMMENT ON COLUMN courses.teacher_id IS NULL");
            DB::statement("COMMENT ON COLUMN courses.student_id IS NULL");
            DB::statement("COMMENT ON COLUMN courses.created_at IS NULL");
            DB::statement("COMMENT ON COLUMN courses.updated_at IS NULL");

            DB::statement("COMMENT ON COLUMN course_bills.id IS NULL");
            DB::statement("COMMENT ON COLUMN course_bills.bill_no IS NULL");
            DB::statement("COMMENT ON COLUMN course_bills.course_id IS NULL");
            DB::statement("COMMENT ON COLUMN course_bills.teacher_id IS NULL");
            DB::statement("COMMENT ON COLUMN course_bills.student_id IS NULL");
            DB::statement("COMMENT ON COLUMN course_bills.amount IS NULL");
            DB::statement("COMMENT ON COLUMN course_bills.status IS NULL");
            DB::statement("COMMENT ON COLUMN course_bills.payment_provider IS NULL");
            DB::statement("COMMENT ON COLUMN course_bills.payment_reference IS NULL");
            DB::statement("COMMENT ON COLUMN course_bills.paid_at IS NULL");
            DB::statement("COMMENT ON COLUMN course_bills.payment_meta IS NULL");
            DB::statement("COMMENT ON COLUMN course_bills.created_at IS NULL");
            DB::statement("COMMENT ON COLUMN course_bills.updated_at IS NULL");
            return;
        }

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE courses MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT");
            DB::statement("ALTER TABLE courses MODIFY course_name VARCHAR(100) NOT NULL");
            DB::statement("ALTER TABLE courses MODIFY course_month VARCHAR(6) NOT NULL");
            DB::statement("ALTER TABLE courses MODIFY fee DECIMAL(10,2) NOT NULL DEFAULT 0");
            DB::statement("ALTER TABLE courses MODIFY teacher_id BIGINT UNSIGNED NOT NULL");
            DB::statement("ALTER TABLE courses MODIFY student_id BIGINT UNSIGNED NOT NULL");
            DB::statement("ALTER TABLE courses MODIFY created_at TIMESTAMP NULL");
            DB::statement("ALTER TABLE courses MODIFY updated_at TIMESTAMP NULL");

            DB::statement("ALTER TABLE course_bills MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT");
            DB::statement("ALTER TABLE course_bills MODIFY bill_no VARCHAR(32) NULL");
            DB::statement("ALTER TABLE course_bills MODIFY course_id BIGINT UNSIGNED NOT NULL");
            DB::statement("ALTER TABLE course_bills MODIFY teacher_id BIGINT UNSIGNED NOT NULL");
            DB::statement("ALTER TABLE course_bills MODIFY student_id BIGINT UNSIGNED NOT NULL");
            DB::statement("ALTER TABLE course_bills MODIFY amount DECIMAL(10,2) NOT NULL DEFAULT 0");
            DB::statement("ALTER TABLE course_bills MODIFY status TINYINT NOT NULL DEFAULT 1");
            DB::statement("ALTER TABLE course_bills MODIFY payment_provider VARCHAR(50) NULL");
            DB::statement("ALTER TABLE course_bills MODIFY payment_reference VARCHAR(120) NULL");
            DB::statement("ALTER TABLE course_bills MODIFY paid_at TIMESTAMP NULL");
            DB::statement("ALTER TABLE course_bills MODIFY payment_meta JSON NULL");
            DB::statement("ALTER TABLE course_bills MODIFY created_at TIMESTAMP NULL");
            DB::statement("ALTER TABLE course_bills MODIFY updated_at TIMESTAMP NULL");
        }
    }
};

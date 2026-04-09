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
        Schema::table('course_bills', function (Blueprint $table) {
            $table->string('status', 20)->default('pending')->after('amount')->comment('支付状态：1待支付 2已支付 3支付失败');
            $table->string('payment_provider', 50)->nullable()->after('status')->comment('支付渠道');
            $table->string('payment_reference', 120)->nullable()->after('payment_provider')->comment('第三方支付流水号');
            $table->timestamp('paid_at')->nullable()->after('payment_reference')->comment('支付时间');
            $table->json('payment_meta')->nullable()->after('paid_at')->comment('支付扩展信息');

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_bills', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn([
                'status',
                'payment_provider',
                'payment_reference',
                'paid_at',
                'payment_meta',
            ]);
        });
    }
};

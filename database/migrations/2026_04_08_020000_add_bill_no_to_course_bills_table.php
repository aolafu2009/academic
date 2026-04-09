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
        $driver = DB::getDriverName();

        Schema::table('course_bills', function (Blueprint $table) use ($driver) {
            $column = $table->string('bill_no', 32)->nullable()->comment('账单号');
            if ($driver === 'mysql') {
                $column->after('id');
            }
        });

        DB::table('course_bills')
            ->select('id')
            ->orderBy('id')
            ->chunkById(200, function ($rows): void {
                foreach ($rows as $row) {
                    DB::table('course_bills')
                        ->where('id', $row->id)
                        ->update([
                            'bill_no' => 'P'.str_pad((string) $row->id, 16, '0', STR_PAD_LEFT),
                        ]);
                }
            });

        Schema::table('course_bills', function (Blueprint $table) {
            $table->unique('bill_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_bills', function (Blueprint $table) {
            $table->dropUnique(['bill_no']);
            $table->dropColumn('bill_no');
        });
    }
};

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
            DB::statement(<<<'SQL'
                ALTER TABLE course_bills
                ALTER COLUMN status DROP DEFAULT
            SQL);

            DB::statement(<<<'SQL'
                ALTER TABLE course_bills
                ALTER COLUMN status TYPE SMALLINT
                USING (
                    CASE
                        WHEN status = 'pending' THEN 1
                        WHEN status = 'paid' THEN 2
                        WHEN status = 'failed' THEN 3
                        WHEN status ~ '^\d+$' THEN status::smallint
                        ELSE 1
                    END
                )
            SQL);

            DB::statement(<<<'SQL'
                ALTER TABLE course_bills
                ALTER COLUMN status SET DEFAULT 1
            SQL);
            return;
        }

        if ($driver === 'mysql') {
            DB::statement(<<<'SQL'
                UPDATE course_bills
                SET status = CASE
                    WHEN status = 'pending' THEN '1'
                    WHEN status = 'paid' THEN '2'
                    WHEN status = 'failed' THEN '3'
                    ELSE status
                END
            SQL);

            DB::statement(<<<'SQL'
                ALTER TABLE course_bills
                MODIFY status TINYINT NOT NULL DEFAULT 1
            SQL);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement(<<<'SQL'
                ALTER TABLE course_bills
                ALTER COLUMN status DROP DEFAULT
            SQL);

            DB::statement(<<<'SQL'
                ALTER TABLE course_bills
                ALTER COLUMN status TYPE VARCHAR(20)
                USING (
                    CASE
                        WHEN status = 1 THEN 'pending'
                        WHEN status = 2 THEN 'paid'
                        WHEN status = 3 THEN 'failed'
                        ELSE 'pending'
                    END
                )
            SQL);

            DB::statement(<<<'SQL'
                ALTER TABLE course_bills
                ALTER COLUMN status SET DEFAULT 'pending'
            SQL);
            return;
        }

        if ($driver === 'mysql') {
            DB::statement(<<<'SQL'
                ALTER TABLE course_bills
                MODIFY status VARCHAR(20) NOT NULL DEFAULT 'pending'
            SQL);

            DB::statement(<<<'SQL'
                UPDATE course_bills
                SET status = CASE
                    WHEN status = '1' THEN 'pending'
                    WHEN status = '2' THEN 'paid'
                    WHEN status = '3' THEN 'failed'
                    ELSE 'pending'
                END
            SQL);
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
            DROP TRIGGER IF EXISTS trg_prevent_double_booking
                ON staff_weekly_rota;
            DROP FUNCTION IF EXISTS trg_fn_prevent_double_booking();

            CREATE OR REPLACE FUNCTION trg_fn_prevent_double_booking()
            RETURNS TRIGGER AS $$
            BEGIN
                IF EXISTS (
                    SELECT 1 FROM staff_weekly_rota
                    WHERE staff_no        = NEW.staff_no
                      AND ward_id         = NEW.ward_id
                      AND week_start_date = NEW.week_start_date
                ) THEN
                    RAISE EXCEPTION
                        'Staff % is already scheduled for ward % on week starting %.',
                        NEW.staff_no, NEW.ward_id, NEW.week_start_date;
                END IF;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER trg_prevent_double_booking
            BEFORE INSERT ON staff_weekly_rota
            FOR EACH ROW EXECUTE FUNCTION trg_fn_prevent_double_booking();
        SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
            DROP TRIGGER IF EXISTS trg_prevent_double_booking
                ON staff_weekly_rota;
            DROP FUNCTION IF EXISTS trg_fn_prevent_double_booking();
        SQL);
    }
};

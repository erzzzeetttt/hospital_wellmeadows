<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_prevent_duplicate_appointment ON appointments');
        DB::unprepared('DROP FUNCTION IF EXISTS trg_fn_prevent_duplicate_appointment()');

        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION trg_fn_prevent_duplicate_appointment()
            RETURNS TRIGGER AS $$
            BEGIN
                IF EXISTS (
                    SELECT 1 FROM appointments
                    WHERE patient_no = NEW.patient_no
                    AND appointment_date = NEW.appointment_date
                    AND appointment_time = NEW.appointment_time
                    AND status != 'Cancelled'
                    AND appointment_id != COALESCE(NEW.appointment_id, 0)
                ) THEN
                    RAISE EXCEPTION 'Patient % already has an appointment on % at %. Please choose a different time.',
                        NEW.patient_no,
                        NEW.appointment_date,
                        NEW.appointment_time;
                END IF;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
            SQL);

        DB::unprepared(<<<'SQL'
            CREATE TRIGGER trg_prevent_duplicate_appointment
            BEFORE INSERT OR UPDATE ON appointments
            FOR EACH ROW
            EXECUTE FUNCTION trg_fn_prevent_duplicate_appointment();
            SQL);
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_prevent_duplicate_appointment ON appointments');
        DB::unprepared('DROP FUNCTION IF EXISTS trg_fn_prevent_duplicate_appointment()');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared("DROP FUNCTION IF EXISTS fn_schedule_appointment(varchar, date, time)");
        DB::unprepared("DROP FUNCTION IF EXISTS fn_schedule_appointment(varchar, integer, date, time, varchar, varchar)");

        DB::unprepared("
CREATE OR REPLACE FUNCTION fn_schedule_appointment(
    p_patient_no VARCHAR(10),
    p_staff_no VARCHAR(10),
    p_appointment_date DATE,
    p_appointment_time TIME,
    p_examination_room VARCHAR(100)
)
RETURNS BIGINT AS \$\$
DECLARE
    v_appointment_id BIGINT;
BEGIN
    INSERT INTO appointments (
        patient_no, staff_no, appointment_date,
        appointment_time, examination_room,
        status, created_at, updated_at
    )
    VALUES (
        p_patient_no, p_staff_no, p_appointment_date,
        p_appointment_time, p_examination_room,
        'Pending', NOW(), NOW()
    )
    RETURNING appointment_id INTO v_appointment_id;
    RETURN v_appointment_id;
EXCEPTION
    WHEN OTHERS THEN
        RAISE EXCEPTION '%', SQLERRM;
END;
\$\$ LANGUAGE plpgsql;
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP FUNCTION IF EXISTS fn_schedule_appointment(varchar, varchar, date, time, varchar)");
    }
};

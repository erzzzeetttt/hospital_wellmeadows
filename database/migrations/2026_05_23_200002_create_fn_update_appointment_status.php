<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared("DROP FUNCTION IF EXISTS fn_update_appointment_status(bigint, varchar)");

        DB::unprepared("
CREATE OR REPLACE FUNCTION fn_update_appointment_status(
    p_appointment_id BIGINT,
    p_status VARCHAR(50)
)
RETURNS TEXT AS \$\$
BEGIN
    UPDATE appointments
    SET status = p_status, updated_at = NOW()
    WHERE appointment_id = p_appointment_id;
    RETURN 'Appointment status updated to ' || p_status;
EXCEPTION
    WHEN OTHERS THEN
        RAISE EXCEPTION '%', SQLERRM;
END;
\$\$ LANGUAGE plpgsql;
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP FUNCTION IF EXISTS fn_update_appointment_status(bigint, varchar)");
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('diagnoses', 'appointment_id')) {
            Schema::table('diagnoses', function (Blueprint $table) {
                $table->bigInteger('appointment_id')->nullable();
            });
        }

        DB::unprepared('DROP FUNCTION IF EXISTS fn_add_diagnosis(varchar,varchar,date,text)');

        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION fn_add_diagnosis(
                p_patient_no        VARCHAR(10),
                p_staff_no          VARCHAR(10),
                p_appointment_id    BIGINT,
                p_diagnosis_date    DATE,
                p_diagnosis_details TEXT
            )
            RETURNS BIGINT AS $$
            DECLARE
                v_diagnosis_id BIGINT;
            BEGIN
                INSERT INTO diagnoses (
                    patient_no, staff_no, appointment_id,
                    diagnosis_details, diagnosis_date,
                    created_at, updated_at
                )
                VALUES (
                    p_patient_no, p_staff_no, p_appointment_id,
                    p_diagnosis_details, p_diagnosis_date,
                    NOW(), NOW()
                )
                RETURNING diagnosis_id INTO v_diagnosis_id;
                RETURN v_diagnosis_id;
            EXCEPTION
                WHEN OTHERS THEN
                    RAISE EXCEPTION '%', SQLERRM;
            END;
            $$ LANGUAGE plpgsql;
            SQL);
    }

    public function down(): void
    {
        DB::unprepared('DROP FUNCTION IF EXISTS fn_add_diagnosis(varchar,varchar,bigint,date,text)');
    }
};

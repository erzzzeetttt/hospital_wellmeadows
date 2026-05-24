<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared('DROP FUNCTION IF EXISTS fn_add_treatment(varchar,varchar,bigint,date,varchar,text,varchar,text)');

        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION fn_add_treatment(
                p_patient_no      VARCHAR(10),
                p_staff_no        VARCHAR(10),
                p_diagnosis_id    BIGINT,
                p_treatment_date  DATE,
                p_treatment_type  VARCHAR(100),
                p_treatment_given TEXT,
                p_method          VARCHAR(100),
                p_remarks         TEXT
            )
            RETURNS BIGINT AS $$
            DECLARE
                v_treatment_id BIGINT;
            BEGIN
                INSERT INTO treatments (
                    patient_no, staff_no, diagnosis_id,
                    treatment_date, treatment_type,
                    treatment_given, treatment_details,
                    method, remarks,
                    created_at, updated_at
                )
                VALUES (
                    p_patient_no, p_staff_no, p_diagnosis_id,
                    p_treatment_date, p_treatment_type,
                    p_treatment_given, p_treatment_given,
                    p_method, p_remarks,
                    NOW(), NOW()
                )
                RETURNING treatment_id INTO v_treatment_id;
                RETURN v_treatment_id;
            EXCEPTION
                WHEN OTHERS THEN
                    RAISE EXCEPTION '%', SQLERRM;
            END;
            $$ LANGUAGE plpgsql;
            SQL);
    }

    public function down(): void
    {
        DB::unprepared('DROP FUNCTION IF EXISTS fn_add_treatment(varchar,varchar,bigint,date,varchar,text,varchar,text)');
    }
};

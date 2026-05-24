<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('appointments', 'appointment_type')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->string('appointment_type', 100)->nullable();
            });
        }

        if (!Schema::hasColumn('diagnoses', 'treatment_type')) {
            Schema::table('diagnoses', function (Blueprint $table) {
                $table->string('treatment_type', 100)->nullable();
            });
        }

        // ── fn_schedule_appointment (add appointment_type param) ───────────────
        DB::unprepared('DROP FUNCTION IF EXISTS fn_schedule_appointment(varchar, varchar, date, time, varchar)');
        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION fn_schedule_appointment(
                p_patient_no       VARCHAR(10),
                p_staff_no         VARCHAR(10),
                p_appointment_date DATE,
                p_appointment_time TIME,
                p_examination_room VARCHAR(100),
                p_appointment_type VARCHAR(100)
            )
            RETURNS BIGINT AS $$
            DECLARE
                v_appointment_id BIGINT;
            BEGIN
                INSERT INTO appointments (
                    patient_no, staff_no, appointment_date,
                    appointment_time, examination_room,
                    appointment_type, status, created_at, updated_at
                )
                VALUES (
                    p_patient_no, p_staff_no, p_appointment_date,
                    p_appointment_time, p_examination_room,
                    p_appointment_type, 'Pending', NOW(), NOW()
                )
                RETURNING appointment_id INTO v_appointment_id;
                RETURN v_appointment_id;
            EXCEPTION
                WHEN OTHERS THEN
                    RAISE EXCEPTION '%', SQLERRM;
            END;
            $$ LANGUAGE plpgsql;
            SQL);

        // ── fn_add_treatment (add treatment_type param) ───────────────────────
        DB::unprepared('DROP FUNCTION IF EXISTS fn_add_treatment(varchar, varchar, date, text, text, text)');
        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION fn_add_treatment(
                p_patient_no        VARCHAR(10),
                p_staff_no          VARCHAR(10),
                p_diagnosis_date    DATE,
                p_diagnosis_details TEXT,
                p_treatment_given   TEXT,
                p_remarks           TEXT,
                p_treatment_type    VARCHAR(100)
            )
            RETURNS BIGINT AS $$
            DECLARE
                v_diagnosis_id BIGINT;
            BEGIN
                INSERT INTO diagnoses (
                    patient_no, staff_no, diagnosis_date,
                    diagnosis_details, treatment_type,
                    created_at, updated_at
                )
                VALUES (
                    p_patient_no, NULL, p_diagnosis_date,
                    p_diagnosis_details || chr(10) || 'Treatment Given: ' || COALESCE(p_treatment_given, '') || chr(10) || 'Remarks: ' || COALESCE(p_remarks, ''),
                    p_treatment_type,
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
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('appointment_type');
        });

        Schema::table('diagnoses', function (Blueprint $table) {
            $table->dropColumn('treatment_type');
        });

        DB::unprepared('DROP FUNCTION IF EXISTS fn_schedule_appointment(varchar, varchar, date, time, varchar, varchar)');
        DB::unprepared('DROP FUNCTION IF EXISTS fn_add_treatment(varchar, varchar, date, text, text, text, varchar)');
    }
};

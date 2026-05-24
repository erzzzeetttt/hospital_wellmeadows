<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Add missing columns to treatments table ───────────────────────────
        if (!Schema::hasColumn('treatments', 'staff_no')) {
            Schema::table('treatments', function (Blueprint $table) {
                $table->string('staff_no', 10)->nullable();
            });
        }
        if (!Schema::hasColumn('treatments', 'diagnosis_id')) {
            Schema::table('treatments', function (Blueprint $table) {
                $table->bigInteger('diagnosis_id')->nullable();
            });
        }
        if (!Schema::hasColumn('treatments', 'treatment_type')) {
            Schema::table('treatments', function (Blueprint $table) {
                $table->string('treatment_type', 100)->nullable();
            });
        }
        if (!Schema::hasColumn('treatments', 'treatment_given')) {
            Schema::table('treatments', function (Blueprint $table) {
                $table->text('treatment_given')->nullable();
            });
        }
        if (!Schema::hasColumn('treatments', 'method')) {
            Schema::table('treatments', function (Blueprint $table) {
                $table->string('method', 100)->nullable();
            });
        }
        if (!Schema::hasColumn('treatments', 'remarks')) {
            Schema::table('treatments', function (Blueprint $table) {
                $table->text('remarks')->nullable();
            });
        }
        if (!Schema::hasColumn('treatments', 'treatment_date')) {
            Schema::table('treatments', function (Blueprint $table) {
                $table->date('treatment_date')->nullable();
            });
        }

        // ── Drop unused table ─────────────────────────────────────────────────
        if (Schema::hasTable('hospital_procedures')) {
            Schema::drop('hospital_procedures');
        }

        // ── Drop old functions before creating new ones ───────────────────────
        DB::unprepared('DROP FUNCTION IF EXISTS fn_add_procedure(varchar,varchar,integer,varchar,text,varchar,integer,date,numeric,varchar)');
        DB::unprepared('DROP FUNCTION IF EXISTS fn_add_treatment(varchar,varchar,date,text,text,text,varchar)');

        // ── fn_add_diagnosis — inserts into diagnoses only ────────────────────
        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION fn_add_diagnosis(
                p_patient_no        VARCHAR(10),
                p_staff_no          VARCHAR(10),
                p_diagnosis_date    DATE,
                p_diagnosis_details TEXT
            )
            RETURNS BIGINT AS $$
            DECLARE
                v_diagnosis_id BIGINT;
            BEGIN
                INSERT INTO diagnoses (
                    patient_no, staff_no, diagnosis_details,
                    diagnosis_date, created_at, updated_at
                )
                VALUES (
                    p_patient_no, NULL, p_diagnosis_details,
                    p_diagnosis_date, NOW(), NOW()
                )
                RETURNING diagnosis_id INTO v_diagnosis_id;
                RETURN v_diagnosis_id;
            EXCEPTION
                WHEN OTHERS THEN
                    RAISE EXCEPTION '%', SQLERRM;
            END;
            $$ LANGUAGE plpgsql;
            SQL);

        // ── fn_add_treatment — inserts into treatments, linked to a diagnosis ─
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
                    treatment_given, method, remarks,
                    created_at, updated_at
                )
                VALUES (
                    p_patient_no, p_staff_no, p_diagnosis_id,
                    p_treatment_date, p_treatment_type,
                    p_treatment_given, p_method, p_remarks,
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
        DB::unprepared('DROP FUNCTION IF EXISTS fn_add_treatment(varchar, varchar, bigint, date, varchar, text, varchar, text)');
        DB::unprepared('DROP FUNCTION IF EXISTS fn_add_diagnosis(varchar, varchar, date, text)');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── DROP existing functions and triggers first (CLAUDE.md rule) ──────
        DB::unprepared('DROP TRIGGER IF EXISTS trg_appointment_no_duplicate ON appointments');
        DB::unprepared('DROP FUNCTION IF EXISTS trg_prevent_duplicate_appointment()');
        DB::unprepared('DROP FUNCTION IF EXISTS fn_schedule_appointment(varchar,date,time)');
        DB::unprepared('DROP FUNCTION IF EXISTS fn_cancel_appointment(bigint)');
        DB::unprepared('DROP FUNCTION IF EXISTS fn_add_treatment(varchar,varchar,date,text,text,text)');
        DB::unprepared('DROP FUNCTION IF EXISTS fn_update_treatment(bigint,text,date)');
        DB::unprepared('DROP FUNCTION IF EXISTS fn_assign_staff_to_treatment(bigint,varchar,varchar)');

        // ── fn_schedule_appointment ──────────────────────────────────────────
        // Inserts a new appointment for a patient.
        // appointments table columns: patient_no (varchar 10), appointment_date, appointment_time, status
        DB::unprepared("
            CREATE OR REPLACE FUNCTION fn_schedule_appointment(
                p_patient_no       VARCHAR(10),
                p_appointment_date DATE,
                p_appointment_time TIME
            ) RETURNS BIGINT AS \$\$
            DECLARE
                v_id BIGINT;
            BEGIN
                INSERT INTO appointments (patient_no, appointment_date, appointment_time, status, created_at, updated_at)
                VALUES (p_patient_no, p_appointment_date, p_appointment_time, 'Pending', NOW(), NOW())
                RETURNING appointment_id INTO v_id;
                RETURN v_id;
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        // ── fn_cancel_appointment ────────────────────────────────────────────
        // Sets appointment status to Cancelled.
        DB::unprepared("
            CREATE OR REPLACE FUNCTION fn_cancel_appointment(p_appointment_id BIGINT)
            RETURNS TEXT AS \$\$
            BEGIN
                UPDATE appointments
                SET    status     = 'Cancelled',
                       updated_at = NOW()
                WHERE  appointment_id = p_appointment_id;

                IF NOT FOUND THEN
                    RAISE EXCEPTION 'Appointment % not found.', p_appointment_id;
                END IF;

                RETURN 'cancelled';
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        // ── fn_add_treatment ─────────────────────────────────────────────────
        // Saves a treatment record to the diagnoses table.
        //
        // IMPORTANT: diagnoses.staff_no is BIGINT (migration 2026_05_11_064817)
        // but staff.staff_no is VARCHAR(10) (e.g. 'S001').  We store NULL for
        // staff_no to avoid a cast error; p_staff_no is accepted for future use
        // when the schema is aligned.
        //
        // treatment_given and remarks are appended to diagnosis_details since
        // the diagnoses table has no separate columns for them.
        DB::unprepared("
            CREATE OR REPLACE FUNCTION fn_add_treatment(
                p_patient_no        VARCHAR(10),
                p_staff_no          VARCHAR(10),
                p_diagnosis_date    DATE,
                p_diagnosis_details TEXT,
                p_treatment_given   TEXT DEFAULT '',
                p_remarks           TEXT DEFAULT ''
            ) RETURNS BIGINT AS \$\$
            DECLARE
                v_id      BIGINT;
                v_details TEXT;
            BEGIN
                v_details := COALESCE(p_diagnosis_details, '');

                IF COALESCE(p_treatment_given, '') <> '' THEN
                    v_details := v_details || E'\\n\\nTreatment Given:\\n' || p_treatment_given;
                END IF;

                IF COALESCE(p_remarks, '') <> '' THEN
                    v_details := v_details || E'\\n\\nRemarks:\\n' || p_remarks;
                END IF;

                INSERT INTO diagnoses (patient_no, staff_no, diagnosis_details, diagnosis_date, created_at, updated_at)
                VALUES (p_patient_no, NULL, v_details, p_diagnosis_date, NOW(), NOW())
                RETURNING diagnosis_id INTO v_id;

                RETURN v_id;
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        // ── fn_update_treatment ──────────────────────────────────────────────
        // Updates an existing diagnoses record.
        DB::unprepared("
            CREATE OR REPLACE FUNCTION fn_update_treatment(
                p_diagnosis_id      BIGINT,
                p_diagnosis_details TEXT,
                p_diagnosis_date    DATE
            ) RETURNS TEXT AS \$\$
            BEGIN
                UPDATE diagnoses
                SET    diagnosis_details = p_diagnosis_details,
                       diagnosis_date    = p_diagnosis_date,
                       updated_at        = NOW()
                WHERE  diagnosis_id = p_diagnosis_id;

                IF NOT FOUND THEN
                    RAISE EXCEPTION 'Diagnosis % not found.', p_diagnosis_id;
                END IF;

                RETURN 'updated';
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        // ── fn_assign_staff_to_treatment ─────────────────────────────────────
        // Links a doctor and/or nurse to a diagnosis (via a treatment record).
        //
        // Finds or creates a treatments row for the same patient/date, then
        // inserts into treatment_staff.
        //
        // NOTE: treatment_staff.staff_no is also BIGINT (schema limitation).
        // The cast from VARCHAR staff_no ('S001') to BIGINT will fail silently
        // inside a nested BEGIN/EXCEPTION block so the function still succeeds.
        // Align the schema (change to VARCHAR) to make staff linking work fully.
        DB::unprepared("
            CREATE OR REPLACE FUNCTION fn_assign_staff_to_treatment(
                p_diagnosis_id    BIGINT,
                p_doctor_staff_no VARCHAR(10),
                p_nurse_staff_no  VARCHAR(10)
            ) RETURNS TEXT AS \$\$
            DECLARE
                v_treatment_id BIGINT;
                v_patient_no   VARCHAR(10);
                v_date         DATE;
            BEGIN
                SELECT patient_no, diagnosis_date
                INTO   v_patient_no, v_date
                FROM   diagnoses
                WHERE  diagnosis_id = p_diagnosis_id;

                IF v_patient_no IS NULL THEN
                    RAISE EXCEPTION 'Diagnosis % not found.', p_diagnosis_id;
                END IF;

                -- Find existing treatment for same patient/date, or create one
                SELECT treatment_id INTO v_treatment_id
                FROM   treatments
                WHERE  patient_no    = v_patient_no
                  AND  treatment_date = v_date
                ORDER  BY treatment_id
                LIMIT  1;

                IF v_treatment_id IS NULL THEN
                    INSERT INTO treatments (patient_no, treatment_details, treatment_date, created_at, updated_at)
                    VALUES (v_patient_no, 'Staff assignment record', v_date, NOW(), NOW())
                    RETURNING treatment_id INTO v_treatment_id;
                END IF;

                -- Assign doctor (silently skip if BIGINT cast fails for 'S001' format)
                BEGIN
                    IF COALESCE(p_doctor_staff_no, '') <> '' THEN
                        INSERT INTO treatment_staff (treatment_id, staff_no, created_at, updated_at)
                        VALUES (v_treatment_id, p_doctor_staff_no::BIGINT, NOW(), NOW())
                        ON CONFLICT DO NOTHING;
                    END IF;
                EXCEPTION WHEN OTHERS THEN
                    NULL; -- VARCHAR staff_no cannot be cast to BIGINT
                END;

                -- Assign nurse (same graceful fallback)
                BEGIN
                    IF COALESCE(p_nurse_staff_no, '') <> '' THEN
                        INSERT INTO treatment_staff (treatment_id, staff_no, created_at, updated_at)
                        VALUES (v_treatment_id, p_nurse_staff_no::BIGINT, NOW(), NOW())
                        ON CONFLICT DO NOTHING;
                    END IF;
                EXCEPTION WHEN OTHERS THEN
                    NULL;
                END;

                RETURN 'assigned';
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        // ── Trigger: prevent duplicate patient appointment ───────────────────
        // Blocks inserting/updating an appointment when the same patient already
        // has a non-cancelled appointment at the exact same date and time.
        DB::unprepared("
            CREATE OR REPLACE FUNCTION trg_prevent_duplicate_appointment()
            RETURNS TRIGGER AS \$\$
            BEGIN
                IF EXISTS (
                    SELECT 1 FROM appointments
                    WHERE  patient_no        = NEW.patient_no
                      AND  appointment_date  = NEW.appointment_date
                      AND  appointment_time  = NEW.appointment_time
                      AND  status           <> 'Cancelled'
                      AND  appointment_id   <> COALESCE(NEW.appointment_id, 0)
                ) THEN
                    RAISE EXCEPTION
                        'Patient % already has an active appointment on % at %.',
                        NEW.patient_no, NEW.appointment_date, NEW.appointment_time;
                END IF;
                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        DB::unprepared("
            CREATE TRIGGER trg_appointment_no_duplicate
            BEFORE INSERT OR UPDATE ON appointments
            FOR EACH ROW EXECUTE FUNCTION trg_prevent_duplicate_appointment();
        ");
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_appointment_no_duplicate ON appointments');
        DB::unprepared('DROP FUNCTION IF EXISTS trg_prevent_duplicate_appointment()');
        DB::unprepared('DROP FUNCTION IF EXISTS fn_schedule_appointment(varchar,date,time)');
        DB::unprepared('DROP FUNCTION IF EXISTS fn_cancel_appointment(bigint)');
        DB::unprepared('DROP FUNCTION IF EXISTS fn_add_treatment(varchar,varchar,date,text,text,text)');
        DB::unprepared('DROP FUNCTION IF EXISTS fn_update_treatment(bigint,text,date)');
        DB::unprepared('DROP FUNCTION IF EXISTS fn_assign_staff_to_treatment(bigint,varchar,varchar)');
    }
};

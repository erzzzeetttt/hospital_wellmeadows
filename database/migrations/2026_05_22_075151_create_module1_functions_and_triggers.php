<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {

        /*
        mag register ni ug patient with next of kin na function
        */

        DB::unprepared("
        CREATE OR REPLACE FUNCTION fn_register_patient(
            p_first_name VARCHAR,
            p_last_name VARCHAR,
            p_date_of_birth DATE,
            p_gender VARCHAR,
            p_address VARCHAR,
            p_phone_no VARCHAR,
            p_marital_status VARCHAR,
            p_doctor_id BIGINT,
            p_nextofkinid BIGINT
        )
        RETURNS TEXT AS \$\$
        DECLARE
            v_patient_no VARCHAR(10);
        BEGIN

            INSERT INTO patients (
                patient_no,
                first_name,
                last_name,
                date_of_birth,
                gender,
                address,
                phone_no,
                marital_status,
                doctor_id,
                nextofkinid,
                created_at,
                updated_at
            )
            VALUES (
                fn_generate_patientno(),
                p_first_name,
                p_last_name,
                p_date_of_birth,
                p_gender,
                p_address,
                p_phone_no,
                p_marital_status,
                p_doctor_id,
                p_nextofkinid,
                NOW(),
                NOW()
            )
            RETURNING patient_no INTO v_patient_no;

            RETURN 'Patient registered successfully: ' || v_patient_no;

        END;
        \$\$ LANGUAGE plpgsql;
        ");



        /*
        fucntion para e admit ang patient
        */

        DB::unprepared("
        CREATE OR REPLACE FUNCTION fn_admit_patient(
            p_patient_no VARCHAR,
            p_date_admitted DATE,
            p_expected_leave_date DATE
        )
        RETURNS TEXT AS \$\$
        BEGIN

            INSERT INTO ward_admissions (
                patient_no,
                date_admitted,
                expected_leave_date,
                status,
                created_at,
                updated_at
            )
            VALUES (
                p_patient_no,
                p_date_admitted,
                p_expected_leave_date,
                'Admitted',
                NOW(),
                NOW()
            );

            RETURN 'Patient admitted successfully';

        END;
        \$\$ LANGUAGE plpgsql;
        ");



        /*
        function para e discharge ang patient
        */

        DB::unprepared("
        CREATE OR REPLACE FUNCTION fn_discharge_patient(
            p_admission_id BIGINT
        )
        RETURNS TEXT AS \$\$
        BEGIN

            UPDATE ward_admissions
            SET
                status = 'Discharged',
                discharge_date = CURRENT_DATE,
                updated_at = NOW()
            WHERE admission_id = p_admission_id;

            RETURN 'Patient discharged successfully';

        END;
        \$\$ LANGUAGE plpgsql;
        ");



        /*
        mag add ni ug medication record na function
        */

        DB::unprepared("
        CREATE OR REPLACE FUNCTION fn_add_medication_record(
            p_patient_no VARCHAR,
            p_drug_name VARCHAR,
            p_dosage VARCHAR,
            p_frequency VARCHAR,
            p_start_date DATE,
            p_end_date DATE
        )
        RETURNS TEXT AS \$\$
        DECLARE
            v_drug_id BIGINT;
        BEGIN

            SELECT drug_id
            INTO v_drug_id
            FROM drugs
            WHERE drug_name = p_drug_name
            LIMIT 1;

            INSERT INTO medication_records (
                patient_no,
                drug_id,
                dosage,
                frequency,
                start_date,
                end_date,
                created_at,
                updated_at
            )
            VALUES (
                p_patient_no,
                v_drug_id,
                p_dosage,
                p_frequency,
                p_start_date,
                p_end_date,
                NOW(),
                NOW()
            );

            RETURN 'Medication record added successfully';

        END;
        \$\$ LANGUAGE plpgsql;
        ");



        /*
        e update ang medication record function
        */

        DB::unprepared("
        CREATE OR REPLACE FUNCTION fn_update_medication_record(
            p_medication_id BIGINT,
            p_dosage VARCHAR,
            p_frequency VARCHAR,
            p_end_date DATE
        )
        RETURNS TEXT AS \$\$
        BEGIN

            UPDATE medication_records
            SET
                dosage = p_dosage,
                frequency = p_frequency,
                end_date = p_end_date,
                updated_at = NOW()
            WHERE medication_id = p_medication_id;

            RETURN 'Medication record updated successfully';

        END;
        \$\$ LANGUAGE plpgsql;
        ");



        /*
        e check active admission trigger
        */

        DB::unprepared("
        CREATE OR REPLACE FUNCTION trg_check_active_admission()
        RETURNS TRIGGER AS \$\$
        BEGIN

            IF EXISTS (
                SELECT 1
                FROM ward_admissions
                WHERE patient_no = NEW.patient_no
                AND status = 'Admitted'
            ) THEN

                RAISE EXCEPTION 'Patient already has an active admission.';

            END IF;

            RETURN NEW;

        END;
        \$\$ LANGUAGE plpgsql;
        ");



        DB::unprepared("
        DROP TRIGGER IF EXISTS before_admission_insert
        ON ward_admissions;

        CREATE TRIGGER before_admission_insert
        BEFORE INSERT
        ON ward_admissions
        FOR EACH ROW
        EXECUTE FUNCTION trg_check_active_admission();
        ");

    }



    public function down(): void
    {

        DB::unprepared('DROP TRIGGER IF EXISTS before_admission_insert ON ward_admissions;');

        DB::unprepared('DROP FUNCTION IF EXISTS trg_check_active_admission();');

        DB::unprepared('DROP FUNCTION IF EXISTS fn_register_patient(VARCHAR, VARCHAR, DATE, VARCHAR, VARCHAR, VARCHAR, VARCHAR, BIGINT, BIGINT);');

        DB::unprepared('DROP FUNCTION IF EXISTS fn_admit_patient(VARCHAR, DATE, DATE);');

        DB::unprepared('DROP FUNCTION IF EXISTS fn_discharge_patient(BIGINT);');

        DB::unprepared('DROP FUNCTION IF EXISTS fn_add_medication_record(VARCHAR, VARCHAR, VARCHAR, VARCHAR, DATE, DATE);');

        DB::unprepared('DROP FUNCTION IF EXISTS fn_update_medication_record(BIGINT, VARCHAR, VARCHAR, DATE);');

    }
};
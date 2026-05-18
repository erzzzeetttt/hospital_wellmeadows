<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // fn_add_staff originally returned TEXT. Change return type to BIGINT so
        // the controller can immediately use the new staff_no for qual/experience inserts.
        // PostgreSQL requires DROP + CREATE when changing return type.
        DB::unprepared("
            DROP FUNCTION IF EXISTS fn_add_staff(
                INTEGER, VARCHAR, VARCHAR, DATE, VARCHAR, VARCHAR, VARCHAR,
                VARCHAR, VARCHAR, NUMERIC, VARCHAR, NUMERIC, VARCHAR, VARCHAR
            );
        ");

        DB::unprepared("
            CREATE FUNCTION fn_add_staff(
                p_role_id        INTEGER,
                p_first_name     VARCHAR,
                p_last_name      VARCHAR,
                p_dob            DATE,
                p_gender         VARCHAR,
                p_address        VARCHAR,
                p_phone_no       VARCHAR,
                p_nin            VARCHAR,
                p_position       VARCHAR,
                p_salary         NUMERIC,
                p_salary_scale   VARCHAR,
                p_hours_per_week NUMERIC,
                p_contract_type  VARCHAR,
                p_payment_type   VARCHAR
            ) RETURNS BIGINT AS \$\$
            DECLARE
                v_staff_no BIGINT;
            BEGIN
                INSERT INTO staff (
                    role_id, first_name, last_name, dob, gender, address,
                    phone_no, nin, position, salary, salary_scale,
                    hours_per_week, contract_type, payment_type,
                    created_at, updated_at
                ) VALUES (
                    p_role_id, p_first_name, p_last_name, p_dob, p_gender, p_address,
                    p_phone_no, p_nin, p_position, p_salary, p_salary_scale,
                    p_hours_per_week, p_contract_type, p_payment_type,
                    NOW(), NOW()
                )
                RETURNING staff_no INTO v_staff_no;

                RETURN v_staff_no;
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        DB::unprepared("
            CREATE OR REPLACE FUNCTION fn_add_staff_qualification(
                p_staff_no      BIGINT,
                p_qual_type     VARCHAR,
                p_date_obtained DATE,
                p_institution   VARCHAR
            ) RETURNS TEXT AS \$\$
            BEGIN
                INSERT INTO staff_qualifications (
                    staff_no, qualification_type, institution, date_obtained,
                    created_at, updated_at
                ) VALUES (
                    p_staff_no, p_qual_type, p_institution, p_date_obtained,
                    NOW(), NOW()
                );
                RETURN 'Qualification added.';
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        DB::unprepared("
            CREATE OR REPLACE FUNCTION fn_add_staff_experience(
                p_staff_no     BIGINT,
                p_position     VARCHAR,
                p_start_date   DATE,
                p_end_date     DATE,
                p_organization VARCHAR
            ) RETURNS TEXT AS \$\$
            BEGIN
                INSERT INTO work_experiences (
                    staff_no, position, start_date, end_date, organization,
                    created_at, updated_at
                ) VALUES (
                    p_staff_no, p_position, p_start_date, p_end_date, p_organization,
                    NOW(), NOW()
                );
                RETURN 'Work experience added.';
            END;
            \$\$ LANGUAGE plpgsql;
        ");
    }

    public function down(): void
    {
        DB::unprepared('DROP FUNCTION IF EXISTS fn_add_staff_qualification CASCADE;');
        DB::unprepared('DROP FUNCTION IF EXISTS fn_add_staff_experience CASCADE;');

        // Restore fn_add_staff returning TEXT
        DB::unprepared("DROP FUNCTION IF EXISTS fn_add_staff(INTEGER,VARCHAR,VARCHAR,DATE,VARCHAR,VARCHAR,VARCHAR,VARCHAR,VARCHAR,NUMERIC,VARCHAR,NUMERIC,VARCHAR,VARCHAR);");
        DB::unprepared("
            CREATE FUNCTION fn_add_staff(
                p_role_id INTEGER, p_first_name VARCHAR, p_last_name VARCHAR,
                p_dob DATE, p_gender VARCHAR, p_address VARCHAR, p_phone_no VARCHAR,
                p_nin VARCHAR, p_position VARCHAR, p_salary NUMERIC,
                p_salary_scale VARCHAR, p_hours_per_week NUMERIC,
                p_contract_type VARCHAR, p_payment_type VARCHAR
            ) RETURNS TEXT AS \$\$
            DECLARE v_staff_no BIGINT;
            BEGIN
                INSERT INTO staff (role_id, first_name, last_name, dob, gender, address,
                    phone_no, nin, position, salary, salary_scale,
                    hours_per_week, contract_type, payment_type, created_at, updated_at)
                VALUES (p_role_id, p_first_name, p_last_name, p_dob, p_gender, p_address,
                    p_phone_no, p_nin, p_position, p_salary, p_salary_scale,
                    p_hours_per_week, p_contract_type, p_payment_type, NOW(), NOW())
                RETURNING staff_no INTO v_staff_no;
                RETURN 'Staff registered successfully. Staff No: S' || LPAD(v_staff_no::TEXT, 3, '0');
            END;
            \$\$ LANGUAGE plpgsql;
        ");
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared('DROP FUNCTION IF EXISTS fn_add_staff(integer,character varying,character varying,date,character varying,character varying,character varying,character varying,character varying,numeric,character varying,numeric,character varying,character varying)');

        DB::unprepared('DROP FUNCTION IF EXISTS fn_update_staff(character varying,integer,character varying,character varying,date,character varying,character varying,character varying,character varying,character varying,numeric,character varying,numeric,character varying,character varying)');

        DB::unprepared("
            CREATE OR REPLACE FUNCTION fn_add_staff(
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
            ) RETURNS TEXT AS \$\$
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

                RETURN 'Staff registered successfully. Staff No: S' || LPAD(v_staff_no::TEXT, 3, '0');
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        DB::unprepared("
            CREATE OR REPLACE FUNCTION fn_update_staff(
                p_staff_no       INTEGER,
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
            ) RETURNS TEXT AS \$\$
            BEGIN
                UPDATE staff SET
                    role_id        = p_role_id,
                    first_name     = p_first_name,
                    last_name      = p_last_name,
                    dob            = p_dob,
                    gender         = p_gender,
                    address        = p_address,
                    phone_no       = p_phone_no,
                    nin            = p_nin,
                    position       = p_position,
                    salary         = p_salary,
                    salary_scale   = p_salary_scale,
                    hours_per_week = p_hours_per_week,
                    contract_type  = p_contract_type,
                    payment_type   = p_payment_type,
                    updated_at     = NOW()
                WHERE staff_no = p_staff_no;

                RETURN 'Staff record updated successfully.';
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        DB::unprepared("
            CREATE OR REPLACE FUNCTION fn_delete_staff(p_staff_no INTEGER)
            RETURNS TEXT AS \$\$
            BEGIN
                DELETE FROM staff WHERE staff_no = p_staff_no;
                RETURN 'Staff record deleted successfully.';
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        DB::unprepared("
            CREATE OR REPLACE FUNCTION fn_assign_staff_to_ward(
                p_staff_no        INTEGER,
                p_ward_id         INTEGER,
                p_assignment_date DATE,
                p_role_in_ward    VARCHAR
            ) RETURNS TEXT AS \$\$
            DECLARE
                v_exists BOOLEAN;
            BEGIN
                SELECT EXISTS(
                    SELECT 1 FROM staff_ward_assignments
                    WHERE staff_no = p_staff_no
                      AND ward_id  = p_ward_id
                      AND end_date IS NULL
                ) INTO v_exists;

                IF v_exists THEN
                    RETURN 'Staff is already actively assigned to this ward.';
                END IF;

                INSERT INTO staff_ward_assignments (
                    staff_no, ward_id, assignment_date, role_in_ward, created_at, updated_at
                ) VALUES (
                    p_staff_no, p_ward_id, p_assignment_date, p_role_in_ward, NOW(), NOW()
                );

                RETURN 'Staff assigned to ward successfully.';
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        DB::unprepared("
            CREATE OR REPLACE FUNCTION fn_set_staff_rota(
                p_staff_no        INTEGER,
                p_ward_id         INTEGER,
                p_week_start_date DATE,
                p_shift_type      VARCHAR
            ) RETURNS TEXT AS \$\$
            BEGIN
                INSERT INTO staff_weekly_rota (
                    staff_no, ward_id, week_start_date, shift_type, created_at, updated_at
                ) VALUES (
                    p_staff_no, p_ward_id, p_week_start_date, p_shift_type, NOW(), NOW()
                )
                ON CONFLICT (staff_no, ward_id, week_start_date)
                DO UPDATE SET shift_type = p_shift_type, updated_at = NOW();

                RETURN 'Staff shift schedule updated successfully.';
            END;
            \$\$ LANGUAGE plpgsql;
        ");
    }

    public function down(): void
    {
        DB::unprepared('DROP FUNCTION IF EXISTS fn_add_staff CASCADE;');
        DB::unprepared('DROP FUNCTION IF EXISTS fn_update_staff CASCADE;');
        DB::unprepared('DROP FUNCTION IF EXISTS fn_delete_staff CASCADE;');
        DB::unprepared('DROP FUNCTION IF EXISTS fn_assign_staff_to_ward CASCADE;');
        DB::unprepared('DROP FUNCTION IF EXISTS fn_set_staff_rota CASCADE;');
    }
};

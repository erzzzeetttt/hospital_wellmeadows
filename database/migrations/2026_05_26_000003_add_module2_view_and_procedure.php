<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared('DROP VIEW IF EXISTS vw_staff_profile;');

        DB::unprepared("
            CREATE OR REPLACE VIEW vw_staff_profile AS
            SELECT
                s.staff_no,
                s.first_name,
                s.last_name,
                CONCAT(s.first_name, ' ', s.last_name) AS full_name,
                s.dob,
                s.gender,
                s.address,
                s.phone_no,
                s.nin,
                s.position,
                s.salary,
                s.salary_scale,
                s.hours_per_week,
                s.contract_type,
                s.payment_type,
                s.date_registered,
                r.role_name,
                r.role_id,
                swa.ward_id,
                swa.role_in_ward,
                swa.assignment_date,
                w.ward_name,
                w.telephone_extension,
                swr.shift_type,
                swr.week_start_date,
                s.created_at,
                s.updated_at
            FROM staff s
            LEFT JOIN roles r ON r.role_id = s.role_id
            LEFT JOIN staff_ward_assignments swa
                ON swa.staff_no = s.staff_no
                AND swa.end_date IS NULL
            LEFT JOIN wards w ON w.ward_id = swa.ward_id
            LEFT JOIN staff_weekly_rota swr
                ON swr.staff_no = s.staff_no
                AND swr.ward_id = swa.ward_id
                AND swr.week_start_date = (
                    SELECT MAX(r2.week_start_date)
                    FROM staff_weekly_rota r2
                    WHERE r2.staff_no = s.staff_no
                );
        ");

        DB::unprepared("
            DROP PROCEDURE IF EXISTS sp_transfer_staff_ward(
                VARCHAR, INTEGER, INTEGER, VARCHAR, DATE
            );
        ");

        DB::unprepared("
            CREATE OR REPLACE PROCEDURE sp_transfer_staff_ward(
                p_staff_no        VARCHAR(10),
                p_old_ward_id     INTEGER,
                p_new_ward_id     INTEGER,
                p_role_in_ward    VARCHAR,
                p_transfer_date   DATE
            )
            LANGUAGE plpgsql
            AS \$\$
            BEGIN
                -- Step 1: End the current active assignment
                UPDATE staff_ward_assignments
                SET end_date   = p_transfer_date,
                    updated_at = NOW()
                WHERE staff_no = p_staff_no
                  AND ward_id  = p_old_ward_id
                  AND end_date IS NULL;

                IF NOT FOUND THEN
                    RAISE EXCEPTION
                        'No active assignment found for staff % in ward %.',
                        p_staff_no, p_old_ward_id;
                END IF;

                -- Step 2: Check if already assigned to new ward
                IF EXISTS (
                    SELECT 1 FROM staff_ward_assignments
                    WHERE staff_no = p_staff_no
                      AND ward_id  = p_new_ward_id
                      AND end_date IS NULL
                ) THEN
                    RAISE EXCEPTION
                        'Staff % is already actively assigned to ward %.',
                        p_staff_no, p_new_ward_id;
                END IF;

                -- Step 3: Create new assignment in new ward
                INSERT INTO staff_ward_assignments (
                    staff_no, ward_id, assignment_date,
                    role_in_ward, created_at, updated_at
                ) VALUES (
                    p_staff_no, p_new_ward_id, p_transfer_date,
                    p_role_in_ward, NOW(), NOW()
                );

                RAISE NOTICE 'Staff % transferred from ward % to ward % successfully.',
                    p_staff_no, p_old_ward_id, p_new_ward_id;
            END;
            \$\$;
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP VIEW IF EXISTS vw_staff_profile;");

        DB::unprepared("
            DROP PROCEDURE IF EXISTS sp_transfer_staff_ward(
                VARCHAR, INTEGER, INTEGER, VARCHAR, DATE
            );
        ");
    }
};

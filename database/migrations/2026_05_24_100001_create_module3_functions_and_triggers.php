<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop any pre-existing conflicting trigger/function before creating ours
        DB::unprepared('DROP TRIGGER IF EXISTS trg_PreventDoubleBooking ON ward_allocations');
        DB::unprepared('DROP FUNCTION IF EXISTS check_staff_double_booking()');

        // ── fn_add_ward ────────────────────────────────────────────────────────
        DB::unprepared('DROP FUNCTION IF EXISTS fn_add_ward(VARCHAR, VARCHAR, VARCHAR, INTEGER, VARCHAR, VARCHAR)');
        DB::unprepared('DROP FUNCTION IF EXISTS fn_add_ward(VARCHAR, VARCHAR, VARCHAR, INTEGER)');
        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION fn_add_ward(
                p_ward_name  VARCHAR,
                p_ward_type  VARCHAR,
                p_location   VARCHAR,
                p_total_beds INTEGER
            ) RETURNS INTEGER AS $$
            DECLARE
                v_ward_id INTEGER;
                v_bed_num INTEGER;
            BEGIN
                INSERT INTO wards (ward_name, ward_type, location, total_beds, created_at, updated_at)
                VALUES (p_ward_name, p_ward_type, p_location, p_total_beds, NOW(), NOW())
                RETURNING ward_id INTO v_ward_id;

                FOR v_bed_num IN 1..p_total_beds LOOP
                    INSERT INTO beds (ward_id, bed_number, status, created_at, updated_at)
                    VALUES (v_ward_id, LPAD(v_bed_num::TEXT, 2, '0'), 'Available', NOW(), NOW());
                END LOOP;

                RETURN v_ward_id;
            END;
            $$ LANGUAGE plpgsql;
            SQL);

        // ── fn_update_ward ─────────────────────────────────────────────────────
        DB::unprepared('DROP FUNCTION IF EXISTS fn_update_ward(INTEGER, VARCHAR, VARCHAR, VARCHAR, INTEGER, VARCHAR, VARCHAR)');
        DB::unprepared('DROP FUNCTION IF EXISTS fn_update_ward(INTEGER, VARCHAR, VARCHAR, VARCHAR, INTEGER)');
        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION fn_update_ward(
                p_ward_id    INTEGER,
                p_ward_name  VARCHAR,
                p_ward_type  VARCHAR,
                p_location   VARCHAR,
                p_total_beds INTEGER
            ) RETURNS TEXT AS $$
            BEGIN
                UPDATE wards SET
                    ward_name  = p_ward_name,
                    ward_type  = p_ward_type,
                    location   = p_location,
                    total_beds = p_total_beds,
                    updated_at = NOW()
                WHERE ward_id = p_ward_id;

                RETURN 'Ward updated successfully.';
            END;
            $$ LANGUAGE plpgsql;
            SQL);

        // ── fn_delete_ward ─────────────────────────────────────────────────────
        DB::unprepared('DROP FUNCTION IF EXISTS fn_delete_ward(INTEGER)');
        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION fn_delete_ward(
                p_ward_id INTEGER
            ) RETURNS TEXT AS $$
            BEGIN
                DELETE FROM wards WHERE ward_id = p_ward_id;
                RETURN 'Ward deleted successfully.';
            END;
            $$ LANGUAGE plpgsql;
            SQL);

        // ── trg_fn_check_duplicate_ward — fires BEFORE INSERT OR UPDATE on wards
        DB::unprepared('DROP TRIGGER IF EXISTS trg_check_duplicate_ward ON wards');
        DB::unprepared('DROP FUNCTION IF EXISTS trg_fn_check_duplicate_ward()');
        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION trg_fn_check_duplicate_ward()
            RETURNS TRIGGER AS $$
            BEGIN
                IF TG_OP = 'INSERT' THEN
                    IF EXISTS (
                        SELECT 1 FROM wards
                        WHERE  LOWER(ward_name) = LOWER(NEW.ward_name)
                    ) THEN
                        RAISE EXCEPTION 'A ward named "%" already exists.', NEW.ward_name;
                    END IF;
                ELSE
                    IF EXISTS (
                        SELECT 1 FROM wards
                        WHERE  LOWER(ward_name) = LOWER(NEW.ward_name)
                        AND    ward_id <> OLD.ward_id
                    ) THEN
                        RAISE EXCEPTION 'A ward named "%" already exists.', NEW.ward_name;
                    END IF;
                END IF;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
            SQL);
        DB::unprepared(<<<'SQL'
            CREATE TRIGGER trg_check_duplicate_ward
            BEFORE INSERT OR UPDATE ON wards
            FOR EACH ROW EXECUTE FUNCTION trg_fn_check_duplicate_ward();
            SQL);

        // ── fn_assign_bed_to_patient ───────────────────────────────────────────
        DB::unprepared('DROP FUNCTION IF EXISTS fn_assign_bed_to_patient(INTEGER, INTEGER, VARCHAR, DATE)');
        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION fn_assign_bed_to_patient(
                p_ward_id         INTEGER,
                p_bed_id          INTEGER,
                p_patient_no      VARCHAR,
                p_allocation_date DATE
            ) RETURNS TEXT AS $$
            BEGIN
                IF NOT EXISTS (
                    SELECT 1 FROM beds
                    WHERE bed_id = p_bed_id AND ward_id = p_ward_id AND status = 'Available'
                ) THEN
                    RAISE EXCEPTION 'Bed % in ward % is not available.', p_bed_id, p_ward_id;
                END IF;

                INSERT INTO ward_allocations (patient_no, ward_id, bed_id, allocation_date, created_at, updated_at)
                VALUES (p_patient_no, p_ward_id, p_bed_id, p_allocation_date, NOW(), NOW());

                UPDATE beds SET status = 'Occupied', updated_at = NOW() WHERE bed_id = p_bed_id;

                RETURN 'Bed assigned successfully.';
            END;
            $$ LANGUAGE plpgsql;
            SQL);

        // ── fn_release_bed ─────────────────────────────────────────────────────
        DB::unprepared('DROP FUNCTION IF EXISTS fn_release_bed(INTEGER)');
        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION fn_release_bed(
                p_bed_id INTEGER
            ) RETURNS TEXT AS $$
            BEGIN
                UPDATE ward_allocations
                SET    release_date = CURRENT_DATE, updated_at = NOW()
                WHERE  bed_id = p_bed_id AND release_date IS NULL;

                UPDATE beds SET status = 'Available', updated_at = NOW()
                WHERE  bed_id = p_bed_id;

                RETURN 'Bed released successfully.';
            END;
            $$ LANGUAGE plpgsql;
            SQL);

        // ── trg_fn_check_bed_availability — fires BEFORE INSERT on ward_allocations
        DB::unprepared('DROP TRIGGER IF EXISTS trg_check_bed_availability ON ward_allocations');
        DB::unprepared('DROP FUNCTION IF EXISTS trg_fn_check_bed_availability()');
        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION trg_fn_check_bed_availability()
            RETURNS TRIGGER AS $$
            BEGIN
                IF NOT EXISTS (
                    SELECT 1 FROM beds
                    WHERE bed_id = NEW.bed_id AND status = 'Available'
                ) THEN
                    RAISE EXCEPTION 'Bed % is not available for assignment.', NEW.bed_id;
                END IF;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
            SQL);
        DB::unprepared(<<<'SQL'
            CREATE TRIGGER trg_check_bed_availability
            BEFORE INSERT ON ward_allocations
            FOR EACH ROW EXECUTE FUNCTION trg_fn_check_bed_availability();
            SQL);

        // ── fn_add_bed ─────────────────────────────────────────────────────────
        DB::unprepared('DROP FUNCTION IF EXISTS fn_add_bed(INTEGER, VARCHAR, VARCHAR)');
        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION fn_add_bed(
                p_ward_id    INTEGER,
                p_bed_number VARCHAR,
                p_status     VARCHAR
            ) RETURNS INTEGER AS $$
            DECLARE
                v_bed_id INTEGER;
            BEGIN
                IF p_status NOT IN ('Available', 'Occupied', 'Maintenance') THEN
                    RAISE EXCEPTION 'Invalid status: %. Must be Available, Occupied, or Maintenance.', p_status;
                END IF;

                INSERT INTO beds (ward_id, bed_number, status, created_at, updated_at)
                VALUES (p_ward_id, p_bed_number, p_status, NOW(), NOW())
                RETURNING bed_id INTO v_bed_id;

                RETURN v_bed_id;
            END;
            $$ LANGUAGE plpgsql;
            SQL);

        // ── fn_update_bed_status ───────────────────────────────────────────────
        DB::unprepared('DROP FUNCTION IF EXISTS fn_update_bed_status(INTEGER, VARCHAR)');
        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION fn_update_bed_status(
                p_bed_id INTEGER,
                p_status VARCHAR
            ) RETURNS TEXT AS $$
            BEGIN
                IF p_status NOT IN ('Available', 'Occupied', 'Maintenance') THEN
                    RAISE EXCEPTION 'Invalid status: %. Must be Available, Occupied, or Maintenance.', p_status;
                END IF;

                UPDATE beds SET status = p_status, updated_at = NOW()
                WHERE  bed_id = p_bed_id;

                RETURN 'Bed status updated to ' || p_status || '.';
            END;
            $$ LANGUAGE plpgsql;
            SQL);
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_check_bed_availability ON ward_allocations');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_check_duplicate_ward ON wards');
        DB::unprepared('DROP FUNCTION IF EXISTS fn_update_bed_status(INTEGER, VARCHAR)');
        DB::unprepared('DROP FUNCTION IF EXISTS fn_add_bed(INTEGER, VARCHAR, VARCHAR)');
        DB::unprepared('DROP FUNCTION IF EXISTS trg_fn_check_bed_availability()');
        DB::unprepared('DROP FUNCTION IF EXISTS fn_release_bed(INTEGER)');
        DB::unprepared('DROP FUNCTION IF EXISTS fn_assign_bed_to_patient(INTEGER, INTEGER, VARCHAR, DATE)');
        DB::unprepared('DROP FUNCTION IF EXISTS trg_fn_check_duplicate_ward()');
        DB::unprepared('DROP FUNCTION IF EXISTS fn_delete_ward(INTEGER)');
        DB::unprepared('DROP FUNCTION IF EXISTS fn_update_ward(INTEGER, VARCHAR, VARCHAR, VARCHAR, INTEGER, VARCHAR, VARCHAR)');
        DB::unprepared('DROP FUNCTION IF EXISTS fn_update_ward(INTEGER, VARCHAR, VARCHAR, VARCHAR, INTEGER)');
        DB::unprepared('DROP FUNCTION IF EXISTS fn_add_ward(VARCHAR, VARCHAR, VARCHAR, INTEGER, VARCHAR, VARCHAR)');
        DB::unprepared('DROP FUNCTION IF EXISTS fn_add_ward(VARCHAR, VARCHAR, VARCHAR, INTEGER)');
    }
};

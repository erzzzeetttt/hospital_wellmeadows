<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── fn_generate_bill ─────────────────────────────────────────────────
        DB::unprepared('DROP FUNCTION IF EXISTS fn_generate_bill(VARCHAR, DATE)');
        DB::unprepared(<<<'SQL'
            CREATE FUNCTION fn_generate_bill(p_patient_no VARCHAR(10), p_bill_date DATE)
            RETURNS TEXT AS $$
            DECLARE
                v_bill_id BIGINT;
            BEGIN
                INSERT INTO bills (patient_no, bill_date, total_amount, status)
                VALUES (p_patient_no, p_bill_date, 0, 'Unpaid')
                RETURNING bill_id INTO v_bill_id;

                RETURN v_bill_id::TEXT;
            END;
            $$ LANGUAGE plpgsql;
        SQL);

        // ── fn_add_bill_item ──────────────────────────────────────────────────
        DB::unprepared('DROP FUNCTION IF EXISTS fn_add_bill_item(BIGINT, VARCHAR, INTEGER, NUMERIC)');
        DB::unprepared(<<<'SQL'
            CREATE FUNCTION fn_add_bill_item(
                p_bill_id          BIGINT,
                p_item_description VARCHAR(255),
                p_quantity         INTEGER,
                p_unit_price       NUMERIC(10,2)
            )
            RETURNS TEXT AS $$
            BEGIN
                INSERT INTO bill_items (bill_id, item_description, quantity, unit_price, subtotal)
                VALUES (p_bill_id, p_item_description, p_quantity, p_unit_price, p_quantity * p_unit_price);

                UPDATE bills
                SET total_amount = (
                    SELECT COALESCE(SUM(quantity * unit_price), 0)
                    FROM bill_items
                    WHERE bill_id = p_bill_id
                )
                WHERE bill_id = p_bill_id;

                RETURN 'Success';
            END;
            $$ LANGUAGE plpgsql;
        SQL);

        // ── fn_record_payment ─────────────────────────────────────────────────
        DB::unprepared('DROP FUNCTION IF EXISTS fn_record_payment(BIGINT, DATE, NUMERIC, VARCHAR)');
        DB::unprepared(<<<'SQL'
            CREATE FUNCTION fn_record_payment(
                p_bill_id        BIGINT,
                p_payment_date   DATE,
                p_amount_paid    NUMERIC(10,2),
                p_payment_method VARCHAR(50)
            )
            RETURNS TEXT AS $$
            DECLARE
                v_total NUMERIC(10,2);
                v_paid  NUMERIC(10,2);
            BEGIN
                INSERT INTO payments (bill_id, payment_date, amount_paid, payment_method)
                VALUES (p_bill_id, p_payment_date, p_amount_paid, p_payment_method);

                SELECT total_amount INTO v_total FROM bills WHERE bill_id = p_bill_id;
                SELECT COALESCE(SUM(amount_paid), 0) INTO v_paid FROM payments WHERE bill_id = p_bill_id;

                IF v_paid >= v_total THEN
                    UPDATE bills SET status = 'Paid' WHERE bill_id = p_bill_id;
                ELSE
                    UPDATE bills SET status = 'Partial' WHERE bill_id = p_bill_id;
                END IF;

                RETURN 'Payment recorded';
            END;
            $$ LANGUAGE plpgsql;
        SQL);

        // ── fn_cancel_bill ────────────────────────────────────────────────────
        DB::unprepared('DROP FUNCTION IF EXISTS fn_cancel_bill(BIGINT)');
        DB::unprepared(<<<'SQL'
            CREATE FUNCTION fn_cancel_bill(p_bill_id BIGINT)
            RETURNS TEXT AS $$
            BEGIN
                UPDATE bills SET status = 'Cancelled' WHERE bill_id = p_bill_id;
                RETURN 'Bill cancelled';
            END;
            $$ LANGUAGE plpgsql;
        SQL);

        // ── Trigger: prevent payment on cancelled bill ────────────────────────
        DB::unprepared('DROP FUNCTION IF EXISTS fn_prevent_payment_on_cancelled_bill()');
        DB::unprepared(<<<'SQL'
            CREATE FUNCTION fn_prevent_payment_on_cancelled_bill()
            RETURNS TRIGGER AS $$
            DECLARE
                v_status VARCHAR;
            BEGIN
                SELECT status INTO v_status FROM bills WHERE bill_id = NEW.bill_id;
                IF v_status = 'Cancelled' THEN
                    RAISE EXCEPTION 'Cannot add payment to a cancelled bill';
                END IF;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
        SQL);

        DB::unprepared('DROP TRIGGER IF EXISTS trg_prevent_payment_on_cancelled_bill ON payments');
        DB::unprepared(<<<'SQL'
            CREATE TRIGGER trg_prevent_payment_on_cancelled_bill
                BEFORE INSERT ON payments
                FOR EACH ROW EXECUTE FUNCTION fn_prevent_payment_on_cancelled_bill();
        SQL);
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_prevent_payment_on_cancelled_bill ON payments');
        DB::unprepared('DROP FUNCTION IF EXISTS fn_prevent_payment_on_cancelled_bill()');
        DB::unprepared('DROP FUNCTION IF EXISTS fn_cancel_bill(BIGINT)');
        DB::unprepared('DROP FUNCTION IF EXISTS fn_record_payment(BIGINT, DATE, NUMERIC, VARCHAR)');
        DB::unprepared('DROP FUNCTION IF EXISTS fn_add_bill_item(BIGINT, VARCHAR, INTEGER, NUMERIC)');
        DB::unprepared('DROP FUNCTION IF EXISTS fn_generate_bill(VARCHAR, DATE)');
    }
};

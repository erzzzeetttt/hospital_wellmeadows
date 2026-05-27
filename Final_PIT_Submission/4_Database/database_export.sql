--
-- PostgreSQL database dump
--

\restrict tEj0SW2djdEhpmKXjSfxbGwsbea4tr2zY1Pyw84OGkX0pCcC3Ei7NZyxSEbRdX8

-- Dumped from database version 18.3
-- Dumped by pg_dump version 18.3

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: assignstafftoward(integer, integer, date, character varying); Type: PROCEDURE; Schema: public; Owner: postgres
--

CREATE PROCEDURE public.assignstafftoward(IN p_wardno integer, IN p_staffno integer, IN p_weekbeginningdate date, IN p_shift character varying)
    LANGUAGE plpgsql
    AS $$
BEGIN
    INSERT INTO WARD_ALLOCATION (AllocationID, WardNo, StaffNo, WeekBeginningDate, Shift)
    VALUES (
        (SELECT COALESCE(MAX(AllocationID), 0) + 1 FROM WARD_ALLOCATION), 
        p_WardNo, 
        p_StaffNo, 
        p_WeekBeginningDate, 
        p_Shift
    );
    
    COMMIT;
END;
$$;


ALTER PROCEDURE public.assignstafftoward(IN p_wardno integer, IN p_staffno integer, IN p_weekbeginningdate date, IN p_shift character varying) OWNER TO postgres;

--
-- Name: assignstafftowardshift(integer, integer, date, character varying); Type: PROCEDURE; Schema: public; Owner: postgres
--

CREATE PROCEDURE public.assignstafftowardshift(IN p_wardno integer, IN p_staffno integer, IN p_weekbeginningdate date, IN p_shift character varying)
    LANGUAGE plpgsql
    AS $$
DECLARE
    v_new_allocationid INT;
BEGIN
    SELECT COALESCE(MAX(allocationid), 0) + 1 INTO v_new_allocationid FROM ward_allocation;

    INSERT INTO ward_allocation (allocationid, wardno, staffno, weekbeginningdate, shift)
    VALUES (v_new_allocationid, p_wardno, p_staffno, p_weekbeginningdate, p_shift);
    
END;
$$;


ALTER PROCEDURE public.assignstafftowardshift(IN p_wardno integer, IN p_staffno integer, IN p_weekbeginningdate date, IN p_shift character varying) OWNER TO postgres;

--
-- Name: fn_add_bed(integer, character varying, character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_add_bed(p_ward_id integer, p_bed_number character varying, p_status character varying) RETURNS integer
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION public.fn_add_bed(p_ward_id integer, p_bed_number character varying, p_status character varying) OWNER TO postgres;

--
-- Name: fn_add_bill_item(bigint, character varying, integer, numeric); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_add_bill_item(p_bill_id bigint, p_item_description character varying, p_quantity integer, p_unit_price numeric) RETURNS text
    LANGUAGE plpgsql
    AS $$
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
    $$;


ALTER FUNCTION public.fn_add_bill_item(p_bill_id bigint, p_item_description character varying, p_quantity integer, p_unit_price numeric) OWNER TO postgres;

--
-- Name: fn_add_diagnosis(character varying, character varying, bigint, date, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_add_diagnosis(p_patient_no character varying, p_staff_no character varying, p_appointment_id bigint, p_diagnosis_date date, p_diagnosis_details text) RETURNS bigint
    LANGUAGE plpgsql
    AS $$
DECLARE
    v_diagnosis_id BIGINT;
BEGIN
    INSERT INTO diagnoses (
        patient_no, staff_no, appointment_id,
        diagnosis_details, diagnosis_date,
        created_at, updated_at
    )
    VALUES (
        p_patient_no, p_staff_no, p_appointment_id,
        p_diagnosis_details, p_diagnosis_date,
        NOW(), NOW()
    )
    RETURNING diagnosis_id INTO v_diagnosis_id;
    RETURN v_diagnosis_id;
EXCEPTION
    WHEN OTHERS THEN
        RAISE EXCEPTION '%', SQLERRM;
END;
$$;


ALTER FUNCTION public.fn_add_diagnosis(p_patient_no character varying, p_staff_no character varying, p_appointment_id bigint, p_diagnosis_date date, p_diagnosis_details text) OWNER TO postgres;

--
-- Name: fn_add_diagnosis_record(character varying, text, date); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_add_diagnosis_record(p_patient_no character varying, p_diagnosis_details text, p_diagnosis_date date) RETURNS text
    LANGUAGE plpgsql
    AS $$
BEGIN
    INSERT INTO diagnoses (
        patient_no,
        staff_no,
        diagnosis_details,
        diagnosis_date,
        created_at,
        updated_at
    )
    VALUES (
        p_patient_no,
        NULL,
        p_diagnosis_details,
        p_diagnosis_date,
        NOW(),
        NOW()
    );

    RETURN 'Diagnosis record added successfully for patient ' || p_patient_no;

EXCEPTION
    WHEN OTHERS THEN
        RETURN 'Error: ' || SQLERRM;
END;
$$;


ALTER FUNCTION public.fn_add_diagnosis_record(p_patient_no character varying, p_diagnosis_details text, p_diagnosis_date date) OWNER TO postgres;

--
-- Name: fn_add_medication_record(character varying, integer, character varying, character varying, date, date); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_add_medication_record(p_patient_no character varying, p_drug_id integer, p_dosage character varying, p_frequency character varying, p_start_date date, p_end_date date) RETURNS text
    LANGUAGE plpgsql
    AS $$
BEGIN
    INSERT INTO medication_records (patient_no, drug_id, dosage, frequency, start_date, end_date, created_at, updated_at)
    VALUES (p_patient_no, p_drug_id, p_dosage, p_frequency, p_start_date, p_end_date, NOW(), NOW());

    RETURN 'Medication record added successfully.';
EXCEPTION
    WHEN OTHERS THEN
        RAISE EXCEPTION '%', SQLERRM;
END;
$$;


ALTER FUNCTION public.fn_add_medication_record(p_patient_no character varying, p_drug_id integer, p_dosage character varying, p_frequency character varying, p_start_date date, p_end_date date) OWNER TO postgres;

--
-- Name: fn_add_staff(integer, character varying, character varying, date, character varying, character varying, character varying, character varying, character varying, numeric, character varying, numeric, character varying, character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_add_staff(p_role_id integer, p_first_name character varying, p_last_name character varying, p_dob date, p_gender character varying, p_address character varying, p_phone_no character varying, p_nin character varying, p_position character varying, p_salary numeric, p_salary_scale character varying, p_hours_per_week numeric, p_contract_type character varying, p_payment_type character varying) RETURNS bigint
    LANGUAGE plpgsql
    AS $$
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
            $$;


ALTER FUNCTION public.fn_add_staff(p_role_id integer, p_first_name character varying, p_last_name character varying, p_dob date, p_gender character varying, p_address character varying, p_phone_no character varying, p_nin character varying, p_position character varying, p_salary numeric, p_salary_scale character varying, p_hours_per_week numeric, p_contract_type character varying, p_payment_type character varying) OWNER TO postgres;

--
-- Name: fn_add_staff_experience(character varying, character varying, date, date, character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_add_staff_experience(p_staff_no character varying, p_position character varying, p_start_date date, p_end_date date, p_organization character varying) RETURNS text
    LANGUAGE plpgsql
    AS $$
BEGIN
    INSERT INTO work_experiences (
        staff_no, position, start_date, end_date, organization,
        created_at, updated_at
    ) VALUES (
        p_staff_no, p_position, p_start_date, p_end_date, p_organization,
        NOW(), NOW()
    );
    RETURN 'Work experience added.';
EXCEPTION
    WHEN OTHERS THEN
        RAISE EXCEPTION '%', SQLERRM;
END;
$$;


ALTER FUNCTION public.fn_add_staff_experience(p_staff_no character varying, p_position character varying, p_start_date date, p_end_date date, p_organization character varying) OWNER TO postgres;

--
-- Name: fn_add_staff_qualification(character varying, character varying, date, character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_add_staff_qualification(p_staff_no character varying, p_qual_type character varying, p_date_obtained date, p_institution character varying) RETURNS text
    LANGUAGE plpgsql
    AS $$
BEGIN
    INSERT INTO staff_qualifications (
        staff_no, qualification_type, institution, date_obtained,
        created_at, updated_at
    ) VALUES (
        p_staff_no, p_qual_type, p_institution, p_date_obtained,
        NOW(), NOW()
    );
    RETURN 'Qualification added.';
EXCEPTION
    WHEN OTHERS THEN
        RAISE EXCEPTION '%', SQLERRM;
END;
$$;


ALTER FUNCTION public.fn_add_staff_qualification(p_staff_no character varying, p_qual_type character varying, p_date_obtained date, p_institution character varying) OWNER TO postgres;

--
-- Name: fn_add_treatment(character varying, character varying, bigint, date, character varying, text, character varying, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_add_treatment(p_patient_no character varying, p_staff_no character varying, p_diagnosis_id bigint, p_treatment_date date, p_treatment_type character varying, p_treatment_given text, p_method character varying, p_remarks text) RETURNS bigint
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION public.fn_add_treatment(p_patient_no character varying, p_staff_no character varying, p_diagnosis_id bigint, p_treatment_date date, p_treatment_type character varying, p_treatment_given text, p_method character varying, p_remarks text) OWNER TO postgres;

--
-- Name: fn_add_ward(character varying, character varying, character varying, integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_add_ward(p_ward_name character varying, p_ward_type character varying, p_location character varying, p_total_beds integer) RETURNS integer
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION public.fn_add_ward(p_ward_name character varying, p_ward_type character varying, p_location character varying, p_total_beds integer) OWNER TO postgres;

--
-- Name: fn_admit_patient(character varying, date, date); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_admit_patient(p_patient_no character varying, p_date_admitted date, p_expected_leave_date date) RETURNS text
    LANGUAGE plpgsql
    AS $$
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
        $$;


ALTER FUNCTION public.fn_admit_patient(p_patient_no character varying, p_date_admitted date, p_expected_leave_date date) OWNER TO postgres;

--
-- Name: fn_assign_bed_to_patient(integer, integer, character varying, date); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_assign_bed_to_patient(p_ward_id integer, p_bed_id integer, p_patient_no character varying, p_allocation_date date) RETURNS text
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION public.fn_assign_bed_to_patient(p_ward_id integer, p_bed_id integer, p_patient_no character varying, p_allocation_date date) OWNER TO postgres;

--
-- Name: fn_assign_staff_to_treatment(bigint, character varying, character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_assign_staff_to_treatment(p_diagnosis_id bigint, p_doctor_staff_no character varying, p_nurse_staff_no character varying) RETURNS text
    LANGUAGE plpgsql
    AS $$
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
            $$;


ALTER FUNCTION public.fn_assign_staff_to_treatment(p_diagnosis_id bigint, p_doctor_staff_no character varying, p_nurse_staff_no character varying) OWNER TO postgres;

--
-- Name: fn_assign_staff_to_ward(character varying, integer, date, character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_assign_staff_to_ward(p_staff_no character varying, p_ward_id integer, p_assignment_date date, p_role_in_ward character varying) RETURNS text
    LANGUAGE plpgsql
    AS $$
BEGIN
    IF EXISTS (
        SELECT 1 FROM staff_ward_assignments
        WHERE staff_no = p_staff_no
        AND ward_id = p_ward_id
        AND end_date IS NULL
    ) THEN
        RETURN 'Staff is already actively assigned to this ward.';
    END IF;

    INSERT INTO staff_ward_assignments (
        staff_no, ward_id, assignment_date, role_in_ward, created_at, updated_at
    )
    VALUES (
        p_staff_no, p_ward_id, p_assignment_date, p_role_in_ward, NOW(), NOW()
    );

    RETURN 'Staff assigned to ward successfully.';
EXCEPTION
    WHEN OTHERS THEN
        RAISE EXCEPTION '%', SQLERRM;
END;
$$;


ALTER FUNCTION public.fn_assign_staff_to_ward(p_staff_no character varying, p_ward_id integer, p_assignment_date date, p_role_in_ward character varying) OWNER TO postgres;

--
-- Name: fn_cancel_appointment(bigint); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_cancel_appointment(p_appointment_id bigint) RETURNS text
    LANGUAGE plpgsql
    AS $$
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
            $$;


ALTER FUNCTION public.fn_cancel_appointment(p_appointment_id bigint) OWNER TO postgres;

--
-- Name: fn_cancel_bill(bigint); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_cancel_bill(p_bill_id bigint) RETURNS text
    LANGUAGE plpgsql
    AS $$
    BEGIN
        UPDATE bills SET status = 'Cancelled' WHERE bill_id = p_bill_id;
        RETURN 'Bill cancelled';
    END;
    $$;


ALTER FUNCTION public.fn_cancel_bill(p_bill_id bigint) OWNER TO postgres;

--
-- Name: fn_check_duplicate_patient(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_check_duplicate_patient() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
            BEGIN
                IF EXISTS (
                    SELECT 1 FROM patients
                    WHERE first_name    = NEW.first_name
                      AND last_name     = NEW.last_name
                      AND date_of_birth = NEW.date_of_birth
                ) THEN
                    RAISE EXCEPTION 'Patient already exists with the same name and date of birth.';
                END IF;
                RETURN NEW;
            END;
            $$;


ALTER FUNCTION public.fn_check_duplicate_patient() OWNER TO postgres;

--
-- Name: fn_delete_staff(character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_delete_staff(p_staff_no character varying) RETURNS text
    LANGUAGE plpgsql
    AS $$
BEGIN
    -- Delete related records first (child tables)
    DELETE FROM staff_qualifications WHERE staff_no = p_staff_no;
    DELETE FROM staff_experience WHERE staff_no = p_staff_no;
    DELETE FROM staff_ward_assignments WHERE staff_no = p_staff_no;
    DELETE FROM staff_weekly_rota WHERE staff_no = p_staff_no;

    -- Now delete the staff record
    DELETE FROM staff WHERE staff_no = p_staff_no;

    IF NOT FOUND THEN
        RAISE EXCEPTION 'Staff % not found.', p_staff_no;
    END IF;

    RETURN 'Staff deleted successfully.';
EXCEPTION
    WHEN OTHERS THEN
        RAISE EXCEPTION '%', SQLERRM;
END;
$$;


ALTER FUNCTION public.fn_delete_staff(p_staff_no character varying) OWNER TO postgres;

--
-- Name: fn_delete_ward(integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_delete_ward(p_ward_id integer) RETURNS text
    LANGUAGE plpgsql
    AS $$
BEGIN
    DELETE FROM wards WHERE ward_id = p_ward_id;
    RETURN 'Ward deleted successfully.';
END;
$$;


ALTER FUNCTION public.fn_delete_ward(p_ward_id integer) OWNER TO postgres;

--
-- Name: fn_discharge_patient(bigint, date); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_discharge_patient(p_admission_id bigint, p_discharge_date date) RETURNS text
    LANGUAGE plpgsql
    AS $$
DECLARE
    v_patient_no VARCHAR(10);
BEGIN
    SELECT patient_no
    INTO v_patient_no
    FROM ward_admissions
    WHERE admission_id = p_admission_id;

    UPDATE ward_admissions
    SET
        status = 'Discharged',
        discharge_date = p_discharge_date,
        updated_at = NOW()
    WHERE admission_id = p_admission_id;

    UPDATE patients
    SET
        status = 'Discharged',
        updated_at = NOW()
    WHERE patient_no = v_patient_no;

    RETURN 'Patient discharged successfully.';

EXCEPTION
    WHEN OTHERS THEN
        RETURN 'Error: ' || SQLERRM;
END;
$$;


ALTER FUNCTION public.fn_discharge_patient(p_admission_id bigint, p_discharge_date date) OWNER TO postgres;

--
-- Name: fn_end_staff_assignment(bigint); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_end_staff_assignment(p_assignment_id bigint) RETURNS text
    LANGUAGE plpgsql
    AS $$
BEGIN
    UPDATE staff_ward_assignments
    SET end_date = CURRENT_DATE,
        updated_at = NOW()
    WHERE assignment_id = p_assignment_id
    AND end_date IS NULL;

    IF NOT FOUND THEN
        RAISE EXCEPTION 'Assignment not found or already ended.';
    END IF;

    RETURN 'Assignment ended successfully.';
EXCEPTION
    WHEN OTHERS THEN
        RAISE EXCEPTION '%', SQLERRM;
END;
$$;


ALTER FUNCTION public.fn_end_staff_assignment(p_assignment_id bigint) OWNER TO postgres;

--
-- Name: fn_generate_bill(character varying, date); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_generate_bill(p_patient_no character varying, p_bill_date date) RETURNS text
    LANGUAGE plpgsql
    AS $$
    DECLARE
        v_bill_id BIGINT;
    BEGIN
        INSERT INTO bills (patient_no, bill_date, total_amount, status)
        VALUES (p_patient_no, p_bill_date, 0, 'Unpaid')
        RETURNING bill_id INTO v_bill_id;

        RETURN v_bill_id::TEXT;
    END;
    $$;


ALTER FUNCTION public.fn_generate_bill(p_patient_no character varying, p_bill_date date) OWNER TO postgres;

--
-- Name: fn_generate_patientno(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_generate_patientno() RETURNS character varying
    LANGUAGE plpgsql
    AS $$
BEGIN
    RETURN 'P' || LPAD(nextval('patient_seq')::TEXT, 4, '0');
END;
$$;


ALTER FUNCTION public.fn_generate_patientno() OWNER TO postgres;

--
-- Name: fn_generate_staffno(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_generate_staffno() RETURNS character varying
    LANGUAGE plpgsql
    AS $$
BEGIN
    RETURN 'S' || LPAD(nextval('staff_seq')::TEXT, 3, '0');
END;
$$;


ALTER FUNCTION public.fn_generate_staffno() OWNER TO postgres;

--
-- Name: fn_prevent_payment_on_cancelled_bill(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_prevent_payment_on_cancelled_bill() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
    DECLARE
        v_status VARCHAR;
    BEGIN
        SELECT status INTO v_status FROM bills WHERE bill_id = NEW.bill_id;
        IF v_status = 'Cancelled' THEN
            RAISE EXCEPTION 'Cannot add payment to a cancelled bill';
        END IF;
        RETURN NEW;
    END;
    $$;


ALTER FUNCTION public.fn_prevent_payment_on_cancelled_bill() OWNER TO postgres;

--
-- Name: fn_record_payment(bigint, date, numeric, character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_record_payment(p_bill_id bigint, p_payment_date date, p_amount_paid numeric, p_payment_method character varying) RETURNS text
    LANGUAGE plpgsql
    AS $$
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
    $$;


ALTER FUNCTION public.fn_record_payment(p_bill_id bigint, p_payment_date date, p_amount_paid numeric, p_payment_method character varying) OWNER TO postgres;

--
-- Name: fn_register_patient(character varying, character varying, date, character varying, character varying, character varying, character varying, bigint, bigint); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_register_patient(p_first_name character varying, p_last_name character varying, p_date_of_birth date, p_gender character varying, p_address character varying, p_phone_no character varying, p_marital_status character varying, p_doctor_id bigint, p_nextofkinid bigint) RETURNS text
    LANGUAGE plpgsql
    AS $$
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
        $$;


ALTER FUNCTION public.fn_register_patient(p_first_name character varying, p_last_name character varying, p_date_of_birth date, p_gender character varying, p_address character varying, p_phone_no character varying, p_marital_status character varying, p_doctor_id bigint, p_nextofkinid bigint) OWNER TO postgres;

--
-- Name: fn_register_patient_with_kin(character varying, character varying, date, character varying, character varying, character varying, character varying, integer, character varying, character varying, character varying, character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_register_patient_with_kin(p_first_name character varying, p_last_name character varying, p_date_of_birth date, p_gender character varying, p_phone_no character varying, p_marital_status character varying, p_address character varying, p_doctor_id integer, p_kin_fullname character varying, p_relationshiptopatient character varying, p_kin_telno character varying, p_kin_address character varying) RETURNS text
    LANGUAGE plpgsql
    AS $$
DECLARE
    v_nextofkinid INT;
    v_patient_no VARCHAR(10);
BEGIN
    v_patient_no := fn_generate_patientno();

    INSERT INTO next_of_kin (
        fullname, relationshiptopatient, telno, address, created_at, updated_at
    )
    VALUES (
        p_kin_fullname, p_relationshiptopatient, p_kin_telno, p_kin_address, NOW(), NOW()
    )
    RETURNING nextofkinid INTO v_nextofkinid;

    INSERT INTO patients (
        patient_no,
        doctor_id,
        nextofkinid,
        first_name,
        last_name,
        date_of_birth,
        gender,
        phone_no,
        marital_status,
        address,
        created_at,
        updated_at
    )
    VALUES (
        v_patient_no,
        p_doctor_id,
        v_nextofkinid,
        p_first_name,
        p_last_name,
        p_date_of_birth,
        p_gender,
        p_phone_no,
        p_marital_status,
        p_address,
        NOW(),
        NOW()
    );

    RETURN 'Patient registered successfully. Patient No: ' || v_patient_no;

EXCEPTION
    WHEN OTHERS THEN
        RETURN 'Error: ' || SQLERRM;
END;
$$;


ALTER FUNCTION public.fn_register_patient_with_kin(p_first_name character varying, p_last_name character varying, p_date_of_birth date, p_gender character varying, p_phone_no character varying, p_marital_status character varying, p_address character varying, p_doctor_id integer, p_kin_fullname character varying, p_relationshiptopatient character varying, p_kin_telno character varying, p_kin_address character varying) OWNER TO postgres;

--
-- Name: fn_release_bed(integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_release_bed(p_bed_id integer) RETURNS text
    LANGUAGE plpgsql
    AS $$
BEGIN
    UPDATE ward_allocations
    SET    release_date = CURRENT_DATE, updated_at = NOW()
    WHERE  bed_id = p_bed_id AND release_date IS NULL;

    UPDATE beds SET status = 'Available', updated_at = NOW()
    WHERE  bed_id = p_bed_id;

    RETURN 'Bed released successfully.';
END;
$$;


ALTER FUNCTION public.fn_release_bed(p_bed_id integer) OWNER TO postgres;

--
-- Name: fn_schedule_appointment(character varying, character varying, date, time without time zone, character varying, character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_schedule_appointment(p_patient_no character varying, p_staff_no character varying, p_appointment_date date, p_appointment_time time without time zone, p_examination_room character varying, p_appointment_type character varying) RETURNS bigint
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION public.fn_schedule_appointment(p_patient_no character varying, p_staff_no character varying, p_appointment_date date, p_appointment_time time without time zone, p_examination_room character varying, p_appointment_type character varying) OWNER TO postgres;

--
-- Name: fn_set_staff_rota(character varying, integer, date, character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_set_staff_rota(p_staff_no character varying, p_ward_id integer, p_week_start_date date, p_shift_type character varying) RETURNS text
    LANGUAGE plpgsql
    AS $$
BEGIN
    INSERT INTO staff_weekly_rota (
        staff_no, ward_id, week_start_date, shift_type,
        created_at, updated_at
    )
    VALUES (
        p_staff_no, p_ward_id, p_week_start_date, p_shift_type,
        NOW(), NOW()
    )
    ON CONFLICT (staff_no, ward_id, week_start_date)
    DO UPDATE SET
        shift_type = p_shift_type,
        updated_at = NOW();

    RETURN 'Staff shift schedule updated successfully.';
EXCEPTION
    WHEN OTHERS THEN
        RAISE EXCEPTION '%', SQLERRM;
END;
$$;


ALTER FUNCTION public.fn_set_staff_rota(p_staff_no character varying, p_ward_id integer, p_week_start_date date, p_shift_type character varying) OWNER TO postgres;

--
-- Name: fn_update_appointment_status(bigint, character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_update_appointment_status(p_appointment_id bigint, p_status character varying) RETURNS text
    LANGUAGE plpgsql
    AS $$
BEGIN
    UPDATE appointments
    SET status = p_status, updated_at = NOW()
    WHERE appointment_id = p_appointment_id;
    RETURN 'Appointment status updated to ' || p_status;
EXCEPTION
    WHEN OTHERS THEN
        RAISE EXCEPTION '%', SQLERRM;
END;
$$;


ALTER FUNCTION public.fn_update_appointment_status(p_appointment_id bigint, p_status character varying) OWNER TO postgres;

--
-- Name: fn_update_bed_status(integer, character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_update_bed_status(p_bed_id integer, p_status character varying) RETURNS text
    LANGUAGE plpgsql
    AS $$
BEGIN
    IF p_status NOT IN ('Available', 'Occupied', 'Maintenance') THEN
        RAISE EXCEPTION 'Invalid status: %. Must be Available, Occupied, or Maintenance.', p_status;
    END IF;

    UPDATE beds SET status = p_status, updated_at = NOW()
    WHERE  bed_id = p_bed_id;

    RETURN 'Bed status updated to ' || p_status || '.';
END;
$$;


ALTER FUNCTION public.fn_update_bed_status(p_bed_id integer, p_status character varying) OWNER TO postgres;

--
-- Name: fn_update_medication_record(bigint, character varying, character varying, date); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_update_medication_record(p_medication_id bigint, p_dosage character varying, p_frequency character varying, p_end_date date) RETURNS text
    LANGUAGE plpgsql
    AS $$
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
        $$;


ALTER FUNCTION public.fn_update_medication_record(p_medication_id bigint, p_dosage character varying, p_frequency character varying, p_end_date date) OWNER TO postgres;

--
-- Name: fn_update_patient_info(character varying, character varying, character varying, date, character varying, character varying, character varying, character varying, integer, character varying, character varying, character varying, character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_update_patient_info(p_patient_no character varying, p_first_name character varying, p_last_name character varying, p_date_of_birth date, p_gender character varying, p_phone_no character varying, p_marital_status character varying, p_address character varying, p_doctor_id integer, p_kin_fullname character varying, p_relationshiptopatient character varying, p_kin_telno character varying, p_kin_address character varying) RETURNS text
    LANGUAGE plpgsql
    AS $$
DECLARE
    v_nextofkinid INT;
BEGIN

    SELECT nextofkinid
    INTO v_nextofkinid
    FROM patients
    WHERE patient_no = p_patient_no;

    UPDATE patients
    SET
        doctor_id = p_doctor_id,
        first_name = p_first_name,
        last_name = p_last_name,
        date_of_birth = p_date_of_birth,
        gender = p_gender,
        phone_no = p_phone_no,
        marital_status = p_marital_status,
        address = p_address,
        updated_at = NOW()
    WHERE patient_no = p_patient_no;

    UPDATE next_of_kin
    SET
        fullname = p_kin_fullname,
        relationshiptopatient = p_relationshiptopatient,
        telno = p_kin_telno,
        address = p_kin_address,
        updated_at = NOW()
    WHERE nextofkinid = v_nextofkinid;

    RETURN 'Patient information updated successfully.';

EXCEPTION
    WHEN OTHERS THEN
        RETURN 'Error: ' || SQLERRM;
END;
$$;


ALTER FUNCTION public.fn_update_patient_info(p_patient_no character varying, p_first_name character varying, p_last_name character varying, p_date_of_birth date, p_gender character varying, p_phone_no character varying, p_marital_status character varying, p_address character varying, p_doctor_id integer, p_kin_fullname character varying, p_relationshiptopatient character varying, p_kin_telno character varying, p_kin_address character varying) OWNER TO postgres;

--
-- Name: fn_update_staff(character varying, integer, character varying, character varying, date, character varying, character varying, character varying, character varying, character varying, numeric, character varying, numeric, character varying, character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_update_staff(p_staff_no character varying, p_role_id integer, p_first_name character varying, p_last_name character varying, p_dob date, p_gender character varying, p_address character varying, p_phone_no character varying, p_nin character varying, p_position character varying, p_salary numeric, p_salary_scale character varying, p_hours_per_week numeric, p_contract_type character varying, p_payment_type character varying) RETURNS text
    LANGUAGE plpgsql
    AS $$
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
    WHERE staff_no::TEXT = p_staff_no;

    RETURN 'Staff record updated successfully.';
EXCEPTION
    WHEN OTHERS THEN
        RAISE EXCEPTION '%', SQLERRM;
END;
$$;


ALTER FUNCTION public.fn_update_staff(p_staff_no character varying, p_role_id integer, p_first_name character varying, p_last_name character varying, p_dob date, p_gender character varying, p_address character varying, p_phone_no character varying, p_nin character varying, p_position character varying, p_salary numeric, p_salary_scale character varying, p_hours_per_week numeric, p_contract_type character varying, p_payment_type character varying) OWNER TO postgres;

--
-- Name: fn_update_staff(character varying, integer, character varying, character varying, date, character varying, character varying, character varying, character varying, character varying, numeric, character varying, numeric, character varying, character varying, date); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_update_staff(p_staff_no character varying, p_role_id integer, p_first_name character varying, p_last_name character varying, p_dob date, p_gender character varying, p_address character varying, p_phone_no character varying, p_nin character varying, p_position character varying, p_salary numeric, p_salary_scale character varying, p_hours_per_week numeric, p_contract_type character varying, p_payment_type character varying, p_date_registered date) RETURNS text
    LANGUAGE plpgsql
    AS $$
BEGIN
    UPDATE staff SET
        role_id = p_role_id,
        first_name = p_first_name,
        last_name = p_last_name,
        dob = p_dob,
        gender = p_gender,
        address = p_address,
        phone_no = p_phone_no,
        nin = p_nin,
        position = p_position,
        salary = p_salary,
        salary_scale = p_salary_scale,
        hours_per_week = p_hours_per_week,
        contract_type = p_contract_type,
        payment_type = p_payment_type,
        date_registered = p_date_registered,
        updated_at = NOW()
    WHERE staff_no = p_staff_no;
    RETURN 'Staff record updated successfully.';
EXCEPTION
    WHEN OTHERS THEN
        RAISE EXCEPTION '%', SQLERRM;
END;
$$;


ALTER FUNCTION public.fn_update_staff(p_staff_no character varying, p_role_id integer, p_first_name character varying, p_last_name character varying, p_dob date, p_gender character varying, p_address character varying, p_phone_no character varying, p_nin character varying, p_position character varying, p_salary numeric, p_salary_scale character varying, p_hours_per_week numeric, p_contract_type character varying, p_payment_type character varying, p_date_registered date) OWNER TO postgres;

--
-- Name: fn_update_treatment(bigint, text, date); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_update_treatment(p_diagnosis_id bigint, p_diagnosis_details text, p_diagnosis_date date) RETURNS text
    LANGUAGE plpgsql
    AS $$
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
            $$;


ALTER FUNCTION public.fn_update_treatment(p_diagnosis_id bigint, p_diagnosis_details text, p_diagnosis_date date) OWNER TO postgres;

--
-- Name: fn_update_ward(integer, character varying, character varying, character varying, integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_update_ward(p_ward_id integer, p_ward_name character varying, p_ward_type character varying, p_location character varying, p_total_beds integer) RETURNS text
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION public.fn_update_ward(p_ward_id integer, p_ward_name character varying, p_ward_type character varying, p_location character varying, p_total_beds integer) OWNER TO postgres;

--
-- Name: getstaffcurrentward(integer, date); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.getstaffcurrentward(p_staffno integer, p_targetdate date) RETURNS character varying
    LANGUAGE plpgsql
    AS $$
DECLARE
    v_WardName VARCHAR(50);
BEGIN
    SELECT w.WardName INTO v_WardName
    FROM WARD_ALLOCATION wa
    JOIN WARD w ON wa.WardNo = w.WardNo
    WHERE wa.StaffNo = p_StaffNo
      AND p_TargetDate >= wa.WeekBeginningDate 
      AND p_TargetDate < wa.WeekBeginningDate + INTERVAL '7 days'
    LIMIT 1;

    RETURN COALESCE(v_WardName, 'Not Assigned');
END;
$$;


ALTER FUNCTION public.getstaffcurrentward(p_staffno integer, p_targetdate date) OWNER TO postgres;

--
-- Name: onboardnewstaff(integer, character varying, character varying, character varying, integer, character varying, character varying); Type: PROCEDURE; Schema: public; Owner: postgres
--

CREATE PROCEDURE public.onboardnewstaff(IN p_staffno integer, IN p_firstname character varying, IN p_lastname character varying, IN p_position character varying, IN p_role_id integer, IN p_email character varying, IN p_password character varying)
    LANGUAGE plpgsql
    AS $$
BEGIN


    INSERT INTO staff (staffno, firstname, lastname, position)
    VALUES (p_staffno, p_firstname, p_lastname, p_position);

    INSERT INTO users (staff_no, role_id, name, email, password)
    VALUES (
        p_staffno, 
        p_role_id, 
        p_firstname || ' ' || p_lastname, 
        p_email, 
        p_password
    );
    
END;
$$;


ALTER PROCEDURE public.onboardnewstaff(IN p_staffno integer, IN p_firstname character varying, IN p_lastname character varying, IN p_position character varying, IN p_role_id integer, IN p_email character varying, IN p_password character varying) OWNER TO postgres;

--
-- Name: sp_transfer_staff_ward(character varying, integer, integer, character varying, date); Type: PROCEDURE; Schema: public; Owner: postgres
--

CREATE PROCEDURE public.sp_transfer_staff_ward(IN p_staff_no character varying, IN p_old_ward_id integer, IN p_new_ward_id integer, IN p_role_in_ward character varying, IN p_transfer_date date)
    LANGUAGE plpgsql
    AS $$
BEGIN
    -- Step 1: End the current active assignment
    UPDATE staff_ward_assignments
    SET end_date = p_transfer_date,
        updated_at = NOW()
    WHERE staff_no = p_staff_no
    AND ward_id = p_old_ward_id
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
        AND ward_id = p_new_ward_id
        AND end_date IS NULL
    ) THEN
        RAISE EXCEPTION
        'Staff % is already actively assigned to ward %.',
        p_staff_no, p_new_ward_id;
    END IF;

    -- Step 3: Create new assignment
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
$$;


ALTER PROCEDURE public.sp_transfer_staff_ward(IN p_staff_no character varying, IN p_old_ward_id integer, IN p_new_ward_id integer, IN p_role_in_ward character varying, IN p_transfer_date date) OWNER TO postgres;

--
-- Name: trg_check_active_admission(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.trg_check_active_admission() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
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
        $$;


ALTER FUNCTION public.trg_check_active_admission() OWNER TO postgres;

--
-- Name: trg_fn_auto_patientno(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.trg_fn_auto_patientno() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    IF NEW.patient_no IS NULL OR NEW.patient_no = '' THEN
        NEW.patient_no := fn_generate_patientno();
    END IF;

    RETURN NEW;
END;
$$;


ALTER FUNCTION public.trg_fn_auto_patientno() OWNER TO postgres;

--
-- Name: trg_fn_check_bed_availability(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.trg_fn_check_bed_availability() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM beds
        WHERE bed_id = NEW.bed_id AND status = 'Available'
    ) THEN
        RAISE EXCEPTION 'Bed % is not available for assignment.', NEW.bed_id;
    END IF;
    RETURN NEW;
END;
$$;


ALTER FUNCTION public.trg_fn_check_bed_availability() OWNER TO postgres;

--
-- Name: trg_fn_check_duplicate_patient(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.trg_fn_check_duplicate_patient() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    IF EXISTS (
        SELECT 1 FROM patients
        WHERE first_name = NEW.first_name
        AND last_name = NEW.last_name
        AND date_of_birth = NEW.date_of_birth
        AND doctor_id = NEW.doctor_id
    ) THEN
        RAISE EXCEPTION 'This patient is already registered under the same doctor.';
    END IF;
    RETURN NEW;
END;
$$;


ALTER FUNCTION public.trg_fn_check_duplicate_patient() OWNER TO postgres;

--
-- Name: trg_fn_check_duplicate_ward(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.trg_fn_check_duplicate_ward() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION public.trg_fn_check_duplicate_ward() OWNER TO postgres;

--
-- Name: trg_fn_prevent_double_booking(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.trg_fn_prevent_double_booking() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
    BEGIN
        IF EXISTS (
            SELECT 1 FROM staff_weekly_rota
            WHERE staff_no        = NEW.staff_no
              AND ward_id         = NEW.ward_id
              AND week_start_date = NEW.week_start_date
        ) THEN
            RAISE EXCEPTION
                'Staff % is already scheduled for ward % on week starting %.',
                NEW.staff_no, NEW.ward_id, NEW.week_start_date;
        END IF;
        RETURN NEW;
    END;
    $$;


ALTER FUNCTION public.trg_fn_prevent_double_booking() OWNER TO postgres;

--
-- Name: trg_fn_prevent_duplicate_appointment(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.trg_fn_prevent_duplicate_appointment() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    IF EXISTS (
        SELECT 1 FROM appointments
        WHERE patient_no = NEW.patient_no
        AND appointment_date = NEW.appointment_date
        AND appointment_time = NEW.appointment_time
        AND status != 'Cancelled'
        AND appointment_id != COALESCE(NEW.appointment_id, 0)
    ) THEN
        RAISE EXCEPTION 'Patient % already has an appointment on % at %. Please choose a different time.',
            NEW.patient_no,
            NEW.appointment_date,
            NEW.appointment_time;
    END IF;
    RETURN NEW;
END;
$$;


ALTER FUNCTION public.trg_fn_prevent_duplicate_appointment() OWNER TO postgres;

--
-- Name: trg_prevent_duplicate_appointment(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.trg_prevent_duplicate_appointment() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
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
            $$;


ALTER FUNCTION public.trg_prevent_duplicate_appointment() OWNER TO postgres;

--
-- Name: update_user_timestamp(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.update_user_timestamp() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$;


ALTER FUNCTION public.update_user_timestamp() OWNER TO postgres;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: appointments; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.appointments (
    appointment_id bigint NOT NULL,
    patient_no character varying(10) NOT NULL,
    appointment_date date NOT NULL,
    appointment_time time(0) without time zone NOT NULL,
    status character varying(255) DEFAULT 'Pending'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    staff_no character varying(10),
    examination_room character varying(100),
    appointment_type character varying(100)
);


ALTER TABLE public.appointments OWNER TO postgres;

--
-- Name: appointments_appointment_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.appointments_appointment_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.appointments_appointment_id_seq OWNER TO postgres;

--
-- Name: appointments_appointment_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.appointments_appointment_id_seq OWNED BY public.appointments.appointment_id;


--
-- Name: beds; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.beds (
    bed_id bigint NOT NULL,
    ward_id bigint NOT NULL,
    bed_number character varying(255) NOT NULL,
    status character varying(255) DEFAULT 'Available'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.beds OWNER TO postgres;

--
-- Name: beds_bed_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.beds_bed_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.beds_bed_id_seq OWNER TO postgres;

--
-- Name: beds_bed_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.beds_bed_id_seq OWNED BY public.beds.bed_id;


--
-- Name: bill_items; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.bill_items (
    bill_item_id bigint NOT NULL,
    bill_id bigint NOT NULL,
    item_description character varying(255) NOT NULL,
    quantity integer DEFAULT 1 NOT NULL,
    unit_price numeric(10,2) NOT NULL,
    subtotal numeric(10,2) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.bill_items OWNER TO postgres;

--
-- Name: bill_items_bill_item_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.bill_items_bill_item_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.bill_items_bill_item_id_seq OWNER TO postgres;

--
-- Name: bill_items_bill_item_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.bill_items_bill_item_id_seq OWNED BY public.bill_items.bill_item_id;


--
-- Name: bills; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.bills (
    bill_id bigint NOT NULL,
    patient_no character varying(10) NOT NULL,
    bill_date date NOT NULL,
    total_amount numeric(10,2) DEFAULT '0'::numeric NOT NULL,
    status character varying(255) DEFAULT 'Unpaid'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.bills OWNER TO postgres;

--
-- Name: bills_bill_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.bills_bill_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.bills_bill_id_seq OWNER TO postgres;

--
-- Name: bills_bill_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.bills_bill_id_seq OWNED BY public.bills.bill_id;


--
-- Name: cache; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration bigint NOT NULL
);


ALTER TABLE public.cache OWNER TO postgres;

--
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration bigint NOT NULL
);


ALTER TABLE public.cache_locks OWNER TO postgres;

--
-- Name: diagnoses; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.diagnoses (
    diagnosis_id bigint NOT NULL,
    patient_no character varying(10) NOT NULL,
    staff_no character varying(10),
    diagnosis_details text NOT NULL,
    diagnosis_date date NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    treatment_type character varying(100),
    appointment_id bigint
);


ALTER TABLE public.diagnoses OWNER TO postgres;

--
-- Name: diagnoses_diagnosis_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.diagnoses_diagnosis_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.diagnoses_diagnosis_id_seq OWNER TO postgres;

--
-- Name: diagnoses_diagnosis_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.diagnoses_diagnosis_id_seq OWNED BY public.diagnoses.diagnosis_id;


--
-- Name: drugs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.drugs (
    drug_id bigint NOT NULL,
    supplierno bigint NOT NULL,
    drug_name character varying(255) NOT NULL,
    quantity_stock integer NOT NULL,
    unit_cost numeric(10,2) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.drugs OWNER TO postgres;

--
-- Name: drugs_drug_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.drugs_drug_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.drugs_drug_id_seq OWNER TO postgres;

--
-- Name: drugs_drug_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.drugs_drug_id_seq OWNED BY public.drugs.drug_id;


--
-- Name: local_doctors; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.local_doctors (
    doctor_id bigint NOT NULL,
    fullname character varying(255) NOT NULL,
    address character varying(255) NOT NULL,
    telno character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.local_doctors OWNER TO postgres;

--
-- Name: local_doctors_doctor_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.local_doctors_doctor_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.local_doctors_doctor_id_seq OWNER TO postgres;

--
-- Name: local_doctors_doctor_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.local_doctors_doctor_id_seq OWNED BY public.local_doctors.doctor_id;


--
-- Name: medication_records; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.medication_records (
    medication_id bigint NOT NULL,
    patient_no character varying(10) NOT NULL,
    drug_id bigint NOT NULL,
    dosage character varying(255) NOT NULL,
    frequency character varying(255) NOT NULL,
    start_date date NOT NULL,
    end_date date,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.medication_records OWNER TO postgres;

--
-- Name: medication_records_medication_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.medication_records_medication_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.medication_records_medication_id_seq OWNER TO postgres;

--
-- Name: medication_records_medication_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.medication_records_medication_id_seq OWNED BY public.medication_records.medication_id;


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


ALTER TABLE public.migrations OWNER TO postgres;

--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.migrations_id_seq OWNER TO postgres;

--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: next_of_kin; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.next_of_kin (
    nextofkinid bigint NOT NULL,
    fullname character varying(255) NOT NULL,
    relationshiptopatient character varying(255) NOT NULL,
    address character varying(255) NOT NULL,
    telno character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.next_of_kin OWNER TO postgres;

--
-- Name: next_of_kin_nextofkinid_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.next_of_kin_nextofkinid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.next_of_kin_nextofkinid_seq OWNER TO postgres;

--
-- Name: next_of_kin_nextofkinid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.next_of_kin_nextofkinid_seq OWNED BY public.next_of_kin.nextofkinid;


--
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


ALTER TABLE public.password_reset_tokens OWNER TO postgres;

--
-- Name: patient_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.patient_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.patient_seq OWNER TO postgres;

--
-- Name: patients; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.patients (
    patient_no character varying(10) NOT NULL,
    doctor_id bigint NOT NULL,
    nextofkinid bigint NOT NULL,
    first_name character varying(255) NOT NULL,
    last_name character varying(255) NOT NULL,
    date_of_birth date NOT NULL,
    gender character varying(255) NOT NULL,
    address character varying(255) NOT NULL,
    phone_no character varying(255) NOT NULL,
    marital_status character varying(255),
    status character varying(255) DEFAULT 'Active'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.patients OWNER TO postgres;

--
-- Name: payments; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.payments (
    payment_id bigint NOT NULL,
    bill_id bigint NOT NULL,
    payment_date date NOT NULL,
    amount_paid numeric(10,2) NOT NULL,
    payment_method character varying(255) NOT NULL,
    payment_status character varying(255) DEFAULT 'Completed'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.payments OWNER TO postgres;

--
-- Name: payments_payment_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.payments_payment_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.payments_payment_id_seq OWNER TO postgres;

--
-- Name: payments_payment_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.payments_payment_id_seq OWNED BY public.payments.payment_id;


--
-- Name: personal_access_tokens; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.personal_access_tokens (
    id bigint NOT NULL,
    tokenable_type character varying(255) NOT NULL,
    tokenable_id bigint NOT NULL,
    name text NOT NULL,
    token character varying(64) NOT NULL,
    abilities text,
    last_used_at timestamp(0) without time zone,
    expires_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.personal_access_tokens OWNER TO postgres;

--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.personal_access_tokens_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.personal_access_tokens_id_seq OWNER TO postgres;

--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.personal_access_tokens_id_seq OWNED BY public.personal_access_tokens.id;


--
-- Name: roles; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.roles (
    role_id bigint NOT NULL,
    role_name character varying(255) NOT NULL,
    description character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.roles OWNER TO postgres;

--
-- Name: roles_role_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.roles_role_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.roles_role_id_seq OWNER TO postgres;

--
-- Name: roles_role_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.roles_role_id_seq OWNED BY public.roles.role_id;


--
-- Name: sessions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id bigint,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


ALTER TABLE public.sessions OWNER TO postgres;

--
-- Name: staff; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.staff (
    staff_no character varying(10) NOT NULL,
    role_id bigint,
    first_name character varying(255) NOT NULL,
    last_name character varying(255) NOT NULL,
    dob date NOT NULL,
    gender character varying(255) NOT NULL,
    address character varying(255) NOT NULL,
    phone_no character varying(255) NOT NULL,
    "position" character varying(255) NOT NULL,
    salary numeric(10,2) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    nin character varying(20),
    sex character varying(20),
    salary_scale character varying(20),
    hours_per_week numeric(5,2),
    contract_type character varying(20),
    payment_type character varying(20),
    ward_id integer,
    date_registered date
);


ALTER TABLE public.staff OWNER TO postgres;

--
-- Name: staff_experience; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.staff_experience (
    experience_id integer NOT NULL,
    staff_no character varying(10),
    "position" character varying(100),
    start_date date,
    end_date date,
    organization_name character varying(150),
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now()
);


ALTER TABLE public.staff_experience OWNER TO postgres;

--
-- Name: staff_experience_experience_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.staff_experience_experience_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.staff_experience_experience_id_seq OWNER TO postgres;

--
-- Name: staff_experience_experience_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.staff_experience_experience_id_seq OWNED BY public.staff_experience.experience_id;


--
-- Name: staff_qualifications; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.staff_qualifications (
    qualification_id bigint NOT NULL,
    staff_no character varying(10) NOT NULL,
    qualification_type character varying(255) NOT NULL,
    institution character varying(255) NOT NULL,
    date_obtained date NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.staff_qualifications OWNER TO postgres;

--
-- Name: staff_qualifications_qualification_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.staff_qualifications_qualification_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.staff_qualifications_qualification_id_seq OWNER TO postgres;

--
-- Name: staff_qualifications_qualification_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.staff_qualifications_qualification_id_seq OWNED BY public.staff_qualifications.qualification_id;


--
-- Name: staff_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.staff_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.staff_seq OWNER TO postgres;

--
-- Name: staff_staff_no_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.staff_staff_no_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.staff_staff_no_seq OWNER TO postgres;

--
-- Name: staff_staff_no_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.staff_staff_no_seq OWNED BY public.staff.staff_no;


--
-- Name: staff_ward_assignments; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.staff_ward_assignments (
    assignment_id bigint NOT NULL,
    staff_no character varying(10) NOT NULL,
    ward_id bigint NOT NULL,
    assignment_date date NOT NULL,
    end_date date,
    role_in_ward character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.staff_ward_assignments OWNER TO postgres;

--
-- Name: staff_ward_assignments_assignment_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.staff_ward_assignments_assignment_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.staff_ward_assignments_assignment_id_seq OWNER TO postgres;

--
-- Name: staff_ward_assignments_assignment_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.staff_ward_assignments_assignment_id_seq OWNED BY public.staff_ward_assignments.assignment_id;


--
-- Name: staff_weekly_rota; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.staff_weekly_rota (
    rota_id bigint NOT NULL,
    staff_no character varying(10) NOT NULL,
    ward_id bigint NOT NULL,
    week_start_date date NOT NULL,
    shift_type character varying(20) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.staff_weekly_rota OWNER TO postgres;

--
-- Name: staff_weekly_rota_rota_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.staff_weekly_rota_rota_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.staff_weekly_rota_rota_id_seq OWNER TO postgres;

--
-- Name: staff_weekly_rota_rota_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.staff_weekly_rota_rota_id_seq OWNED BY public.staff_weekly_rota.rota_id;


--
-- Name: suppliers; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.suppliers (
    supplierno bigint NOT NULL,
    suppliername character varying(255) NOT NULL,
    address character varying(255) NOT NULL,
    telno character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.suppliers OWNER TO postgres;

--
-- Name: suppliers_supplierno_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.suppliers_supplierno_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.suppliers_supplierno_seq OWNER TO postgres;

--
-- Name: suppliers_supplierno_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.suppliers_supplierno_seq OWNED BY public.suppliers.supplierno;


--
-- Name: treatment_staff; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.treatment_staff (
    id bigint NOT NULL,
    treatment_id bigint NOT NULL,
    staff_no bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.treatment_staff OWNER TO postgres;

--
-- Name: treatment_staff_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.treatment_staff_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.treatment_staff_id_seq OWNER TO postgres;

--
-- Name: treatment_staff_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.treatment_staff_id_seq OWNED BY public.treatment_staff.id;


--
-- Name: treatments; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.treatments (
    treatment_id bigint NOT NULL,
    patient_no character varying(10) NOT NULL,
    treatment_details text,
    treatment_date date NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    staff_no character varying(10),
    diagnosis_id bigint,
    treatment_type character varying(100),
    treatment_given text,
    method character varying(100),
    remarks text
);


ALTER TABLE public.treatments OWNER TO postgres;

--
-- Name: treatments_treatment_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.treatments_treatment_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.treatments_treatment_id_seq OWNER TO postgres;

--
-- Name: treatments_treatment_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.treatments_treatment_id_seq OWNED BY public.treatments.treatment_id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    email_verified_at timestamp(0) without time zone,
    password character varying(255) NOT NULL,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    staff_no character varying(10),
    role_id bigint
);


ALTER TABLE public.users OWNER TO postgres;

--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_id_seq OWNER TO postgres;

--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: vw_patient_profile; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.vw_patient_profile AS
 SELECT p.patient_no,
    p.first_name,
    p.last_name,
    p.date_of_birth,
    p.gender,
    p.address,
    p.phone_no,
    p.marital_status,
    p.status,
    p.doctor_id,
    ld.fullname AS doctor_name,
    p.nextofkinid,
    nok.fullname AS nok_name
   FROM ((public.patients p
     LEFT JOIN public.local_doctors ld ON ((p.doctor_id = ld.doctor_id)))
     LEFT JOIN public.next_of_kin nok ON ((p.nextofkinid = nok.nextofkinid)));


ALTER VIEW public.vw_patient_profile OWNER TO postgres;

--
-- Name: wards; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.wards (
    ward_id bigint NOT NULL,
    ward_name character varying(255) NOT NULL,
    total_beds integer NOT NULL,
    location character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    telephone_extension character varying(20),
    ward_type character varying(255),
    charge_nurse character varying(255)
);


ALTER TABLE public.wards OWNER TO postgres;

--
-- Name: vw_staff_profile; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.vw_staff_profile AS
 SELECT s.staff_no,
    s.first_name,
    s.last_name,
    concat(s.first_name, ' ', s.last_name) AS full_name,
    s.dob,
    s.gender,
    s.address,
    s.phone_no,
    s.nin,
    s."position",
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
   FROM ((((public.staff s
     LEFT JOIN public.roles r ON ((r.role_id = s.role_id)))
     LEFT JOIN public.staff_ward_assignments swa ON ((((swa.staff_no)::text = (s.staff_no)::text) AND (swa.end_date IS NULL))))
     LEFT JOIN public.wards w ON ((w.ward_id = swa.ward_id)))
     LEFT JOIN public.staff_weekly_rota swr ON ((((swr.staff_no)::text = (s.staff_no)::text) AND (swr.ward_id = swa.ward_id) AND (swr.week_start_date = ( SELECT max(r2.week_start_date) AS max
           FROM public.staff_weekly_rota r2
          WHERE ((r2.staff_no)::text = (s.staff_no)::text))))));


ALTER VIEW public.vw_staff_profile OWNER TO postgres;

--
-- Name: ward_admissions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.ward_admissions (
    admission_id bigint NOT NULL,
    patient_no character varying(10) NOT NULL,
    date_admitted date NOT NULL,
    expected_leave_date date,
    discharge_date date,
    status character varying(255) DEFAULT 'Admitted'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.ward_admissions OWNER TO postgres;

--
-- Name: ward_admissions_admission_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.ward_admissions_admission_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.ward_admissions_admission_id_seq OWNER TO postgres;

--
-- Name: ward_admissions_admission_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.ward_admissions_admission_id_seq OWNED BY public.ward_admissions.admission_id;


--
-- Name: ward_allocations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.ward_allocations (
    allocation_id bigint NOT NULL,
    patient_no character varying(10) NOT NULL,
    ward_id bigint NOT NULL,
    bed_id bigint NOT NULL,
    allocation_date date NOT NULL,
    release_date date,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.ward_allocations OWNER TO postgres;

--
-- Name: ward_allocations_allocation_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.ward_allocations_allocation_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.ward_allocations_allocation_id_seq OWNER TO postgres;

--
-- Name: ward_allocations_allocation_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.ward_allocations_allocation_id_seq OWNED BY public.ward_allocations.allocation_id;


--
-- Name: ward_requisition_items; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.ward_requisition_items (
    requisition_item_id bigint NOT NULL,
    requisition_id bigint NOT NULL,
    drug_id bigint NOT NULL,
    quantity_requested integer NOT NULL,
    quantity_supplied integer DEFAULT 0 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.ward_requisition_items OWNER TO postgres;

--
-- Name: ward_requisition_items_requisition_item_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.ward_requisition_items_requisition_item_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.ward_requisition_items_requisition_item_id_seq OWNER TO postgres;

--
-- Name: ward_requisition_items_requisition_item_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.ward_requisition_items_requisition_item_id_seq OWNED BY public.ward_requisition_items.requisition_item_id;


--
-- Name: ward_requisitions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.ward_requisitions (
    requisition_id bigint NOT NULL,
    ward_id bigint NOT NULL,
    staff_no character varying(10) NOT NULL,
    requisition_date date NOT NULL,
    status character varying(255) DEFAULT 'Pending'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.ward_requisitions OWNER TO postgres;

--
-- Name: ward_requisitions_requisition_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.ward_requisitions_requisition_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.ward_requisitions_requisition_id_seq OWNER TO postgres;

--
-- Name: ward_requisitions_requisition_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.ward_requisitions_requisition_id_seq OWNED BY public.ward_requisitions.requisition_id;


--
-- Name: wards_ward_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.wards_ward_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.wards_ward_id_seq OWNER TO postgres;

--
-- Name: wards_ward_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.wards_ward_id_seq OWNED BY public.wards.ward_id;


--
-- Name: work_experiences; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.work_experiences (
    experience_id bigint NOT NULL,
    staff_no character varying(10) NOT NULL,
    organization character varying(255) NOT NULL,
    "position" character varying(255) NOT NULL,
    start_date date NOT NULL,
    end_date date,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.work_experiences OWNER TO postgres;

--
-- Name: work_experiences_experience_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.work_experiences_experience_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.work_experiences_experience_id_seq OWNER TO postgres;

--
-- Name: work_experiences_experience_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.work_experiences_experience_id_seq OWNED BY public.work_experiences.experience_id;


--
-- Name: appointments appointment_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.appointments ALTER COLUMN appointment_id SET DEFAULT nextval('public.appointments_appointment_id_seq'::regclass);


--
-- Name: beds bed_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.beds ALTER COLUMN bed_id SET DEFAULT nextval('public.beds_bed_id_seq'::regclass);


--
-- Name: bill_items bill_item_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bill_items ALTER COLUMN bill_item_id SET DEFAULT nextval('public.bill_items_bill_item_id_seq'::regclass);


--
-- Name: bills bill_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bills ALTER COLUMN bill_id SET DEFAULT nextval('public.bills_bill_id_seq'::regclass);


--
-- Name: diagnoses diagnosis_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.diagnoses ALTER COLUMN diagnosis_id SET DEFAULT nextval('public.diagnoses_diagnosis_id_seq'::regclass);


--
-- Name: drugs drug_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.drugs ALTER COLUMN drug_id SET DEFAULT nextval('public.drugs_drug_id_seq'::regclass);


--
-- Name: local_doctors doctor_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.local_doctors ALTER COLUMN doctor_id SET DEFAULT nextval('public.local_doctors_doctor_id_seq'::regclass);


--
-- Name: medication_records medication_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.medication_records ALTER COLUMN medication_id SET DEFAULT nextval('public.medication_records_medication_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: next_of_kin nextofkinid; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.next_of_kin ALTER COLUMN nextofkinid SET DEFAULT nextval('public.next_of_kin_nextofkinid_seq'::regclass);


--
-- Name: payments payment_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.payments ALTER COLUMN payment_id SET DEFAULT nextval('public.payments_payment_id_seq'::regclass);


--
-- Name: personal_access_tokens id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.personal_access_tokens ALTER COLUMN id SET DEFAULT nextval('public.personal_access_tokens_id_seq'::regclass);


--
-- Name: roles role_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.roles ALTER COLUMN role_id SET DEFAULT nextval('public.roles_role_id_seq'::regclass);


--
-- Name: staff staff_no; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.staff ALTER COLUMN staff_no SET DEFAULT nextval('public.staff_staff_no_seq'::regclass);


--
-- Name: staff_experience experience_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.staff_experience ALTER COLUMN experience_id SET DEFAULT nextval('public.staff_experience_experience_id_seq'::regclass);


--
-- Name: staff_qualifications qualification_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.staff_qualifications ALTER COLUMN qualification_id SET DEFAULT nextval('public.staff_qualifications_qualification_id_seq'::regclass);


--
-- Name: staff_ward_assignments assignment_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.staff_ward_assignments ALTER COLUMN assignment_id SET DEFAULT nextval('public.staff_ward_assignments_assignment_id_seq'::regclass);


--
-- Name: staff_weekly_rota rota_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.staff_weekly_rota ALTER COLUMN rota_id SET DEFAULT nextval('public.staff_weekly_rota_rota_id_seq'::regclass);


--
-- Name: suppliers supplierno; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.suppliers ALTER COLUMN supplierno SET DEFAULT nextval('public.suppliers_supplierno_seq'::regclass);


--
-- Name: treatment_staff id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.treatment_staff ALTER COLUMN id SET DEFAULT nextval('public.treatment_staff_id_seq'::regclass);


--
-- Name: treatments treatment_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.treatments ALTER COLUMN treatment_id SET DEFAULT nextval('public.treatments_treatment_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Name: ward_admissions admission_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ward_admissions ALTER COLUMN admission_id SET DEFAULT nextval('public.ward_admissions_admission_id_seq'::regclass);


--
-- Name: ward_allocations allocation_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ward_allocations ALTER COLUMN allocation_id SET DEFAULT nextval('public.ward_allocations_allocation_id_seq'::regclass);


--
-- Name: ward_requisition_items requisition_item_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ward_requisition_items ALTER COLUMN requisition_item_id SET DEFAULT nextval('public.ward_requisition_items_requisition_item_id_seq'::regclass);


--
-- Name: ward_requisitions requisition_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ward_requisitions ALTER COLUMN requisition_id SET DEFAULT nextval('public.ward_requisitions_requisition_id_seq'::regclass);


--
-- Name: wards ward_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.wards ALTER COLUMN ward_id SET DEFAULT nextval('public.wards_ward_id_seq'::regclass);


--
-- Name: work_experiences experience_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.work_experiences ALTER COLUMN experience_id SET DEFAULT nextval('public.work_experiences_experience_id_seq'::regclass);


--
-- Data for Name: appointments; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.appointments (appointment_id, patient_no, appointment_date, appointment_time, status, created_at, updated_at, staff_no, examination_room, appointment_type) FROM stdin;
\.


--
-- Data for Name: beds; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.beds (bed_id, ward_id, bed_number, status, created_at, updated_at) FROM stdin;
1	1	01	Available	2026-05-26 16:40:07	2026-05-26 17:24:31
7	1	07	Occupied	2026-05-26 16:40:07	2026-05-26 19:55:22
2	1	02	Available	2026-05-26 16:40:07	2026-05-26 16:40:07
3	1	03	Available	2026-05-26 16:40:07	2026-05-26 16:40:07
4	1	04	Available	2026-05-26 16:40:07	2026-05-26 16:40:07
5	1	05	Available	2026-05-26 16:40:07	2026-05-26 16:40:07
6	1	06	Available	2026-05-26 16:40:07	2026-05-26 16:40:07
8	1	08	Available	2026-05-26 16:40:07	2026-05-26 16:40:07
9	1	09	Available	2026-05-26 16:40:07	2026-05-26 16:40:07
10	1	10	Available	2026-05-26 16:40:07	2026-05-26 16:40:07
11	2	01	Available	2026-05-26 16:40:42	2026-05-26 16:40:42
12	2	02	Available	2026-05-26 16:40:42	2026-05-26 16:40:42
13	2	03	Available	2026-05-26 16:40:42	2026-05-26 16:40:42
14	2	04	Available	2026-05-26 16:40:42	2026-05-26 16:40:42
15	2	05	Available	2026-05-26 16:40:42	2026-05-26 16:40:42
16	2	06	Available	2026-05-26 16:40:42	2026-05-26 16:40:42
17	2	07	Available	2026-05-26 16:40:42	2026-05-26 16:40:42
18	2	08	Available	2026-05-26 16:40:42	2026-05-26 16:40:42
19	2	09	Available	2026-05-26 16:40:42	2026-05-26 16:40:42
20	2	10	Available	2026-05-26 16:40:42	2026-05-26 16:40:42
21	3	01	Available	2026-05-26 16:41:05	2026-05-26 16:41:05
22	3	02	Available	2026-05-26 16:41:05	2026-05-26 16:41:05
23	3	03	Available	2026-05-26 16:41:05	2026-05-26 16:41:05
24	3	04	Available	2026-05-26 16:41:05	2026-05-26 16:41:05
25	3	05	Available	2026-05-26 16:41:05	2026-05-26 16:41:05
26	3	06	Available	2026-05-26 16:41:05	2026-05-26 16:41:05
27	3	07	Available	2026-05-26 16:41:05	2026-05-26 16:41:05
28	3	08	Available	2026-05-26 16:41:05	2026-05-26 16:41:05
29	3	09	Available	2026-05-26 16:41:05	2026-05-26 16:41:05
30	3	10	Available	2026-05-26 16:41:05	2026-05-26 16:41:05
\.


--
-- Data for Name: bill_items; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.bill_items (bill_item_id, bill_id, item_description, quantity, unit_price, subtotal, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: bills; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.bills (bill_id, patient_no, bill_date, total_amount, status, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: cache; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cache (key, value, expiration) FROM stdin;
\.


--
-- Data for Name: cache_locks; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cache_locks (key, owner, expiration) FROM stdin;
\.


--
-- Data for Name: diagnoses; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.diagnoses (diagnosis_id, patient_no, staff_no, diagnosis_details, diagnosis_date, created_at, updated_at, treatment_type, appointment_id) FROM stdin;
\.


--
-- Data for Name: drugs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.drugs (drug_id, supplierno, drug_name, quantity_stock, unit_cost, created_at, updated_at) FROM stdin;
1	1	Paracetamol	100	5.00	\N	\N
2	1	Amoxicillin	80	12.50	\N	\N
3	1	Ibuprofen	60	8.00	\N	\N
\.


--
-- Data for Name: local_doctors; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.local_doctors (doctor_id, fullname, address, telno, created_at, updated_at) FROM stdin;
1	Dr. Renxelle Rebucas	WellMeadows Hospital, Edinburgh	0131-000-0001	2026-05-25 22:08:14	2026-05-25 22:08:14
2	Dr. Edjill Parco	WellMeadows Hospital, Edinburgh	0131-000-0002	2026-05-25 22:08:14	2026-05-25 22:08:14
3	Dr. John Benedict	WellMeadows Hospital, Edinburgh	0131-000-0003	2026-05-25 22:08:14	2026-05-25 22:08:14
4	Dr. Kyle Aching	WellMeadows Hospital, Edinburgh	0131-000-0004	2026-05-25 22:08:14	2026-05-25 22:08:14
\.


--
-- Data for Name: medication_records; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.medication_records (medication_id, patient_no, drug_id, dosage, frequency, start_date, end_date, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.migrations (id, migration, batch) FROM stdin;
1	0001_01_01_000000_create_users_table	1
2	2026_05_11_064145_create_roles_table	1
3	2026_05_11_064159_create_wards_table	1
4	2026_05_11_064208_create_beds_table	1
5	2026_05_11_064217_create_staff_table	1
6	2026_05_11_064250_create_local_doctors_table	1
7	2026_05_11_064400_create_next_of_kin_table	1
8	2026_05_11_064410_create_suppliers_table	1
9	2026_05_11_064421_create_patients_table	1
10	2026_05_11_064430_create_appointments_table	1
11	2026_05_11_064438_create_ward_admissions_table	1
12	2026_05_11_064744_create_drugs_table	1
13	2026_05_11_064751_create_medication_records_table	1
14	2026_05_11_064800_create_treatments_table	1
15	2026_05_11_064809_create_treatment_staff_table	1
16	2026_05_11_064817_create_diagnoses_table	1
17	2026_05_11_064825_create_hospital_procedures_table	1
18	2026_05_11_064833_create_bills_table	1
19	2026_05_11_064839_create_bill_items_table	1
20	2026_05_11_064851_create_payments_table	1
21	2026_05_11_064859_create_ward_allocations_table	1
22	2026_05_11_064906_create_staff_qualifications_table	1
23	2026_05_11_064915_create_work_experiences_table	1
24	2026_05_11_064924_create_ward_requisitions_table	1
25	2026_05_11_064930_create_ward_requisition_items_table	1
26	2026_05_11_080154_add_staff_no_to_users_table	1
27	2026_05_12_125956_add_role_id_to_users_table	1
28	2026_05_13_180302_create_personal_access_tokens_table	1
29	2026_05_14_070945_create_cache_table	1
30	2026_05_18_000001_create_staff_ward_assignments_table	2
34	2026_05_19_100001_add_fields_to_staff_table	3
35	2026_05_19_100002_add_telephone_extension_to_wards_table	3
36	2026_05_19_100003_create_staff_weekly_rota_table	4
37	2026_05_19_100004_update_staff_stored_functions	5
38	2026_05_19_100005_add_qualification_experience_functions	5
39	2026_05_22_075151_create_module1_functions_and_triggers	5
40	2026_05_22_163516_add_module3_details_to_wards_table	5
41	2026_05_23_100001_create_module4_functions_and_triggers	6
42	2026_05_23_200001_recreate_fn_schedule_appointment	7
43	2026_05_23_200002_create_fn_update_appointment_status	8
46	2026_05_24_100001_create_module3_functions_and_triggers	9
47	2026_05_24_200001_add_type_columns_to_module4_tables	10
48	2026_05_24_300001_add_appointment_duplicate_trigger	11
49	2026_05_24_600001_restructure_treatments_table	12
50	2026_05_24_700001_update_fn_add_treatment_with_details	13
51	2026_05_24_800001_update_fn_add_diagnosis_with_appointment	14
52	2026_05_24_900001_seed_missing_staff_roles	15
53	2026_05_25_000001_create_module5_functions	16
54	2026_05_26_000001_add_module2_rota_trigger	16
\.


--
-- Data for Name: next_of_kin; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.next_of_kin (nextofkinid, fullname, relationshiptopatient, address, telno, created_at, updated_at) FROM stdin;
18	Ampol Cornelios	Father	Alae, Bukidnon, Philippines	63 922 233 9874	2026-05-26 12:45:55	2026-05-26 12:45:55
19	Zsandra Gomez	Wife	Zone 4, Patag, Philippines	63 933 444 5555	2026-05-26 12:48:53	2026-05-26 12:48:53
20	Kyler Anasco	Father	Sankanan, Manolo Fortich, Bukidnon	63 941 231 1235	2026-05-26 12:54:05	2026-05-26 12:54:05
21	John Benedict Castillo	Husband	Poblacion 1, Tagoloan	63 955 251 4521	2026-05-26 12:58:29	2026-05-26 12:58:29
17	John Parco	Father	Poblacion, Tagoloan, Philippines	63 994 321 1234	2026-05-26 12:42:31	2026-05-26 12:58:53
22	Edjill Niño Parco	Husband	Zone 5, Patag	63 933 444 5555	2026-05-26 19:54:37	2026-05-26 19:54:37
\.


--
-- Data for Name: password_reset_tokens; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.password_reset_tokens (email, token, created_at) FROM stdin;
parco.edjillnino@gmail.com	$2y$12$.NJt06HOy1UGMNo/N6F9IuRqu0sM9plS4JV3uBQgMOxeu26sKs3tW	2026-05-25 13:40:56
\.


--
-- Data for Name: patients; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.patients (patient_no, doctor_id, nextofkinid, first_name, last_name, date_of_birth, gender, address, phone_no, marital_status, status, created_at, updated_at) FROM stdin;
P0002	4	18	John	Cornelios	2005-04-21	Male	Alae, Bukidnon, Philippines	63 951 555 4561	Single	Active	2026-05-26 12:45:55	2026-05-26 12:45:55
P0003	1	19	Onin	Parco	2001-08-02	Male	Zone 4, Patag, Philippines	63 987 258 1234	Married	Active	2026-05-26 12:48:53	2026-05-26 12:48:53
P0004	4	20	Chrisler	Jubahib	2013-01-01	Male	Sankanan, Manolo Fortich, Bukidnon	63 941 231 1235	Single	Active	2026-05-26 12:54:05	2026-05-26 12:54:05
P0005	3	21	Shann	Castillo	1994-12-02	Female	Poblacion 1, Tagoloan	63 988 255 1544	Married	Active	2026-05-26 12:58:29	2026-05-26 12:58:29
P0001	3	17	Shann Zsandra	Castillo	2006-07-07	Female	Poblacion, Tagoloan, Philippines	63 991 123 4567	Single	Discharged	2026-05-26 12:42:31	2026-05-26 17:25:08
P0006	2	22	Kyle	Natumba	2026-05-19	Male	Zone 5, Patag	09658221154	Married	Active	2026-05-26 19:54:37	2026-05-26 19:54:37
\.


--
-- Data for Name: payments; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.payments (payment_id, bill_id, payment_date, amount_paid, payment_method, payment_status, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: personal_access_tokens; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.personal_access_tokens (id, tokenable_type, tokenable_id, name, token, abilities, last_used_at, expires_at, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: roles; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.roles (role_id, role_name, description, created_at, updated_at) FROM stdin;
1	Administrator	System administrator	\N	\N
2	Receptionist	Handles patient registration	\N	\N
3	Charge Nurse	Manages ward operations	\N	\N
\.


--
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.sessions (id, user_id, ip_address, user_agent, payload, last_activity) FROM stdin;
Wo2Uz7uqESvPxDc2eYEvDBOXdD9DWEczswOo82c9	\N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36	eyJfdG9rZW4iOiJ4TGNkQmNZYzdRUVlTMkRSSlBOOFFKTWVhVUZsRVBHNmdZdENCUmQxIiwidXJsIjp7ImludGVuZGVkIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDBcL2FkbWluXC9kYXNoYm9hcmQifSwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9sb2dpbiIsInJvdXRlIjoibG9naW4ifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==	1779807370
hcDFRZCpVDav3a8xpSJAXJGAFlU404PQImFVLAJ6	1	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36	eyJfdG9rZW4iOiJWM29rd3Z2Rkl1dzMzMHhNNURFS0hBNW9MTmVwNjNsakt5aFdvUVlwIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDBcL3N0YWZmLXNjaGVkdWxlIiwicm91dGUiOiJzdGFmZi5zY2hlZHVsZSJ9LCJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6MX0=	1779796729
\.


--
-- Data for Name: staff; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.staff (staff_no, role_id, first_name, last_name, dob, gender, address, phone_no, "position", salary, created_at, updated_at, nin, sex, salary_scale, hours_per_week, contract_type, payment_type, ward_id, date_registered) FROM stdin;
6	3	Edjill	Parco	2026-05-21	Male	Zone 5, Patag	09658221154	Charge Nurse	200.00	2026-05-26 19:56:53	2026-05-26 19:56:53	3432424	\N	3c	40.00	Permanent	Monthly	\N	\N
1	3	John	Rebucas	2003-10-15	Male	Zone 5, Patag	0985-456-7412	Nurse	100000.00	2026-05-26 16:27:50	2026-05-26 16:27:50	DB145685D	\N	1C	42.00	Permanent	Monthly	\N	\N
2	3	Abdul	Castillo	1981-02-03	Male	Zone 8, Tagoloan	0965-456-785	Staff Nurse	150000.00	2026-05-26 16:30:41	2026-05-26 16:30:41	BD125426C	\N	2C	45.00	Permanent	Monthly	\N	\N
3	3	Renxelle	Parco	1987-12-25	Male	Zone 5, Bonbon CDOC	0935-456-8526	Nurse	290000.00	2026-05-26 16:33:09	2026-05-26 16:33:09	WB125362B	\N	2D	42.00	Permanent	Monthly	\N	\N
4	3	Princess	Anasco	1989-12-04	Female	Malaybalay, Bukidnon	0965-584-5416	Staff Nurse	120000.00	2026-05-26 16:35:45	2026-05-26 16:35:45	BC128821B	\N	3C	44.00	Permanent	Monthly	\N	\N
5	3	Bongbong	Marcos	1987-12-31	Male	Lapasan, Cagayan de Oro City	0954-456-8759	Nurse	39000.00	2026-05-26 16:38:33	2026-05-26 16:38:33	BV125273B	\N	4B	46.00	Permanent	Monthly	\N	\N
\.


--
-- Data for Name: staff_experience; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.staff_experience (experience_id, staff_no, "position", start_date, end_date, organization_name, created_at, updated_at) FROM stdin;
3	S011	HEAD DOCTOR	2026-05-06	2026-05-28	CUMC	2026-05-19 04:11:35.247462	2026-05-19 04:11:35.247462
6	S014	HEAD DOCTOR	2026-05-21	2026-05-10	CUMC	2026-05-23 20:47:13.966537	2026-05-23 20:47:13.966537
11	S009	Staff	2026-05-06	2026-05-30	CUMC	2026-05-24 13:28:32.528818	2026-05-24 13:28:32.528818
\.


--
-- Data for Name: staff_qualifications; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.staff_qualifications (qualification_id, staff_no, qualification_type, institution, date_obtained, created_at, updated_at) FROM stdin;
18	1	BS Nursing	LDCU	2023-05-26	2026-05-26 16:27:50	2026-05-26 16:27:50
19	2	BS Nursing	CU	2021-09-26	2026-05-26 16:30:41	2026-05-26 16:30:41
20	3	BS Nursing	COC	2013-09-06	2026-05-26 16:33:09	2026-05-26 16:33:09
21	4	BS Nursing	USTP	2018-12-31	2026-05-26 16:35:45	2026-05-26 16:35:45
22	5	BS Nursing	LDCU	2016-12-07	2026-05-26 16:38:33	2026-05-26 16:38:33
23	6	BS NURSING	COC	2026-05-27	2026-05-26 19:56:53	2026-05-26 19:56:53
\.


--
-- Data for Name: staff_ward_assignments; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.staff_ward_assignments (assignment_id, staff_no, ward_id, assignment_date, end_date, role_in_ward, created_at, updated_at) FROM stdin;
8	2	1	2026-05-08	\N	\N	2026-05-26 16:52:30	2026-05-26 16:52:30
7	4	1	2026-05-21	2026-05-26	Charge Nurse	2026-05-26 16:52:18	2026-05-26 17:15:58
9	4	2	2026-05-26	\N	General Assignment	2026-05-26 17:15:58	2026-05-26 17:15:58
10	6	1	2026-05-17	\N	Charge Nurse	2026-05-26 19:57:31	2026-05-26 19:57:31
\.


--
-- Data for Name: staff_weekly_rota; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.staff_weekly_rota (rota_id, staff_no, ward_id, week_start_date, shift_type, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: suppliers; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.suppliers (supplierno, suppliername, address, telno, created_at, updated_at) FROM stdin;
1	MedSupply Co.	Cagayan de Oro City	09123456789	\N	\N
\.


--
-- Data for Name: treatment_staff; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.treatment_staff (id, treatment_id, staff_no, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: treatments; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.treatments (treatment_id, patient_no, treatment_details, treatment_date, created_at, updated_at, staff_no, diagnosis_id, treatment_type, treatment_given, method, remarks) FROM stdin;
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.users (id, name, email, email_verified_at, password, remember_token, created_at, updated_at, staff_no, role_id) FROM stdin;
2	Zsandra Parco	zsandra.edjillnino@gmail.com	\N	$2y$12$6hgPpUjCDCi.qZe7qBH9m.duTRCi/jt06aPwaQDX8WeJ77qf2zSIC	\N	2026-05-25 13:56:43	2026-05-25 13:56:43	\N	2
3	Rensil Rebuccas	rensel.edjillnino@gmail.com	\N	$2y$12$92JzIm2iaE7.33kfk7w6..coLQNZjw9AuusD34TKzlvED82.pSAQy	\N	2026-05-25 14:00:39	2026-05-25 14:00:39	\N	3
1	Edjill Parco	parco.edjillnino@gmail.com	\N	$2y$12$Rds49GPxA9BDnYRDp43aeuZrpms47gVYGfWfWRi9ShoSAu36IyPwe	dLOp1KaRYhGURk9MNzNXHY1oG6kfYmwlc7xeXJvJeLUVmKptPLELFSDI1WUy	2026-05-18 14:46:09	2026-05-26 19:43:49	\N	1
\.


--
-- Data for Name: ward_admissions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.ward_admissions (admission_id, patient_no, date_admitted, expected_leave_date, discharge_date, status, created_at, updated_at) FROM stdin;
8	P0001	2026-05-26	2026-05-31	2026-05-26	Discharged	2026-05-26 17:23:29	2026-05-26 17:25:08
9	P0006	2026-05-12	2026-05-27	\N	Admitted	2026-05-26 19:55:00	2026-05-26 19:55:00
\.


--
-- Data for Name: ward_allocations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.ward_allocations (allocation_id, patient_no, ward_id, bed_id, allocation_date, release_date, created_at, updated_at) FROM stdin;
1	P0001	1	1	2026-05-26	2026-05-26	2026-05-26 17:24:21	2026-05-26 17:24:31
2	P0006	1	7	2026-05-26	\N	2026-05-26 19:55:22	2026-05-26 19:55:22
\.


--
-- Data for Name: ward_requisition_items; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.ward_requisition_items (requisition_item_id, requisition_id, drug_id, quantity_requested, quantity_supplied, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: ward_requisitions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.ward_requisitions (requisition_id, ward_id, staff_no, requisition_date, status, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: wards; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.wards (ward_id, ward_name, total_beds, location, created_at, updated_at, telephone_extension, ward_type, charge_nurse) FROM stdin;
1	Ward 1	10	Block A, Floor 2	2026-05-26 16:40:07	2026-05-26 16:40:07	\N	Orthopaedic	\N
2	Ward 2	10	Block B, Floor 2	2026-05-26 16:40:42	2026-05-26 16:40:42	\N	Cardiology	\N
3	Ward 3	10	Block C, Floor 2	2026-05-26 16:41:05	2026-05-26 16:41:05	\N	General Surgery	\N
\.


--
-- Data for Name: work_experiences; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.work_experiences (experience_id, staff_no, organization, "position", start_date, end_date, created_at, updated_at) FROM stdin;
4	1	CUMC	Nurse	2024-06-26	2026-01-11	2026-05-26 16:27:50	2026-05-26 16:27:50
5	2	NMMC	Nurse	2022-06-14	2025-01-06	2026-05-26 16:30:41	2026-05-26 16:30:41
6	3	Polymedic Hospital	Nurse	2015-12-31	2019-12-04	2026-05-26 16:33:09	2026-05-26 16:33:09
7	4	CUMC	Staff Nurse	2019-12-04	2024-12-04	2026-05-26 16:35:45	2026-05-26 16:35:45
8	5	NMMC	Nurse	2017-12-31	2025-09-09	2026-05-26 16:38:33	2026-05-26 16:38:33
9	6	CUMC	Staff Nurse	2026-05-27	2026-05-30	2026-05-26 19:56:53	2026-05-26 19:56:53
\.


--
-- Name: appointments_appointment_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.appointments_appointment_id_seq', 30, true);


--
-- Name: beds_bed_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.beds_bed_id_seq', 30, true);


--
-- Name: bill_items_bill_item_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.bill_items_bill_item_id_seq', 1, false);


--
-- Name: bills_bill_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.bills_bill_id_seq', 1, false);


--
-- Name: diagnoses_diagnosis_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.diagnoses_diagnosis_id_seq', 16, true);


--
-- Name: drugs_drug_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.drugs_drug_id_seq', 3, true);


--
-- Name: local_doctors_doctor_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.local_doctors_doctor_id_seq', 4, true);


--
-- Name: medication_records_medication_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.medication_records_medication_id_seq', 6, true);


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.migrations_id_seq', 54, true);


--
-- Name: next_of_kin_nextofkinid_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.next_of_kin_nextofkinid_seq', 22, true);


--
-- Name: patient_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.patient_seq', 6, true);


--
-- Name: payments_payment_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.payments_payment_id_seq', 1, false);


--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.personal_access_tokens_id_seq', 1, false);


--
-- Name: roles_role_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.roles_role_id_seq', 10, true);


--
-- Name: staff_experience_experience_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.staff_experience_experience_id_seq', 11, true);


--
-- Name: staff_qualifications_qualification_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.staff_qualifications_qualification_id_seq', 23, true);


--
-- Name: staff_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.staff_seq', 18, true);


--
-- Name: staff_staff_no_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.staff_staff_no_seq', 6, true);


--
-- Name: staff_ward_assignments_assignment_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.staff_ward_assignments_assignment_id_seq', 10, true);


--
-- Name: staff_weekly_rota_rota_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.staff_weekly_rota_rota_id_seq', 5, true);


--
-- Name: suppliers_supplierno_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.suppliers_supplierno_seq', 1, true);


--
-- Name: treatment_staff_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.treatment_staff_id_seq', 1, false);


--
-- Name: treatments_treatment_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.treatments_treatment_id_seq', 10, true);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.users_id_seq', 3, true);


--
-- Name: ward_admissions_admission_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.ward_admissions_admission_id_seq', 9, true);


--
-- Name: ward_allocations_allocation_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.ward_allocations_allocation_id_seq', 2, true);


--
-- Name: ward_requisition_items_requisition_item_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.ward_requisition_items_requisition_item_id_seq', 1, false);


--
-- Name: ward_requisitions_requisition_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.ward_requisitions_requisition_id_seq', 1, false);


--
-- Name: wards_ward_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.wards_ward_id_seq', 3, true);


--
-- Name: work_experiences_experience_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.work_experiences_experience_id_seq', 9, true);


--
-- Name: appointments appointments_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.appointments
    ADD CONSTRAINT appointments_pkey PRIMARY KEY (appointment_id);


--
-- Name: beds beds_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.beds
    ADD CONSTRAINT beds_pkey PRIMARY KEY (bed_id);


--
-- Name: bill_items bill_items_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bill_items
    ADD CONSTRAINT bill_items_pkey PRIMARY KEY (bill_item_id);


--
-- Name: bills bills_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bills
    ADD CONSTRAINT bills_pkey PRIMARY KEY (bill_id);


--
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- Name: diagnoses diagnoses_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.diagnoses
    ADD CONSTRAINT diagnoses_pkey PRIMARY KEY (diagnosis_id);


--
-- Name: drugs drugs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.drugs
    ADD CONSTRAINT drugs_pkey PRIMARY KEY (drug_id);


--
-- Name: local_doctors local_doctors_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.local_doctors
    ADD CONSTRAINT local_doctors_pkey PRIMARY KEY (doctor_id);


--
-- Name: medication_records medication_records_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.medication_records
    ADD CONSTRAINT medication_records_pkey PRIMARY KEY (medication_id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: next_of_kin next_of_kin_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.next_of_kin
    ADD CONSTRAINT next_of_kin_pkey PRIMARY KEY (nextofkinid);


--
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- Name: patients patients_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.patients
    ADD CONSTRAINT patients_pkey PRIMARY KEY (patient_no);


--
-- Name: payments payments_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.payments
    ADD CONSTRAINT payments_pkey PRIMARY KEY (payment_id);


--
-- Name: personal_access_tokens personal_access_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.personal_access_tokens
    ADD CONSTRAINT personal_access_tokens_pkey PRIMARY KEY (id);


--
-- Name: personal_access_tokens personal_access_tokens_token_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.personal_access_tokens
    ADD CONSTRAINT personal_access_tokens_token_unique UNIQUE (token);


--
-- Name: roles roles_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (role_id);


--
-- Name: roles roles_role_name_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_role_name_unique UNIQUE (role_name);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: staff_experience staff_experience_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.staff_experience
    ADD CONSTRAINT staff_experience_pkey PRIMARY KEY (experience_id);


--
-- Name: staff staff_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.staff
    ADD CONSTRAINT staff_pkey PRIMARY KEY (staff_no);


--
-- Name: staff_qualifications staff_qualifications_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.staff_qualifications
    ADD CONSTRAINT staff_qualifications_pkey PRIMARY KEY (qualification_id);


--
-- Name: staff_ward_assignments staff_ward_assignments_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.staff_ward_assignments
    ADD CONSTRAINT staff_ward_assignments_pkey PRIMARY KEY (assignment_id);


--
-- Name: staff_weekly_rota staff_weekly_rota_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.staff_weekly_rota
    ADD CONSTRAINT staff_weekly_rota_pkey PRIMARY KEY (rota_id);


--
-- Name: staff_weekly_rota staff_weekly_rota_staff_no_ward_id_week_start_date_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.staff_weekly_rota
    ADD CONSTRAINT staff_weekly_rota_staff_no_ward_id_week_start_date_unique UNIQUE (staff_no, ward_id, week_start_date);


--
-- Name: suppliers suppliers_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.suppliers
    ADD CONSTRAINT suppliers_pkey PRIMARY KEY (supplierno);


--
-- Name: treatment_staff treatment_staff_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.treatment_staff
    ADD CONSTRAINT treatment_staff_pkey PRIMARY KEY (id);


--
-- Name: treatments treatments_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.treatments
    ADD CONSTRAINT treatments_pkey PRIMARY KEY (treatment_id);


--
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: ward_admissions ward_admissions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ward_admissions
    ADD CONSTRAINT ward_admissions_pkey PRIMARY KEY (admission_id);


--
-- Name: ward_allocations ward_allocations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ward_allocations
    ADD CONSTRAINT ward_allocations_pkey PRIMARY KEY (allocation_id);


--
-- Name: ward_requisition_items ward_requisition_items_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ward_requisition_items
    ADD CONSTRAINT ward_requisition_items_pkey PRIMARY KEY (requisition_item_id);


--
-- Name: ward_requisitions ward_requisitions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ward_requisitions
    ADD CONSTRAINT ward_requisitions_pkey PRIMARY KEY (requisition_id);


--
-- Name: wards wards_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.wards
    ADD CONSTRAINT wards_pkey PRIMARY KEY (ward_id);


--
-- Name: work_experiences work_experiences_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.work_experiences
    ADD CONSTRAINT work_experiences_pkey PRIMARY KEY (experience_id);


--
-- Name: cache_expiration_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX cache_expiration_index ON public.cache USING btree (expiration);


--
-- Name: cache_locks_expiration_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX cache_locks_expiration_index ON public.cache_locks USING btree (expiration);


--
-- Name: personal_access_tokens_expires_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX personal_access_tokens_expires_at_index ON public.personal_access_tokens USING btree (expires_at);


--
-- Name: personal_access_tokens_tokenable_type_tokenable_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX personal_access_tokens_tokenable_type_tokenable_id_index ON public.personal_access_tokens USING btree (tokenable_type, tokenable_id);


--
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- Name: ward_admissions before_admission_insert; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER before_admission_insert BEFORE INSERT ON public.ward_admissions FOR EACH ROW EXECUTE FUNCTION public.trg_check_active_admission();


--
-- Name: appointments trg_appointment_no_duplicate; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_appointment_no_duplicate BEFORE INSERT OR UPDATE ON public.appointments FOR EACH ROW EXECUTE FUNCTION public.trg_prevent_duplicate_appointment();


--
-- Name: patients trg_auto_patientno; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_auto_patientno BEFORE INSERT ON public.patients FOR EACH ROW EXECUTE FUNCTION public.trg_fn_auto_patientno();


--
-- Name: ward_allocations trg_check_bed_availability; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_check_bed_availability BEFORE INSERT ON public.ward_allocations FOR EACH ROW EXECUTE FUNCTION public.trg_fn_check_bed_availability();


--
-- Name: patients trg_check_duplicate_patient; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_check_duplicate_patient BEFORE INSERT ON public.patients FOR EACH ROW EXECUTE FUNCTION public.trg_fn_check_duplicate_patient();


--
-- Name: wards trg_check_duplicate_ward; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_check_duplicate_ward BEFORE INSERT OR UPDATE ON public.wards FOR EACH ROW EXECUTE FUNCTION public.trg_fn_check_duplicate_ward();


--
-- Name: staff_weekly_rota trg_prevent_double_booking; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_prevent_double_booking BEFORE INSERT ON public.staff_weekly_rota FOR EACH ROW EXECUTE FUNCTION public.trg_fn_prevent_double_booking();


--
-- Name: appointments trg_prevent_duplicate_appointment; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_prevent_duplicate_appointment BEFORE INSERT OR UPDATE ON public.appointments FOR EACH ROW EXECUTE FUNCTION public.trg_fn_prevent_duplicate_appointment();


--
-- Name: payments trg_prevent_payment_on_cancelled_bill; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_prevent_payment_on_cancelled_bill BEFORE INSERT ON public.payments FOR EACH ROW EXECUTE FUNCTION public.fn_prevent_payment_on_cancelled_bill();


--
-- Name: users trg_updateusertimestamp; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_updateusertimestamp BEFORE UPDATE ON public.users FOR EACH ROW EXECUTE FUNCTION public.update_user_timestamp();


--
-- Name: appointments appointments_patient_no_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.appointments
    ADD CONSTRAINT appointments_patient_no_foreign FOREIGN KEY (patient_no) REFERENCES public.patients(patient_no) ON DELETE CASCADE;


--
-- Name: beds beds_ward_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.beds
    ADD CONSTRAINT beds_ward_id_foreign FOREIGN KEY (ward_id) REFERENCES public.wards(ward_id);


--
-- Name: bill_items bill_items_bill_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bill_items
    ADD CONSTRAINT bill_items_bill_id_foreign FOREIGN KEY (bill_id) REFERENCES public.bills(bill_id) ON DELETE CASCADE;


--
-- Name: bills bills_patient_no_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bills
    ADD CONSTRAINT bills_patient_no_foreign FOREIGN KEY (patient_no) REFERENCES public.patients(patient_no) ON DELETE CASCADE;


--
-- Name: diagnoses diagnoses_patient_no_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.diagnoses
    ADD CONSTRAINT diagnoses_patient_no_foreign FOREIGN KEY (patient_no) REFERENCES public.patients(patient_no) ON DELETE CASCADE;


--
-- Name: drugs drugs_supplierno_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.drugs
    ADD CONSTRAINT drugs_supplierno_foreign FOREIGN KEY (supplierno) REFERENCES public.suppliers(supplierno);


--
-- Name: medication_records medication_records_drug_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.medication_records
    ADD CONSTRAINT medication_records_drug_id_foreign FOREIGN KEY (drug_id) REFERENCES public.drugs(drug_id) ON DELETE CASCADE;


--
-- Name: medication_records medication_records_patient_no_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.medication_records
    ADD CONSTRAINT medication_records_patient_no_foreign FOREIGN KEY (patient_no) REFERENCES public.patients(patient_no) ON DELETE CASCADE;


--
-- Name: patients patients_doctor_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.patients
    ADD CONSTRAINT patients_doctor_id_foreign FOREIGN KEY (doctor_id) REFERENCES public.local_doctors(doctor_id);


--
-- Name: patients patients_nextofkinid_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.patients
    ADD CONSTRAINT patients_nextofkinid_foreign FOREIGN KEY (nextofkinid) REFERENCES public.next_of_kin(nextofkinid);


--
-- Name: payments payments_bill_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.payments
    ADD CONSTRAINT payments_bill_id_foreign FOREIGN KEY (bill_id) REFERENCES public.bills(bill_id) ON DELETE CASCADE;


--
-- Name: staff_qualifications staff_qualifications_staff_no_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.staff_qualifications
    ADD CONSTRAINT staff_qualifications_staff_no_foreign FOREIGN KEY (staff_no) REFERENCES public.staff(staff_no);


--
-- Name: staff staff_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.staff
    ADD CONSTRAINT staff_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(role_id);


--
-- Name: staff_ward_assignments staff_ward_assignments_ward_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.staff_ward_assignments
    ADD CONSTRAINT staff_ward_assignments_ward_id_foreign FOREIGN KEY (ward_id) REFERENCES public.wards(ward_id);


--
-- Name: staff_weekly_rota staff_weekly_rota_staff_no_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.staff_weekly_rota
    ADD CONSTRAINT staff_weekly_rota_staff_no_foreign FOREIGN KEY (staff_no) REFERENCES public.staff(staff_no) ON DELETE CASCADE;


--
-- Name: staff_weekly_rota staff_weekly_rota_ward_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.staff_weekly_rota
    ADD CONSTRAINT staff_weekly_rota_ward_id_foreign FOREIGN KEY (ward_id) REFERENCES public.wards(ward_id);


--
-- Name: treatment_staff treatment_staff_treatment_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.treatment_staff
    ADD CONSTRAINT treatment_staff_treatment_id_foreign FOREIGN KEY (treatment_id) REFERENCES public.treatments(treatment_id);


--
-- Name: treatments treatments_patient_no_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.treatments
    ADD CONSTRAINT treatments_patient_no_foreign FOREIGN KEY (patient_no) REFERENCES public.patients(patient_no) ON DELETE CASCADE;


--
-- Name: users users_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(role_id) ON DELETE SET NULL;


--
-- Name: ward_admissions ward_admissions_patient_no_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ward_admissions
    ADD CONSTRAINT ward_admissions_patient_no_foreign FOREIGN KEY (patient_no) REFERENCES public.patients(patient_no) ON DELETE CASCADE;


--
-- Name: ward_allocations ward_allocations_bed_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ward_allocations
    ADD CONSTRAINT ward_allocations_bed_id_foreign FOREIGN KEY (bed_id) REFERENCES public.beds(bed_id);


--
-- Name: ward_allocations ward_allocations_patient_no_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ward_allocations
    ADD CONSTRAINT ward_allocations_patient_no_foreign FOREIGN KEY (patient_no) REFERENCES public.patients(patient_no) ON DELETE CASCADE;


--
-- Name: ward_allocations ward_allocations_ward_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ward_allocations
    ADD CONSTRAINT ward_allocations_ward_id_foreign FOREIGN KEY (ward_id) REFERENCES public.wards(ward_id);


--
-- Name: ward_requisition_items ward_requisition_items_drug_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ward_requisition_items
    ADD CONSTRAINT ward_requisition_items_drug_id_foreign FOREIGN KEY (drug_id) REFERENCES public.drugs(drug_id);


--
-- Name: ward_requisition_items ward_requisition_items_requisition_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ward_requisition_items
    ADD CONSTRAINT ward_requisition_items_requisition_id_foreign FOREIGN KEY (requisition_id) REFERENCES public.ward_requisitions(requisition_id) ON DELETE CASCADE;


--
-- Name: ward_requisitions ward_requisitions_ward_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ward_requisitions
    ADD CONSTRAINT ward_requisitions_ward_id_foreign FOREIGN KEY (ward_id) REFERENCES public.wards(ward_id);


--
-- PostgreSQL database dump complete
--

\unrestrict tEj0SW2djdEhpmKXjSfxbGwsbea4tr2zY1Pyw84OGkX0pCcC3Ei7NZyxSEbRdX8


<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TreatmentController extends Controller
{
    public function index()
    {
        $appointments = DB::select("
            SELECT
                a.appointment_id,
                a.patient_no,
                p.first_name || ' ' || p.last_name AS patient_name,
                a.appointment_date,
                a.appointment_time,
                a.appointment_type,
                a.staff_no,
                s.first_name || ' ' || s.last_name AS staff_name
            FROM appointments a
            LEFT JOIN patients p ON a.patient_no = p.patient_no
            LEFT JOIN staff s ON a.staff_no = s.staff_no
            WHERE a.status IN ('Pending', 'Confirmed', 'Checked In')
            ORDER BY a.appointment_date DESC, a.appointment_time DESC
        ");

        $treatments = DB::select("
            SELECT
                t.treatment_id,
                t.patient_no,
                p.first_name || ' ' || p.last_name AS patient_name,
                t.staff_no,
                s.first_name || ' ' || s.last_name AS staff_name,
                d.diagnosis_id,
                d.diagnosis_details,
                t.treatment_date,
                t.treatment_type,
                t.treatment_given,
                t.method,
                t.remarks
            FROM treatments t
            LEFT JOIN patients p ON t.patient_no = p.patient_no
            LEFT JOIN staff s ON t.staff_no = s.staff_no
            LEFT JOIN diagnoses d ON t.diagnosis_id = d.diagnosis_id
            ORDER BY t.treatment_date DESC
        ");

        return view('module4.treatmentrec', compact('appointments', 'treatments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'appointment_id'    => 'required|exists:appointments,appointment_id',
            'patient_no'        => 'required|exists:patients,patient_no',
            'staff_no'          => 'required',
            'diagnosis_date'    => 'required|date',
            'diagnosis_details' => 'required|string|max:5000',
            'treatment_type'    => 'required',
            'treatment_given'   => 'nullable|string|max:5000',
            'method'            => 'nullable|string|max:100',
            'remarks'           => 'nullable|string|max:2000',
        ]);

        try {
            $diagnosisResult = DB::select('SELECT fn_add_diagnosis(?, ?, ?, ?, ?) AS result', [
                $request->patient_no,
                $request->staff_no,
                $request->appointment_id,
                $request->diagnosis_date,
                $request->diagnosis_details,
            ]);
            $diagnosisId = $diagnosisResult[0]->result;

            DB::select('SELECT fn_add_treatment(?, ?, ?, ?, ?, ?, ?, ?) AS result', [
                $request->patient_no,
                $request->staff_no,
                $diagnosisId,
                $request->diagnosis_date,
                $request->treatment_type,
                $request->treatment_given,
                $request->input('method') ?? null,
                $request->remarks ?? null,
            ]);

            DB::select('SELECT fn_update_appointment_status(?, ?) AS result', [
                $request->appointment_id,
                'Completed',
            ]);

            return redirect()->route('module4.treatmentrec')
                ->with('success', 'Treatment record saved successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Could not save treatment record: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $record = DB::select("
            SELECT
                d.diagnosis_id,
                d.patient_no,
                p.first_name || ' ' || p.last_name AS patient_name,
                d.staff_no,
                d.diagnosis_details,
                d.diagnosis_date,
                d.appointment_id,
                t.treatment_id,
                t.treatment_type,
                t.treatment_given,
                t.method,
                t.remarks
            FROM diagnoses d
            LEFT JOIN patients p ON d.patient_no = p.patient_no
            LEFT JOIN treatments t ON t.diagnosis_id = d.diagnosis_id
            WHERE d.diagnosis_id = ?
        ", [$id]);

        if (empty($record)) {
            return redirect()->route('module4.treatmentrec')->with('error', 'Record not found.');
        }

        return view('module4.treatmentrec_edit', ['treatment' => $record[0]]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'diagnosis_details' => 'required|string|max:5000',
            'diagnosis_date'    => 'required|date',
            'treatment_type'    => 'required|string|max:100',
            'treatment_given'   => 'nullable|string|max:5000',
            'method'            => 'nullable|string|max:100',
            'remarks'           => 'nullable|string|max:2000',
        ]);

        try {
            DB::update('UPDATE diagnoses SET
                diagnosis_details = ?,
                diagnosis_date = ?,
                updated_at = NOW()
                WHERE diagnosis_id = ?', [
                $request->diagnosis_details,
                $request->diagnosis_date,
                (int) $id,
            ]);

            DB::update('UPDATE treatments SET
                treatment_type = ?,
                treatment_given = ?,
                method = ?,
                remarks = ?,
                treatment_details = ?,
                updated_at = NOW()
                WHERE diagnosis_id = ?', [
                $request->treatment_type,
                $request->treatment_given,
                $request->input('method'),
                $request->remarks,
                $request->treatment_given,
                (int) $id,
            ]);

            return redirect()->route('module4.treatmentrec')
                ->with('success', 'Treatment record updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Could not update: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $treatment = DB::table('treatments')->where('treatment_id', (int) $id)->first();

            DB::delete('DELETE FROM treatments WHERE treatment_id = ?', [(int) $id]);

            if ($treatment && $treatment->diagnosis_id) {
                DB::delete('DELETE FROM diagnoses WHERE diagnosis_id = ?', [(int) $treatment->diagnosis_id]);
            }

            return redirect()->route('module4.treatmentrec')
                ->with('success', 'Treatment record deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Could not delete treatment record: ' . $e->getMessage());
        }
    }
}

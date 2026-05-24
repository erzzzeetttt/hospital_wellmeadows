<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TreatmentStaffController extends Controller
{
    /**
     * Show the staff assignment page with all required dropdowns and tables.
     */
    public function index()
    {
        // All diagnoses for "Select Treatment / Diagnosis" dropdown
        $diagnoses = DB::table('diagnoses as d')
            ->join('patients as p', 'd.patient_no', '=', 'p.patient_no')
            ->select(
                'd.diagnosis_id',
                'd.diagnosis_date',
                DB::raw("d.diagnosis_id || ' — ' || p.first_name || ' ' || p.last_name || ' (' || d.diagnosis_date || ')' AS label")
            )
            ->orderBy('d.diagnosis_date', 'desc')
            ->get();

        // Staff who can serve as doctors / consultants
        $doctors = DB::table('staff')
            ->where(function ($q) {
                $q->where('position', 'ilike', '%doctor%')
                  ->orWhere('position', 'ilike', '%consultant%')
                  ->orWhere('position', 'ilike', '%physician%');
            })
            ->select('staff_no', 'first_name', 'last_name', 'position')
            ->orderBy('last_name')
            ->get();

        // Staff who can serve as nurses / assistants
        $nurses = DB::table('staff')
            ->where(function ($q) {
                $q->where('position', 'ilike', '%nurse%')
                  ->orWhere('position', 'ilike', '%auxiliary%')
                  ->orWhere('position', 'ilike', '%assistant%');
            })
            ->select('staff_no', 'first_name', 'last_name', 'position')
            ->orderBy('last_name')
            ->get();

        // All staff for the Staff List table
        $allStaff = DB::table('staff')
            ->select('staff_no', 'first_name', 'last_name', 'position')
            ->orderBy('last_name')
            ->get();

        // Diagnoses that have no treatment_staff records (pending assignment)
        $pendingAssignments = DB::table('diagnoses as d')
            ->join('patients as p', 'd.patient_no', '=', 'p.patient_no')
            ->leftJoin('treatments as t', function ($join) {
                $join->on('t.patient_no', '=', 'd.patient_no')
                     ->on('t.treatment_date', '=', 'd.diagnosis_date');
            })
            ->leftJoin('treatment_staff as ts', 'ts.treatment_id', '=', 't.treatment_id')
            ->whereNull('ts.id')
            ->select(
                'd.diagnosis_id',
                'd.diagnosis_date',
                DB::raw("p.first_name || ' ' || p.last_name AS patient_name")
            )
            ->orderBy('d.diagnosis_date', 'desc')
            ->get();

        $treatments = DB::select("
            SELECT
                d.diagnosis_id,
                d.patient_no,
                p.first_name || ' ' || p.last_name AS patient_name,
                d.diagnosis_date
            FROM diagnoses d
            LEFT JOIN patients p ON d.patient_no = p.patient_no
            ORDER BY d.diagnosis_date DESC
        ");

        $todayAssignments = DB::select("
            SELECT
                p.first_name || ' ' || p.last_name AS patient_name,
                d.diagnosis_date AS treatment_date,
                s.first_name || ' ' || s.last_name AS staff_name,
                s.position
            FROM treatment_staff ts
            LEFT JOIN diagnoses d ON ts.treatment_id = d.diagnosis_id
            LEFT JOIN patients p ON d.patient_no = p.patient_no
            LEFT JOIN staff s ON ts.staff_no::VARCHAR = s.staff_no
            ORDER BY d.diagnosis_date DESC
        ");

        $summary = [
            ['label' => 'Total Doctors',       'value' => $doctors->count()],
            ['label' => 'Total Nurses',        'value' => $nurses->count()],
            ['label' => 'Available Staff',     'value' => $allStaff->count()],
            ['label' => 'Pending Assignments', 'value' => $pendingAssignments->count()],
        ];

        return view('module4.staffassign', compact(
            'diagnoses', 'doctors', 'nurses', 'allStaff',
            'pendingAssignments', 'summary', 'treatments', 'todayAssignments'
        ));
    }

    /**
     * Assign a doctor and/or nurse to a diagnosis using fn_assign_staff_to_treatment.
     */
    public function store(Request $request)
    {
        $request->validate([
            'diagnosis_id' => 'required|exists:diagnoses,diagnosis_id',
            'staff_no'     => 'required|exists:staff,staff_no',
        ]);

        try {
            $result = DB::select('SELECT fn_assign_staff_to_treatment(?, ?, ?) AS result', [
                (int) $request->diagnosis_id,
                $request->staff_no,
                $request->notes ?? 'No notes',
            ]);

            return redirect()->route('module4.staffassign')
                ->with('success', 'Staff assigned successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Could not assign staff: ' . $e->getMessage());
        }
    }
}

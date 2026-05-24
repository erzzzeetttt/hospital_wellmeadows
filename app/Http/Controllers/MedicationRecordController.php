<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MedicationRecordController extends Controller
{
    public function index()
{
    $patients = DB::table('patients')
        ->orderBy('patient_no')
        ->get();

    $drugs = DB::table('drugs')
        ->orderBy('drug_name')
        ->get();

    return view('module1.medicalrecords', compact('patients', 'drugs'));
    }

    public function store(Request $request)
{
    $request->validate([
        'patient_no' => 'required|exists:patients,patient_no',
        'drug_id' => 'required|exists:drugs,drug_id',
        'dosage' => 'required|max:100',
        'frequency' => 'required|max:100',
        'start_date' => 'required|date',
        'end_date' => 'nullable|date',
    ]);

    $result = DB::select('SELECT fn_add_medication_record(?, ?, ?, ?, ?, ?) AS message', [
        $request->patient_no,
        (int) $request->drug_id,
        $request->dosage,
        $request->frequency,
        $request->start_date,
        $request->end_date ?? null,
    ]);

    return redirect()
        ->route('medical-records.index')
        ->with('success', $result[0]->message);
    }

    public function show($patient_no)
{
    $patients = DB::table('patients')
        ->orderBy('patient_no')
        ->get();

    $selectedPatient = DB::table('patients')
        ->where('patient_no', $patient_no)
        ->first();

    $drugs = DB::table('drugs')
        ->orderBy('drug_name')
        ->get();

    $medications = DB::table('medication_records')
        ->join('drugs', 'medication_records.drug_id', '=', 'drugs.drug_id')
        ->where('medication_records.patient_no', $patient_no)
        ->select('medication_records.*', 'drugs.drug_name')
        ->orderBy('start_date', 'desc')
        ->get();

    return view('module1.medicalrecords', compact(
        'patients',
        'selectedPatient',
        'drugs',
        'medications'
    ));
    }

    public function update(Request $request, $medication_id)
{
    $request->validate([
        'drug_id' => 'required|exists:drugs,drug_id',
        'dosage' => 'required|max:100',
        'frequency' => 'required|max:100',
        'start_date' => 'required|date',
        'end_date' => 'nullable|date',
    ]);

    $result = DB::select(
        "SELECT fn_update_medication_record(?, ?, ?, ?, ?, ?) AS message",
        [
            (int) $medication_id,
            (int) $request->drug_id,
            $request->dosage,
            $request->frequency,
            $request->start_date,
            $request->end_date,
        ]
    );

    return back()->with('success', $result[0]->message);
    }
}
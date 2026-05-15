<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\Request;

class PatientApiController extends Controller
{
    public function index()
    {
        return response()->json(Patient::all());
    }

    // CREATE patient
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_no' => 'required|string|unique:patients,patient_no',
            'doctor_id' => 'required|integer|exists:local_doctors,doctor_id',
            'nextofkinid' => 'required|integer|exists:next_of_kin,nextofkinid',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'date_of_birth' => 'required|date',
            'gender' => 'required|string',
            'address' => 'required|string',
            'phone_no' => 'required|string',
            'marital_status' => 'nullable|string',
        ]);

        $patient = Patient::create($validated);

        return response()->json([
            'message' => 'Patient created successfully',
            'patient' => $patient
        ], 201);
    }

    // GET single patient
    public function show($patient_no)
    {
        $patient = Patient::findOrFail($patient_no);

        return response()->json($patient);
    }

    // UPDATE patient
    public function update(Request $request, $patient_no)
    {
        $patient = Patient::findOrFail($patient_no);

        $validated = $request->validate([
            'doctor_id' => 'sometimes|integer|exists:local_doctors,doctor_id',
            'nextofkinid' => 'sometimes|integer|exists:next_of_kin,nextofkinid',
            'first_name' => 'sometimes|string',
            'last_name' => 'sometimes|string',
            'date_of_birth' => 'sometimes|date',
            'gender' => 'sometimes|string',
            'address' => 'sometimes|string',
            'phone_no' => 'sometimes|string',
            'marital_status' => 'nullable|string',
        ]);

        $patient->update($validated);

        return response()->json([
            'message' => 'Patient updated successfully',
            'patient' => $patient
        ]);
    }

    // DELETE patient
    public function destroy($patient_no)
    {
        $patient = Patient::findOrFail($patient_no);

        $patient->delete();

        return response()->json([
            'message' => 'Patient deleted successfully'
        ]);
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\LocalDoctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PatientController extends Controller
{
    public function create()
    {
        $doctors = LocalDoctor::all();

        $patients = DB::table('vw_patient_profile')
            ->orderBy('patient_no', 'desc')
            ->get();

        return view('module1.patientreg', compact('doctors', 'patients'));
    }

    public function store(Request $request)
    {

        $request->validate([
            'first_name' => 'required|max:100',
            'last_name' => 'required|max:100',
            'date_of_birth' => 'required|date',
            'gender' => 'required',
            'phone_no' => 'required|max:20',
            'marital_status' => 'nullable|max:50',
            'address' => 'required|max:255',
            'doctor_id' => 'required|exists:local_doctors,doctor_id',

            'kin_fullname' => 'required|max:150',
            'relationshiptopatient' => 'required|max:100',
            'kin_telno' => 'required|max:20',
            'kin_address' => 'required|max:255',
        ]);

        $result = DB::select(
            "SELECT fn_register_patient_with_kin(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) AS message",
            [
                $request->first_name,
                $request->last_name,
                $request->date_of_birth,
                $request->gender,
                $request->phone_no,
                $request->marital_status,
                $request->address,
                (int) $request->doctor_id,

                $request->kin_fullname,
                $request->relationshiptopatient,
                $request->kin_telno,
                $request->kin_address,
            ]
        );

        return redirect()
            ->route('patients.create')
            ->with('success', $result[0]->message);
    }
   public function edit($patient_no)
{
    $patient = DB::table('patients')
        ->where('patient_no', $patient_no)
        ->first();

    if (!$patient) {
        abort(404);
    }

    $nextOfKin = DB::table('next_of_kin')
        ->where('nextofkinid', $patient->nextofkinid)
        ->first();

    $doctors = LocalDoctor::all();

    return view('module1.editpatientinfo', compact('patient', 'nextOfKin', 'doctors'));
}

public function update(Request $request, $patient_no)
{
    $request->validate([
        'first_name' => 'required|max:100',
        'last_name' => 'required|max:100',
        'date_of_birth' => 'required|date',
        'gender' => 'required',
        'phone_no' => 'required|max:20',
        'marital_status' => 'nullable|max:50',
        'address' => 'required|max:255',
        'doctor_id' => 'required|exists:local_doctors,doctor_id',

        'kin_fullname' => 'required|max:150',
        'relationshiptopatient' => 'required|max:100',
        'kin_telno' => 'required|max:20',
        'kin_address' => 'required|max:255',
    ]);

    $result = DB::select(
        "SELECT fn_update_patient_info(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) AS message",
        [
            $patient_no,
            $request->first_name,
            $request->last_name,
            $request->date_of_birth,
            $request->gender,
            $request->phone_no,
            $request->marital_status,
            $request->address,
            (int) $request->doctor_id,

            $request->kin_fullname,
            $request->relationshiptopatient,
            $request->kin_telno,
            $request->kin_address,
        ]
    );

    return redirect()
        ->route('patients.create')
        ->with('success', $result[0]->message);
}
}
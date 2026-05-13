<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\LocalDoctor;
use App\Models\NextOfKin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PatientController extends Controller
{
    public function index()
    {
        $patients = Patient::with(['localDoctor', 'nextOfKin'])
            ->orderBy('patientno', 'desc')
            ->get();

        return view('patients.index', compact('patients'));
    }

    public function create()
    {
        $doctors = LocalDoctor::all();
        $nextOfKins = NextOfKin::all();

        return view('patients.create', compact('doctors', 'nextOfKins'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'firstname' => 'required|max:50',
            'lastname' => 'required|max:50',
            'address' => 'nullable|max:150',
            'telno' => 'nullable|max:20',
            'dob' => 'required|date',
            'sex' => 'required',
            'maritalstatus' => 'nullable|max:50',
            'doctor_id' => 'nullable|integer',
            'nextofkinid' => 'nullable|integer',
        ]);

        $result = DB::select(
            "SELECT fn_register_patient(?, ?, ?, ?, ?, ?, ?, ?, ?) AS message",
            [
                $request->firstname,
                $request->lastname,
                $request->address,
                $request->telno,
                $request->dob,
                $request->sex,
                $request->maritalstatus,
                $request->doctor_id,
                $request->nextofkinid,
            ]
        );

        return redirect()
            ->route('patients.index')
            ->with('success', $result[0]->message);
    }

    public function show(string $id)
    {
        $patient = Patient::with(['localDoctor', 'nextOfKin'])->findOrFail($id);

        return view('patients.show', compact('patient'));
    }

    public function edit(string $id)
    {
        $patient = Patient::findOrFail($id);
        $doctors = LocalDoctor::all();
        $nextOfKins = NextOfKin::all();

        return view('patients.edit', compact('patient', 'doctors', 'nextOfKins'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'address' => 'nullable|max:150',
            'telno' => 'nullable|max:20',
            'maritalstatus' => 'nullable|max:50',
        ]);

        $result = DB::select(
            "SELECT fn_update_patient(?, ?, ?, ?) AS message",
            [
                $id,
                $request->address,
                $request->telno,
                $request->maritalstatus,
            ]
        );

        return redirect()
            ->route('patients.index')
            ->with('success', $result[0]->message);
    }

    public function destroy(string $id)
    {
        Patient::findOrFail($id)->delete();

        return redirect()
            ->route('patients.index')
            ->with('success', 'Patient deleted successfully.');
    }
}
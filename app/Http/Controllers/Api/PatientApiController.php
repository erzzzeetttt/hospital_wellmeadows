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

    public function store(Request $request)
    {
        $patient = Patient::create($request->all());

        return response()->json([
            'message' => 'Patient created successfully',
            'patient' => $patient
        ], 201);
    }
}
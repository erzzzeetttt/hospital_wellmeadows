<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RoleDashboardController extends Controller
{
    public function receptionist()
    {
        $name = Auth::user()->name ?? 'Receptionist';

        $todayAppointments = DB::table('appointments')
            ->whereDate('appointment_date', today())
            ->whereNotIn('status', ['Completed', 'Cancelled'])
            ->count();

        $totalPatients = DB::table('patients')->count();

        return view('dashboards.receptionist', compact('name', 'todayAppointments', 'totalPatients'));
    }

    public function chargeNurse()
    {
        $name = Auth::user()->name ?? 'Charge Nurse';

        $occupiedBeds = DB::table('beds')
            ->where('status', 'Occupied')
            ->count();

        $admittedPatients = DB::table('ward_admissions')
            ->where('status', 'Admitted')
            ->count();

        return view('dashboards.charge_nurse', compact('name', 'occupiedBeds', 'admittedPatients'));
    }
}

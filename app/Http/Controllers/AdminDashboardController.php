<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $totalUsers = DB::table('users')->count();

        $totalPatients = DB::table('patients')->count();

        $activeAdmissions = DB::table('ward_admissions')
            ->where('status', 'Admitted')
            ->count();

        $totalWards = DB::table('wards')->count();

        $availableBeds = DB::table('beds')
            ->where('status', 'Available')
            ->count();

        return view('dashboards.admin', compact(
            'totalUsers',
            'totalPatients',
            'activeAdmissions',
            'totalWards',
            'availableBeds',
        ));
    }
}

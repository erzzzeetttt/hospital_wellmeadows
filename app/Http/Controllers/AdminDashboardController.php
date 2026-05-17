<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $totalUsers = DB::table('users')->count();

        $totalPatients = DB::table('patients')->count();

        $activeAdmissions = DB::table('ward_admissions')
            ->where('status', 'Admitted')
            ->count();

        return view('dashboards.admin', compact(
            'totalUsers',
            'totalPatients',
            'activeAdmissions'
        ));
    }
}
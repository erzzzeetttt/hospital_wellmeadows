<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StaffController extends Controller
{
    public function index()
    {
        $staff = DB::table('staff as s')
            ->leftJoin('roles as r', 's.role_id', '=', 'r.role_id')
            ->select('s.*', 'r.role_name')
            ->orderBy('s.staff_no', 'desc')
            ->get();

        $roles = DB::table('roles')
            ->whereIn('role_name', ['Administrator', 'Receptionist', 'Charge Nurse'])
            ->orderBy('role_name')
            ->get();

        return view('staff.index', compact('staff', 'roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'role_id'                          => 'required|exists:roles,role_id',
            'first_name'                       => 'required|max:100',
            'last_name'                        => 'required|max:100',
            'dob'                              => 'required|date',
            'gender'                           => 'required',
            'address'                          => 'required|max:255',
            'phone_no'                         => 'required|max:20',
            'nin'                              => 'nullable|max:20',
            'position'                         => 'required|max:100',
            'salary'                           => 'required|numeric|min:0|max:99999999.99',
            'salary_scale'                     => 'nullable|max:20',
            'hours_per_week'                   => 'required|numeric|min:0|max:999.99',
            'contract_type'                    => 'nullable|in:Permanent,Temporary',
            'payment_type'                     => 'nullable|in:Weekly,Monthly',
            'date_registered'                  => 'nullable|date',
            'qualifications.*.qualification_type' => 'nullable|max:150',
            'qualifications.*.date_obtained'      => 'nullable|date',
            'qualifications.*.institution'        => 'nullable|max:150',
            'experiences.*.position'              => 'nullable|max:100',
            'experiences.*.start_date'            => 'nullable|date',
            'experiences.*.end_date'              => 'nullable|date',
            'experiences.*.organization_name'     => 'nullable|max:150',
        ]);

        $existing = DB::select(
            'SELECT staff_no FROM staff WHERE LOWER(first_name) = LOWER(?) AND LOWER(last_name) = LOWER(?)',
            [$request->first_name, $request->last_name]
        );

        if (!empty($existing)) {
            return redirect()->back()
                ->with('error', 'A staff member with the same name already exists.')
                ->withInput();
        }

        try {
            $result = DB::select(
                "SELECT fn_add_staff(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) AS new_staff_no",
                [
                    (int) $request->role_id,
                    $request->first_name,
                    $request->last_name,
                    $request->dob,
                    $request->gender,
                    $request->address,
                    $request->phone_no,
                    $request->nin,
                    $request->position,
                    (float) $request->salary,
                    $request->salary_scale,
                    $request->hours_per_week ? (float) $request->hours_per_week : null,
                    $request->contract_type,
                    $request->payment_type,
                    $request->date_registered,
                ]
            );

            $staffNo = $result[0]->new_staff_no;

            // Insert qualifications
            foreach (($request->qualifications ?? []) as $qual) {
                if (!empty($qual['qualification_type'])) {
                    DB::select(
                        "SELECT fn_add_staff_qualification(?, ?, ?, ?) AS message",
                        [
                            $staffNo,
                            $qual['qualification_type'],
                            !empty($qual['date_obtained']) ? $qual['date_obtained'] : null,
                            $qual['institution'] ?? null,
                        ]
                    );
                }
            }

            // Insert work experiences
            foreach (($request->experiences ?? []) as $exp) {
                if (!empty($exp['organization_name'])) {
                    DB::select(
                        "SELECT fn_add_staff_experience(?, ?, ?, ?, ?) AS message",
                        [
                            $staffNo,
                            $exp['position'] ?? null,
                            !empty($exp['start_date']) ? $exp['start_date'] : null,
                            !empty($exp['end_date']) ? $exp['end_date'] : null,
                            $exp['organization_name'],
                        ]
                    );
                }
            }

            return redirect()->route('staff.index')
                ->with('success', $request->first_name . ' ' . $request->last_name . ' has been registered successfully.');

        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to register staff: ' . $e->getMessage());
        }
    }

    public function edit($staff_no)
    {
        $staff = DB::table('staff as s')
            ->leftJoin('roles as r', 's.role_id', '=', 'r.role_id')
            ->select('s.*', 'r.role_name')
            ->where('s.staff_no', $staff_no)
            ->first();

        if (!$staff) {
            abort(404);
        }

        $roles = DB::table('roles')
            ->whereIn('role_name', ['Administrator', 'Receptionist', 'Charge Nurse'])
            ->orderBy('role_name')
            ->get();

        $qualifications = DB::table('staff_qualifications')
            ->where('staff_no', $staff_no)
            ->orderBy('date_obtained', 'asc')
            ->get();

        $workExperiences = DB::table('staff_experience')
            ->where('staff_no', $staff_no)
            ->orderBy('start_date', 'asc')
            ->get();

        return view('staff.edit', compact('staff', 'roles', 'qualifications', 'workExperiences'));
    }

    public function update(Request $request, $staff_no)
    {
        $request->validate([
            'role_id'                          => 'required|exists:roles,role_id',
            'first_name'                       => 'required|max:100',
            'last_name'                        => 'required|max:100',
            'dob'                              => 'required|date',
            'gender'                           => 'required',
            'address'                          => 'required|max:255',
            'phone_no'                         => 'required|max:20',
            'nin'                              => 'nullable|max:20',
            'position'                         => 'required|max:100',
            'salary'                           => 'required|numeric|min:0|max:99999999.99',
            'salary_scale'                     => 'nullable|max:20',
            'hours_per_week'                   => 'required|numeric|min:0|max:999.99',
            'contract_type'                    => 'nullable|in:Permanent,Temporary',
            'payment_type'                     => 'nullable|in:Weekly,Monthly',
            'date_registered'                  => 'nullable|date',
            'qualifications.*.qualification_type' => 'nullable|max:150',
            'qualifications.*.date_obtained'      => 'nullable|date',
            'qualifications.*.institution'        => 'nullable|max:150',
            'experiences.*.position'              => 'nullable|max:100',
            'experiences.*.start_date'            => 'nullable|date',
            'experiences.*.end_date'              => 'nullable|date',
            'experiences.*.organization_name'     => 'nullable|max:150',
        ]);

        try {
            DB::select(
                "SELECT fn_update_staff(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) AS message",
                [
                    $staff_no,
                    (int) $request->role_id,
                    $request->first_name,
                    $request->last_name,
                    $request->dob,
                    $request->gender,
                    $request->address,
                    $request->phone_no,
                    $request->nin,
                    $request->position,
                    (float) $request->salary,
                    $request->salary_scale,
                    $request->hours_per_week ? (float) $request->hours_per_week : null,
                    $request->contract_type,
                    $request->payment_type,
                    $request->date_registered,
                ]
            );

            // Replace qualifications: delete existing then reinsert from form
            DB::delete("DELETE FROM staff_qualifications WHERE staff_no = ?", [$staff_no]);
            foreach (($request->qualifications ?? []) as $qual) {
                if (!empty($qual['qualification_type'])) {
                    DB::select(
                        "SELECT fn_add_staff_qualification(?, ?, ?, ?) AS message",
                        [
                            $staff_no,
                            $qual['qualification_type'],
                            !empty($qual['date_obtained']) ? $qual['date_obtained'] : null,
                            $qual['institution'] ?? null,
                        ]
                    );
                }
            }

            // Replace work experience: delete existing then reinsert from form
            DB::delete("DELETE FROM staff_experience WHERE staff_no = ?", [$staff_no]);
            foreach (($request->experiences ?? []) as $exp) {
                if (!empty($exp['organization_name'])) {
                    DB::select(
                        "SELECT fn_add_staff_experience(?, ?, ?, ?, ?) AS message",
                        [
                            $staff_no,
                            $exp['position'] ?? null,
                            !empty($exp['start_date']) ? $exp['start_date'] : null,
                            !empty($exp['end_date']) ? $exp['end_date'] : null,
                            $exp['organization_name'],
                        ]
                    );
                }
            }

            return redirect()->route('staff.index')
                ->with('success', 'Staff record updated successfully.');

        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to update staff: ' . $e->getMessage());
        }
    }

    public function show($staff_no)
    {
        $staff = DB::table('staff as s')
            ->leftJoin('roles as r', 's.role_id', '=', 'r.role_id')
            ->select('s.*', 'r.role_name')
            ->where('s.staff_no', $staff_no)
            ->first();

        if (!$staff) {
            abort(404);
        }

        $qualifications = DB::table('staff_qualifications')
            ->where('staff_no', $staff_no)
            ->orderBy('date_obtained', 'desc')
            ->get();

        $workExperiences = DB::table('staff_experience')
            ->where('staff_no', $staff_no)
            ->orderBy('start_date', 'desc')
            ->get();

        $wardAssignments = DB::table('staff_ward_assignments as swa')
            ->join('wards as w', 'swa.ward_id', '=', 'w.ward_id')
            ->select('swa.*', 'w.ward_name')
            ->where('swa.staff_no', $staff_no)
            ->orderBy('swa.assignment_date', 'desc')
            ->get();

        return view('staff.show', compact('staff', 'qualifications', 'workExperiences', 'wardAssignments'));
    }

    public function destroy($staff_no)
    {
        try {
            $result = DB::select('SELECT fn_delete_staff(?) AS message', [
                $staff_no,
            ]);

            return redirect()->route('staff.index')->with('success', $result[0]->message);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete staff: ' . $e->getMessage());
        }
    }

    public function wardAssignment()
    {
        $staff = DB::table('staff as s')
            ->leftJoin('roles as r', 's.role_id', '=', 'r.role_id')
            ->select('s.staff_no', 's.first_name', 's.last_name', 's.position', 'r.role_name')
            ->orderBy('s.last_name')
            ->get();

        $wards = DB::table('wards')->orderBy('ward_name')->get();

        $assignments = DB::table('staff_ward_assignments as swa')
            ->join('staff as s', 'swa.staff_no', '=', 's.staff_no')
            ->join('wards as w', 'swa.ward_id', '=', 'w.ward_id')
            ->select('swa.*', 's.first_name', 's.last_name', 's.position', 'w.ward_name')
            ->whereNull('swa.end_date')
            ->orderBy('swa.assignment_date', 'desc')
            ->get();

        return view('staff.ward_assignment', compact('staff', 'wards', 'assignments'));
    }

    public function storeWardAssignment(Request $request)
    {
        $request->validate([
            'staff_no'        => 'required|exists:staff,staff_no',
            'ward_id'         => 'required|exists:wards,ward_id',
            'assignment_date' => 'required|date',
            'role_in_ward'    => 'nullable|max:100',
        ]);

        $existing = DB::select(
            'SELECT assignment_id FROM staff_ward_assignments WHERE staff_no = ? AND ward_id = ? AND end_date IS NULL',
            [$request->staff_no, (int) $request->ward_id]
        );

        if (!empty($existing)) {
            return redirect()->back()->with('error', 'This staff member is already actively assigned to that ward.');
        }

        try {
            $result = DB::select(
                "SELECT fn_assign_staff_to_ward(?, ?, ?, ?) AS message",
                [
                    $request->staff_no,
                    (int) $request->ward_id,
                    $request->assignment_date,
                    $request->role_in_ward,
                ]
            );

            return redirect()->route('staff.ward-assignment')->with('success', $result[0]->message);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to assign staff: ' . $e->getMessage());
        }
    }

    public function schedule()
    {
        $totalStaff = DB::table('staff')->count();

        $assignedStaff = DB::table('staff_ward_assignments')
            ->whereNull('end_date')
            ->distinct('staff_no')
            ->count('staff_no');

        $totalWards = DB::table('wards')->count();

        // Active assignments — Charge Nurse ordered first within each ward
        $wardStaff = DB::table('staff_ward_assignments as swa')
            ->join('staff as s', 'swa.staff_no', '=', 's.staff_no')
            ->join('wards as w', 'swa.ward_id', '=', 'w.ward_id')
            ->leftJoin('roles as r', 's.role_id', '=', 'r.role_id')
            ->select(
                'w.ward_id', 'w.ward_name', 'w.telephone_extension',
                'swa.staff_no', 's.first_name', 's.last_name',
                's.position', 's.hours_per_week', 'r.role_name',
                'swa.role_in_ward', 'swa.assignment_date'
            )
            ->whereNull('swa.end_date')
            ->orderBy('w.ward_name')
            ->orderByRaw("CASE WHEN swa.role_in_ward = 'Charge Nurse' THEN 0 ELSE 1 END")
            ->orderBy('s.last_name')
            ->get();

        // Most recent shift per staff/ward pair
        $latestRota = DB::table('staff_weekly_rota as r1')
            ->whereRaw('r1.week_start_date = (SELECT MAX(r2.week_start_date) FROM staff_weekly_rota r2 WHERE r2.staff_no = r1.staff_no AND r2.ward_id = r1.ward_id)')
            ->select('staff_no', 'ward_id', 'shift_type', 'week_start_date')
            ->get()
            ->keyBy(fn($r) => $r->staff_no . '_' . $r->ward_id);

        // Group by ward for the view
        $wardGroups = $wardStaff->groupBy('ward_id');

        // Dropdowns for Set Shift modal
        $staff = DB::table('staff as s')
            ->select('s.staff_no', 's.first_name', 's.last_name', 's.position')
            ->orderBy('s.last_name')
            ->get();

        $wards = DB::table('wards')->orderBy('ward_name')->get();

        return view('staff.schedule', compact(
            'totalStaff', 'assignedStaff', 'totalWards',
            'wardGroups', 'latestRota', 'staff', 'wards'
        ));
    }

    public function storeRota(Request $request)
    {
        $request->validate([
            'staff_no'        => 'required|exists:staff,staff_no',
            'ward_id'         => 'required|exists:wards,ward_id',
            'week_start_date' => 'required|date',
            'shift_type'      => 'required|in:Early,Late,Night',
        ]);

        try {
            $result = DB::select('SELECT fn_set_staff_rota(?, ?, ?, ?) AS message', [
                $request->staff_no,
                $request->ward_id,
                $request->week_start_date,
                $request->shift_type,
            ]);

            return redirect()->route('staff.schedule')->with('success', $result[0]->message);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to set rota: ' . $e->getMessage());
        }
    }
}

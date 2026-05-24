<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use App\Models\Ward;
use App\Models\WardAdmission;
use App\Models\WardAllocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WardBedManagementController extends Controller
{
    // ── READ METHODS ─────────────────────────────────────────────────────────

    /**
     * All Wards page — lists every ward with live bed counts.
     */
    public function index(): View
    {
        $wards = Ward::query()
            ->withCount([
                'beds',
                'beds as vacant_beds_count'      => fn ($q) => $q->where('status', 'Available'),
                'beds as occupied_beds_count'    => fn ($q) => $q->where('status', 'Occupied'),
                'beds as maintenance_beds_count' => fn ($q) => $q->where('status', 'Maintenance'),
            ])
            ->orderBy('ward_name')
            ->get();

        return view('module3.index', compact('wards'));
    }

    /**
     * Add Ward page.
     */
    public function create(): View
    {
        return view('module3.create');
    }

    /**
     * Assign Bed page — loads all data needed for the functional assignment form.
     */
    public function showAssignBed(): View
    {
        // Ward summary cards at the top of the page.
        $wardStats = Ward::query()
            ->withCount([
                'beds',
                'beds as vacant_beds_count'   => fn ($q) => $q->where('status', 'Available'),
                'beds as occupied_beds_count' => fn ($q) => $q->where('status', 'Occupied'),
            ])
            ->orderBy('ward_name')
            ->get();

        $wards = Ward::query()->orderBy('ward_name')->get();

        $availableBeds = Bed::query()
            ->with('ward')
            ->where('status', 'Available')
            ->orderBy('ward_id')
            ->orderBy('bed_number')
            ->get();

        // Only admitted patients without an active bed allocation can be assigned.
        $admittedPatients = WardAdmission::query()
            ->with('patient')
            ->where('status', 'Admitted')
            ->whereDoesntHave('patient.activeWardAllocation')
            ->orderBy('date_admitted', 'desc')
            ->get();

        $recentAssignments = WardAllocation::query()
            ->with(['patient', 'bed', 'ward'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('module3.assign-bed', compact(
            'wardStats', 'wards', 'availableBeds', 'admittedPatients', 'recentAssignments'
        ));
    }

    /**
     * Bed Availability page — shows per-ward bed status with an optional ward filter.
     */
    public function bedAvailability(Request $request): View
    {
        $selectedWardId = $request->integer('ward_id') ?: null;

        // $wards feeds the filter dropdown; $availabilityWards carries the bed data.
        $wards = Ward::query()->orderBy('ward_name')->get();

        $availabilityWards = Ward::query()
            ->with([
                'beds' => fn ($q) => $q
                    ->with(['activeAllocation.patient'])
                    ->orderBy('bed_number'),
            ])
            ->withCount([
                'beds',
                'beds as vacant_beds_count'      => fn ($q) => $q->where('status', 'Available'),
                'beds as occupied_beds_count'    => fn ($q) => $q->where('status', 'Occupied'),
                'beds as maintenance_beds_count' => fn ($q) => $q->where('status', 'Maintenance'),
            ])
            ->when($selectedWardId, fn ($q) => $q->where('ward_id', $selectedWardId))
            ->orderBy('ward_name')
            ->get();

        $stats = [
            'totalBeds'       => $availabilityWards->sum('beds_count'),
            'vacantBeds'      => $availabilityWards->sum('vacant_beds_count'),
            'occupiedBeds'    => $availabilityWards->sum('occupied_beds_count'),
            'maintenanceBeds' => $availabilityWards->sum('maintenance_beds_count'),
        ];

        return view('module3.bed-availability', compact('wards', 'availabilityWards', 'stats', 'selectedWardId'));
    }

    // ── WARD WRITE METHODS ────────────────────────────────────────────────────

    /**
     * Store a new ward — calls fn_add_ward() which also creates bed records.
     */
    public function storeWard(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'ward_name'  => ['required', 'string', 'max:255', 'unique:wards,ward_name'],
            'ward_type'  => ['required', 'string', 'max:255'],
            'location'   => ['required', 'string', 'max:255'],
            'total_beds' => ['required', 'integer', 'min:1', 'max:200'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        try {
            DB::select('SELECT fn_add_ward(?, ?, ?, ?) AS ward_id', [
                $request->ward_name,
                $request->ward_type,
                $request->location,
                (int) $request->total_beds,
            ]);

            return redirect()->route('ward-bed-management.index')
                ->with('success', 'Ward created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Could not create ward: ' . $e->getMessage());
        }
    }

    /**
     * Update an existing ward — calls fn_update_ward().
     */
    public function updateWard(Request $request, int $id): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'ward_name'  => ['required', 'string', 'max:255', Rule::unique('wards', 'ward_name')->ignore($id, 'ward_id')],
            'ward_type'  => ['required', 'string', 'max:255'],
            'location'   => ['required', 'string', 'max:255'],
            'total_beds' => ['required', 'integer', 'min:1', 'max:200'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        try {
            DB::select('SELECT fn_update_ward(?, ?, ?, ?, ?) AS result', [
                $id,
                $request->ward_name,
                $request->ward_type,
                $request->location,
                (int) $request->total_beds,
            ]);

            return redirect()->route('ward-bed-management.index')
                ->with('success', 'Ward updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Could not update ward: ' . $e->getMessage());
        }
    }

    /**
     * Delete a ward — calls fn_delete_ward().
     */
    public function destroyWard(int $id): RedirectResponse
    {
        try {
            DB::select('SELECT fn_delete_ward(?) AS result', [$id]);

            return redirect()->route('ward-bed-management.index')
                ->with('success', 'Ward deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Could not delete ward: ' . $e->getMessage());
        }
    }

    // ── BED ASSIGNMENT METHODS ────────────────────────────────────────────────

    /**
     * Assign a bed to an admitted patient — calls fn_assign_bed_to_patient().
     */
    public function assignBed(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'ward_id'         => ['required', 'exists:wards,ward_id'],
            'bed_id'          => ['required', 'exists:beds,bed_id'],
            'patient_no'      => ['required', 'exists:patients,patient_no'],
            'allocation_date' => ['required', 'date'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        try {
            DB::select('SELECT fn_assign_bed_to_patient(?, ?, ?, ?) AS result', [
                (int) $request->ward_id,
                (int) $request->bed_id,
                $request->patient_no,
                $request->allocation_date,
            ]);

            return redirect()->route('ward-bed-management.bed-availability')
                ->with('success', 'Bed assigned successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Could not assign bed: ' . $e->getMessage());
        }
    }

    /**
     * Release an occupied bed — calls fn_release_bed().
     */
    public function releaseBed(int $id): RedirectResponse
    {
        try {
            DB::select('SELECT fn_release_bed(?) AS result', [$id]);

            return redirect()->route('ward-bed-management.bed-availability')
                ->with('success', 'Bed released successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Could not release bed: ' . $e->getMessage());
        }
    }

    // ── BED MANAGEMENT METHODS ────────────────────────────────────────────────

    /**
     * Add a single bed to a ward — calls fn_add_bed().
     */
    public function storeBed(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'ward_id'    => ['required', 'exists:wards,ward_id'],
            'bed_number' => ['required', 'string', 'max:10'],
            'status'     => ['nullable', 'string', 'in:Available,Occupied,Maintenance'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        try {
            DB::select('SELECT fn_add_bed(?, ?, ?) AS bed_id', [
                (int) $request->ward_id,
                $request->bed_number,
                $request->status ?? 'Available',
            ]);

            return redirect()->route('ward-bed-management.bed-availability')
                ->with('success', 'Bed added successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Could not add bed: ' . $e->getMessage());
        }
    }

    /**
     * Update a bed's status — calls fn_update_bed_status().
     */
    public function updateBedStatus(Request $request, int $id): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => ['required', 'string', 'in:Available,Occupied,Maintenance'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        try {
            DB::select('SELECT fn_update_bed_status(?, ?) AS result', [
                $id,
                $request->status,
            ]);

            return redirect()->route('ward-bed-management.bed-availability')
                ->with('success', 'Bed status updated to ' . $request->status . '.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Could not update bed status: ' . $e->getMessage());
        }
    }
}

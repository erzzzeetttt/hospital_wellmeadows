<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use App\Models\Patient;
use App\Models\Staff;
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
    /**
     * Load the data used by all four Module 3 tabs.
     */
    public function index(Request $request): View
    {
        $selectedWardId = $request->integer('ward_id') ?: null;

        // The Add Ward form only offers staff whose position is Charge Nurse.
        $chargeNurses = Staff::query()
            ->where('position', 'Charge Nurse')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        // Counts are loaded from the beds table so the All Wards tab always reflects the database.
        $wards = Ward::query()
            ->withCount([
                'beds',
                'beds as vacant_beds_count' => fn ($query) => $query->where('status', 'Available'),
                'beds as occupied_beds_count' => fn ($query) => $query->where('status', 'Occupied'),
                'beds as maintenance_beds_count' => fn ($query) => $query->where('status', 'Maintenance'),
            ])
            ->orderBy('ward_name')
            ->get();

        $availabilityWards = Ward::query()
            ->with([
                'beds' => fn ($query) => $query
                    ->with(['activeAllocation.patient'])
                    ->orderBy('bed_number'),
            ])
            ->withCount([
                'beds',
                'beds as vacant_beds_count' => fn ($query) => $query->where('status', 'Available'),
                'beds as occupied_beds_count' => fn ($query) => $query->where('status', 'Occupied'),
                'beds as maintenance_beds_count' => fn ($query) => $query->where('status', 'Maintenance'),
            ])
            ->when($selectedWardId, fn ($query) => $query->where('ward_id', $selectedWardId))
            ->orderBy('ward_name')
            ->get();

        $stats = [
            'totalBeds' => $availabilityWards->sum('beds_count'),
            'vacantBeds' => $availabilityWards->sum('vacant_beds_count'),
            'occupiedBeds' => $availabilityWards->sum('occupied_beds_count'),
            'maintenanceBeds' => $availabilityWards->sum('maintenance_beds_count'),
        ];

        $availableBeds = Bed::query()
            ->with('ward')
            ->where('status', 'Available')
            ->orderBy('ward_id')
            ->orderBy('bed_number')
            ->get();

        // The patient dropdown uses admitted patients from Module 1 and hides patients already assigned to a bed.
        $admittedPatients = WardAdmission::query()
            ->with(['patient.doctor'])
            ->where('status', 'Admitted')
            ->whereDoesntHave('patient.activeWardAllocation')
            ->orderBy('date_admitted', 'desc')
            ->get();

        return view('module3.wardbedmanagement', [
            'activeTab' => session('active_tab', $request->query('tab', 'all-wards')),
            'admittedPatients' => $admittedPatients,
            'availableBeds' => $availableBeds,
            'availabilityWards' => $availabilityWards,
            'chargeNurses' => $chargeNurses,
            'selectedWardId' => $selectedWardId,
            'stats' => $stats,
            'wards' => $wards,
        ]);
    }

    /**
     * Create a ward and generate its available bed records.
     */
    public function storeWard(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'ward_name' => ['required', 'string', 'max:255', 'unique:wards,ward_name'],
            'ward_type' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'total_beds' => ['required', 'integer', 'min:1', 'max:200'],
            'charge_nurse_staff_no' => [
                'required',
                Rule::exists('staff', 'staff_no')->where('position', 'Charge Nurse'),
            ],
            'telephone_extension' => ['nullable', 'string', 'max:20'],
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withInput()
                ->with('active_tab', 'add-ward')
                ->withErrors($validator);
        }

        $validated = $validator->validated();

        // The selected staff member is saved as the ward's charge nurse display name.
        $chargeNurse = Staff::query()
            ->where('position', 'Charge Nurse')
            ->findOrFail($validated['charge_nurse_staff_no']);

        $wardData = [
            'ward_name' => $validated['ward_name'],
            'ward_type' => $validated['ward_type'],
            'location' => $validated['location'],
            'total_beds' => $validated['total_beds'],
            'charge_nurse' => $chargeNurse->first_name.' '.$chargeNurse->last_name,
            'telephone_extension' => $validated['telephone_extension'] ?? null,
        ];

        DB::transaction(function () use ($wardData): void {
            $ward = Ward::create($wardData);

            // Bed records are created immediately so the new ward appears in Assign Bed and Availability.
            for ($bedNumber = 1; $bedNumber <= $ward->total_beds; $bedNumber++) {
                Bed::create([
                    'ward_id' => $ward->ward_id,
                    'bed_number' => str_pad((string) $bedNumber, 2, '0', STR_PAD_LEFT),
                    'status' => 'Available',
                ]);
            }
        });

        return redirect()
            ->route('ward-bed-management.index')
            ->with('active_tab', 'all-wards')
            ->with('success', 'Ward created successfully.');
    }

    /**
     * Assign one available bed to one admitted patient.
     */
    public function assignBed(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'ward_id' => ['required', 'exists:wards,ward_id'],
            'bed_id' => ['required', 'exists:beds,bed_id'],
            'patient_no' => ['required', 'exists:patients,patient_no'],
            'allocation_date' => ['required', 'date'],
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withInput()
                ->with('active_tab', 'assign-bed')
                ->withErrors($validator);
        }

        $validated = $validator->validated();

        return DB::transaction(function () use ($validated): RedirectResponse {
            $bed = Bed::query()
                ->where('bed_id', $validated['bed_id'])
                ->where('ward_id', $validated['ward_id'])
                ->where('status', 'Available')
                ->lockForUpdate()
                ->first();

            if (! $bed) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('active_tab', 'assign-bed')
                    ->withErrors(['bed_id' => 'The selected bed is no longer available for this ward.']);
            }

            $isAdmitted = WardAdmission::query()
                ->where('patient_no', $validated['patient_no'])
                ->where('status', 'Admitted')
                ->exists();

            if (! $isAdmitted) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('active_tab', 'assign-bed')
                    ->withErrors(['patient_no' => 'Only admitted patients can be assigned to a bed.']);
            }

            $hasActiveAllocation = Patient::query()
                ->where('patient_no', $validated['patient_no'])
                ->whereHas('activeWardAllocation')
                ->exists();

            if ($hasActiveAllocation) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('active_tab', 'assign-bed')
                    ->withErrors(['patient_no' => 'This patient is already assigned to a bed.']);
            }

            WardAllocation::create($validated);

            $bed->update(['status' => 'Occupied']);

            return redirect()
                ->route('ward-bed-management.index', ['tab' => 'bed-availability'])
                ->with('success', 'Bed assigned successfully.');
        });
    }
}

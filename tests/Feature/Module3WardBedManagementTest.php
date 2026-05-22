<?php

namespace Tests\Feature;

use App\Http\Controllers\WardBedManagementController;
use App\Models\Bed;
use App\Models\LocalDoctor;
use App\Models\Patient;
use App\Models\Staff;
use App\Models\Ward;
use App\Models\WardAdmission;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class Module3WardBedManagementTest extends TestCase
{
    public function test_module3_routes_point_to_the_backend_controller(): void
    {
        $this->assertSame(
            WardBedManagementController::class.'@index',
            Route::getRoutes()->getByName('ward-bed-management.index')->getActionName()
        );
        $this->assertSame(
            WardBedManagementController::class.'@storeWard',
            Route::getRoutes()->getByName('ward-bed-management.wards.store')->getActionName()
        );
        $this->assertSame(
            WardBedManagementController::class.'@assignBed',
            Route::getRoutes()->getByName('ward-bed-management.assign-bed.store')->getActionName()
        );
    }

    public function test_ward_bed_management_view_renders_backend_data(): void
    {
        $ward = new Ward([
            'ward_name' => 'Grampian',
            'ward_type' => 'Orthopaedic',
            'location' => 'Block A, Floor 2',
            'charge_nurse' => 'Helen Forsythe',
            'total_beds' => 1,
        ]);
        $ward->ward_id = 1;
        $ward->beds_count = 1;
        $ward->vacant_beds_count = 1;
        $ward->occupied_beds_count = 0;
        $ward->maintenance_beds_count = 0;

        $bed = new Bed([
            'bed_number' => '01',
            'status' => 'Available',
        ]);
        $bed->bed_id = 1;
        $bed->ward_id = 1;
        $bed->setRelation('ward', $ward);
        $bed->setRelation('activeAllocation', null);
        $ward->setRelation('beds', collect([$bed]));

        $doctor = new LocalDoctor(['fullname' => 'Dr. R. Kinnaird']);
        $patient = new Patient([
            'patient_no' => 'PT-0001',
            'first_name' => 'Margaret',
            'last_name' => 'Tulloch',
        ]);
        $patient->setRelation('doctor', $doctor);

        $admission = new WardAdmission([
            'patient_no' => 'PT-0001',
            'date_admitted' => '2026-05-23',
            'status' => 'Admitted',
        ]);
        $admission->setRelation('patient', $patient);

        $chargeNurse = new Staff([
            'first_name' => 'Helen',
            'last_name' => 'Forsythe',
            'position' => 'Charge Nurse',
        ]);
        $chargeNurse->staff_no = 1;

        $this->view('module3.wardbedmanagement', [
            'activeTab' => 'all-wards',
            'admittedPatients' => collect([$admission]),
            'availableBeds' => collect([$bed]),
            'availabilityWards' => collect([$ward]),
            'chargeNurses' => collect([$chargeNurse]),
            'selectedWardId' => null,
            'stats' => [
                'totalBeds' => 1,
                'vacantBeds' => 1,
                'occupiedBeds' => 0,
                'maintenanceBeds' => 0,
            ],
            'wards' => collect([$ward]),
        ])
            ->assertSee('Grampian')
            ->assertSee('Helen Forsythe')
            ->assertSee('PT-0001 - Margaret Tulloch')
            ->assertSee('Select charge nurse')
            ->assertSee('ward-bed-management/wards')
            ->assertSee('ward-bed-management/assign-bed');
    }
}

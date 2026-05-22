# Module 3 Ward and Bed Management Changes

This document records the front-end, backend, routing, database, and testing changes made for the Ward and Bed Management module.

## Files Added

### `app/Http/Controllers/WardBedManagementController.php`

Added the backend controller for Module 3.

The controller handles:

- Loading all wards and their bed counts for the `All Wards` tab.
- Loading staff whose position is `Charge Nurse` for the Add Ward contact dropdown.
- Loading admitted patients from Module 1 for the `Assign Bed` patient dropdown.
- Loading available beds for the selected ward.
- Loading bed availability totals and ward-by-ward bed status.
- Creating wards.
- Creating bed records automatically when a ward is created.
- Assigning a patient to an available bed.
- Marking the assigned bed as `Occupied`.
- Filtering bed availability by ward.

Important methods:

```php
index()
```

Loads the data needed by all four tabs.

```php
storeWard()
```

Validates and creates a ward, then creates one available bed record for each bed in the ward capacity.

```php
assignBed()
```

Validates ward, bed, patient, and allocation date, then creates a ward allocation and updates the bed status to `Occupied`.

### `resources/views/module3/wardbedmanagement.blade.php`

Added and then connected the Module 3 Ward and Bed Management interface to backend data.

The page contains four CSS-tab sections:

- `All Wards`
- `Add Ward`
- `Assign Bed`
- `Bed Availability`

Current backend behavior:

- `All Wards` displays real wards from the `wards` table.
- Bed counts are calculated from the `beds` table.
- `Add Ward` posts to the backend and creates a ward.
- The Add Ward charge nurse field is a dropdown of staff records where `position = Charge Nurse`.
- `Assign Bed` lists wards, available beds, and admitted patients from existing module data.
- The patient detail text inputs were replaced with an admitted-patient dropdown.
- Selecting a patient fills the consulting doctor and admission date fields.
- `Bed Availability` displays total, vacant, occupied, and maintenance beds.
- The bed availability section can be filtered by ward.

The page links to its external stylesheet using:

```blade
<link rel="stylesheet" href="{{ asset('css/module3css/wardbedmanagement.css') }}">
```

The page also includes a `Back to Dashboard` link using:

```blade
{{ route('admin.dashboard') }}
```

### `public/css/module3css/wardbedmanagement.css`

Added the external CSS for the Module 3 interface.

This stylesheet controls:

- Header layout
- Blue tab navigation
- Ward table layout
- Add Ward form layout
- Assign Bed form layout
- Bed availability summary cards
- Empty-state styling
- Success and validation error messages
- Responsive behavior for smaller screens

The CSS is stored in `public/css/module3css` so it follows the existing project pattern used by other modules.

### `tests/Feature/Module3WardBedManagementTest.php`

Added focused feature tests for Module 3.

The tests verify:

- Module 3 routes point to `WardBedManagementController`.
- The Blade view renders backend-provided ward data.
- The Blade view renders backend-provided patient data.
- The Add Ward form points to the correct backend route.
- The Assign Bed form points to the correct backend route.

The test avoids database migrations because the current PHPUnit setup uses SQLite, while existing project migrations include PostgreSQL stored-function SQL that SQLite cannot run.

### `database/migrations/2026_05_22_163516_add_module3_details_to_wards_table.php`

Added nullable fields needed by the Module 3 frontend:

```text
ward_type
charge_nurse
```

These were added because the reference UI includes Ward Type and Charge Nurse, but the original `wards` table did not have columns for those values.

## Files Updated

### `routes/web.php`

The original Module 3 route was changed from a static closure to controller routes.

```php
Route::get('/ward-bed-management', [WardBedManagementController::class, 'index'])
    ->middleware('auth')
    ->name('ward-bed-management.index');

Route::post('/ward-bed-management/wards', [WardBedManagementController::class, 'storeWard'])
    ->middleware('auth')
    ->name('ward-bed-management.wards.store');

Route::post('/ward-bed-management/assign-bed', [WardBedManagementController::class, 'assignBed'])
    ->middleware('auth')
    ->name('ward-bed-management.assign-bed.store');
```

Route purposes:

- `ward-bed-management.index`: shows the Module 3 page.
- `ward-bed-management.wards.store`: creates a ward and its bed records.
- `ward-bed-management.assign-bed.store`: assigns an available bed to an admitted patient.

The Module 3 page is available at:

```text
/ward-bed-management
```

The named route is:

```text
ward-bed-management.index
```

### `resources/views/dashboards/admin.blade.php`

Updated the dashboard sidebar link for `Ward and Bed Management`.

Before:

```blade
<a href="#">Ward and Bed Management</a>
```

After:

```blade
<a href="{{ route('ward-bed-management.index') }}">Ward and Bed Management</a>
```

This connects the dashboard sidebar button to the Module 3 interface.

The dashboard cards were also updated:

```blade
{{ $totalWards }}
{{ $availableBeds }}
```

Those values now come from the database instead of hardcoded zeroes.

### `app/Http/Controllers/AdminDashboardController.php`

Added database counts for:

- Total wards
- Available beds

These values are passed to `dashboards.admin`.

### `app/Models/Staff.php`

No schema change was needed for staff. Module 3 uses existing staff data and filters staff by:

```text
position = Charge Nurse
```

Those filtered staff records populate the Add Ward `Charge Nurse` dropdown.

### `app/Models/Ward.php`

Updated the fillable fields to support Module 3 ward creation:

```php
ward_name
ward_type
total_beds
location
charge_nurse
telephone_extension
```

The `beds()` relationship is used to calculate ward and availability counts.

### `app/Models/Bed.php`

Added:

```php
activeAllocation()
```

This relationship finds the current active allocation for a bed, where `release_date` is null.

### `app/Models/Patient.php`

Added:

```php
wardAdmissions()
activeWardAllocation()
```

These relationships let Module 3 list admitted patients and prevent assigning a patient who already has an active bed allocation.

### `app/Models/WardAdmission.php`

Updated the `patient()` relationship so it uses the correct owner key:

```php
patient_no
```

### `app/Models/WardAllocation.php`

Updated the `patient()` relationship so it uses the correct owner key:

```php
patient_no
```

## Data Changes

The original Module 3 mockup included sample data such as:

- Ward names: `Grampian`, `Tay`, `Forth`
- Sample nurses
- Sample bed counts
- Sample patient names
- Sample bed availability rows

Those sample records were removed because no real Module 3 data has been added yet.

Current default state:

- Ward count: `0`
- Total beds: `0`
- Vacant beds: `0`
- Occupied beds: `0`
- Maintenance beds: `0`

The interface now shows empty-state messages:

```text
No wards have been added yet.
No bed availability records to display yet.
```

The form placeholders remain because they are input examples, not saved records.

## Backend Flow

### All Wards Tab

1. Controller loads wards from `wards`.
2. Controller counts related beds from `beds`.
3. Blade displays:
   - Ward ID
   - Ward name
   - Ward type
   - Location
   - Charge nurse
   - Total beds
   - Vacant beds
   - Occupied beds
   - Maintenance beds

### Add Ward Tab

1. User fills in ward details.
2. User selects a charge nurse from staff whose position is `Charge Nurse`.
3. Form posts to `ward-bed-management.wards.store`.
4. Controller validates the form.
5. Controller confirms the selected staff member is a charge nurse.
6. Controller saves the staff member's name into the ward `charge_nurse` field.
7. Controller creates the ward.
8. Controller creates bed records from `01` up to the entered bed capacity.
9. Each new bed starts with status `Available`.

### Assign Bed Tab

1. User selects a ward.
2. The available bed dropdown filters to beds from that ward.
3. User selects an admitted patient from existing patient/admission data.
4. Patient doctor and admission date display automatically.
5. Form posts to `ward-bed-management.assign-bed.store`.
6. Controller validates that:
   - The bed belongs to the selected ward.
   - The bed is still available.
   - The patient is admitted.
   - The patient does not already have an active bed allocation.
7. Controller creates a `ward_allocations` record.
8. Controller changes the bed status to `Occupied`.

### Bed Availability Tab

1. Controller loads wards and beds.
2. Summary cards count:
   - Total beds
   - Vacant beds
   - Occupied beds
   - Maintenance beds
3. Bed availability is grouped by ward.
4. The ward filter reloads the page with a `ward_id` query parameter.

## How to Open the Module

1. Log in.
2. Go to the admin dashboard.
3. Click `Ward and Bed Management` in the sidebar.
4. The app opens `/ward-bed-management`.

## Verification Performed

PHP formatting was checked with:

```bash
vendor\bin\pint --dirty --format agent
```

The focused Module 3 test file was run with:

```bash
php artisan test --compact tests\Feature\Module3WardBedManagementTest.php
```

Result:

```text
2 tests, 8 assertions, passed
```

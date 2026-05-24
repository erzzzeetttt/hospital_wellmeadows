# Module 3 — Ward & Bed Management: Blade Split Changes

## What Changed

The single `wardbedmanagement.blade.php` (CSS radio-tab driven, all tabs in one file)
was split into four separate pages. The original file is kept as a backup and was NOT deleted.

---

## New Files Created

| File | Route Name | URL |
|------|-----------|-----|
| `resources/views/module3/index.blade.php` | `ward-bed-management.index` | `/ward-bed-management` |
| `resources/views/module3/create.blade.php` | `ward-bed-management.create` | `/ward-bed-management/create` |
| `resources/views/module3/assign-bed.blade.php` | `ward-bed-management.assign-bed` | `/ward-bed-management/assign-bed` |
| `resources/views/module3/bed-availability.blade.php` | `ward-bed-management.bed-availability` | `/ward-bed-management/bed-availability` |

---

## Routes Added to `routes/web.php`

```php
Route::get('/ward-bed-management/create',          'create')          → ward-bed-management.create
Route::get('/ward-bed-management/assign-bed',      'showAssignBed')   → ward-bed-management.assign-bed
Route::get('/ward-bed-management/bed-availability','bedAvailability') → ward-bed-management.bed-availability
```

Existing POST routes (`ward-bed-management.wards.store`, `ward-bed-management.assign-bed.store`) are unchanged.

---

## Controller Changes (`WardBedManagementController.php`)

### Methods updated

| Method | Before | After |
|--------|--------|-------|
| `index()` | Loaded data for all 4 tabs, returned `module3.wardbedmanagement` | Loads only `$wards` with bed counts, returns `module3.index` |
| `storeWard()` | Redirected with `active_tab` flash | Redirects to `ward-bed-management.index` (no tab flash needed) |
| `assignBed()` | Redirected to `ward-bed-management.index` with `tab=bed-availability` | Redirects to `ward-bed-management.bed-availability` |

### Methods added

| Method | Returns view | Data passed |
|--------|-------------|-------------|
| `create()` | `module3.create` | `$chargeNurses` |
| `showAssignBed()` | `module3.assign-bed` | `$wards`, `$availableBeds`, `$admittedPatients` |
| `bedAvailability()` | `module3.bed-availability` | `$wards`, `$availabilityWards`, `$stats`, `$selectedWardId` |

---

## CSS Note

`wardbedmanagement.css` was NOT modified. The original `.sub-nav label` rules only apply
to the old radio-tab blade. Each new blade adds a small `<style>` block in its `<head>`
to style `.sub-nav a` links to match the original label appearance.

---

## Backup

`resources/views/module3/wardbedmanagement.blade.php` — original single-file version, kept intact.

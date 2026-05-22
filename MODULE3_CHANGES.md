# Module 3 Ward and Bed Management Changes

This document records the front-end and routing changes made for the Ward and Bed Management module.

## Files Added

### `resources/views/module3/wardbedmanagement.blade.php`

Added the Module 3 Ward and Bed Management interface.

The page contains four CSS-tab sections:

- `All Wards`
- `Add Ward`
- `Assign Bed`
- `Bed Availability`

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
- Responsive behavior for smaller screens

The CSS is stored in `public/css/module3css` so it follows the existing project pattern used by other modules.

### `tests/Feature/Module3WardBedManagementTest.php`

Added feature tests for Module 3.

The tests verify:

- The Ward and Bed Management page renders for an authenticated user.
- The page loads the external Module 3 CSS file.
- Main Module 3 sections are visible.
- Empty-state messages are visible.
- The admin dashboard links to the Module 3 route.

## Files Updated

### `routes/web.php`

Added this authenticated route:

```php
Route::get('/ward-bed-management', function () {
    return view('module3.wardbedmanagement');
})->middleware('auth')->name('ward-bed-management.index');
```

This makes the Module 3 page available at:

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
2 tests, 10 assertions, passed
```

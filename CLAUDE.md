## Migration Rules
- NEVER use Schema::create() without checking if table exists first
- ALWAYS use Schema::hasColumn() before adding columns
- ALWAYS use DROP IF EXISTS before creating functions, triggers, or views
- NEVER create migrations for validation logic - use Controller instead
- ALWAYS use DB::unprepared('DROP FUNCTION IF EXISTS fn_name') before creating functions in migrations

## Session Start Rules
When starting any new session always:
1. Read this CLAUDE.md file completely
2. Read WellMeadows-Case-Study_.pdf in project root
3. Check routes/web.php to see current project structure
4. Confirm which modules are complete before starting work

---

## Module Definitions & Requirements

### Module 1 — Patient Management ✅ COMPLETED
1. Register and update patient information
2. Maintain patient medical records
3. Assign patients to wards and beds
4. Track patient admission and discharge details

### Module 2 — Staff & Department Management 🔨 IN PROGRESS
1. Manage staff records (doctors, nurses, administrative staff)
2. Assign staff to departments and wards
3. Maintain staff schedules and roles
4. Track staff responsibilities for patient care

### Module 3 — Ward & Bed Management ⏳ PENDING
1. Maintain ward details (ward name, type, capacity)
2. Manage bed allocation and availability
3. Track occupied and vacant beds
4. Assign beds to admitted patients

### Module 4 — Appointment & Treatment Module ⏳ PENDING
1. Schedule patient appointments with doctors
2. Record treatments, diagnoses, and procedures
3. Maintain patient treatment records
4. Assign doctors and nurses to treatments

---

## CSS Structure (Correct Folder Convention)
public/css/
  module1css/  → All Module 1 CSS files
  module2css/  → All Module 2 CSS files (base.css, staff-registration.css, ward-assignment.css, staff-profile.css, staff-schedule.css)
  module3css/  → All Module 3 CSS files
  module4css/  → All Module 4 CSS files
  admindash.css
  landing.css
  login.css
  register.css

## Views Structure (Correct Folder Convention)
resources/views/
  module1/     → All Module 1 blade files
  staff/       → All Module 2 blade files
  module3/     → All Module 3 blade files
  module4/     → All Module 4 blade files

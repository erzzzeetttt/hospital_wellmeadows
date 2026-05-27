# WellMeadows Hospital Management System

---

## Project Title
**WellMeadows Hospital Management System**
 hospital management system built for WellMeadows Hospital — Laravel 13 · PostgreSQL · Blade Templates

---

## System Requirements

| Requirement | Version |
|-------------|---------|
| PHP | ^8.3 |
| Laravel | ^13.7 |
| PostgreSQL | 14 or higher |
| Node.js | 18 or higher |
| npm | 9 or higher |
| Composer | 2.x |

---

## Installation Steps

```bash
# Step 1 - Clone the repository
git clone https://github.com/erzzzeetttt/hospital_wellmeadows.git
cd hospital_wellmeadows

# Step 2 - Install PHP dependencies
composer install

# Step 3 - Install Node dependencies
npm install

# Step 4 - Environment setup
cp .env.example .env
php artisan key:generate

# Step 5 - Build frontend assets
npm run build
```

---

## Database Setup

```bash
# Step 1 - Create PostgreSQL database
psql -U postgres -c "CREATE DATABASE hospital_wellmeadows;"

# Step 2 - Run all migrations
# Creates all tables + all stored functions (fn_*) + all triggers (trg_*)
php artisan migrate

# Step 3 - Run seeders
php artisan db:seed

# Step 4 - Create roles and first admin account
php artisan tinker
```

Inside tinker run:
```php
DB::table('roles')->insertOrIgnore(['role_name' => 'Administrator', 'description' => 'Full system access']);
DB::table('roles')->insertOrIgnore(['role_name' => 'Receptionist', 'description' => 'Patient registration and appointments']);
DB::table('roles')->insertOrIgnore(['role_name' => 'Charge Nurse', 'description' => 'Medical records and ward management']);

$adminRole = DB::table('roles')->where('role_name', 'Administrator')->first();

App\Models\User::create([
    'name'     => 'Admin User',
    'email'    => 'admin@wellmeadows.com',
    'password' => bcrypt('password123'),
    'role_id'  => $adminRole->role_id,
]);

exit
```

> **Important:** The `.env.example` defaults to `DB_CONNECTION=sqlite`.
> You **must** change this to `pgsql` in your `.env` file:
> ```env
> DB_CONNECTION=pgsql
> DB_HOST=127.0.0.1
> DB_PORT=5432
> DB_DATABASE=hospital_wellmeadows
> DB_USERNAME=your_postgres_username
> DB_PASSWORD=your_postgres_password
> ```

---

## Run Instructions


php artisan serve
```

Open in browser:
```
http://127.0.0.1:8000
```

Login with:
| Email | Password | Role |
|-------|----------|------|
| admin@wellmeadows.com | password123 | Administrator |

> Change the password after first login.
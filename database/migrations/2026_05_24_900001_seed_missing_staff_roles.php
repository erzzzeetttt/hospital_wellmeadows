<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $roles = [
            ['role_name' => 'Doctor',          'description' => 'Medical doctor / physician'],
            ['role_name' => 'Nurse',            'description' => 'Registered nurse'],
            ['role_name' => 'Consultant',       'description' => 'Specialist medical consultant'],
            ['role_name' => 'Auxiliary',        'description' => 'Auxiliary support staff'],
            ['role_name' => 'Physiotherapist',  'description' => 'Physiotherapy staff'],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->insertOrIgnore($role);
        }
    }

    public function down(): void
    {
        DB::table('roles')->whereIn('role_name', [
            'Doctor', 'Nurse', 'Consultant', 'Auxiliary', 'Physiotherapist',
        ])->delete();
    }
};

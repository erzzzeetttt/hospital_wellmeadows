<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class Module3WardBedManagementTest extends TestCase
{
    public function test_ward_bed_management_page_renders(): void
    {
        $user = new User([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);
        $user->id = 1;
        $user->role_id = 1;

        $response = $this->actingAs($user)->get(route('ward-bed-management.index'));

        $response
            ->assertOk()
            ->assertSee('Ward &amp; Bed Management System', false)
            ->assertSee('css/module3css/wardbedmanagement.css')
            ->assertSee('Available Wards')
            ->assertSee('Assign Bed to Patient')
            ->assertSee('Bed Availability by Ward')
            ->assertSee('No wards have been added yet.')
            ->assertSee('No bed availability records to display yet.');
    }

    public function test_admin_dashboard_links_to_ward_bed_management(): void
    {
        $user = new User([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);
        $user->id = 1;
        $user->role_id = 1;

        $this->actingAs($user)
            ->view('dashboards.admin', [
                'totalUsers' => 0,
                'totalPatients' => 0,
                'activeAdmissions' => 0,
            ])
            ->assertSee(route('ward-bed-management.index'))
            ->assertSee('Ward and Bed Management');
    }
}

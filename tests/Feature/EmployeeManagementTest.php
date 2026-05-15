<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EmployeeManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_employee_with_email_as_default_password(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('employees.store'), [
                'name' => 'Sample Employee',
                'email' => 'sample.employee@cdasia.com',
                'password' => '',
            ])
            ->assertRedirect(route('employees.index'));

        $employee = User::where('email', 'sample.employee@cdasia.com')->first();

        $this->assertNotNull($employee);
        $this->assertFalse($employee->isAdmin());
        $this->assertTrue(Hash::check('sample.employee@cdasia.com', $employee->password));
    }

    public function test_admin_can_delete_employee_account_without_deleting_reports(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $employee = User::factory()->create(['is_admin' => false]);

        $this->actingAs($employee)
            ->post(route('reports.store'), [
                'report_date' => '2026-05-21',
                'title' => 'Employee Audit',
                'issuances' => [
                    [
                        'issuance_no' => 'COA Decision No. 2026-521',
                        'links' => [
                            ['title' => 'Employee citation'],
                        ],
                    ],
                ],
            ])
            ->assertRedirect();

        $this->actingAs($admin)
            ->delete(route('employees.destroy', $employee))
            ->assertRedirect(route('employees.index'));

        $this->assertDatabaseMissing('users', ['id' => $employee->id]);
        $this->assertDatabaseHas('daily_reports', [
            'report_date' => '2026-05-21 00:00:00',
            'title' => 'Employee Audit',
            'user_id' => null,
        ]);
    }

    public function test_regular_employee_cannot_manage_employee_accounts(): void
    {
        $employee = User::factory()->create(['is_admin' => false]);

        $this->actingAs($employee)
            ->get(route('employees.index'))
            ->assertForbidden();

        $this->actingAs($employee)
            ->post(route('employees.store'), [
                'name' => 'Blocked Employee',
                'email' => 'blocked@cdasia.com',
            ])
            ->assertForbidden();
    }

    public function test_employee_table_does_not_allow_deleting_admin_accounts(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $otherAdmin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->delete(route('employees.destroy', $otherAdmin))
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $otherAdmin->id]);
    }
}

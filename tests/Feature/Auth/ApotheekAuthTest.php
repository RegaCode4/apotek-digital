<?php

/** Feature test untuk autentikasi dan role-based access control aplikasi apotek. */

use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

// ── Helper ────────────────────────────────────────────────────────────────────

/**
 * Create a User with the given role using the factory.
 * Password is always 'password' for convenience in tests.
 */
function createUser(string $role, bool $isActive = true): User
{
    return User::factory()->create([
        'role' => $role,
        'is_active' => $isActive,
        'password' => Hash::make('password'),
    ]);
}

// ── Authentication tests ──────────────────────────────────────────────────────

// Test: user bisa login dengan kredensial valid
test('test_user_can_login_with_valid_credentials', function () {
    $user = createUser('cashier');

    $response = $this->post(route('sistem.login.post'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('sistem.dashboard'));
    $this->assertAuthenticatedAs($user);
});

// Test: user gagal login dengan password salah
test('test_user_cannot_login_with_wrong_password', function () {
    $user = createUser('cashier');

    $response = $this->post(route('sistem.login.post'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrors(['email']);
    $this->assertGuest();
});

// Test: user nonaktif tidak bisa login
test('test_inactive_user_cannot_login', function () {
    $user = createUser('cashier', isActive: false);

    $response = $this->post(route('sistem.login.post'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertSessionHasErrors(['email']);
    $this->assertGuest();
});

// ── Role-based access control tests ──────────────────────────────────────────

// Test: cashier tidak bisa akses route inventaris
test('test_cashier_cannot_access_inventaris_route', function () {
    $cashier = createUser('cashier');

    $response = $this->actingAs($cashier)->get(route('inventaris.medicines.index'));

    $response->assertForbidden();
});

// Test: pharmacist bisa akses route inventaris
test('test_pharmacist_can_access_inventaris_route', function () {
    $pharmacist = createUser('pharmacist');

    $response = $this->actingAs($pharmacist)->get(route('inventaris.medicines.index'));

    // 200 OK or Livewire redirect — just not forbidden / redirect-to-login
    $response->assertStatus(200);
});

// Test: admin bisa akses semua route
test('test_admin_can_access_all_routes', function () {
    $admin = createUser('admin');

    // Mock DashboardService agar tidak crash di SQLite (DATE_FORMAT tidak didukung)
    $mock = Mockery::mock(DashboardService::class);
    $mock->shouldReceive('getTodayRevenue')->andReturn(0.0)->byDefault();
    $mock->shouldReceive('getTodayTransactionCount')->andReturn(0)->byDefault();
    $mock->shouldReceive('getLowStockMedicines')->andReturn(collect())->byDefault();
    $mock->shouldReceive('getExpiringSoonMedicines')->andReturn(collect())->byDefault();
    $mock->shouldReceive('getSalesChartData')->andReturn(['labels' => [], 'data' => []])->byDefault();
    $mock->shouldReceive('getTopSellingMedicines')->andReturn(collect())->byDefault();
    app()->instance(DashboardService::class, $mock);

    $this->actingAs($admin)
        ->get(route('sistem.dashboard'))
        ->assertOk();

    $this->actingAs($admin)
        ->get(route('inventaris.medicines.index'))
        ->assertOk();

    $this->actingAs($admin)
        ->get(route('pos.kasir'))
        ->assertOk();

    $this->actingAs($admin)
        ->get(route('sistem.users'))
        ->assertOk();
});

// Test: user tidak terautentikasi diarahkan ke login
test('test_unauthenticated_user_redirected_to_login', function () {
    $response = $this->get(route('sistem.dashboard'));

    $response->assertRedirect(route('sistem.login'));
});

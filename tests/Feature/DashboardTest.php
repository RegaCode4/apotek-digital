<?php

use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Test akses sistem.dashboard (internal apotek), bukan Fortify default /dashboard.
 */
test('guests are redirected to sistem login when accessing sistem dashboard', function () {
    $response = $this->get(route('sistem.dashboard'));
    $response->assertRedirect(route('sistem.login'));
});

test('authenticated users can visit the sistem dashboard', function () {
    $mock = Mockery::mock(DashboardService::class);
    $mock->shouldReceive('getTodayRevenue')->andReturn(0.0)->byDefault();
    $mock->shouldReceive('getTodayTransactionCount')->andReturn(0)->byDefault();
    $mock->shouldReceive('getLowStockMedicines')->andReturn(collect())->byDefault();
    $mock->shouldReceive('getExpiringSoonMedicines')->andReturn(collect())->byDefault();
    $mock->shouldReceive('getSalesChartData')->andReturn(['labels' => [], 'data' => []])->byDefault();
    $mock->shouldReceive('getTopSellingMedicines')->andReturn(collect())->byDefault();
    app()->instance(DashboardService::class, $mock);

    $user = User::factory()->create(['role' => 'admin']);
    $this->actingAs($user);

    $response = $this->get(route('sistem.dashboard'));
    $response->assertOk();
});

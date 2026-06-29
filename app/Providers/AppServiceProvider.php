<?php

namespace App\Providers;

use App\Contracts\BpjsServiceInterface;
use App\Services\DashboardService;
use App\Services\MockBpjsService;
use App\Services\PosService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

/** Penyedia layanan aplikasi. */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Mendaftarkan layanan aplikasi apa pun.
     */
    public function register(): void
    {
        $this->app->singleton(PosService::class);
        $this->app->singleton(DashboardService::class);
        $this->app->bind(BpjsServiceInterface::class, MockBpjsService::class);
    }

    /**
     * Mem-bootstrap layanan aplikasi apa pun.
     */
    public function boot(): void
    {
        $this->configureDefaults();
    }

    /**
     * Mengonfigurasi perilaku bawaan untuk aplikasi siap-produksi.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}

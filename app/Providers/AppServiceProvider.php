<?php

namespace App\Providers;

use App\Services\AuditTrailService;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\Leasing\LeasingManager::class);
        $this->app->singleton(\App\Services\SettingService::class);
        $this->app->singleton(\App\Services\AuditTrailService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Audit Trail Authentication Listeners
        Event::listen(Login::class, function (Login $event) {
            app(AuditTrailService::class)->logAuth('login', $event->user, true);
        });

        Event::listen(Failed::class, function (Failed $event) {
            $userOrEmail = $event->user ?? ($event->credentials['email'] ?? 'Akun Tidak Dikenal');
            app(AuditTrailService::class)->logAuth('login_failed', $userOrEmail, false, 'Kredensial atau kata sandi tidak valid');
        });

        Event::listen(Logout::class, function (Logout $event) {
            if ($event->user) {
                app(AuditTrailService::class)->logAuth('logout', $event->user, true);
            }
        });
    }
}

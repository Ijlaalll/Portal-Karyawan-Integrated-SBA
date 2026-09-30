<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (request()->server('HTTP_X_FORWARDED_PROTO') === 'https' || request()->isSecure()) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\AtkRequest::class,
            \App\Policies\AtkRequestPolicy::class
        );

        // Define gate for accessing administrative settings
        \Illuminate\Support\Facades\Gate::define('access-settings', function ($user) {
            return $user->isAdmin();
        });

        // Dynamic SMTP runtime configuration from database
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('smtp_settings')) {
                $smtpService = app(\App\Services\SmtpSettingsService::class);
                $smtp = $smtpService->getSettings();
                if ($smtp) {
                    config([
                        'mail.mailers.smtp.host' => $smtp->host,
                        'mail.mailers.smtp.port' => $smtp->port,
                        'mail.mailers.smtp.username' => $smtp->username,
                        'mail.mailers.smtp.password' => $smtpService->decryptPassword($smtp->password),
                        'mail.mailers.smtp.encryption' => $smtp->encryption,
                        'mail.from.address' => $smtp->from_address,
                        'mail.from.name' => $smtp->from_name,
                        'mail.default' => 'smtp',
                    ]);
                }
            }
        } catch (\Exception $e) {
            // Safe fallback to .env configuration if database is not ready
        }
    }
}

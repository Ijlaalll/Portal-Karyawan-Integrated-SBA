<?php

namespace App\Services;

use App\Models\SmtpSetting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Cache;

class SmtpSettingsService
{
    private const CACHE_KEY = 'smtp_settings_global';

    /**
     * Get the cached active/default SMTP settings, or retrieve from database.
     */
    public function getSettings(): ?SmtpSetting
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return SmtpSetting::where('is_active', true)->first();
        });
    }

    /**
     * Get all SMTP configurations.
     */
    public function getAllSettings()
    {
        return SmtpSetting::orderBy('created_at', 'desc')->get();
    }

    /**
     * Get a specific SMTP configuration by ID.
     */
    public function getSettingsById($id): ?SmtpSetting
    {
        return SmtpSetting::find($id);
    }

    /**
     * Create a new SMTP configuration.
     */
    public function createSettings(array $data): SmtpSetting
    {
        // Encrypt the password
        if (!empty($data['password'])) {
            $data['password'] = Crypt::encryptString($data['password']);
        }

        // Default first configuration to active
        if (SmtpSetting::count() === 0) {
            $data['is_active'] = true;
        } else {
            $data['is_active'] = false;
        }

        $settings = SmtpSetting::create($data);

        // Invalidate cache
        Cache::forget(self::CACHE_KEY);

        return $settings;
    }

    /**
     * Update an existing SMTP configuration.
     */
    public function updateSettings($id, array $data): SmtpSetting
    {
        $settings = SmtpSetting::findOrFail($id);

        // Encrypt the password if provided
        if (!empty($data['password'])) {
            $data['password'] = Crypt::encryptString($data['password']);
        } else {
            // Keep the old password if it's empty/not-provided
            $data['password'] = $settings->password;
        }

        $settings->update($data);

        // Invalidate cache
        Cache::forget(self::CACHE_KEY);

        return $settings;
    }

    /**
     * Delete an SMTP configuration.
     */
    public function deleteSettings($id): void
    {
        $settings = SmtpSetting::findOrFail($id);
        $wasActive = $settings->is_active;
        $settings->delete();

        // If the deleted setting was active, activate the next remaining setting if any exists
        if ($wasActive) {
            $nextSetting = SmtpSetting::orderBy('created_at', 'desc')->first();
            if ($nextSetting) {
                $nextSetting->update(['is_active' => true]);
            }
        }

        // Invalidate cache
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Set a configuration as active/default.
     */
    public function setActiveSettings($id): void
    {
        \DB::transaction(function () use ($id) {
            SmtpSetting::query()->update(['is_active' => false]);
            
            $settings = SmtpSetting::findOrFail($id);
            $settings->update(['is_active' => true]);
        });

        // Invalidate cache
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Decrypt password safely.
     */
    public function decryptPassword(?string $encryptedPassword): ?string
    {
        if (empty($encryptedPassword)) {
            return null;
        }

        try {
            return Crypt::decryptString($encryptedPassword);
        } catch (\Exception $e) {
            return null;
        }
    }
}

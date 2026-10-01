<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SmtpSettingsService;
use App\Models\SmtpSetting;
use App\Models\SmtpHistory;
use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Models\EmailNotificationSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class SmtpSettingsController extends Controller
{
    protected SmtpSettingsService $smtpService;

    public function __construct(SmtpSettingsService $smtpService)
    {
        $this->smtpService = $smtpService;
    }

    /**
     * Display the SMTP configuration tabbed pages.
     */
    public function show(Request $request)
    {
        $tab = $request->query('tab', 'config');
        if (!in_array($tab, ['config', 'history', 'logs', 'templates', 'notifications'])) {
            $tab = 'config';
        }

        $action = $request->query('action', 'view');
        if (!in_array($action, ['view', 'create'])) {
            $action = 'view';
        }

        $allSettings = $this->smtpService->getAllSettings();
        
        // Find active config
        $activeSettings = $allSettings->where('is_active', true)->first();

        // Determine selected ID
        $selectedId = $request->query('id');
        if (!$selectedId && $activeSettings) {
            $selectedId = $activeSettings->id;
        } elseif (!$selectedId && $allSettings->isNotEmpty()) {
            $selectedId = $allSettings->first()->id;
        }

        $settings = null;
        if ($selectedId && $action !== 'create') {
            $settings = $this->smtpService->getSettingsById($selectedId);
        }

        $hasPassword = $settings && !empty($settings->password);

        // Fetch Tab-specific data
        $histories = null;
        $logs = null;
        $templates = null;
        $notificationSettings = null;

        if ($tab === 'history') {
            $histories = SmtpHistory::with('changer')
                ->orderBy('created_at', 'desc')
                ->paginate(15);
        } elseif ($tab === 'logs') {
            $query = EmailLog::query();
            
            // Apply Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('recipient', 'like', "%{$search}%")
                      ->orWhere('subject', 'like', "%{$search}%");
                });
            }

            // Apply Email Type Filter
            if ($request->filled('email_type') && $request->email_type !== 'all') {
                $query->where('email_type', $request->email_type);
            }

            // Apply Status Filter
            if ($request->filled('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            // Apply Date Range Filter
            if ($request->filled('start_date')) {
                $query->whereDate('created_at', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->whereDate('created_at', '<=', $request->end_date);
            }

            $logs = $query->orderBy('created_at', 'desc')->paginate(15);
        } elseif ($tab === 'templates') {
            $templates = EmailTemplate::orderBy('template_key', 'asc')->get();
        } elseif ($tab === 'notifications') {
            $notificationSettings = EmailNotificationSetting::orderBy('event_key', 'asc')->get();
        }

        return view('admin.settings.smtp.show', compact(
            'tab',
            'action',
            'settings',
            'allSettings',
            'activeSettings',
            'selectedId',
            'hasPassword',
            'histories',
            'logs',
            'templates',
            'notificationSettings'
        ));
    }

    /**
     * Update/Save the SMTP settings (Create new or Update existing).
     */
    public function update(Request $request)
    {
        $isCreate = !$request->filled('id');

        $isNoAuth = $request->boolean('no_auth') || ((int)$request->input('port') === 25 && !$request->filled('password') && !$request->filled('username'));

        $rules = [
            'host' => ['required', 'string'],
            'port' => ['required', 'integer'],
            'username' => [$isNoAuth ? 'nullable' : 'required', 'string'],
            'encryption' => ['nullable', 'string', 'in:tls,ssl,none'],
            'from_address' => ['required', 'email'],
            'from_name' => ['required', 'string', 'max:255'],
        ];

        if ($isCreate) {
            $rules['password'] = [$isNoAuth ? 'nullable' : 'required', 'string'];
        } else {
            $rules['password'] = ['nullable', 'string'];
            $rules['id'] = ['required', 'exists:smtp_settings,id'];
        }

        $validated = $request->validate($rules);

        // Normalize 'none' encryption to null in database
        if (isset($validated['encryption']) && $validated['encryption'] === 'none') {
            $validated['encryption'] = null;
        }

        if ($isNoAuth) {
            $validated['username'] = $validated['username'] ?: null;
            $validated['password'] = null;
            $validated['clear_password'] = true;
        }

        if ($isCreate) {
            DB::transaction(function () use ($validated) {
                // Create SMTP Settings
                $settings = $this->smtpService->createSettings($validated);

                // Create History entry (audit log, no password recorded)
                SmtpHistory::create([
                    'user_id' => auth()->id(),
                    'host' => $settings->host,
                    'port' => $settings->port,
                    'encryption' => $settings->encryption,
                    'username' => $settings->username,
                    'from_address' => $settings->from_address,
                    'from_name' => $settings->from_name,
                    'action' => 'created',
                ]);
            });

            // Redirect to newly created one
            $newSettings = SmtpSetting::orderBy('created_at', 'desc')->first();
            $newId = $newSettings ? $newSettings->id : null;

            return redirect()->route('admin.settings.smtp.show', ['tab' => 'config', 'id' => $newId])
                ->with('success', 'Konfigurasi SMTP baru berhasil dibuat.');
        } else {
            $id = $validated['id'];
            $oldSettings = SmtpSetting::findOrFail($id);

            // Check if there are actual changes
            $hasChanges = false;
            if ($oldSettings->host !== $validated['host'] ||
                $oldSettings->port !== (int)$validated['port'] ||
                $oldSettings->username !== $validated['username'] ||
                $oldSettings->encryption !== $validated['encryption'] ||
                $oldSettings->from_address !== $validated['from_address'] ||
                $oldSettings->from_name !== $validated['from_name'] ||
                (!empty($validated['password']) && $validated['password'] !== $this->smtpService->decryptPassword($oldSettings->password)) ||
                (!empty($validated['clear_password']) && !empty($oldSettings->password))
            ) {
                $hasChanges = true;
            }

            if ($hasChanges) {
                DB::transaction(function () use ($id, $validated) {
                    // Save SMTP Settings
                    $settings = $this->smtpService->updateSettings($id, $validated);

                    // Create History entry (audit log, no password recorded)
                    SmtpHistory::create([
                        'user_id' => auth()->id(),
                        'host' => $settings->host,
                        'port' => $settings->port,
                        'encryption' => $settings->encryption,
                        'username' => $settings->username,
                        'from_address' => $settings->from_address,
                        'from_name' => $settings->from_name,
                        'action' => 'updated',
                    ]);
                });

                return redirect()->route('admin.settings.smtp.show', ['tab' => 'config', 'id' => $id])
                    ->with('success', 'Konfigurasi SMTP berhasil diperbarui.');
            }

            return redirect()->route('admin.settings.smtp.show', ['tab' => 'config', 'id' => $id])
                ->with('success', 'Konfigurasi SMTP disimpan (tidak ada perubahan).');
        }
    }

    /**
     * Delete SMTP configuration.
     */
    public function destroy($id)
    {
        $settings = SmtpSetting::findOrFail($id);
        
        DB::transaction(function () use ($settings, $id) {
            // Log History
            SmtpHistory::create([
                'user_id' => auth()->id(),
                'host' => $settings->host,
                'port' => $settings->port,
                'encryption' => $settings->encryption,
                'username' => $settings->username,
                'from_address' => $settings->from_address,
                'from_name' => $settings->from_name,
                'action' => 'deleted',
            ]);

            $this->smtpService->deleteSettings($id);
        });

        return redirect()->route('admin.settings.smtp.show', ['tab' => 'config'])
            ->with('success', 'Konfigurasi SMTP berhasil dihapus.');
    }

    /**
     * Set SMTP configuration as active/default.
     */
    public function activate($id)
    {
        $settings = SmtpSetting::findOrFail($id);
        
        DB::transaction(function () use ($id, $settings) {
            $this->smtpService->setActiveSettings($id);

            // Log History
            SmtpHistory::create([
                'user_id' => auth()->id(),
                'host' => $settings->host,
                'port' => $settings->port,
                'encryption' => $settings->encryption,
                'username' => $settings->username,
                'from_address' => $settings->from_address,
                'from_name' => $settings->from_name,
                'action' => 'activated',
            ]);
        });

        return redirect()->route('admin.settings.smtp.show', ['tab' => 'config', 'id' => $id])
            ->with('success', 'Konfigurasi SMTP berhasil diaktifkan sebagai default.');
    }

    /**
     * Test SMTP connection.
     */
    public function testConnection(Request $request)
    {
        $validated = $request->validate([
            'smtp_setting_id' => ['required', 'exists:smtp_settings,id'],
        ]);

        $settings = $this->smtpService->getSettingsById($validated['smtp_setting_id']);

        // 1. Backup original mail configuration
        $originalConfig = config('mail');

        try {
            // 2. Apply target SMTP configuration to test
            $emailService = app(\App\Services\EmailService::class);
            $emailService->applySmtpConfig($settings);

            // 3. Clear/forget resolved SMTP mailer before resolving the transport
            if (app()->resolved('mail.manager')) {
                app('mail.manager')->forgetMailers();
            }

            // 4. Resolve mailer SMTP after configuration is applied and get Symfony transport
            $transport = \Illuminate\Support\Facades\Mail::mailer('smtp')->getSymfonyTransport();

            // 5. Attempt connection and authentication
            $transport->start();
            $transport->stop();

            // Update status
            $settings->update([
                'status' => 'connected',
                'last_success_at' => now(),
                'last_failure_at' => null,
                'last_error_message' => null,
            ]);

            if ($settings->is_active) {
                Cache::forget('smtp_settings_global');
            }

            return redirect()->route('admin.settings.smtp.show', ['tab' => 'config', 'id' => $settings->id])
                ->with('success', 'SMTP connection successful.');
        } catch (\Throwable $e) {
            $errorMessage = $e->getMessage();

            // Redact credentials safely
            $decryptedPassword = $this->smtpService->decryptPassword($settings->password);
            if (!empty($decryptedPassword)) {
                $errorMessage = str_replace($decryptedPassword, '[REDACTED]', $errorMessage);
                
                // Redact URL-encoded versions of the password to clean up DSN-style errors
                $urlEncodedPassword = urlencode($decryptedPassword);
                $errorMessage = str_replace($urlEncodedPassword, '[REDACTED]', $errorMessage);

                $rawUrlEncodedPassword = rawurlencode($decryptedPassword);
                if ($rawUrlEncodedPassword !== $urlEncodedPassword) {
                    $errorMessage = str_replace($rawUrlEncodedPassword, '[REDACTED]', $errorMessage);
                }
            }
            if (!empty($settings->username)) {
                $errorMessage = str_replace($settings->username, '[REDACTED]', $errorMessage);
                
                // Redact URL-encoded username
                $urlEncodedUsername = urlencode($settings->username);
                $errorMessage = str_replace($urlEncodedUsername, '[REDACTED]', $errorMessage);

                $rawUrlEncodedUsername = rawurlencode($settings->username);
                if ($rawUrlEncodedUsername !== $urlEncodedUsername) {
                    $errorMessage = str_replace($rawUrlEncodedUsername, '[REDACTED]', $errorMessage);
                }
            }

            // Update status
            $settings->update([
                'status' => 'failed',
                'last_failure_at' => now(),
                'last_error_message' => 'SMTP Connection Error: ' . $errorMessage,
            ]);

            if ($settings->is_active) {
                Cache::forget('smtp_settings_global');
            }

            return redirect()->route('admin.settings.smtp.show', ['tab' => 'config', 'id' => $settings->id])
                ->with('error', 'SMTP authentication/connection failed. Please verify host, port, encryption, and credentials. Detail: ' . $errorMessage);
        } finally {
            // 6. Restore original mail configuration (guaranteed to run in finally block)
            config(['mail' => $originalConfig]);
            if (app()->resolved('mail.manager')) {
                app('mail.manager')->forgetMailers();
            }
        }
    }

    /**
     * Send a test email using the selected SMTP configuration.
     */
    public function testMail(Request $request)
    {
        $validated = $request->validate([
            'smtp_setting_id' => ['required', 'exists:smtp_settings,id'],
            'recipient' => ['required', 'email'],
        ]);

        $settings = $this->smtpService->getSettingsById($validated['smtp_setting_id']);

        // Prepare SMTP config override at runtime
        $emailService = app(\App\Services\EmailService::class);
        $emailService->applySmtpConfig($settings);

        try {
            // Send the test email using our custom Mailable
            \Illuminate\Support\Facades\Mail::to($validated['recipient'])->send(
                new \App\Mail\TestSmtpMail([
                    'host' => $settings->host,
                    'port' => $settings->port,
                    'encryption' => $settings->encryption,
                    'from_address' => $settings->from_address,
                    'from_name' => $settings->from_name,
                ])
            );

            // Update SmtpSetting stats on SUCCESS
            $settings->update([
                'status' => 'connected',
                'last_success_at' => now(),
                'last_failure_at' => null,
                'last_error_message' => null,
            ]);

            // Log the successful test email
            EmailLog::create([
                'recipient' => $validated['recipient'],
                'subject' => 'Uji Coba SMTP Berhasil - Portal Karyawan Terintegrasi SBA',
                'email_type' => 'test_email',
                'status' => 'sent',
            ]);

            if ($settings->is_active) {
                Cache::forget('smtp_settings_global');
            }

            return redirect()->route('admin.settings.smtp.show', ['tab' => 'config', 'id' => $settings->id])
                ->with('success', 'Email test berhasil dikirim. Silakan periksa inbox email.');
        } catch (\Throwable $e) {
            $errorMessage = $e->getMessage();

            // Redact credentials from the exception message if present
            if (!empty($settings->password)) {
                $decryptedPassword = $this->smtpService->decryptPassword($settings->password);
                if (!empty($decryptedPassword)) {
                    $errorMessage = str_replace($decryptedPassword, '[REDACTED]', $errorMessage);
                }
            }
            if (!empty($settings->username)) {
                $errorMessage = str_replace($settings->username, '[REDACTED]', $errorMessage);
            }

            // Update SmtpSetting stats on FAILURE
            $settings->update([
                'status' => 'failed',
                'last_failure_at' => now(),
                'last_error_message' => 'SMTP Error: ' . $errorMessage,
            ]);

            // Log the failed test email
            EmailLog::create([
                'recipient' => $validated['recipient'],
                'subject' => 'Uji Coba SMTP Berhasil - Portal Karyawan Terintegrasi SBA',
                'email_type' => 'test_email',
                'status' => 'failed',
                'error_detail' => 'SMTP Error: ' . $errorMessage,
            ]);

            if ($settings->is_active) {
                Cache::forget('smtp_settings_global');
            }

            // Log the redacted error
            \Illuminate\Support\Facades\Log::error('SMTP Test Mail Failed: ' . $errorMessage);

            return redirect()->route('admin.settings.smtp.show', ['tab' => 'config', 'id' => $settings->id])
                ->with('error', 'Gagal mengirim email test. Terjadi kesalahan SMTP: ' . $errorMessage);
        }
    }

    /**
     * Update an email template.
     */
    public function updateTemplate(Request $request, EmailTemplate $template)
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'is_active' => ['required', 'boolean'],
        ]);

        $template->update($validated);

        return redirect()->route('admin.settings.smtp.show', ['tab' => 'templates'])
            ->with('success', "Template '{$template->name}' berhasil diperbarui.");
    }

    /**
     * Update notification settings.
     */
    public function updateNotifications(Request $request)
    {
        $validated = $request->validate([
            'notifications' => ['required', 'array'],
            'notifications.*.is_active' => ['required', 'boolean'],
            'notifications.*.reminder_time' => ['nullable', 'integer', 'in:15,30,60'],
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['notifications'] as $key => $data) {
                EmailNotificationSetting::where('event_key', $key)->update([
                    'is_active' => $data['is_active'],
                    'reminder_time' => $data['reminder_time'] ?? 15,
                ]);
            }
        });

        return redirect()->route('admin.settings.smtp.show', ['tab' => 'notifications'])
            ->with('success', 'Pengaturan notifikasi berhasil diperbarui.');
    }
}

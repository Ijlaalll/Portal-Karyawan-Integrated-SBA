<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TestSmtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $settingsData;

    /**
     * Create a new message instance.
     */
    public function __construct(array $settingsData)
    {
        $this->settingsData = $settingsData;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->html("
            <div style='font-family: sans-serif; padding: 20px; color: #0F172A; max-width: 600px; margin: 0 auto; border: 1px solid #E5E7EB; border-radius: 12px;'>
                <h2 style='color: #2563EB;'>Test SMTP Berhasil!</h2>
                <p>Halo,</p>
                <p>Ini adalah email pengujian untuk memverifikasi bahwa konfigurasi server SMTP pada aplikasi <strong>Meeting Room Booking System (SBA)</strong> Anda berfungsi dengan benar.</p>
                <hr style='border: 0; border-top: 1px solid #E5E7EB; margin: 20px 0;'>
                <p><strong>Detail Konfigurasi Pengujian:</strong></p>
                <ul style='list-style: none; padding: 0;'>
                    <li style='margin-bottom: 8px;'><strong>Host:</strong> {$this->settingsData['host']}</li>
                    <li style='margin-bottom: 8px;'><strong>Port:</strong> {$this->settingsData['port']}</li>
                    <li style='margin-bottom: 8px;'><strong>Encryption:</strong> " . ($this->settingsData['encryption'] ?: 'None') . "</li>
                    <li style='margin-bottom: 8px;'><strong>Sender Address:</strong> {$this->settingsData['from_address']}</li>
                    <li style='margin-bottom: 8px;'><strong>Sender Name:</strong> {$this->settingsData['from_name']}</li>
                </ul>
                <hr style='border: 0; border-top: 1px solid #E5E7EB; margin: 20px 0;'>
                <p>Jika Anda menerima email ini, konfigurasi SMTP Anda sudah berhasil dan berjalan dengan ideal.</p>
                <p style='font-size: 12px; color: #64748B; margin-top: 24px;'>Salam,<br>SBA System</p>
            </div>
        ")->subject('Test SMTP - Meeting Room System');
    }
}

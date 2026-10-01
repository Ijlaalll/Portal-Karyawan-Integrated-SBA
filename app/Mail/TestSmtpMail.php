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
            <div style='font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif; padding: 24px; color: #0F172A; max-width: 600px; margin: 0 auto; border: 1px solid #E2E8F0; border-radius: 16px; background-color: #ffffff;'>
                <div style='margin-bottom: 20px;'>
                    <span style='display: inline-block; padding: 4px 12px; background-color: #EFF6FF; color: #2563EB; font-weight: 700; font-size: 11px; border-radius: 6px; text-transform: uppercase; letter-spacing: 0.5px;'>Konfigurasi Email Berhasil</span>
                    <h2 style='color: #0F172A; margin: 12px 0 4px 0; font-size: 20px;'>Uji Coba Pengiriman Email Berhasil!</h2>
                    <p style='color: #64748B; font-size: 13px; margin: 0;'>Portal Karyawan Terintegrasi — PT Solusi Bangun Andalas (SIG Group)</p>
                </div>
                
                <p style='font-size: 14px; line-height: 1.6;'>Halo,</p>
                <p style='font-size: 14px; line-height: 1.6;'>Email pengujian ini mengonfirmasikan bahwa konfigurasi server SMTP pada <strong>Portal Karyawan Terintegrasi PT Solusi Bangun Andalas (SBA)</strong> telah terhubung dengan benar dan siap beroperasi.</p>
                
                <div style='background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 16px; margin: 20px 0;'>
                    <p style='margin: 0 0 12px 0; font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;'>Ringkasan Konfigurasi SMTP:</p>
                    <table style='width: 100%; border-collapse: collapse; font-size: 13px;'>
                        <tr>
                            <td style='padding: 6px 0; color: #64748B; width: 150px;'>Host Server:</td>
                            <td style='padding: 6px 0; color: #0F172A; font-weight: 600;'>{$this->settingsData['host']}</td>
                        </tr>
                        <tr>
                            <td style='padding: 6px 0; color: #64748B;'>Port:</td>
                            <td style='padding: 6px 0; color: #0F172A; font-weight: 600;'>{$this->settingsData['port']}</td>
                        </tr>
                        <tr>
                            <td style='padding: 6px 0; color: #64748B;'>Enkripsi:</td>
                            <td style='padding: 6px 0; color: #0F172A; font-weight: 600;'>" . ($this->settingsData['encryption'] ?: 'None (Relay Tanpa Enkripsi)') . "</td>
                        </tr>
                        <tr>
                            <td style='padding: 6px 0; color: #64748B;'>Alamat Pengirim:</td>
                            <td style='padding: 6px 0; color: #0F172A; font-weight: 600;'>{$this->settingsData['from_address']}</td>
                        </tr>
                        <tr>
                            <td style='padding: 6px 0; color: #64748B;'>Nama Pengirim:</td>
                            <td style='padding: 6px 0; color: #0F172A; font-weight: 600;'>{$this->settingsData['from_name']}</td>
                        </tr>
                    </table>
                </div>

                <div style='background-color: #F0FDF4; border: 1px solid #BBF7D0; padding: 14px 16px; border-radius: 10px; margin: 20px 0;'>
                    <p style='font-size: 13px; line-height: 1.5; color: #166534; margin: 0;'>
                        <strong>Status Operasional:</strong> Server mail relay siap mendistribusikan notifikasi otomatis untuk seluruh modul terintegrasi (Reservasi Ruang Meeting, Permintaan ATK, Surat Izin Keluar / SIK, Kendaraan Operasional, dan Inspeksi Kebersihan Toilet).
                    </p>
                </div>

                <div style='border-top: 1px solid #E2E8F0; margin-top: 24px; padding-top: 16px;'>
                    <p style='font-size: 12px; color: #64748B; margin: 0; line-height: 1.5;'>
                        Salam hangat,<br>
                        <strong>Tim IT & Administrator Portal SBA</strong><br>
                        PT Solusi Bangun Andalas (SIG Group)
                    </p>
                </div>
            </div>
        ")->subject('Uji Coba SMTP Berhasil - Portal Karyawan Terintegrasi SBA');
    }
}

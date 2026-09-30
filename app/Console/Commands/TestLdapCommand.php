<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\LdapService;
use Illuminate\Console\Command;

class TestLdapCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ldap:test {username? : Username Active Directory atau email SIG} {password? : Password akun}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Uji konektivitas dan autentikasi Active Directory LDAP PT Semen Indonesia (SIG)';

    /**
     * Execute the console command.
     */
    public function handle(LdapService $ldapService): int
    {
        $this->newLine();
        $this->info('===========================================================');
        $this->info('  UJI KONEKSI & AUTENTIKASI LDAP ACTIVE DIRECTORY (SIG)   ');
        $this->info('===========================================================');
        $this->newLine();

        $host = config('ldap.host', '10.60.12.44');
        $port = config('ldap.port', 389);
        $domain = config('ldap.domain', 'sig.id');
        $baseDn = config('ldap.base_dn', 'DC=sig,DC=id');
        $timeout = config('ldap.timeout', 3);

        $this->line("• Host LDAP     : <comment>{$host}</comment>");
        $this->line("• Port LDAP     : <comment>{$port}</comment>");
        $this->line("• Domain        : <comment>{$domain}</comment>");
        $this->line("• Base DN       : <comment>{$baseDn}</comment>");
        $this->line("• Timeout       : <comment>{$timeout} detik</comment>");
        $this->newLine();

        // 1. Check PHP LDAP extension
        $this->line('1. Memeriksa Ekstensi PHP LDAP:');
        if (extension_loaded('ldap')) {
            $this->info('   [OK] Ekstensi PHP "ldap" aktif.');
        } else {
            $this->warn('   [PERINGATAN] Ekstensi PHP "ldap" belum aktif di php.ini.');
            $this->line('   Saran: Aktifkan "extension=ldap" di file C:\xampp\php\php.ini lalu restart web server.');
        }

        // 2. Check Network Port Reachability
        $this->newLine();
        $this->line("2. Menguji Jaringan ke {$host}:{$port}...");
        $connTest = $ldapService->testConnection();

        if ($connTest['network_reachable']) {
            $this->info("   [OK] Terhubung ke {$host}:{$port} (Latency: {$connTest['network_latency_ms']} ms)");
        } else {
            $this->error("   [GAGAL] Tidak dapat menjangkau server LDAP {$host}:{$port}");
            $this->line("   Pesan: {$connTest['error']}");
            $this->warn('   Catatan: Pastikan komputer ini berada di jaringan intranet kantor SIG atau VPN SIG.');
        }

        // 3. User Authentication Test
        $username = $this->argument('username');
        $password = $this->argument('password');

        if (!$username) {
            $this->newLine();
            $this->line('Ingin menguji login akun Active Directory? (Tekan Enter tanpa mengetik untuk lewati)');
            $username = $this->ask('Masukkan Username / NIP / Email SIG');
        }

        if (empty($username)) {
            $this->newLine();
            $this->info('Pengujian jaringan selesai.');
            return $connTest['network_reachable'] ? 0 : 1;
        }

        if (!$password) {
            $password = $this->secret('Masukkan Password Akun SIG');
        }

        if (empty($password)) {
            $this->error('Password tidak boleh kosong untuk pengujian login.');
            return 1;
        }

        $this->newLine();
        $this->line("3. Menguji Autentikasi untuk: <comment>{$username}</comment>...");
        $authResult = $ldapService->authenticate($username, $password);

        if ($authResult['success']) {
            $this->info('   [BERHASIL] Autentikasi Active Directory Sukses!');
            $this->line("   Driver Digunakan : <comment>{$authResult['driver']}</comment>");
            $this->newLine();
            
            $data = $authResult['data'];
            $this->line('   Detail Data Pengguna dari AD:');
            $this->line("   • Nama Lengkap  : <info>{$data['name']}</info>");
            $this->line("   • Email Resmi   : <info>{$data['email']}</info>");
            $this->line("   • Departemen    : " . ($data['department'] ? "<info>{$data['department']}</info>" : '<comment>(Tidak diisi di AD)</comment>'));
            $this->line("   • Posisi/Jabatan: " . ($data['position'] ? "<info>{$data['position']}</info>" : '<comment>(Tidak diisi di AD)</comment>'));
            $this->line("   • NIP / Emp ID  : " . ($data['employee_id'] ? "<info>{$data['employee_id']}</info>" : '<comment>(Tidak diisi di AD)</comment>'));
            
            // Check in local database
            $localUser = User::where('email', $data['email'])->first();
            $this->newLine();
            if ($localUser) {
                $this->info("   [DATABASE] Akun ini sudah terdaftar di database lokal (ID: {$localUser->id}, Role: {$localUser->role}).");
            } else {
                $this->comment("   [DATABASE] Akun ini belum ada di database lokal. Akan otomatis dibuatkan akun saat user pertama kali login.");
            }

            return 0;
        }

        $this->error('   [GAGAL] Autentikasi Active Directory Gagal.');
        $this->line("   Pesan Kesalahan: <error>{$authResult['error']}</error>");
        if (!empty($authResult['diagnostic'])) {
            $this->line("   AD Diagnostic  : <comment>{$authResult['diagnostic']}</comment>");
        }
        if (!empty($authResult['candidates'])) {
            $this->line("   Format Dicoba  : " . implode(', ', $authResult['candidates']));
        }
        $this->line("   Driver         : " . ($authResult['driver'] ?? 'unknown'));
        return 1;
    }
}

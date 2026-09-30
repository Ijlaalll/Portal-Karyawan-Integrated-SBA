<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class LdapService
{
    protected bool $enabled;
    protected string $host;
    protected int $port;
    protected string $domain;
    protected string $baseDn;
    protected int $timeout;

    public function __construct()
    {
        $this->enabled = (bool) config('ldap.enabled', true);
        $this->host = (string) config('ldap.host', '10.60.12.44');
        $this->port = (int) config('ldap.port', 389);
        $this->domain = (string) config('ldap.domain', 'sig.id');
        $this->baseDn = (string) config('ldap.base_dn', 'DC=sig,DC=id');
        $this->timeout = (int) config('ldap.timeout', 3);
    }

    /**
     * Test network reachability and extension readiness for LDAP.
     */
    public function testConnection(): array
    {
        $result = [
            'host' => $this->host,
            'port' => $this->port,
            'domain' => $this->domain,
            'php_ldap_loaded' => extension_loaded('ldap'),
            'network_reachable' => false,
            'network_latency_ms' => null,
            'error' => null,
        ];

        // 1. Probe TCP socket connection
        $startTime = microtime(true);
        $socket = @fsockopen($this->host, $this->port, $errno, $errstr, 2.0);
        
        if ($socket) {
            $result['network_reachable'] = true;
            $result['network_latency_ms'] = round((microtime(true) - $startTime) * 1000, 2);
            fclose($socket);
        } else {
            $result['error'] = "Tidak dapat terhubung ke {$this->host}:{$this->port} ($errno: $errstr)";
        }

        return $result;
    }

    /**
     * Authenticate user credentials against Active Directory LDAP.
     */
    public function authenticate(string $identifier, string $password): array
    {
        if (!$this->enabled) {
            return [
                'success' => false,
                'error' => 'LDAP authentication dinonaktifkan di konfigurasi.',
            ];
        }

        if (empty(trim($identifier)) || empty($password)) {
            return [
                'success' => false,
                'error' => 'Username/email dan kata sandi tidak boleh kosong.',
            ];
        }

        $cleanIdentifier = trim($identifier);
        $username = str_contains($cleanIdentifier, '@') ? explode('@', $cleanIdentifier)[0] : $cleanIdentifier;
        $upn = str_contains($cleanIdentifier, '@') ? $cleanIdentifier : "{$cleanIdentifier}@{$this->domain}";

        // Priority 1: Native PHP LDAP (fastest, in-process)
        if (extension_loaded('ldap')) {
            return $this->authenticateViaNativePhp($username, $upn, $password);
        }

        // Priority 2: Python Helper Script (fallback if PHP LDAP extension is not loaded)
        return $this->authenticateViaPython($cleanIdentifier, $password);
    }

    /**
     * Authenticate via PHP native ext-ldap.
     */
    protected function authenticateViaNativePhp(string $username, string $upn, string $password): array
    {
        try {
            $ldapUri = "ldap://{$this->host}:{$this->port}";
            $conn = @ldap_connect($ldapUri);

            if (!$conn) {
                return [
                    'success' => false,
                    'error' => "Gagal membuat koneksi LDAP ke {$ldapUri}",
                    'driver' => 'php-ldap',
                ];
            }

            // Set essential Active Directory LDAP options
            @ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
            @ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);
            
            if (defined('LDAP_OPT_NETWORK_TIMEOUT')) {
                @ldap_set_option($conn, LDAP_OPT_NETWORK_TIMEOUT, $this->timeout);
            }
            if (defined('LDAP_OPT_TIMELIMIT')) {
                @ldap_set_option($conn, LDAP_OPT_TIMELIMIT, $this->timeout);
            }

            $domainPrefix = strtoupper(explode('.', $this->domain)[0]); // e.g. SIG
            
            // Build candidate logon identities supported by Active Directory
            $candidates = [];
            if (!str_contains($cleanIdentifier, '@')) {
                $candidates[] = "{$cleanIdentifier}@{$this->domain}"; // UPN format: username@sig.id
                $candidates[] = "{$domainPrefix}\\{$cleanIdentifier}"; // NetBIOS format: SIG\username
                $candidates[] = $cleanIdentifier;                     // sAMAccountName: username
            } else {
                $candidates[] = $cleanIdentifier;                     // Full email / UPN
                $candidates[] = "{$domainPrefix}\\{$username}";       // NetBIOS format: SIG\username
            }
            $candidates = array_values(array_unique($candidates));

            $bindSucceeded = false;
            $successfulCandidate = null;
            $lastDiagnostic = '';
            $lastError = '';

            foreach ($candidates as $candidate) {
                $bind = @ldap_bind($conn, $candidate, $password);
                if ($bind) {
                    $bindSucceeded = true;
                    $successfulCandidate = $candidate;
                    break;
                }

                $lastError = @ldap_error($conn) ?: 'Invalid credentials';
                $diag = '';
                @ldap_get_option($conn, 0x0032, $diag); // 0x0032 = LDAP_OPT_DIAGNOSTIC_MESSAGE
                if (!empty($diag)) {
                    $lastDiagnostic = $diag;
                }
            }

            if (!$bindSucceeded) {
                @ldap_close($conn);

                $adErrorExplanation = self::parseAdDiagnostic($lastDiagnostic);
                $finalMessage = $adErrorExplanation 
                    ?: "Autentikasi Active Directory gagal ({$lastError}).";

                Log::warning("[LDAP Auth Failed] User: {$cleanIdentifier} | Kandidat dicoba: " . implode(', ', $candidates) . " | Error: {$lastError} | Diag: {$lastDiagnostic}");

                return [
                    'success' => false,
                    'error' => $finalMessage,
                    'raw_error' => $lastError,
                    'diagnostic' => $lastDiagnostic,
                    'candidates' => $candidates,
                    'driver' => 'php-ldap',
                ];
            }

            // Bind succeeded! Extract Active Directory user details
            $userData = [
                'username' => $username,
                'email' => str_contains($cleanIdentifier, '@') ? $cleanIdentifier : "{$username}@{$this->domain}",
                'name' => $username,
                'department' => null,
                'position' => null,
                'employee_id' => null,
                'bound_as' => $successfulCandidate,
            ];

            try {
                $searchBase = !empty($this->baseDn) ? $this->baseDn : 'DC=' . str_replace('.', ',DC=', $this->domain);
                $escapedUser = ldap_escape($username, '', LDAP_ESCAPE_FILTER);
                $filter = "(&(objectClass=user)(|(sAMAccountName={$escapedUser})(userPrincipalName={$escapedUser}@{$this->domain})(mail={$escapedUser}@{$this->domain})))";
                $attributes = ['displayName', 'cn', 'mail', 'department', 'title', 'employeeID'];

                $search = @ldap_search($conn, $searchBase, $filter, $attributes);
                if ($search) {
                    $entries = @ldap_get_entries($conn, $search);
                    if ($entries && isset($entries[0])) {
                        $entry = $entries[0];
                        $userData['name'] = $entry['displayname'][0] ?? $entry['cn'][0] ?? $username;
                        if (!empty($entry['mail'][0])) {
                            $userData['email'] = strtolower($entry['mail'][0]);
                        }
                        $userData['department'] = $entry['department'][0] ?? null;
                        $userData['position'] = $entry['title'][0] ?? null;
                        $userData['employee_id'] = $entry['employeeid'][0] ?? null;
                    }
                }
            } catch (Exception $searchEx) {
                Log::warning('[LDAP] Berhasil bind tetapi pencarian atribut AD gagal: ' . $searchEx->getMessage());
            }

            @ldap_close($conn);

            return [
                'success' => true,
                'data' => $userData,
                'driver' => 'php-ldap',
            ];

        } catch (Exception $e) {
            Log::error('[LDAP Exception]: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => 'Koneksi ke server LDAP gagal: ' . $e->getMessage(),
                'driver' => 'php-ldap',
            ];
        }
    }

    /**
     * Authenticate via Python ldap3 helper script as a fallback.
     */
    protected function authenticateViaPython(string $identifier, string $password): array
    {
        $scriptPath = base_path('scripts/ldap_auth.py');
        if (!file_exists($scriptPath)) {
            return [
                'success' => false,
                'error' => 'Ekstensi php-ldap tidak aktif dan scripts/ldap_auth.py tidak ditemukan.',
                'driver' => 'python-fallback',
            ];
        }

        try {
            $process = new Process([
                'python',
                $scriptPath,
                $identifier,
                $password,
            ], base_path(), [
                'LDAP_HOST' => $this->host,
                'LDAP_PORT' => (string) $this->port,
                'LDAP_DOMAIN' => $this->domain,
                'LDAP_BASE_DN' => $this->baseDn,
            ], null, 5);

            $process->run();

            if (!$process->isSuccessful()) {
                $output = trim($process->getErrorOutput() ?: $process->getOutput());
                return [
                    'success' => false,
                    'error' => 'Python LDAP process failed: ' . $output,
                    'driver' => 'python-fallback',
                ];
            }

            $json = json_decode(trim($process->getOutput()), true);
            if (is_array($json)) {
                $json['driver'] = 'python-fallback';
                return $json;
            }

            return [
                'success' => false,
                'error' => 'Format balasan Python LDAP tidak valid: ' . $process->getOutput(),
                'driver' => 'python-fallback',
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Gagal menjalankan Python LDAP: ' . $e->getMessage(),
                'driver' => 'python-fallback',
            ];
        }
    }

    /**
     * Parse Active Directory diagnostic string to user-friendly explanation.
     */
    public static function parseAdDiagnostic(string $diagnostic): ?string
    {
        if (preg_match('/data\s+([0-9a-fA-F]{3,4})/', $diagnostic, $matches)) {
            $code = strtolower($matches[1]);
            return match ($code) {
                '525' => 'Pengguna/Akun tidak ditemukan di Active Directory (Kode: 525). Pastikan username atau NIP benar.',
                '52e' => 'Kata sandi atau nama akun Active Directory salah (Kode: 52e).',
                '530' => 'Akun tidak diizinkan masuk pada waktu ini (Kode: 530).',
                '531' => 'Akun tidak diizinkan masuk dari komputer ini (Kode: 531).',
                '532' => 'Kata sandi akun Active Directory telah kedaluwarsa (Kode: 532). Silakan perbarui sandi akun SIG.',
                '533' => 'Akun Active Directory dinonaktifkan (Kode: 533). Silakan hubungi IT Administrator SIG.',
                '701' => 'Masa berlaku akun Active Directory telah habis (Kode: 701).',
                '773' => 'Pengguna wajib mengganti kata sandi sebelum dapat masuk (Kode: 773).',
                '775' => 'Akun Active Directory terkunci karena salah memasukkan kata sandi beberapa kali (Kode: 775).',
                default => "Respon Active Directory: data {$code}",
            };
        }
        return null;
    }
}


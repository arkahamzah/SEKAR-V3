<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Karyawan;
use App\Models\Iuran;
use App\Models\Params;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected $ssoUrl = 'https://auth.telkom.co.id/v2/account/validate';
    protected $ldapServer = 'ldap://dc.telkom.co.id:389'; // LDAP server Telkom

    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * SSO Login - hanya dengan NIK tanpa password di form utama
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'nik' => 'required|string',
        ]);

        $nik = $validated['nik'];

        // Check if NIK exists in karyawan table
        $karyawan = Karyawan::where('N_NIK', $nik)->first();
        if (!$karyawan) {
            throw ValidationException::withMessages([
                'nik' => ['NIK tidak ditemukan di data karyawan.']
            ]);
        }

        // Generate SSO token untuk popup authentication
        $ssoToken = $this->generateSSOToken($nik);
        
        // Store temporary session untuk SSO process
        Session::put('sso_pending', [
            'nik' => $nik,
            'token' => $ssoToken,
            'timestamp' => now(),
            'karyawan_data' => $karyawan->toArray()
        ]);

        Log::info('SSO Login initiated', [
            'NIK' => $nik,
            'token' => $ssoToken
        ]);

        // Return response untuk trigger popup authentication
        return response()->json([
            'success' => true,
            'redirect_to_popup' => true,
            'sso_token' => $ssoToken,
            'nik' => $nik,
            'popup_url' => route('sso.popup', ['token' => $ssoToken])
        ]);
    }

    /**
     * Show SSO popup untuk authentication
     */
    public function showSSOPopup(Request $request, $token)
    {
        // Verify token masih valid
        $pendingSSO = Session::get('sso_pending');
        
        if (!$pendingSSO || $pendingSSO['token'] !== $token) {
            return view('auth.sso-error', [
                'message' => 'Invalid SSO token or session expired.'
            ]);
        }

        // Check if token expired (5 menit)
        if (now()->diffInMinutes($pendingSSO['timestamp']) > 5) {
            Session::forget('sso_pending');
            return view('auth.sso-error', [
                'message' => 'SSO session expired. Please try again.'
            ]);
        }

        return view('auth.sso-popup', [
            'nik' => $pendingSSO['nik'],
            'token' => $token,
            'user_name' => $pendingSSO['karyawan_data']['V_NAMA_KARYAWAN']
        ]);
    }

    /**
     * Process SSO authentication dari popup
     */
    public function processSSOAuth(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string',
            'sso_password' => 'required|string',
        ]);

        // Verify SSO session
        $pendingSSO = Session::get('sso_pending');
        
        if (!$pendingSSO || $pendingSSO['token'] !== $validated['token']) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid SSO session.'
            ], 401);
        }

        $nik = $pendingSSO['nik'];
        $ssoPassword = $validated['sso_password'];

        try {
            // Authenticate via SSO/LDAP
            $authResult = $this->authenticateSSO($nik, $ssoPassword);
            
            if (!$authResult['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $authResult['message']
                ], 401);
            }

            // Get karyawan data
            $karyawan = Karyawan::where('N_NIK', $nik)->first();
            
            // Check if user already exists
            $user = User::where('nik', $nik)->first();
            
            // Create user if doesn't exist (auto-registration)
            if (!$user) {
                $user = $this->createUserFromKaryawan($karyawan, $ssoPassword);
            }

            // Login user
            Auth::login($user);
            
            // Set detailed session information
            $this->setDetailedSession($karyawan);

            // Clear SSO pending session
            Session::forget('sso_pending');

            Log::info('SSO Authentication successful', [
                'NIK' => $nik,
                'user_id' => $user->id,
                'method' => $authResult['method']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Authentication successful',
                'redirect_url' => route('dashboard')
            ]);

        } catch (\Exception $e) {
            Log::error('SSO Authentication failed', [
                'NIK' => $nik,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Authentication failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Authenticate via SSO/LDAP
     */
    private function authenticateSSO($nik, $password)
    {
        // Try Telkom SSO API first
        $ssoResult = $this->validateTelkomSSO($nik, $password);
        if ($ssoResult) {
            return [
                'success' => true,
                'method' => 'Telkom_SSO',
                'data' => $ssoResult
            ];
        }

        // Try LDAP authentication
        $ldapResult = $this->validateLDAP($nik, $password);
        if ($ldapResult) {
            return [
                'success' => true,
                'method' => 'LDAP',
                'data' => $ldapResult
            ];
        }

        // Fallback untuk development
        if (env('SSO_USE_FALLBACK', false) && $password === env('SSO_FALLBACK_PASSWORD', 'Telkom')) {
            return [
                'success' => true,
                'method' => 'fallback',
                'data' => ['message' => 'Development fallback used']
            ];
        }

        return [
            'success' => false,
            'message' => 'Invalid SSO credentials'
        ];
    }

    /**
     * Validate via Telkom SSO API
     */
    private function validateTelkomSSO($username, $password)
    {
        try {
            $response = Http::withHeaders([
                'AppsToken' => env('SSO_APPS_TOKEN'),
                'AppsName' => env('SSO_APPS_NAME', 'sekar'),
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->timeout(10)->post($this->ssoUrl, [
                'username' => $username,
                'password' => $password,
            ]);

            $responseData = $response->json();
            
            if ($responseData && $responseData['status'] === 'success' && $responseData['code'] == 200) {
                return $responseData;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Telkom SSO Authentication failed', [
                'NIK' => $username,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Validate via LDAP
     */
    private function validateLDAP($nik, $password)
    {
        try {
            // Check if LDAP extension is available
            if (!extension_loaded('ldap')) {
                Log::warning('LDAP extension not available');
                return null;
            }

            $ldapConnection = ldap_connect($this->ldapServer);
            
            if (!$ldapConnection) {
                Log::error('Cannot connect to LDAP server');
                return null;
            }

            // Set LDAP options
            ldap_set_option($ldapConnection, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($ldapConnection, LDAP_OPT_REFERRALS, 0);

            // Try to bind with user credentials
            $userDN = "uid={$nik},ou=users,dc=telkom,dc=co,dc=id"; // Adjust based on your LDAP structure
            $bind = @ldap_bind($ldapConnection, $userDN, $password);

            if ($bind) {
                // Get user information from LDAP
                $searchResult = ldap_search($ldapConnection, "ou=users,dc=telkom,dc=co,dc=id", "(uid={$nik})");
                $entries = ldap_get_entries($ldapConnection, $searchResult);
                
                ldap_close($ldapConnection);
                
                return [
                    'authenticated' => true,
                    'user_data' => $entries[0] ?? []
                ];
            }

            ldap_close($ldapConnection);
            return null;

        } catch (\Exception $e) {
            Log::error('LDAP Authentication failed', [
                'NIK' => $nik,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Generate SSO token
     */
    private function generateSSOToken($nik)
    {
        return hash('sha256', $nik . time() . env('APP_KEY'));
    }

    /**
     * Create user from karyawan data
     */
    private function createUserFromKaryawan(Karyawan $karyawan, string $password): User
    {
        return DB::transaction(function () use ($karyawan, $password) {
            // Generate email
            $email = $karyawan->N_NIK . '@sekar.local';

            $user = User::create([
                'nik' => $karyawan->N_NIK,
                'name' => $karyawan->V_NAMA_KARYAWAN,
                'email' => $email,
                'password' => Hash::make($password), // Store SSO password hash
            ]);

            // Create default iuran record
            $this->createOrUpdateIuranRecord($karyawan->N_NIK, 0);

            Log::info('User created from SSO authentication', [
                'NIK' => $karyawan->N_NIK,
                'user_id' => $user->id
            ]);

            return $user;
        });
    }

    /**
     * Set detailed session information
     */
    private function setDetailedSession(Karyawan $karyawan)
    {
        Session::put('USER_DETAIL', [
            'NIK' => $karyawan->N_NIK,
            'name' => $karyawan->V_NAMA_KARYAWAN,
            'position' => $karyawan->V_SHORT_POSISI,
            'unit' => $karyawan->V_SHORT_UNIT,
            'divisi' => $karyawan->V_SHORT_DIVISI,
            'location' => $karyawan->V_KOTA_GEDUNG,
            'pa' => $karyawan->C_PERSONNEL_AREA,
            'psa' => $karyawan->C_PERSONNEL_SUB_AREA,
            'kodeDivisi' => $karyawan->C_KODE_DIVISI,
            'kodeUnit' => $karyawan->C_KODE_UNIT,
            'kodePosisi' => $karyawan->C_KODE_POSISI,
            'bandPosisi' => $karyawan->V_BAND_POSISI,
            'noTelp' => $karyawan->NO_TELP,
        ]);
    }

    /**
     * Regular registration method (tetap tersedia)
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'nik' => 'required|string|unique:users,nik',
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed',
            'iuran_sukarela' => 'nullable|string',
        ]);

        // Check if NIK exists in karyawan table
        $karyawan = Karyawan::where('N_NIK', $validated['nik'])->first();
        
        if (!$karyawan) {
            throw ValidationException::withMessages([
                'nik' => ['NIK tidak ditemukan dalam data karyawan.'],
            ]);
        }

        // Process iuran sukarela
        $iuranSukarela = 0;
        if (!empty($validated['iuran_sukarela'])) {
            $cleanedIuran = preg_replace('/[^0-9]/', '', $validated['iuran_sukarela']);
            $iuranSukarela = (int) $cleanedIuran;
        }

        try {
            DB::transaction(function () use ($validated, $karyawan, $iuranSukarela) {
                $email = $validated['nik'] . '@sekar.local';

                $user = User::create([
                    'nik' => $validated['nik'],
                    'name' => $validated['name'],
                    'email' => $email,
                    'password' => Hash::make($validated['password']),
                ]);

                $this->createOrUpdateIuranRecord($validated['nik'], $iuranSukarela);
                $this->setDetailedSession($karyawan);

                Auth::login($user);
            });

            return redirect()->route('dashboard')->with('success', 'Pendaftaran berhasil! Selamat datang di SEKAR.');
            
        } catch (\Exception $e) {
            Log::error('Registration Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withInput()->with('error', 'Terjadi kesalahan saat registrasi: ' . $e->getMessage());
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Create or update iuran record
     */
    private function createOrUpdateIuranRecord(string $nik, int $iuranSukarela): void
    {
        $params = Params::where('IS_AKTIF', '1')
                        ->where('TAHUN', date('Y'))
                        ->first();
        
        $iuranWajib = $params ? $params->NOMINAL_IURAN_WAJIB : '25000';

        $existingIuran = Iuran::where('N_NIK', $nik)->first();
        
        if ($existingIuran) {
            $existingIuran->update([
                'IURAN_WAJIB' => $iuranWajib,
                'IURAN_SUKARELA' => (string) $iuranSukarela,
                'UPDATE_BY' => $nik,
                'UPDATED_AT' => now(),
            ]);
        } else {
            Iuran::create([
                'N_NIK' => $nik,
                'IURAN_WAJIB' => $iuranWajib,
                'IURAN_SUKARELA' => (string) $iuranSukarela,
                'CREATED_BY' => $nik,
                'CREATED_AT' => now(),
            ]);
        }
    }
}
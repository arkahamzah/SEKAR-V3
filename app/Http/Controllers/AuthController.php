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
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    // === SSO LOGIN METHODS ===
    
    public function login(Request $request)
    {
        $validated = $request->validate([
            'nik' => 'required|string',
        ]);

        $nik = $validated['nik'];

        // Check if NIK exists as a registered USER (bukan dari tabel karyawan)
        $user = User::where('nik', $nik)->first();
        if (!$user) {
            throw ValidationException::withMessages([
                'nik' => ['NIK belum terdaftar sebagai anggota SEKAR. Silakan daftar terlebih dahulu.']
            ]);
        }
        
        /*
        // Pastikan user masih aktif (optional validation)
        if ($user->membership_status === 'inactive') {
            throw ValidationException::withMessages([
                'nik' => ['Akun Anda tidak aktif. Hubungi administrator untuk bantuan.']
            ]);
        }
        */

        // Get karyawan data untuk session (masih perlu untuk data lengkap)
        $karyawan = Karyawan::where('N_NIK', $nik)->first();
        if (!$karyawan) {
            // Ini seharusnya tidak terjadi karena user dibuat dari karyawan
            // Tapi tetap handle edge case
            throw ValidationException::withMessages([
                'nik' => ['Data karyawan tidak ditemukan. Hubungi administrator.']
            ]);
        }

        // Generate SSO token untuk popup authentication
        $ssoToken = $this->generateSSOToken($nik);
        
        // Store temporary session untuk SSO process
        Session::put('sso_pending', [
            'nik' => $nik,
            'token' => $ssoToken,
            'timestamp' => now(),
            'user_data' => $user->toArray(),
            'karyawan_data' => $karyawan->toArray()
        ]);

        Log::info('Login initiated for registered user', [
            'NIK' => $nik,
            'user_id' => $user->id,
            'membership_status' => $user->membership_status,
            'token' => $ssoToken
        ]);

        return response()->json([
            'success' => true,
            'redirect_to_popup' => true,
            'sso_token' => $ssoToken,
            'nik' => $nik,
            'popup_url' => route('sso.popup', ['token' => $ssoToken])
        ]);
    }

    public function showSSOPopup(Request $request, $token)
    {
        $pendingSSO = Session::get('sso_pending');
        
        if (!$pendingSSO || $pendingSSO['token'] !== $token) {
            return view('auth.sso-error', [
                'message' => 'Invalid token or session expired.'
            ]);
        }

        if (now()->diffInMinutes($pendingSSO['timestamp']) > 5) {
            Session::forget('sso_pending');
            return view('auth.sso-error', [
                'message' => 'Session expired. Please try again.'
            ]);
        }

        // Get data from session
        $userData = $pendingSSO['user_data'];
        $karyawanData = $pendingSSO['karyawan_data'];

        return view('auth.sso-popup', [
            'nik' => $pendingSSO['nik'],
            'token' => $token,
            // Pastikan semua variabel yang dibutuhkan view ada
            'user_name' => $userData['name'], // Nama dari tabel users
            'user_email' => $userData['email'] ?? ($userData['nik'] . '@sekar.local'),
            'membership_status' => $userData['membership_status'] ?? 'active',
            'karyawan_position' => $karyawanData['V_SHORT_POSISI'] ?? 'Unknown',
            'is_gptp' => ($userData['is_gptp_preorder'] ?? false) || (stripos($karyawanData['V_SHORT_POSISI'] ?? '', 'GPTP') !== false)
        ]);
    }

    public function processSSOAuth(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string',
            'sso_password' => 'required|string',
        ]);

        $pendingSSO = Session::get('sso_pending');
        
        if (!$pendingSSO || $pendingSSO['token'] !== $validated['token']) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid session.'
            ], 401);
        }

        $nik = $pendingSSO['nik'];
        $password = $validated['sso_password'];

        try {
            // Authenticate using fallback password
            $authResult = $this->authenticateSSO($nik, $password);
            
            if (!$authResult['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $authResult['message']
                ], 401);
            }

            // Get the existing user (sudah divalidasi di method login)
            $user = User::where('nik', $nik)->first();
            $karyawan = Karyawan::where('N_NIK', $nik)->first();
            
            if (!$user) {
                // Ini seharusnya tidak terjadi karena sudah divalidasi di login
                Log::error('User not found during SSO auth', ['NIK' => $nik]);
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan. Silakan daftar terlebih dahulu.'
                ], 404);
            }

            // Login the existing user
            Auth::login($user);
            $this->setDetailedSession($karyawan);
            Session::forget('sso_pending');

            Log::info('Login successful for registered user', [
                'NIK' => $nik,
                'user_id' => $user->id,
                'method' => $authResult['method']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil! Selamat datang kembali.',
                'redirect_url' => route('dashboard')
            ]);

        } catch (\Exception $e) {
            Log::error('Login authentication failed', [
                'NIK' => $nik,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Login gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    // === REGISTRATION METHODS ===
    
    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'nik' => 'required|string|min:6|max:10',
                'name' => 'required|string',
                'iuran_sukarela' => 'required|numeric|min:0',
                'sso_password' => 'required|string',
            ]);

            $nik = $validated['nik'];
            $name = $validated['name'];
            $iuranSukarela = (int) $validated['iuran_sukarela'];
            $ssoPassword = $validated['sso_password'];

            Log::info('Development registration started', [
                'NIK' => $nik,
                'name' => $name,
                'iuran_sukarela' => $iuranSukarela
            ]);

            // Check if user already exists
            if (User::where('nik', $nik)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'NIK sudah terdaftar sebagai anggota SEKAR.'
                ], 400);
            }

            // Check if NIK exists in karyawan table
            $karyawan = Karyawan::where('N_NIK', $nik)->first();
            if (!$karyawan) {
                return response()->json([
                    'success' => false,
                    'message' => 'NIK tidak ditemukan di data karyawan Telkom.'
                ], 404);
            }

            // Validate using fallback authentication
            $authResult = $this->authenticateSSO($nik, $ssoPassword);
            if (!$authResult['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $authResult['message']
                ], 401);
            }

            // Create user account
            DB::transaction(function () use ($karyawan, $ssoPassword, $iuranSukarela) {
                $user = $this->createUserFromKaryawan($karyawan, $ssoPassword);
                $this->createOrUpdateIuranRecord($karyawan->N_NIK, $iuranSukarela);
                
                // Auto login
                Auth::login($user);
                $this->setDetailedSession($karyawan);
            });

            Log::info('Development registration successful', [
                'NIK' => $nik,
                'method' => $authResult['method']
            ]);

            // Check if GPTP for different redirect
            $isGPTP = stripos($karyawan->V_SHORT_DIVISI, 'GPTP') !== false;
            
            if ($isGPTP) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pendaftaran berhasil! Sebagai karyawan GPTP, Anda akan resmi menjadi anggota SEKAR pada ' . 
                        now()->addYear()->format('d F Y') . '. Selamat datang di SEKAR!',
                    'redirect_url' => route('dashboard')
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'Pendaftaran berhasil! Selamat datang di SEKAR.',
                    'redirect_url' => route('dashboard')
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Registration Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'nik' => $request->input('nik')
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat registrasi: ' . $e->getMessage()
            ], 500);
        }
    }

    // === HELPER METHODS ===

    private function authenticateSSO($nik, $password)
    {
        Log::info('Starting fallback authentication for development', ['NIK' => $nik]);

        // Hanya gunakan fallback untuk development
        if (env('SSO_USE_FALLBACK', true) && $password === env('SSO_FALLBACK_PASSWORD', 'Telkom')) {
            Log::info('Fallback authentication successful', ['NIK' => $nik]);
            return [
                'success' => true,
                'method' => 'fallback_development',
                'data' => ['message' => 'Development fallback authentication used']
            ];
        }

        Log::warning('Fallback authentication failed - wrong password', ['NIK' => $nik]);
        return [
            'success' => false,
            'message' => 'Password salah. Gunakan password development: "' . env('SSO_FALLBACK_PASSWORD', 'Telkom') . '"',
            'error_type' => 'invalid_credentials'
        ];
    }

    private function generateSSOToken($nik)
    {
        return hash('sha256', $nik . time() . env('APP_KEY'));
    }

    private function createUserFromKaryawan(Karyawan $karyawan, string $password): User
    {
        return DB::transaction(function () use ($karyawan, $password) {
            $email = $karyawan->N_NIK . '@sekar.local';

            // Check if GPTP
            $isGPTP = stripos($karyawan->V_SHORT_POSISI, 'GPTP') !== false;
            $membershipActiveDate = $isGPTP ? now()->addYear() : now();

            $user = User::create([
                'nik' => $karyawan->N_NIK,
                'name' => $karyawan->V_NAMA_KARYAWAN,
                'email' => $email,
                'password' => Hash::make($password),
                'membership_status' => $isGPTP ? 'pending' : 'active',
                'membership_active_date' => $membershipActiveDate,
                'is_gptp_preorder' => $isGPTP,
            ]);

            Log::info('User created from fallback authentication', [
                'NIK' => $karyawan->N_NIK,
                'user_id' => $user->id,
                'is_gptp' => $isGPTP,
                'membership_active_date' => $membershipActiveDate
            ]);

            return $user;
        });
    }

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

   private function createOrUpdateIuranRecord(string $nik, int $iuranSukarela): void
    {
        $params = Params::where('IS_AKTIF', '1')
                        ->where('TAHUN', date('Y'))
                        ->first();
        
        // Gunakan kolom yang benar dari model Params
        $iuranWajib = $params ? (int)$params->NOMINAL_IURAN_WAJIB : 25000;

        // Gunakan kolom yang benar dari model Iuran
        Iuran::updateOrCreate(
            ['N_NIK' => $nik], // Key untuk mencari record
            [
                'N_NIK' => $nik,
                'IURAN_WAJIB' => $iuranWajib,
                'IURAN_SUKARELA' => $iuranSukarela,
                'CREATED_BY' => $nik,
                'CREATED_AT' => now(),
                'UPDATE_BY' => $nik,
                'UPDATED_AT' => now()
            ]
        );

        Log::info('Iuran record created/updated', [
            'NIK' => $nik,
            'iuran_wajib' => $iuranWajib,
            'iuran_sukarela' => $iuranSukarela,
            'total' => $iuranWajib + $iuranSukarela
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        Session::flush();
        
        return redirect()->route('login')->with('success', 'Berhasil logout.');
    }
    public function getKaryawanData(Request $request)
    {
        try {
            $validated = $request->validate([
                'nik' => 'required|string|min:6|max:10',
            ]);

            $nik = $validated['nik'];

            // Check if NIK exists in karyawan table
            $karyawan = Karyawan::where('N_NIK', $nik)->first();
            
            if (!$karyawan) {
                return response()->json([
                    'success' => false,
                    'message' => 'NIK tidak ditemukan di data karyawan Telkom.'
                ], 404);
            }

            // Check if already registered as user
            $existingUser = User::where('nik', $nik)->first();
            if ($existingUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'NIK sudah terdaftar sebagai anggota SEKAR.'
                ], 400);
            }

            // Check if GPTP
            $isGPTP = stripos($karyawan->V_SHORT_POSISI, 'GPTP') !== false;

            // Return karyawan data
            return response()->json([
                'success' => true,
                'data' => [
                    'nik' => $karyawan->N_NIK,
                    'name' => $karyawan->V_NAMA_KARYAWAN,
                    'position' => $karyawan->V_SHORT_POSISI,
                    'unit' => $karyawan->V_SHORT_UNIT,
                    'divisi' => $karyawan->V_SHORT_DIVISI,
                    'location' => $karyawan->V_KOTA_GEDUNG,
                    'is_gptp' => $isGPTP,
                    'email' => $karyawan->N_NIK . '@sekar.local'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Karyawan data fetch error:', [
                'nik' => $request->input('nik'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memuat data karyawan.'
            ], 500);
        }
    }

}
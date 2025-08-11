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
    
    public function login(Request $request)
    {
        $validated = $request->validate(['nik' => 'required|string']);
        $nik = $validated['nik'];
        
        $user = User::where('nik', $nik)->first();
        if (!$user) {
            throw ValidationException::withMessages([
                'nik' => ['NIK belum terdaftar sebagai anggota SEKAR. Silakan daftar terlebih dahulu.']
            ]);
        }
        
        $karyawan = Karyawan::where('N_NIK', $nik)->first();
        if (!$karyawan) {
            throw ValidationException::withMessages([
                'nik' => ['Data karyawan tidak ditemukan. Hubungi administrator.']
            ]);
        }

        $ssoToken = $this->generateSSOToken($nik);
        
        Session::put('sso_pending', [
            'nik' => $nik,
            'token' => $ssoToken,
            'timestamp' => now(),
            'user_data' => $user->toArray(),
            'karyawan_data' => $karyawan->toArray()
        ]);

        Log::info('Login initiated', ['NIK' => $nik, 'token' => $ssoToken]);

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
            return view('auth.sso-error', ['message' => 'Token tidak valid atau sesi telah berakhir.']);
        }

        if (now()->diffInMinutes($pendingSSO['timestamp']) > 5) {
            Session::forget('sso_pending');
            return view('auth.sso-error', ['message' => 'Sesi telah berakhir. Silakan coba lagi.']);
        }

        $userData = $pendingSSO['user_data'];
        $karyawanData = $pendingSSO['karyawan_data'];

        return view('auth.sso-popup', [
            'nik' => $pendingSSO['nik'],
            'token' => $token,
            'user_name' => $userData['name'],
            'user_email' => $userData['email'] ?? ($userData['nik'] . '@sekar.local'),
            'membership_status' => $userData['membership_status'] ?? 'active',
            'karyawan_position' => $karyawanData['V_SHORT_POSISI'] ?? 'Unknown',
            'is_gptp' => ($userData['is_gptp_preorder'] ?? false) || $this->isGPTPEmployee((object)$karyawanData)
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
            return $this->jsonError('Sesi tidak valid.', 401);
        }

        $nik = $pendingSSO['nik'];
        $password = $validated['sso_password'];

        try {
            $authResult = $this->authenticateSSO($nik, $password);
            
            if (!$authResult['success']) {
                return $this->jsonError($authResult['message'], 401);
            }

            $user = User::where('nik', $nik)->first();
            $karyawan = Karyawan::where('N_NIK', $nik)->first();
            
            if (!$user) {
                return $this->jsonError('User tidak ditemukan.', 404);
            }

            Auth::login($user);
            $this->setDetailedSession($karyawan);
            Session::forget('sso_pending');

            Log::info('Login successful', ['NIK' => $nik, 'method' => $authResult['method']]);

            return $this->jsonSuccess('Login berhasil! Selamat datang kembali.', route('dashboard'));

        } catch (\Exception $e) {
            Log::error('Login failed', ['NIK' => $nik, 'error' => $e->getMessage()]);
            return $this->jsonError('Login gagal: ' . $e->getMessage(), 500);
        }
    }
    
    public function register(Request $request)
    {
        try {
            $validated = $this->validateRegistration($request);
            
            $nik = $validated['nik'];
            $iuranSukarela = (int) $validated['iuran_sukarela'];
            $ssoPassword = $validated['sso_password'];

            $iuranWajib = $this->getIuranWajib();
            $totalIuran = $iuranWajib + $iuranSukarela;

            Log::info('Registration started', [
                'NIK' => $nik,
                'iuran_wajib' => $iuranWajib,
                'iuran_sukarela' => $iuranSukarela,
                'total_iuran' => $totalIuran
            ]);

            // Check existing user
            if (User::where('nik', $nik)->exists()) {
                return $this->jsonError('NIK sudah terdaftar sebagai anggota SEKAR.', 400);
            }

            // Get employee data
            $karyawan = Karyawan::where('N_NIK', $nik)->first();
            if (!$karyawan) {
                return $this->jsonError('NIK tidak ditemukan di data karyawan Telkom.', 404);
            }

            // Authenticate SSO
            $authResult = $this->authenticateSSO($nik, $ssoPassword);
            if (!$authResult['success']) {
                return $this->jsonError($authResult['message'], 401);
            }

            // Create user and records
            DB::transaction(function () use ($karyawan, $ssoPassword, $iuranSukarela) {
                $user = $this->createUserFromKaryawan($karyawan, $ssoPassword);
                $this->createOrUpdateIuranRecord($karyawan->N_NIK, $iuranSukarela);
                
                Auth::login($user);
                $this->setDetailedSession($karyawan);
            });

            $isGPTP = $this->isGPTPEmployee($karyawan);
            $successMessage = $this->buildSuccessMessage($isGPTP, $iuranWajib, $iuranSukarela, $totalIuran);

            Log::info('Registration successful', ['NIK' => $nik, 'is_gptp' => $isGPTP, 'total_iuran' => $totalIuran]);
            
            return $this->jsonSuccess($successMessage, route('dashboard'));
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->jsonError(collect($e->errors())->flatten()->first(), 422, $e->errors());
        } catch (\Exception $e) {
            Log::error('Registration Error', ['message' => $e->getMessage(), 'nik' => $request->input('nik')]);
            return $this->jsonError('Terjadi kesalahan saat registrasi: ' . $e->getMessage(), 500);
        }
    }

    public function getKaryawanData(Request $request)
    {
        try {
            $validated = $request->validate(['nik' => 'required|string|min:6|max:10']);
            $nik = $validated['nik'];

            // Check if already registered
            if (User::where('nik', $nik)->exists()) {
                return $this->jsonError('NIK sudah terdaftar sebagai anggota SEKAR.', 400);
            }

            // Get employee data
            $karyawan = Karyawan::where('N_NIK', $nik)->first();
            if (!$karyawan) {
                return $this->jsonError('NIK tidak ditemukan di data karyawan Telkom.', 404);
            }

            return response()->json([
                'success' => true,
                'data' => $this->formatKaryawanData($karyawan)
            ]);

        } catch (\Exception $e) {
            Log::error('Get karyawan data error', ['nik' => $request->input('nik'), 'error' => $e->getMessage()]);
            return $this->jsonError('Terjadi kesalahan saat memuat data karyawan.', 500);
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        Session::flush();
        return redirect()->route('login')->with('success', 'Berhasil logout.');
    }

    // Private helper methods
    private function validateRegistration(Request $request): array
    {
        return $request->validate([
            'nik' => 'required|string|min:6|max:10',
            'name' => 'required|string|max:150',
            'iuran_sukarela' => [
                'required',
                'numeric',
                'min:0',
                'max:1000000',
                function ($attribute, $value, $fail) {
                    if ($value % 5000 !== 0) {
                        $fail('Iuran sukarela harus dalam kelipatan Rp 5.000.');
                    }
                },
            ],
            'sso_password' => 'required|string',
            'agreement' => 'required|accepted'
        ], [
            'agreement.required' => 'Anda harus menyetujui pernyataan keanggotaan',
            'agreement.accepted' => 'Anda harus menyetujui pernyataan keanggotaan',
        ]);
    }

    private function authenticateSSO(string $nik, string $password): array
    {
        Log::info('Starting SSO authentication', ['NIK' => $nik]);

        if (env('SSO_USE_FALLBACK', true) && $password === env('SSO_FALLBACK_PASSWORD', 'Telkom')) {
            return ['success' => true, 'method' => 'fallback_development'];
        }

        return [
            'success' => false,
            'message' => 'Password salah. Gunakan password development: "' . env('SSO_FALLBACK_PASSWORD', 'Telkom') . '"'
        ];
    }

    private function generateSSOToken(string $nik): string
    {
        return hash('sha256', $nik . time() . env('APP_KEY'));
    }

    private function createUserFromKaryawan(Karyawan $karyawan, string $password): User
    {
        $isGPTP = $this->isGPTPEmployee($karyawan);
        $membershipActiveDate = $isGPTP ? now()->addYear() : now();

        return User::create([
            'nik' => $karyawan->N_NIK,
            'name' => $karyawan->V_NAMA_KARYAWAN,
            'email' => $karyawan->N_NIK . '@sekar.local',
            'password' => Hash::make($password),
            'membership_status' => $isGPTP ? 'pending' : 'active',
            'membership_active_date' => $membershipActiveDate,
            'is_gptp_preorder' => $isGPTP,
        ]);
    }

    private function setDetailedSession(Karyawan $karyawan): void
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
        $iuranWajib = $this->getIuranWajib();

        Iuran::updateOrCreate(
            ['N_NIK' => $nik], 
            [
                'IURAN_WAJIB' => $iuranWajib,
                'IURAN_SUKARELA' => $iuranSukarela,
                'CREATED_BY' => $nik,
                'CREATED_AT' => now(),
                'UPDATE_BY' => $nik,
                'UPDATED_AT' => now()
            ]
        );

        Log::info('Iuran record updated', [
            'NIK' => $nik,
            'iuran_wajib' => $iuranWajib,
            'iuran_sukarela' => $iuranSukarela,
            'total' => $iuranWajib + $iuranSukarela
        ]);
    }

    private function getIuranWajib(): int
    {
        $params = Params::where('IS_AKTIF', '1')->where('TAHUN', date('Y'))->first();
        return $params ? (int)$params->NOMINAL_IURAN_WAJIB : 25000;
    }

    private function isGPTPEmployee($karyawan): bool
    {
        $divisi = is_object($karyawan) ? $karyawan->V_SHORT_DIVISI : $karyawan['V_SHORT_DIVISI'];
        return stripos($divisi ?? '', 'GPTP') !== false;
    }

    private function formatKaryawanData(Karyawan $karyawan): array
    {
        return [
            'nik' => $karyawan->N_NIK,
            'name' => $karyawan->V_NAMA_KARYAWAN,
            'position' => $karyawan->V_SHORT_POSISI ?: 'KARYAWAN',
            'unit' => $karyawan->V_SHORT_UNIT ?: 'TELKOM',
            'divisi' => $karyawan->V_SHORT_DIVISI ?: 'DIVISI TIDAK DIKETAHUI',
            'location' => $karyawan->V_KOTA_GEDUNG ?: 'LOKASI TIDAK DIKETAHUI',
            'is_gptp' => $this->isGPTPEmployee($karyawan),
            'email' => $karyawan->N_NIK . '@sekar.local'
        ];
    }

    private function buildSuccessMessage(bool $isGPTP, int $iuranWajib, int $iuranSukarela, int $totalIuran): string
    {
        $iuranInfo = "Iuran bulanan Anda: Rp " . number_format($totalIuran, 0, ',', '.') . 
            " (Wajib: Rp " . number_format($iuranWajib, 0, ',', '.') . 
            ", Sukarela: Rp " . number_format($iuranSukarela, 0, ',', '.') . ").";

        if ($isGPTP) {
            return "Pendaftaran berhasil! Sebagai karyawan GPTP, Anda akan resmi menjadi anggota SEKAR pada " . 
                now()->addYear()->format('d F Y') . ". " . $iuranInfo . " Selamat datang di SEKAR TELKOM!";
        }

        return "Pendaftaran berhasil! Selamat datang di SEKAR TELKOM. " . $iuranInfo;
    }

    private function jsonSuccess(string $message, string $redirectUrl = null): \Illuminate\Http\JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message
        ];

        if ($redirectUrl) {
            $response['redirect_url'] = $redirectUrl;
        }

        return response()->json($response);
    }

    private function jsonError(string $message, int $status = 400, array $errors = null): \Illuminate\Http\JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }
}
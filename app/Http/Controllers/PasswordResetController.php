<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Karyawan;
use App\Models\PasswordResetToken;
use App\Mail\PasswordResetNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PasswordResetController extends Controller
{
    /**
     * Show the password reset request form
     */
    public function showRequestForm()
    {
        return view('auth.passwords.email');
    }

    /**
     * Send password reset link via email
     */
    public function sendResetLink(Request $request)
    {
        $validated = $request->validate([
            'nik' => 'required|string|exists:users,nik',
        ], [
            'nik.required' => 'NIK wajib diisi.',
            'nik.exists' => 'NIK tidak ditemukan dalam sistem.',
        ]);

        try {
            $user = User::where('nik', $validated['nik'])->first();
            
            if (!$user) {
                return back()->withErrors(['nik' => 'NIK tidak ditemukan.']);
            }

            // Check if user has valid email (dummy emails won't work)
            if (!$user->email || str_contains($user->email, '@sekar.local')) {
                return back()->withErrors(['nik' => 'Akun Anda tidak memiliki email yang valid. Silakan hubungi administrator.']);
            }

            // Generate reset token
            $token = $this->generateResetToken($user);
            
            if (!$token) {
                return back()->withErrors(['nik' => 'Terjadi kesalahan. Silakan coba lagi.']);
            }

            // Send email
            Mail::to($user->email)->send(new PasswordResetNotification($user, $token));

            Log::info('Password reset link sent', [
                'nik' => $user->nik,
                'email' => $user->email,
                'timestamp' => now()
            ]);

            return back()->with('status', 'Link reset password telah dikirim ke email Anda.');

        } catch (\Exception $e) {
            Log::error('Password reset link sending failed', [
                'nik' => $validated['nik'],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors(['nik' => 'Terjadi kesalahan saat mengirim email. Silakan coba lagi.']);
        }
    }

    /**
     * Show the password reset form
     */
    public function showResetForm(Request $request, $token)
    {
        $email = $request->get('email');
        
        // Verify token exists and is valid
        $resetRecord = $this->getValidResetRecord($token, $email);
        
        if (!$resetRecord) {
            return redirect()->route('login')->withErrors(['email' => 'Link reset password tidak valid atau sudah kadaluarsa.']);
        }

        return view('auth.passwords.reset', [
            'token' => $token,
            'email' => $email,
            'user' => $resetRecord->user
        ]);
    }

    /**
     * Reset the user password
     */
    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password_portal' => 'required|string|min:6',
            'password_portal_confirmation' => 'required|string|same:password_portal',
        ], [
            'password_portal.required' => 'Password portal wajib diisi.',
            'password_portal.min' => 'Password portal minimal 6 karakter.',
            'password_portal_confirmation.required' => 'Konfirmasi password wajib diisi.',
            'password_portal_confirmation.same' => 'Konfirmasi password tidak sesuai.',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                // Verify token
                $resetRecord = $this->getValidResetRecord($validated['token'], $validated['email']);
                
                if (!$resetRecord) {
                    throw new \Exception('Token reset password tidak valid atau sudah kadaluarsa.');
                }

                $user = $resetRecord->user;

                // Validate with portal password (if API available)
                if (!$this->validatePortalPassword($user->nik, $validated['password_portal'])) {
                    throw new \Exception('Password portal tidak valid.');
                }

                // Update user password
                $user->update([
                    'password' => Hash::make($validated['password_portal'])
                ]);

                // Delete used token
                $resetRecord->delete();

                // Delete any other tokens for this user
                PasswordResetToken::where('email', $validated['email'])->delete();

                Log::info('Password reset successful', [
                    'nik' => $user->nik,
                    'email' => $user->email,
                    'timestamp' => now()
                ]);
            });

            return redirect()->route('login')->with('status', 'Password berhasil direset. Silakan login dengan password baru Anda.');

        } catch (\Exception $e) {
            Log::error('Password reset failed', [
                'email' => $validated['email'],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withInput()->withErrors(['password_portal' => $e->getMessage()]);
        }
    }

    /**
     * Show change password form for authenticated users
     */
    public function showChangeForm()
    {
        $user = Auth::user();
        $karyawan = $user->karyawan;
        
        return view('auth.passwords.change', compact('user', 'karyawan'));
    }

    /**
     * Change password for authenticated users
     */
    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6',
            'new_password_confirmation' => 'required|string|same:new_password',
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'new_password.required' => 'Password baru wajib diisi.',
            'new_password.min' => 'Password baru minimal 6 karakter.',
            'new_password_confirmation.required' => 'Konfirmasi password baru wajib diisi.',
            'new_password_confirmation.same' => 'Konfirmasi password baru tidak sesuai.',
        ]);

        $user = Auth::user();

        try {
            DB::transaction(function () use ($user, $validated) {
                // Verify current password
                if (!Hash::check($validated['current_password'], $user->password)) {
                    throw new \Exception('Password saat ini tidak benar.');
                }

                // Validate new password with portal (if API available)
                if (!$this->validatePortalPassword($user->nik, $validated['new_password'])) {
                    throw new \Exception('Password baru harus sama dengan password portal Anda.');
                }

                // Update user password
                $user->update([
                    'password' => Hash::make($validated['new_password'])
                ]);

                Log::info('Password changed successfully', [
                    'nik' => $user->nik,
                    'user_id' => $user->id,
                    'timestamp' => now()
                ]);
            });

            return redirect()->route('dashboard')->with('success', 'Password berhasil diubah.');

        } catch (\Exception $e) {
            Log::error('Password change failed', [
                'nik' => $user->nik,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors(['current_password' => $e->getMessage()]);
        }
    }

    /**
     * Generate password reset token
     */
    private function generateResetToken(User $user): ?string
    {
        try {
            // Delete existing tokens for this user
            PasswordResetToken::where('email', $user->email)->delete();

            // Generate new token
            $token = Str::random(64);
            
            // Store token
            PasswordResetToken::create([
                'email' => $user->email,
                'token' => Hash::make($token),
                'created_at' => now()
            ]);

            return $token;

        } catch (\Exception $e) {
            Log::error('Token generation failed', [
                'email' => $user->email,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get valid reset record
     */
    private function getValidResetRecord(string $token, string $email): ?PasswordResetToken
    {
        $resetRecords = PasswordResetToken::where('email', $email)->get();
        
        foreach ($resetRecords as $record) {
            // Check if token matches
            if (Hash::check($token, $record->token)) {
                // Check if token is not expired (1 hour)
                if (Carbon::parse($record->created_at)->addHour()->isFuture()) {
                    return $record;
                }
            }
        }

        // Clean up expired tokens
        PasswordResetToken::where('email', $email)
            ->where('created_at', '<', now()->subHour())
            ->delete();

        return null;
    }

    /**
     * Validate portal password
     * This is a placeholder - replace with actual API call if available
     */
    private function validatePortalPassword(string $nik, string $password): bool
    {
        // TODO: Implement actual portal password validation via API
        // For now, we'll assume it's valid if password length is acceptable
        
        try {
            // Placeholder validation - replace with actual API call
            if (strlen($password) < 6) {
                return false;
            }

            // Log the validation attempt
            Log::info('Portal password validation', [
                'nik' => $nik,
                'password_length' => strlen($password),
                'timestamp' => now()
            ]);

            // In production, this should call the actual portal API
            // Example:
            // $response = Http::post('https://portal-api.telkom.co.id/validate', [
            //     'nik' => $nik,
            //     'password' => $password
            // ]);
            // return $response->successful() && $response->json('valid') === true;

            return true; // Temporary - always valid for development

        } catch (\Exception $e) {
            Log::error('Portal password validation failed', [
                'nik' => $nik,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Show password reset success page
     */
    public function showSuccessPage()
    {
        return view('auth.passwords.success');
    }

    /**
     * Clean up expired tokens (can be called by scheduler)
     */
    public function cleanupExpiredTokens()
    {
        $deleted = PasswordResetToken::where('created_at', '<', now()->subHour())->delete();
        
        Log::info('Cleaned up expired password reset tokens', [
            'deleted_count' => $deleted,
            'timestamp' => now()
        ]);

        return $deleted;
    }
}
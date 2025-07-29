 @extends('layouts.app')

@section('title', 'Atur Ulang Password - SEKAR')

@section('content')
<div class="min-h-screen flex">
    <!-- Left Side - Illustration -->
    <div class="hidden lg:flex lg:w-1/2 bg-white items-center justify-center p-8">
        <div class="max-w-lg w-full flex justify-center">
            <img src="{{ asset('asset/asset-image-index.png') }}" alt="Reset Password Illustration" class="w-full max-w-md">
        </div>
    </div>

    <!-- Right Side - Reset Form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-8 bg-white">
        <div class="max-w-md w-full">
            <div class="text-center mb-8">
                <div class="flex justify-center mb-8">
                    <img src="{{ asset('asset/logo.png') }}" alt="SEKAR Logo" class="h-12">
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Atur Ulang Password</h2>
                <p class="text-gray-600 text-sm">Masukkan password portal untuk mengonfirmasi identitas Anda</p>
            </div>

            <!-- User Info Card -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center mr-3">
                        <span class="text-white text-sm font-medium">{{ substr($user->name, 0, 1) }}</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-blue-900">{{ $user->name }}</p>
                        <p class="text-xs text-blue-700">NIK: {{ $user->nik }}</p>
                        <p class="text-xs text-blue-600">{{ $email }}</p>
                    </div>
                </div>
            </div>

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                    <div class="flex items-center mb-2">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="font-medium">Terjadi kesalahan:</span>
                    </div>
                    @foreach ($errors->all() as $error)
                        <p class="text-sm">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" class="space-y-6" id="resetForm">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                <div>
                    <label for="password_portal" class="block text-sm font-medium text-gray-700 mb-2">
                        Password Portal
                    </label>
                    <div class="relative">
                        <input
                            type="password"
                            name="password_portal"
                            id="password_portal"
                            placeholder="Masukkan password portal Anda"
                            class="w-full px-4 py-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition duration-200 text-gray-700 @error('password_portal') border-red-300 @enderror pr-12"
                            required
                            autofocus
                        >
                        <button type="button" onclick="togglePassword('password_portal')" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                            <svg id="eye-password_portal" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Password yang sama dengan Global Protect/Portal Telkom</p>
                </div>

                <div>
                    <label for="password_portal_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                        Konfirmasi Password Portal
                    </label>
                    <div class="relative">
                        <input
                            type="password"
                            name="password_portal_confirmation"
                            id="password_portal_confirmation"
                            placeholder="Ulangi password portal Anda"
                            class="w-full px-4 py-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition duration-200 text-gray-700 @error('password_portal_confirmation') border-red-300 @enderror pr-12"
                            required
                        >
                        <button type="button" onclick="togglePassword('password_portal_confirmation')" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                            <svg id="eye-password_portal_confirmation" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Password Match Indicator -->
                <div id="passwordMatch" class="hidden">
                    <div id="matchIndicator" class="flex items-center text-sm">
                        <svg id="matchIcon" class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span id="matchText">Password cocok</span>
                    </div>
                </div>

                <button
                    type="submit"
                    id="submitBtn"
                    class="w-full bg-blue-700 text-white py-4 rounded-lg font-medium hover:bg-blue-800 transition duration-200 text-lg disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    Reset Password
                </button>

                <div class="text-center space-y-3">
                    <a href="{{ route('login') }}" class="text-blue-600 hover:underline font-medium text-sm">
                        ← Kembali ke Login
                    </a>

                    <div class="text-xs text-gray-500">
                        <p>Link bermasalah? <a href="{{ route('password.request') }}" class="text-blue-600 hover:underline">Minta link baru</a></p>
                    </div>
                </div>
            </form>

            <!-- Security Notice -->
            <div class="mt-8 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.996-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    <div>
                        <h4 class="text-sm font-medium text-yellow-800 mb-1">Keamanan Akun</h4>
                        <ul class="text-xs text-yellow-700 space-y-1">
                            <li>• Password baru akan sama dengan password portal Anda</li>
                            <li>• Pastikan password portal Anda aman dan up-to-date</li>
                            <li>• Logout dari semua perangkat setelah reset</li>
                            <li>• Jangan bagikan password kepada siapa pun</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Token Expiry Warning -->
            <div class="mt-4 text-center">
                <p class="text-xs text-gray-500" id="expiryWarning">
                    Link ini akan kadaluarsa dalam <span id="countdown" class="font-medium text-red-600">--:--</span>
                </p>
            </div>
        </div>
    </div>
</div>

<style>
/* Focus states */
input:focus {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
}

/* Button hover effects */
button:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Error state styling */
input.border-red-300:focus {
    border-color: #ef4444;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.15);
}

/* Password strength indicator */
.password-match-success {
    color: #059669;
}

.password-match-error {
    color: #dc2626;
}

/* Countdown animation */
#countdown {
    animation: pulse 1s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password_portal');
    const confirmInput = document.getElementById('password_portal_confirmation');
    const matchIndicator = document.getElementById('passwordMatch');
    const matchIcon = document.getElementById('matchIcon');
    const matchText = document.getElementById('matchText');
    const submitBtn = document.getElementById('submitBtn');
    const form = document.getElementById('resetForm');

    // Password visibility toggle
    window.togglePassword = function(inputId) {
        const input = document.getElementById(inputId);
        const eye = document.getElementById('eye-' + inputId);

        if (input.type === 'password') {
            input.type = 'text';
            eye.innerHTML = `
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
            `;
        } else {
            input.type = 'password';
            eye.innerHTML = `
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
            `;
        }
    };

    // Password match validation
    function checkPasswordMatch() {
        const password = passwordInput.value;
        const confirm = confirmInput.value;

        if (confirm.length > 0) {
            matchIndicator.classList.remove('hidden');

            if (password === confirm) {
                matchIndicator.className = 'password-match-success';
                matchIcon.innerHTML = `
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                `;
                matchText.textContent = 'Password cocok';
                submitBtn.disabled = false;
            } else {
                matchIndicator.className = 'password-match-error';
                matchIcon.innerHTML = `
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                `;
                matchText.textContent = 'Password tidak cocok';
                submitBtn.disabled = true;
            }
        } else {
            matchIndicator.classList.add('hidden');
            submitBtn.disabled = false;
        }
    }

    passwordInput.addEventListener('input', checkPasswordMatch);
    confirmInput.addEventListener('input', checkPasswordMatch);

    // Form submission with loading state
    form.addEventListener('submit', function() {
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.innerHTML = `
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Memproses...
        `;

        // Reset button after 10 seconds as fallback
        setTimeout(() => {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }, 10000);
    });

    // Countdown timer (assuming 1 hour expiry)
    let timeLeft = 3600; // 1 hour in seconds
    const countdownEl = document.getElementById('countdown');

    function updateCountdown() {
        const hours = Math.floor(timeLeft / 3600);
        const minutes = Math.floor((timeLeft % 3600) / 60);
        const seconds = timeLeft % 60;

        if (timeLeft > 0) {
            countdownEl.textContent = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            timeLeft--;
        } else {
            countdownEl.textContent = 'EXPIRED';
            countdownEl.className = 'font-medium text-red-600 animate-pulse';
            document.getElementById('expiryWarning').innerHTML = '<span class="text-red-600 font-medium">⚠️ Link telah kadaluarsa. Silakan minta link reset baru.</span>';
            submitBtn.disabled = true;
            submitBtn.textContent = 'Link Kadaluarsa';
        }
    }

    updateCountdown();
    setInterval(updateCountdown, 1000);
});
</script>
@endsection
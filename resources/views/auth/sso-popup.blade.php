<!-- resources/views/auth/sso-popup.blade.php -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SSO Authentication - SEKAR</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-lg p-8 w-full max-w-md">
            <!-- Header -->
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-3a1 1 0 011-1h2.586l6.414-6.414a6 6 0 015.743-7.743z"></path>
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-gray-900">Autentikasi SSO</h2>
                <p class="text-sm text-gray-600 mt-2">Masukkan password SSO/LDAP Anda</p>
            </div>

           <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                        <span class="text-blue-600 font-bold text-lg">{{ strtoupper(substr($user_name, 0, 2)) }}</span>
                    </div>
                    <div class="flex-1">
                        <p class="font-medium text-blue-900">{{ $user_name }}</p>
                        <p class="text-sm text-blue-700">NIK: {{ $nik }}</p>
                        <p class="text-xs text-blue-600">Status: 
                            @if($membership_status === 'active')
                                <span class="text-green-600 font-medium">Anggota Aktif</span>
                            @elseif($membership_status === 'pending')
                                <span class="text-orange-600 font-medium">Pending (GPTP)</span>
                            @else
                                <span class="text-gray-600">{{ ucfirst($membership_status) }}</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- SSO Form -->
            <form id="ssoAuthForm" class="space-y-6">
                <input type="hidden" name="token" value="{{ $token }}">
                
                <!-- Error Display -->
                <div id="errorContainer" class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 3a1 1 0 00-1-1H3a1 1 0 00-1 1v12a1 1 0 001 1h12a1 1 0 001-1V3zM8.293 6.293a1 1 0 011.414 0L12 8.586l2.293-2.293a1 1 0 111.414 1.414L13.414 10l2.293 2.293a1 1 0 01-1.414 1.414L12 11.414l-2.293 2.293a1 1 0 01-1.414-1.414L10.586 10 8.293 7.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                        <span id="errorMessage" class="text-sm"></span>
                    </div>
                </div>

                <!-- Password Input -->
                <div>
                    <label for="sso_password" class="block text-sm font-medium text-gray-700 mb-2">
                        Password SSO/LDAP
                    </label>
                    <div class="relative">
                        <input 
                            type="password" 
                            id="sso_password"
                            name="sso_password" 
                            placeholder="Masukkan password SSO Anda"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition duration-200"
                            required
                            autocomplete="current-password"
                        >
                        <button 
                            type="button" 
                            id="togglePassword"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center"
                        >
                            <svg id="eyeIcon" class="w-5 h-5 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">
                        Password yang sama dengan Global Protect, email, atau sistem Telkom lainnya
                    </p>
                </div>

                <!-- Buttons -->
                <div class="flex space-x-3">
                    <button 
                        type="button"
                        id="cancelBtn"
                        class="flex-1 bg-gray-100 text-gray-700 py-3 rounded-lg font-medium hover:bg-gray-200 transition duration-200"
                    >
                        Batal
                    </button>
                    <button 
                        type="submit"
                        id="authBtn"
                        class="flex-1 bg-blue-600 text-white py-3 rounded-lg font-medium hover:bg-blue-700 transition duration-200"
                    >
                        Autentikasi
                    </button>
                </div>

                <!-- Loading State -->
                <div id="loadingState" class="hidden">
                    <div class="flex items-center justify-center py-3">
                        <svg class="animate-spin w-5 h-5 text-blue-600 mr-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-blue-600 font-medium">Memverifikasi kredensial...</span>
                    </div>
                </div>
            </form>

            <!-- Help Text -->
            <div class="mt-6 text-center">
                <details class="text-xs text-gray-600">
                    <summary class="cursor-pointer hover:text-gray-800 font-medium">
                        Butuh bantuan?
                    </summary>
                    <div class="mt-2 text-left bg-gray-50 p-3 rounded">
                        <p class="mb-2"><strong>Password SSO/LDAP adalah:</strong></p>
                        <ul class="list-disc list-inside space-y-1">
                            <li>Password Global Protect VPN</li>
                            <li>Password email Telkom (@telkom.co.id)</li>
                            <li>Password sistem internal Telkom lainnya</li>
                        </ul>
                    </div>
                </details>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('ssoAuthForm');
        const passwordInput = document.getElementById('sso_password');
        const togglePassword = document.getElementById('togglePassword');
        const eyeIcon = document.getElementById('eyeIcon');
        const authBtn = document.getElementById('authBtn');
        const cancelBtn = document.getElementById('cancelBtn');
        const loadingState = document.getElementById('loadingState');
        const errorContainer = document.getElementById('errorContainer');
        const errorMessage = document.getElementById('errorMessage');

        // Password toggle functionality
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            if (type === 'password') {
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                `;
            } else {
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
                `;
            }
        });

        function showError(message) {
            errorMessage.textContent = message;
            errorContainer.classList.remove('hidden');
        }

        function hideError() {
            errorContainer.classList.add('hidden');
        }

        function showLoading() {
            form.style.display = 'none';
            loadingState.classList.remove('hidden');
        }

        function hideLoading() {
            form.style.display = 'block';
            loadingState.classList.add('hidden');
        }

        // Cancel button
        cancelBtn.addEventListener('click', function() {
            window.close();
        });

        // Form submission
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const password = passwordInput.value.trim();
            if (!password) {
                showError('Password SSO tidak boleh kosong');
                return;
            }

            hideError();
            showLoading();

            try {
                const response = await fetch('{{ route("sso.auth") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        token: '{{ $token }}',
                        sso_password: password
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Notify parent window
                    window.opener.postMessage({
                        type: 'sso_success',
                        redirect_url: data.redirect_url
                    }, window.location.origin);
                    
                    window.close();
                } else {
                    hideLoading();
                    showError(data.message || 'Autentikasi gagal');
                }

            } catch (error) {
                hideLoading();
                showError('Terjadi kesalahan koneksi. Silakan coba lagi.');
                console.error('SSO Auth error:', error);
            }
        });

        // Auto-focus password input
        passwordInput.focus();

        // Handle Enter key
        passwordInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                form.dispatchEvent(new Event('submit'));
            }
        });

        // Handle window close
        window.addEventListener('beforeunload', function() {
            window.opener.postMessage({
                type: 'sso_cancelled'
            }, window.location.origin);
        });
    });
    </script>
</body>
</html>
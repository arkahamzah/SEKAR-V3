<!-- resources/views/auth/login.blade.php -->
@extends('layouts.app')

@section('title', 'Login SSO - SEKAR')

@section('content')
<div class="min-h-screen flex">
    <!-- Left Side - Illustration -->
    <div class="hidden lg:flex lg:w-1/2 bg-white items-center justify-center p-8">
        <div class="max-w-lg w-full flex justify-center">
            <img src="{{ asset('asset/asset-image-index.png') }}" alt="Login Illustration" class="w-full max-w-md">
        </div>
    </div>

    <!-- Right Side - SSO Login Form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-8 bg-white">
        <div class="max-w-md w-full">
            <div class="text-center mb-8">
                <div class="flex justify-center mb-6">
                    <img src="{{ asset('asset/logo.png') }}" alt="SEKAR Logo" class="h-12">
                </div>
                
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Single Sign-On</h1>
                <p class="text-gray-600">Masuk dengan akun Telkom Anda</p>
            </div>

            <!-- Success Message -->
            @if(session('status'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        {{ session('status') }}
                    </div>
                </div>
            @endif

            <!-- SSO Login Form -->
            <form id="ssoLoginForm" class="space-y-6">
                @csrf
                
                <!-- Error Display -->
                <div id="errorContainer" class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    <p id="errorMessage" class="text-sm"></p>
                </div>

                <!-- NIK Input -->
                <div>
                    <label for="nik" class="block text-sm font-medium text-gray-700 mb-2">
                        NIK Telkom
                    </label>
                    <input 
                        type="text" 
                        id="nik"
                        name="nik" 
                        placeholder="Masukkan NIK Anda" 
                        value="{{ old('nik') }}"
                        class="w-full px-4 py-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition duration-200 text-gray-700 text-lg"
                        required
                        autocomplete="username"
                    >
                    <p class="text-xs text-gray-500 mt-2">NIK akan divalidasi dengan sistem Telkom</p>
                </div>

                <!-- SSO Login Button -->
                <button 
                    type="submit"
                    id="ssoLoginBtn"
                    class="w-full bg-blue-600 text-white py-4 rounded-lg font-medium hover:bg-blue-700 transition duration-200 text-lg flex items-center justify-center"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-3a1 1 0 011-1h2.586l6.414-6.414a6 6 0 015.743-7.743z"></path>
                    </svg>
                    Login dengan SSO
                </button>

                <!-- Loading State -->
                <div id="loadingState" class="hidden w-full bg-gray-100 text-gray-600 py-4 rounded-lg font-medium text-lg flex items-center justify-center">
                    <svg class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Memproses SSO...
                </div>
            </form>

            <!-- Divider -->
            <div class="mt-8 mb-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white text-gray-500">atau</span>
                    </div>
                </div>
            </div>

            <!-- Manual Registration Link -->
            <div class="text-center space-y-3">
                <a href="{{ route('register') }}" class="block w-full bg-gray-100 text-gray-700 py-3 rounded-lg font-medium hover:bg-gray-200 transition duration-200">
                    Daftar Manual (untuk pengujian)
                </a>
                
                <div class="text-sm text-gray-600">
                    <p>Belum terdaftar sebagai karyawan?</p>
                    <p>Hubungi administrator untuk bantuan</p>
                </div>
            </div>

            <!-- SSO Flow Information -->
            <div class="mt-8 text-center border-t border-gray-200 pt-6">
                <h4 class="text-sm font-medium text-gray-700 mb-3">Alur Login SSO:</h4>
                <div class="text-xs text-gray-600 space-y-2">
                    <div class="flex items-center text-left">
                        <span class="flex-shrink-0 w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs font-bold mr-3">1</span>
                        <span>Masukkan NIK Telkom Anda</span>
                    </div>
                    <div class="flex items-center text-left">
                        <span class="flex-shrink-0 w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs font-bold mr-3">2</span>
                        <span>Popup SSO akan muncul</span>
                    </div>
                    <div class="flex items-center text-left">
                        <span class="flex-shrink-0 w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs font-bold mr-3">3</span>
                        <span>Masukkan password SSO/LDAP pada popup</span>
                    </div>
                    <div class="flex items-center text-left">
                        <span class="flex-shrink-0 w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs font-bold mr-3">4</span>
                        <span>Otomatis login ke dashboard</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('ssoLoginForm');
    const nikInput = document.getElementById('nik');
    const loginBtn = document.getElementById('ssoLoginBtn');
    const loadingState = document.getElementById('loadingState');
    const errorContainer = document.getElementById('errorContainer');
    const errorMessage = document.getElementById('errorMessage');

    function showError(message) {
        errorMessage.textContent = message;
        errorContainer.classList.remove('hidden');
    }

    function hideError() {
        errorContainer.classList.add('hidden');
    }

    function showLoading() {
        loginBtn.classList.add('hidden');
        loadingState.classList.remove('hidden');
    }

    function hideLoading() {
        loginBtn.classList.remove('hidden');
        loadingState.classList.add('hidden');
    }

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const nik = nikInput.value.trim();
        if (!nik) {
            showError('NIK tidak boleh kosong');
            return;
        }

        hideError();
        showLoading();

        try {
            const response = await fetch('{{ route("login.post") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: JSON.stringify({ nik: nik })
            });

            const data = await response.json();

            if (data.success && data.redirect_to_popup) {
                // Open SSO popup
                const popup = window.open(
                    data.popup_url,
                    'sso_auth',
                    'width=500,height=600,scrollbars=yes,resizable=yes,location=no,menubar=no,toolbar=no'
                );

                // Listen for popup completion
                const checkClosed = setInterval(function() {
                    if (popup.closed) {
                        clearInterval(checkClosed);
                        hideLoading();
                        // Check if authentication was successful
                        window.location.reload();
                    }
                }, 1000);

                // Listen for postMessage from popup
                window.addEventListener('message', function(event) {
                    if (event.origin !== window.location.origin) return;
                    
                    if (event.data.type === 'sso_success') {
                        popup.close();
                        window.location.href = event.data.redirect_url;
                    } else if (event.data.type === 'sso_error') {
                        popup.close();
                        hideLoading();
                        showError(event.data.message);
                    }
                });

            } else {
                hideLoading();
                showError(data.message || 'Terjadi kesalahan saat memproses login');
            }

        } catch (error) {
            hideLoading();
            showError('Terjadi kesalahan koneksi. Silakan coba lagi.');
            console.error('Login error:', error);
        }
    });

    // Auto-focus NIK input
    nikInput.focus();
});
</script>
@endsection
@extends('layouts.app')

@section('title', 'Daftar SEKAR - Single Sign-On')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
    <!-- Center - Register Form -->
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-6">
        <div class="text-center mb-6">
            <div class="flex justify-center mb-4">
                <img src="{{ asset('asset/logo.png') }}" alt="SEKAR Logo" class="h-12">
            </div>
            <h2 class="text-xl font-bold text-gray-900">Daftar SEKAR</h2>
            <p class="text-sm text-gray-600 mt-1">Single Sign-On Registration</p>
        </div>

        <!-- Form -->
        <form id="registerForm" class="space-y-4">
            @csrf
            
            <!-- Error/Success Messages -->
            <div id="alertContainer" class="hidden"></div>
            
            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-3 py-2 rounded-lg">
                    @foreach ($errors->all() as $error)
                        <p class="text-xs">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <!-- NIK Input -->
            <div>
                <label class="block text-gray-700 text-sm font-medium mb-1">
                    NIK Telkom <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="nikInput"
                    name="nik" 
                    placeholder="Masukkan NIK Anda" 
                    value="{{ old('nik') }}"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition duration-200 text-sm"
                    required
                >
                <p class="text-xs text-gray-500 mt-1">NIK akan divalidasi dengan data karyawan Telkom</p>
            </div>

            <!-- Nama Input (Auto-filled) -->
            <div>
                <label class="block text-gray-700 text-sm font-medium mb-1">
                    Nama Lengkap <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="nameInput"
                    name="name" 
                    placeholder="Nama akan terisi otomatis" 
                    value="{{ old('name') }}"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-sm"
                    readonly
                    required
                >
                <p class="text-xs text-gray-500 mt-1">Nama diambil dari data karyawan</p>
            </div>

            <!-- Employee Info Display -->
            <div id="employeeInfo" class="hidden bg-blue-50 border border-blue-200 rounded-lg p-3">
                <h4 class="font-medium text-blue-900 text-sm mb-2">Informasi Karyawan:</h4>
                <div class="space-y-1 text-xs text-blue-800">
                    <p><span class="font-medium">Posisi:</span> <span id="employeePosition"></span></p>
                    <p><span class="font-medium">Unit:</span> <span id="employeeUnit"></span></p>
                    <p><span class="font-medium">Divisi:</span> <span id="employeeDivisi"></span></p>
                    <p><span class="font-medium">Lokasi:</span> <span id="employeeLocation"></span></p>
                </div>
            </div>

            <!-- GPTP Notice -->
            <div id="gptpNotice" class="hidden bg-orange-50 border border-orange-200 rounded-lg p-3">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-orange-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <div class="text-sm">
                        <p class="font-medium text-orange-800">Karyawan GPTP - Pre-order Membership</p>
                        <p class="text-orange-700 mt-1">Sebagai karyawan GPTP, Anda akan resmi menjadi anggota SEKAR <strong>1 tahun</strong> setelah mendaftar.</p>
                    </div>
                </div>
            </div>

            <!-- Iuran Sukarela -->
            <div>
                <label class="block text-gray-700 text-sm font-medium mb-1">
                    Iuran Sukarela (Opsional)
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-2.5 text-gray-500 text-sm">Rp</span>
                    <input 
                        type="number" 
                        id="iuranInput"
                        name="iuran_sukarela" 
                        placeholder="0" 
                        value="{{ old('iuran_sukarela') }}"
                        min="0"
                        step="5000"
                        class="w-full pl-8 pr-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition duration-200 text-sm"
                    >
                </div>
                <p class="text-xs text-gray-500 mt-1">Dalam kelipatan Rp 5.000 (contoh: 10000, 15000, 20000)</p>
            </div>

            <!-- Loading State -->
            <div id="loadingState" class="hidden text-center py-2">
                <svg class="animate-spin w-5 h-5 text-blue-600 mx-auto" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="text-sm text-gray-600 mt-1">Memvalidasi data karyawan...</p>
            </div>

            <!-- Register Button -->
            <button 
                type="button"
                id="registerBtn"
                class="w-full bg-blue-600 text-white py-2.5 rounded-lg font-medium hover:bg-blue-700 transition duration-200 text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                disabled
            >
                Daftar dengan SSO
            </button>

            <!-- Login Link -->
            <div class="text-center">
                <span class="text-gray-600 text-xs">Sudah menjadi anggota? </span>
                <a href="{{ route('login') }}" class="text-blue-600 hover:underline font-medium text-xs">Login dengan SSO</a>
            </div>
        </form>
    </div>
</div>

<!-- SSO Popup Modal -->
<div id="ssoModal" class="fixed inset-0 bg-black bg-opacity-30 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-xl p-8 max-w-md w-full mx-4 shadow-2xl">
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-3a1 1 0 011-1h2.586l6.414-6.414a6 6 0 015.743-7.743z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-1">Konfirmasi dengan SSO</h3>
            <p class="text-sm text-gray-600">Masukkan password SSO/LDAP untuk melanjutkan pendaftaran</p>
        </div>

        <!-- User Info -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                    <span id="userInitials" class="text-blue-600 font-bold text-sm"></span>
                </div>
                <div>
                    <p id="modalUserName" class="font-medium text-blue-900"></p>
                    <p id="modalUserNIK" class="text-sm text-blue-700"></p>
                </div>
            </div>
        </div>

        <form id="ssoForm" class="space-y-6">
            @csrf
            
            <!-- Error Display -->
            <div id="ssoErrorContainer" class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 3a1 1 0 00-1-1H3a1 1 0 00-1 1v12a1 1 0 001 1h12a1 1 0 001-1V3zM8.293 6.293a1 1 0 011.414 0L12 8.586l2.293-2.293a1 1 0 111.414 1.414L13.414 10l2.293 2.293a1 1 0 01-1.414 1.414L12 11.414l-2.293 2.293a1 1 0 01-1.414-1.414L10.586 10 8.293 7.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                    <span id="ssoErrorMessage" class="text-sm"></span>
                </div>
            </div>

            <!-- Hidden inputs for form data -->
            <input type="hidden" id="hiddenNik" name="nik">
            <input type="hidden" id="hiddenName" name="name">
            <input type="hidden" id="hiddenIuran" name="iuran_sukarela">

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
                        id="toggleSSOPassword"
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
                    id="cancelSSOBtn"
                    class="flex-1 bg-gray-100 text-gray-700 py-3 rounded-lg font-medium hover:bg-gray-200 transition duration-200"
                >
                    Batal
                </button>
                <button 
                    type="submit"
                    id="submitSSOBtn"
                    class="flex-1 bg-blue-600 text-white py-3 rounded-lg font-medium hover:bg-blue-700 transition duration-200"
                >
                    Daftar
                </button>
            </div>

            <!-- SSO Loading State -->
            <div id="ssoLoadingState" class="hidden">
                <div class="flex items-center justify-center py-3">
                    <svg class="animate-spin w-5 h-5 text-blue-600 mr-2" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-blue-600 font-medium">Memproses registrasi...</span>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const nikInput = document.getElementById('nikInput');
    const nameInput = document.getElementById('nameInput');
    const iuranInput = document.getElementById('iuranInput');
    const registerBtn = document.getElementById('registerBtn');
    const loadingState = document.getElementById('loadingState');
    const employeeInfo = document.getElementById('employeeInfo');
    const gptpNotice = document.getElementById('gptpNotice');
    const ssoModal = document.getElementById('ssoModal');
    const ssoForm = document.getElementById('ssoForm');
    const ssoPassword = document.getElementById('sso_password');
    const toggleSSOPassword = document.getElementById('toggleSSOPassword');
    const eyeIcon = document.getElementById('eyeIcon');
    
    let currentKaryawanData = null;
    let debounceTimer = null;

    // Show alert message
    function showAlert(message, type = 'info') {
        const alertContainer = document.getElementById('alertContainer');
        const alertClass = {
            'success': 'bg-green-50 border-green-200 text-green-700',
            'error': 'bg-red-50 border-red-200 text-red-700',
            'warning': 'bg-orange-50 border-orange-200 text-orange-700',
            'info': 'bg-blue-50 border-blue-200 text-blue-700'
        };
        
        alertContainer.className = `border px-4 py-3 rounded-lg text-sm ${alertClass[type]}`;
        alertContainer.textContent = message;
        alertContainer.classList.remove('hidden');
        
        setTimeout(() => {
            alertContainer.classList.add('hidden');
        }, 5000);
    }

    // NIK input handler with debounce
    nikInput.addEventListener('input', function(e) {
        const nik = e.target.value.trim();
        
        // Reset form state
        nameInput.value = '';
        registerBtn.disabled = true;
        employeeInfo.classList.add('hidden');
        gptpNotice.classList.add('hidden');
        currentKaryawanData = null;
        
        // Clear previous timer
        clearTimeout(debounceTimer);
        
        if (nik.length >= 6) {
            // Debounce API call
            debounceTimer = setTimeout(() => {
                fetchKaryawanData(nik);
            }, 800);
        }
    });

    // Fetch karyawan data
    async function fetchKaryawanData(nik) {
        loadingState.classList.remove('hidden');
        
        try {
            const response = await fetch('/api/karyawan-data', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: JSON.stringify({ nik: nik })
            });
            
            const data = await response.json();
            
            if (data.success) {
                currentKaryawanData = data.data;
                
                // Fill form data
                nameInput.value = data.data.name;
                
                // Show employee info
                document.getElementById('employeePosition').textContent = data.data.position;
                document.getElementById('employeeUnit').textContent = data.data.unit;
                document.getElementById('employeeDivisi').textContent = data.data.divisi;
                document.getElementById('employeeLocation').textContent = data.data.location;
                employeeInfo.classList.remove('hidden');
                
                // Show GPTP notice if applicable
                if (data.data.is_gptp) {
                    gptpNotice.classList.remove('hidden');
                }
                
                // Enable register button
                registerBtn.disabled = false;
                
                showAlert('Data karyawan ditemukan!', 'success');
            } else {
                showAlert(data.message, 'error');
            }
        } catch (error) {
            showAlert('Terjadi kesalahan saat memuat data karyawan', 'error');
            console.error('Error:', error);
        } finally {
            loadingState.classList.add('hidden');
        }
    }

    // Iuran input formatting
    iuranInput.addEventListener('input', function(e) {
        let value = parseInt(e.target.value) || 0;
        if (value > 0) {
            // Round to nearest 5000
            value = Math.round(value / 5000) * 5000;
            e.target.value = value;
        }
    });

    // Register button click
    registerBtn.addEventListener('click', function() {
        if (!currentKaryawanData) {
            showAlert('Silakan masukkan NIK yang valid terlebih dahulu', 'warning');
            return;
        }

        // Validate iuran
        const iuranValue = parseInt(iuranInput.value) || 0;
        if (iuranValue > 0 && iuranValue % 5000 !== 0) {
            showAlert('Iuran sukarela harus dalam kelipatan Rp 5.000', 'warning');
            return;
        }

        // Show SSO modal
        showSSOModal();
    });

    // Show SSO Modal
    function showSSOModal() {
        if (!currentKaryawanData) return;
        
        // Fill modal data
        document.getElementById('userInitials').textContent = currentKaryawanData.name.substring(0, 2).toUpperCase();
        document.getElementById('modalUserName').textContent = currentKaryawanData.name;
        document.getElementById('modalUserNIK').textContent = 'NIK: ' + currentKaryawanData.nik;
        
        // Fill hidden inputs
        document.getElementById('hiddenNik').value = currentKaryawanData.nik;
        document.getElementById('hiddenName').value = currentKaryawanData.name;
        document.getElementById('hiddenIuran').value = iuranInput.value || '0';
        
        // Show modal
        ssoModal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        // Focus password input
        setTimeout(() => {
            ssoPassword.focus();
        }, 100);
    }

    // Hide SSO Modal
    function hideSSOModal() {
        ssoModal.classList.add('hidden');
        document.body.style.overflow = 'auto';
        ssoForm.reset();
        document.getElementById('ssoErrorContainer').classList.add('hidden');
    }

    // Cancel SSO
    document.getElementById('cancelSSOBtn').addEventListener('click', hideSSOModal);

    // Close modal on outside click
    ssoModal.addEventListener('click', function(e) {
        if (e.target === ssoModal) {
            hideSSOModal();
        }
    });

    // Toggle password visibility
    toggleSSOPassword.addEventListener('click', function() {
        const type = ssoPassword.getAttribute('type') === 'password' ? 'text' : 'password';
        ssoPassword.setAttribute('type', type);
        
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

    // SSO Form submission
    ssoForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(ssoForm);
        const submitBtn = document.getElementById('submitSSOBtn');
        const loadingState = document.getElementById('ssoLoadingState');
        
        // Show loading
        ssoForm.style.display = 'none';
        loadingState.classList.remove('hidden');
        
        try {
            const response = await fetch('{{ route("register.post") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json',
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(formData)
            });
            
            const data = await response.json();
            
            if (response.ok && data.success !== false) {
                // Registration successful - redirect
                window.location.href = '{{ route("dashboard") }}';
            } else {
                // Show error in popup
                showSSOError(data.message || 'Password SSO/LDAP tidak valid. Silakan periksa kembali.');
                
                // Hide loading, show form
                loadingState.classList.add('hidden');
                ssoForm.style.display = 'block';
                
                // Focus back to password input
                ssoPassword.focus();
                ssoPassword.select();
            }
        } catch (error) {
            console.error('Registration error:', error);
            showSSOError('Terjadi kesalahan koneksi. Silakan coba lagi.');
            
            // Hide loading, show form
            loadingState.classList.add('hidden');
            ssoForm.style.display = 'block';
        }
    });

    // Show SSO error
    function showSSOError(message) {
        const errorContainer = document.getElementById('ssoErrorContainer');
        const errorMessage = document.getElementById('ssoErrorMessage');
        
        errorMessage.textContent = message;
        errorContainer.classList.remove('hidden');
    }

    // ESC key to close modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !ssoModal.classList.contains('hidden')) {
            hideSSOModal();
        }
    });
});
</script>
@endsection
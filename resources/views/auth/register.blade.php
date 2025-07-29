<!-- resources/views/auth/register.blade.php -->
@extends('layouts.app')

@section('title', 'Daftar Sekar - SEKAR')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
    <!-- Left Side - Logo -->
    <div class="absolute top-6 left-6">
        <img src="{{ asset('asset/logo.png') }}" alt="SEKAR Logo" class="h-10">
    </div>

    <!-- Center - Register Form -->
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-6">
        <div class="text-center mb-6">
            <h2 class="text-xl font-bold text-gray-900">Daftar Sekar</h2>
        </div>

        <form id="registerForm" method="POST" action="{{ route('register.post') }}" class="space-y-4">
            @csrf
            
            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-3 py-2 rounded-lg">
                    @foreach ($errors->all() as $error)
                        <p class="text-xs">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div>
                <label class="block text-gray-700 text-sm font-medium mb-1">NIK</label>
                <input 
                    type="text" 
                    name="nik" 
                    placeholder="NIK" 
                    value="{{ old('nik') }}"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition duration-200 bg-gray-50 text-sm"
                    required
                >
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-medium mb-1">Nama</label>
                <input 
                    type="text" 
                    name="name" 
                    placeholder="Nama" 
                    value="{{ old('name') }}"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition duration-200 bg-gray-50 text-sm"
                    required
                >
            </div>

            <!-- Email Input -->
            <div>
                <label class="block text-gray-700 text-sm font-medium mb-1">
                    Email Pribadi <span class="text-red-500">*</span>
                </label>
                <input 
                    type="email" 
                    name="email" 
                    placeholder="email.pribadi@gmail.com" 
                    value="{{ old('email') }}"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition duration-200 text-sm"
                    required
                >
                <p class="text-xs text-gray-500 mt-0.5">Email untuk notifikasi dan reset password</p>
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-medium mb-1">
                    Iuran Sukarela <span class="text-gray-500">(Opsional)</span>
                </label>
                <input 
                    type="text" 
                    name="iuran_sukarela" 
                    id="iuranInput"
                    placeholder="0" 
                    value="{{ old('iuran_sukarela') }}"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition duration-200 bg-gray-50 text-sm"
                >
                <p class="text-xs text-gray-500 mt-0.5">Minimal kelipatan Rp 5.000</p>
            </div>

            <div class="flex items-start space-x-2">
                <input 
                    type="checkbox" 
                    id="agreement" 
                    name="agreement"
                    class="mt-0.5 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 flex-shrink-0"
                    required
                >
                <label for="agreement" class="text-xs text-gray-700 leading-tight">
                    Saya bersedia menjadi anggota SEKAR TELKOM, telah memahami hak dan kewajiban anggota, serta menyetujui pemotongan iuran Rp25.000/bulan melalui payroll.
                </label>
            </div>

            <div class="flex items-start space-x-2">
                <input 
                    type="checkbox" 
                    id="email_agreement" 
                    name="email_agreement"
                    class="mt-0.5 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 flex-shrink-0"
                    required
                >
                <label for="email_agreement" class="text-xs text-gray-700 leading-tight">
                    Saya menyetujui penggunaan email pribadi untuk notifikasi sistem SEKAR dan konfirmasi reset password.
                </label>
            </div>

            <button 
                type="button"
                id="daftarBtn"
                class="w-full bg-blue-700 text-white py-2.5 rounded-lg font-medium hover:bg-blue-800 transition duration-200 text-sm"
            >
                Daftar
            </button>

            <div class="text-center">
                <span class="text-gray-600 text-xs">Sudah menjadi anggota? </span>
                <a href="{{ route('login') }}" class="text-blue-600 hover:underline font-medium text-xs">Login dengan NIK</a>
            </div>
        </form>
    </div>
</div>

<!-- Popup Modal for Password Validation with Blur Effect -->
<div id="passwordModal" class="fixed inset-0 bg-black bg-opacity-30 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-xl p-8 max-w-md w-full mx-4 shadow-2xl">
        <div class="text-center mb-6">
            <div class="flex justify-center mb-4">
                <img src="{{ asset('asset/logo.png') }}" alt="SEKAR Logo" class="h-12">
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-1">Validasi dengan Password Portal</h3>
            <p class="text-sm text-gray-600">Konfirmasi identitas dengan password Global Protect</p>
        </div>

        <form id="passwordForm" class="space-y-6">
            <div>
                <input 
                    type="password" 
                    id="password_portal" 
                    name="password_portal" 
                    placeholder="Password Portal/Global Protect"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition duration-200"
                    required
                >
                <p class="text-xs text-gray-500 mt-1">Password yang sama dengan akses Global Protect Telkom</p>
            </div>

            <div class="flex space-x-3">
                <button 
                    type="button"
                    id="cancelBtn"
                    class="flex-1 bg-gray-200 text-gray-700 py-3 rounded-lg font-medium hover:bg-gray-300 transition duration-200"
                >
                    Batal
                </button>
                <button 
                    type="submit"
                    class="flex-1 bg-blue-700 text-white py-3 rounded-lg font-medium hover:bg-blue-800 transition duration-200"
                >
                    Daftar
                </button>
            </div>
        </form>
    </div>
</div>

<style>
/* Custom blur effect for modal */
.backdrop-blur-sm {
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
}

/* Smooth transitions */
.transition-all {
    transition: all 0.3s ease;
}

/* Focus states */
input:focus {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
}

/* Button hover effects */
button:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Modal animation */
#passwordModal.show {
    animation: modalFadeIn 0.3s ease;
}

@keyframes modalFadeIn {
    from {
        opacity: 0;
        transform: scale(0.9);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const daftarBtn = document.getElementById('daftarBtn');
    const passwordModal = document.getElementById('passwordModal');
    const cancelBtn = document.getElementById('cancelBtn');
    const passwordForm = document.getElementById('passwordForm');
    const registerForm = document.getElementById('registerForm');
    const iuranInput = document.getElementById('iuranInput');

    // Format iuran input as number
    iuranInput.addEventListener('input', function(e) {
        let value = e.target.value;
        // Remove all non-numeric characters
        value = value.replace(/[^0-9]/g, '');
        
        // Convert to number and format
        if (value) {
            const number = parseInt(value);
            e.target.value = number.toString();
        } else {
            e.target.value = '';
        }
    });

    // Validate iuran sukarela on blur
    iuranInput.addEventListener('blur', function(e) {
        let value = parseInt(e.target.value) || 0;
        
        if (value > 0 && value % 5000 !== 0) {
            // Round to nearest 5000
            value = Math.round(value / 5000) * 5000;
            e.target.value = value.toString();
            
            // Show warning
            showTemporaryMessage('Iuran sukarela dibulatkan ke kelipatan Rp 5.000', 'warning');
        }
    });

    // Show temporary message
    function showTemporaryMessage(message, type = 'info') {
        const existingAlert = document.querySelector('.temp-alert');
        if (existingAlert) {
            existingAlert.remove();
        }

        const alertDiv = document.createElement('div');
        alertDiv.className = `temp-alert bg-${type === 'warning' ? 'yellow' : 'blue'}-50 border border-${type === 'warning' ? 'yellow' : 'blue'}-200 text-${type === 'warning' ? 'yellow' : 'blue'}-700 px-3 py-2 rounded-lg text-xs mb-4`;
        alertDiv.textContent = message;
        
        const form = document.getElementById('registerForm');
        form.insertBefore(alertDiv, form.firstChild);
        
        setTimeout(() => {
            alertDiv.remove();
        }, 3000);
    }

    // Email validation
    const emailInput = document.querySelector('input[name="email"]');
    emailInput.addEventListener('blur', function(e) {
        const email = e.target.value;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (email && !emailRegex.test(email)) {
            showTemporaryMessage('Format email tidak valid', 'warning');
            e.target.focus();
        }
    });

    // Show modal when Daftar button is clicked
    daftarBtn.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Validate required fields first
        const nik = document.querySelector('input[name="nik"]').value;
        const name = document.querySelector('input[name="name"]').value;
        const email = document.querySelector('input[name="email"]').value;
        const agreement = document.querySelector('input[name="agreement"]').checked;
        const emailAgreement = document.querySelector('input[name="email_agreement"]').checked;
        
        if (!nik || !name || !email || !agreement || !emailAgreement) {
            showTemporaryMessage('Silakan lengkapi semua field yang wajib diisi', 'warning');
            return;
        }
        
        // Validate email format
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            showTemporaryMessage('Format email tidak valid', 'warning');
            return;
        }
        
        // Validate iuran sukarela if provided
        const iuranValue = parseInt(iuranInput.value) || 0;
        if (iuranValue > 0 && iuranValue % 5000 !== 0) {
            showTemporaryMessage('Iuran sukarela harus dalam kelipatan Rp 5.000', 'warning');
            return;
        }
        
        passwordModal.classList.remove('hidden');
        passwordModal.classList.add('show');
        document.body.style.overflow = 'hidden'; // Prevent scroll
    });

    // Hide modal when Cancel button is clicked
    cancelBtn.addEventListener('click', function() {
        hideModal();
    });

    // Hide modal when clicking outside
    passwordModal.addEventListener('click', function(e) {
        if (e.target === passwordModal) {
            hideModal();
        }
    });

    // Hide modal function
    function hideModal() {
        passwordModal.classList.add('hidden');
        passwordModal.classList.remove('show');
        document.body.style.overflow = 'auto'; // Restore scroll
        document.getElementById('password_portal').value = '';
    }

    // Handle password form submission
    passwordForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const passwordPortal = document.getElementById('password_portal').value;
        
        if (!passwordPortal) {
            alert('Silakan masukkan Password Portal');
            return;
        }
        
        // Add password to main form and submit
        const hiddenPasswordInput = document.createElement('input');
        hiddenPasswordInput.type = 'hidden';
        hiddenPasswordInput.name = 'password';
        hiddenPasswordInput.value = passwordPortal;
        
        const hiddenPasswordConfirmInput = document.createElement('input');
        hiddenPasswordConfirmInput.type = 'hidden';
        hiddenPasswordConfirmInput.name = 'password_confirmation';
        hiddenPasswordConfirmInput.value = passwordPortal;
        
        registerForm.appendChild(hiddenPasswordInput);
        registerForm.appendChild(hiddenPasswordConfirmInput);
        
        registerForm.submit();
    });

    // ESC key to close modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !passwordModal.classList.contains('hidden')) {
            hideModal();
        }
    });
});
</script>

@endsection
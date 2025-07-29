 
@extends('layouts.app')

@section('title', 'Reset Password - SEKAR')

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
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Lupa Password?</h2>
                <p class="text-gray-600 text-sm">Masukkan NIK Anda untuk menerima link reset password</p>
            </div>

            @if (session('status'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        {{ session('status') }}
                    </div>
                </div>
            @endif

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

            <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                @csrf
                
                <div>
                    <label for="nik" class="block text-sm font-medium text-gray-700 mb-2">NIK</label>
                    <input 
                        type="text" 
                        name="nik" 
                        id="nik"
                        placeholder="Masukkan NIK Anda" 
                        value="{{ old('nik') }}"
                        class="w-full px-4 py-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition duration-200 text-gray-700 @error('nik') border-red-300 @enderror"
                        required
                        autofocus
                    >
                </div>

                <button 
                    type="submit"
                    class="w-full bg-blue-700 text-white py-4 rounded-lg font-medium hover:bg-blue-800 transition duration-200 text-lg"
                >
                    Kirim Link Reset Password
                </button>

                <div class="text-center space-y-3">
                    <a href="{{ route('login') }}" class="text-blue-600 hover:underline font-medium text-sm">
                        ← Kembali ke Login
                    </a>
                    
                    <div class="text-xs text-gray-500">
                        <p>Belum punya akun? <a href="{{ route('register') }}" class="text-blue-600 hover:underline">Daftar SEKAR</a></p>
                    </div>
                </div>
            </form>

            <!-- Information Card -->
            <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <h4 class="text-sm font-medium text-blue-900 mb-1">Informasi Reset Password</h4>
                        <ul class="text-xs text-blue-700 space-y-1">
                            <li>• Link reset akan dikirim ke email terdaftar</li>
                            <li>• Link berlaku selama 1 jam</li>
                            <li>• Anda perlu password portal untuk konfirmasi</li>
                            <li>• Hubungi admin jika tidak menerima email</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Help Section -->
            <div class="mt-6 text-center">
                <p class="text-xs text-gray-500 mb-2">Butuh bantuan?</p>
                <div class="text-xs text-gray-600 space-y-1">
                    <p>📧 admin@sekar.telkom.co.id</p>
                    <p>📞 0800-1-SEKAR (73527)</p>
                </div>
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
button:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Error state styling */
input.border-red-300:focus {
    border-color: #ef4444;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.15);
}

/* Link hover effects */
a:hover {
    transform: translateY(-1px);
    transition: all 0.2s ease;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const nikInput = document.getElementById('nik');
    
    // Auto focus on NIK input
    if (nikInput) {
        nikInput.focus();
    }
    
    // Format NIK input (numbers only)
    nikInput.addEventListener('input', function(e) {
        let value = e.target.value;
        // Remove all non-numeric characters
        value = value.replace(/[^0-9]/g, '');
        e.target.value = value;
    });
    
    // Show loading state on form submit
    const form = document.querySelector('form');
    const submitBtn = document.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    
    form.addEventListener('submit', function() {
        submitBtn.disabled = true;
        submitBtn.innerHTML = `
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Mengirim Link...
        `;
        
        // Reset button after 10 seconds as fallback
        setTimeout(() => {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }, 10000);
    });
});
</script>
@endsection
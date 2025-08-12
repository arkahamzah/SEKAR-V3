@extends('layouts.app')

@section('title', 'Login - SEKAR')

@section('content')
{{-- Wadah utama dibuat fixed setinggi layar (h-screen) dan mencegah overflow --}}
<div class="h-screen flex overflow-hidden bg-white">
    
    {{-- Bagian Kiri - Ilustrasi --}}
    <div class="hidden lg:flex lg:w-1/2 items-center justify-center p-12">
        <div class="max-w-lg w-full flex justify-center">
            <img src="{{ asset('asset/asset-image-index.png') }}" alt="Login Illustration" class="w-full max-w-md">
        </div>
    </div>

    {{-- Bagian Kanan - Form Login, dibuat bisa scroll internal jika perlu --}}
    <div class="w-full lg:w-1/2 flex flex-col justify-center items-center p-8 overflow-y-auto">
        <div class="max-w-md w-full">
            <div class="text-center mb-6">
                <div class="flex justify-center mb-4">
                    <img src="{{ asset('asset/logo.png') }}" alt="SEKAR Logo" class="h-12">
                </div>
            </div>

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf
                
                {{-- Input NIK --}}
                <div>
                    <label for="nik" class="block text-sm font-medium text-gray-700 mb-1">
                        NIK
                    </label>
                    <input 
                        type="text" 
                        id="nik"
                        name="nik" 
                        placeholder="Masukkan NIK Anda" 
                        value="{{ old('nik') }}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition duration-200 text-gray-700 text-base"
                        required
                        autocomplete="username"
                        autofocus
                    >
                </div>
                
                {{-- Input Password --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                        Password
                    </label>
                    <input 
                        type="password" 
                        id="password"
                        name="password" 
                        placeholder="Masukkan password Anda" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition duration-200 text-gray-700 text-base"
                        required
                        autocomplete="current-password"
                    >
                </div>
                
                {{-- Tombol Login --}}
                <div class="pt-4">
                    <button 
                        type="submit"
                        class="w-full bg-blue-600 text-white py-3 rounded-lg font-medium hover:bg-blue-700 transition duration-200 text-lg flex items-center justify-center"
                    >
                        Login
                    </button>
                </div>
            </form>

            {{-- Divider --}}
            <div class="mt-6 mb-4">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white text-gray-500">atau</span>
                    </div>
                </div>
            </div>

            {{-- Tombol Daftar --}}
            <div class="text-center">
                <a href="{{ route('register') }}" class="block w-full bg-gray-100 text-gray-700 py-3 rounded-lg font-medium hover:bg-gray-200 transition duration-200">
                    Daftar
                </a>    
            </div>
        </div>
    </div>
</div>
@endsection
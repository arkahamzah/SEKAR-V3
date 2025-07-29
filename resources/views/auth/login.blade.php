<!-- resources/views/auth/login.blade.php -->
@extends('layouts.app')

@section('title', 'Login - SEKAR')

@section('content')
<div class="min-h-screen flex">
    <!-- Left Side - Illustration -->
    <div class="hidden lg:flex lg:w-1/2 bg-white items-center justify-center p-8">
        <div class="max-w-lg w-full flex justify-center">
            <!-- Illustration Image -->
            <img src="{{ asset('asset/asset-image-index.png') }}" alt="Login Illustration" class="w-full max-w-md">
        </div>
    </div>

    <!-- Right Side - Login Form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-8 bg-white">
        <div class="max-w-md w-full">
            <div class="text-center mb-8">
                <div class="flex justify-center mb-8">
                    <img src="{{ asset('asset/logo.png') }}" alt="SEKAR Logo" class="h-12">
                </div>
            </div>

            <!-- Success Message for Password Reset -->
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

            <form method="POST" action="{{ route('login.post') }}" class="space-y-6">
                @csrf
                
                @if ($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                        @foreach ($errors->all() as $error)
                            <p class="text-sm">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <div>
                    <input 
                        type="text" 
                        name="nik" 
                        placeholder="NIK" 
                        value="{{ old('nik') }}"
                        class="w-full px-4 py-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition duration-200 text-gray-700"
                        required
                    >
                </div>

                <div>
                    <input 
                        type="password" 
                        name="password" 
                        placeholder="Password"
                        class="w-full px-4 py-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition duration-200 text-gray-700"
                        required
                    >
                </div>

                <button 
                    type="submit"
                    class="w-full bg-blue-700 text-white py-4 rounded-lg font-medium hover:bg-blue-800 transition duration-200 text-lg"
                >
                    Login
                </button>

                <!-- Password Reset Link -->
                <div class="text-center mb-3">
                    <a href="{{ route('password.request') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium hover:underline transition duration-200">
                        Lupa Password?
                    </a>
                </div>

                <div class="text-center">
                    <span class="text-gray-600">Ingin menjadi anggota? </span>
                    <a href="{{ route('register') }}" class="text-blue-600 hover:underline font-medium">Daftar Sekar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
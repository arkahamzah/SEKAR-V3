<!-- resources/views/layouts/app.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SEKAR')</title>
    <link rel="icon" type="image/png" href="{{ asset('asset/logo-tabs.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Custom styles for better match with design */
        .form-input:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .modal-overlay {
            backdrop-filter: blur(4px);
        }

        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
        }

        /* Fixed header transition */
        header {
            transition: box-shadow 0.2s ease;
        }

        /* Sidebar scrolling */
        aside {
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 transparent;
        }

        aside::-webkit-scrollbar {
            width: 4px;
        }

        aside::-webkit-scrollbar-track {
            background: transparent;
        }

        aside::-webkit-scrollbar-thumb {
            background-color: #cbd5e1;
            border-radius: 2px;
        }

        aside::-webkit-scrollbar-thumb:hover {
            background-color: #94a3b8;
        }

        /* Dropdown animation */
        .dropdown-enter {
            opacity: 0;
            transform: scale(0.95) translateY(-10px);
        }
        
        .dropdown-enter-active {
            opacity: 1;
            transform: scale(1) translateY(0);
            transition: all 0.15s ease-out;
        }
        
        .dropdown-exit {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
        
        .dropdown-exit-active {
            opacity: 0;
            transform: scale(0.95) translateY(-10px);
            transition: all 0.1s ease-in;
        }

        /* Content area smooth scrolling */
        main {
            scroll-behavior: smooth;
        }

        /* Prevent content jumping when scrollbar appears */
        body {
            overflow-y: scroll;
        }

        /* Active nav indicator */
        .nav-active {
            position: relative;
        }

        .nav-active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 20px;
            background-color: #2563eb;
            border-radius: 0 2px 2px 0;
        }

        /* Admin section styling */
        .admin-section {
            background: linear-gradient(135deg, #f0f4ff 0%, #e0edff 100%);
            border: 1px solid #c3d9ff;
        }

        /* Menu group styling */
        .menu-group {
            position: relative;
        }

        .menu-group:not(:last-child)::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 12px;
            right: 12px;
            height: 1px;
            background: linear-gradient(90deg, transparent 0%, #e5e7eb 20%, #e5e7eb 80%, transparent 100%);
        }
        /* Alert animations */
        #successAlert {
            animation: slideIn 0.3s ease-out;
            transition: all 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    @if(Auth::check())
        <!-- Header dengan User Dropdown - Fixed -->
        <header class="bg-white shadow-sm border-b border-gray-200 fixed top-0 left-0 right-0 z-50">
            <div class="max-w-none px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-14">
                    <div class="flex items-center">
                        <img src="{{ asset('asset/logo.png') }}" alt="SEKAR Logo" class="h-8">
                    </div>
                    
                    <!-- User Dropdown -->
                    <div class="relative">
                        <button id="userMenuButton" class="flex items-center space-x-2 text-gray-700 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded-lg px-2 py-1">
                            @if(Auth::user()->profile_picture)
                                <img src="{{ asset('storage/profile-pictures/' . Auth::user()->profile_picture) }}" 
                                     alt="Profile Picture" 
                                     class="w-7 h-7 rounded-full object-cover border border-gray-200">
                            @else
                                <div class="w-7 h-7 bg-blue-600 rounded-full flex items-center justify-center">
                                    <span class="text-white text-xs font-medium">{{ substr(Auth::user()->name, 0, 1) }}</span>
                                </div>
                            @endif
                            <span class="text-sm font-medium">{{ Auth::user()->name }}</span>
                            <svg class="w-4 h-4 transition-transform duration-200" id="userMenuChevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <div id="userDropdown" class="hidden absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">
                            <!-- User Info Header -->
                            <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                                <div class="flex items-center space-x-3">
                                    @if(Auth::user()->profile_picture)
                                        <img src="{{ asset('storage/profile-pictures/' . Auth::user()->profile_picture) }}" 
                                             alt="Profile Picture" 
                                             class="w-10 h-10 rounded-full object-cover border border-gray-200">
                                    @else
                                        <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center">
                                            <span class="text-white text-sm font-medium">{{ substr(Auth::user()->name, 0, 1) }}</span>
                                        </div>
                                    @endif
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ Auth::user()->name }}</p>
                                        <p class="text-xs text-gray-500">NIK: {{ Auth::user()->nik }}</p>
                                        @if(Auth::user()->pengurus && Auth::user()->pengurus->role)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 mt-1">
                                                {{ Auth::user()->pengurus->role->NAME }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Main Menu Items -->
                            <div class="py-1">
                                <a href="{{ route('profile.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                    <svg class="w-4 h-4 mr-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span>Profile Sekar</span>
                                </a>
                                
                                <a href="{{ route('sertifikat.show') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                    <svg class="w-4 h-4 mr-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span>Sertifikat Anggota</span>
                                </a>

                                <!-- Password Reset Link -->
                                <a href="{{ route('password.change') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                    <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m0 0a2 2 0 012 2m-2-2a2 2 0 00-2 2m2-2a2 2 0 01-2-2m0 0a2 2 0 012-2m0 0a2 2 0 00-2 2M3 12l1.5 1.5L9 9"></path>
                                    </svg>
                                    <span>Ubah Password</span>
                                </a>
                            </div>
                            
                            <!-- Admin Menu Section -->
                            @if(auth()->user()->pengurus && auth()->user()->pengurus->role && in_array(auth()->user()->pengurus->role->NAME, ['ADM', 'ADMIN_DPP', 'ADMIN_DPW', 'ADMIN_DPD']))
                            <div class="border-t border-gray-100 mt-1">
                                <div class="px-4 py-2 bg-blue-50">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                        </svg>
                                        <p class="text-xs font-semibold text-blue-800 uppercase tracking-wider">Panel Admin</p>
                                    </div>
                                </div>
                                
                                <div class="py-1">
                                    <a href="{{ route('setting.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 transition-colors">
                                        <svg class="w-4 h-4 mr-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span>Pengaturan Sistem</span>
                                        <span class="ml-auto text-xs bg-blue-100 text-blue-600 px-2 py-0.5 rounded-full">Admin</span>
                                    </a>
                                </div>
                            </div>
                            @endif
                            
                            <!-- Logout Section -->
                            <div class="border-t border-gray-100 mt-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="flex items-center w-full px-4 py-2 text-sm text-red-700 hover:bg-red-50 transition-colors">
                                        <svg class="w-4 h-4 mr-3 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                        </svg>
                                        <span>Keluar dari Sistem</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Sidebar - Fixed -->
        <aside class="fixed left-0 top-14 w-64 bg-white shadow-sm border-r border-gray-200 z-40" style="height: calc(100vh - 3.5rem);">
            <nav class="h-full overflow-y-auto py-6">
                <div class="px-3 space-y-2">
                    <!-- Main Navigation -->
                    <div class="menu-group pb-4">
                        <div class="px-3 mb-3">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Menu Utama</p>
                        </div>
                        
                        <a href="{{ route('dashboard') }}" class="flex items-center px-3 py-2.5 {{ request()->routeIs('dashboard') ? 'text-blue-600 bg-blue-50 nav-active' : 'text-gray-600 hover:bg-gray-50' }} rounded-lg text-sm transition-all duration-200 {{ request()->routeIs('dashboard') ? 'font-medium' : '' }}">
                            <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                            </svg>
                            <span>Dashboard</span>
                        </a>
                        
                        <a href="{{ route('data-anggota.index') }}" class="flex items-center px-3 py-2.5 {{ request()->routeIs('data-anggota*') ? 'text-blue-600 bg-blue-50 nav-active' : 'text-gray-600 hover:bg-gray-50' }} rounded-lg text-sm transition-all duration-200 {{ request()->routeIs('data-anggota*') ? 'font-medium' : '' }}">
                            <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"></path>
                            </svg>
                            <span>Data Anggota</span>
                        </a>
                    </div>

                    <!-- Services Navigation -->
                    <div class="menu-group pb-4">
                        <div class="px-3 mb-3">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Layanan</p>
                        </div>
                        
                        <a href="{{ route('konsultasi.index') }}" class="flex items-center px-3 py-2.5 {{ request()->routeIs('konsultasi*') ? 'text-blue-600 bg-blue-50 nav-active' : 'text-gray-600 hover:bg-gray-50' }} rounded-lg text-sm transition-all duration-200 {{ request()->routeIs('konsultasi*') ? 'font-medium' : '' }}">
                            <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2 5a2 2 0 012-2h7a2 2 0 012 2v4a2 2 0 01-2 2H9l-3 3v-3H4a2 2 0 01-2-2V5z"></path>
                                <path d="M15 7v2a4 4 0 01-4 4H9.828l-1.766 1.767c.28.149.599.233.938.233h2l3 3v-3h2a2 2 0 002-2V9a2 2 0 00-2-2h-1z"></path>
                            </svg>
                            <span>Advokasi & Aspirasi</span>
                        </a>
                        
                        <a href="{{ route('banpers.index') }}" class="flex items-center px-3 py-2.5 {{ request()->routeIs('banpers*') ? 'text-blue-600 bg-blue-50 nav-active' : 'text-gray-600 hover:bg-gray-50' }} rounded-lg text-sm transition-all duration-200 {{ request()->routeIs('banpers*') ? 'font-medium' : '' }}">
                            <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"></path>
                                <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Bantuan Perusahaan</span>
                        </a>
                    </div>
                    
                    <!-- Admin Section -->
                    @if(auth()->user()->pengurus && auth()->user()->pengurus->role && in_array(auth()->user()->pengurus->role->NAME, ['ADM', 'ADMIN_DPP', 'ADMIN_DPW', 'ADMIN_DPD']))
                    <div class="menu-group">
                        <div class="px-3 mb-3">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                </svg>
                                <p class="text-xs font-semibold text-blue-600 uppercase tracking-wider">Administrasi</p>
                            </div>
                        </div>
                        
                        <div class="admin-section rounded-lg p-2 mx-1">
                            <a href="{{ route('setting.index') }}" class="flex items-center px-3 py-2.5 {{ request()->routeIs('setting*') ? 'text-blue-600 bg-white nav-active shadow-sm' : 'text-blue-700 hover:bg-white hover:shadow-sm' }} rounded-md text-sm transition-all duration-200 {{ request()->routeIs('setting*') ? 'font-medium' : '' }}">
                                <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"></path>
                                </svg>
                                <span>Pengaturan Sistem</span>
                                <span class="ml-auto text-xs bg-blue-100 text-blue-600 px-2 py-0.5 rounded-full">
                                    {{ auth()->user()->pengurus->role->NAME }}
                                </span>
                            </a>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Footer Info -->
                <div class="absolute bottom-0 left-0 right-0 p-4 bg-gray-50 border-t border-gray-200">
                    <div class="text-center">
                        <p class="text-xs text-gray-500">SEKAR v1.0</p>
                        <p class="text-xs text-gray-400">Serikat Karyawan Telkom</p>
                    </div>
                </div>
            </nav>
        </aside>
    @endif

    <!-- Main Content -->
    <main class="{{ Auth::check() ? 'ml-64 pt-14 min-h-screen' : 'min-h-screen' }}">
        <!-- Success Message Global -->
        @if(session('success'))
        <div id="successAlert" class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 mx-4 mt-4 rounded-lg relative">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <span class="font-medium">{{ session('success') }}</span>
                <button onclick="closeAlert('successAlert')" class="ml-auto text-green-600 hover:text-green-800">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
        </div>
        @endif
        @yield('content')
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const userMenuButton = document.getElementById('userMenuButton');
            const userDropdown = document.getElementById('userDropdown');
            const userMenuChevron = document.getElementById('userMenuChevron');
            const header = document.querySelector('header');
            
            // Header scroll effect
            let lastScrollTop = 0;
            window.addEventListener('scroll', function() {
                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                
                // Add shadow when scrolled
                if (scrollTop > 0) {
                    header.style.boxShadow = '0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06)';
                } else {
                    header.style.boxShadow = '0 1px 2px 0 rgba(0, 0, 0, 0.05)';
                }
                
                lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
            });
            
            // User dropdown functionality
            if (userMenuButton && userDropdown) {
                let isOpen = false;
                
                userMenuButton.addEventListener('click', function(e) {
                    e.stopPropagation();
                    toggleDropdown();
                });
                
                function toggleDropdown() {
                    if (isOpen) {
                        closeDropdown();
                    } else {
                        openDropdown();
                    }
                }
                
                function openDropdown() {
                    userDropdown.classList.remove('hidden');
                    userMenuChevron.style.transform = 'rotate(180deg)';
                    isOpen = true;
                    
                    // Close on outside click
                    setTimeout(() => {
                        document.addEventListener('click', closeDropdown);
                    }, 10);
                }
                
                function closeDropdown() {
                    userDropdown.classList.add('hidden');
                    userMenuChevron.style.transform = 'rotate(0deg)';
                    isOpen = false;
                    document.removeEventListener('click', closeDropdown);
                }
                
                // Close on escape key
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && isOpen) {
                        closeDropdown();
                    }
                });
            }

            // Auto hide alerts after 5 seconds - GABUNG DI SINI
            const alerts = ['successAlert'];
            alerts.forEach(alertId => {
                const alert = document.getElementById(alertId);
                if (alert) {
                    setTimeout(() => {
                        closeAlert(alertId);
                    }, 5000);
                }
            });
        }); // TUTUP DOMContentLoaded dengan benar

        // Alert close function - DI LUAR DOMContentLoaded
        function closeAlert(alertId) {
            const alert = document.getElementById(alertId);
            if (alert) {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(() => {
                    alert.remove();
                }, 300);
            }
        }
        </script>
</body>
</html>
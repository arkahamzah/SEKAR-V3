<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SEKAR')</title>
    <link rel="icon" type="image/png" href="{{ asset('asset/icon.png') }}">
    <script src="{{ asset('js/tailwindcss.js') }}"></script>
    <link href="{{ asset('css/fontawesome-all.min.css') }}" rel="stylesheet">
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
            transition: all 0.15s ease-in;
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

        /* Alert animation */
        .alert {
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        /* Notification & Document styles */
        .notification-item:hover, .document-item:hover {
            transform: translateX(2px);
            transition: transform 0.2s ease;
        }

        /* Custom notification colors that work with dynamic classes */
        .bg-blue-100 { background-color: rgb(219 234 254); }
        .bg-green-100 { background-color: rgb(220 252 231); }
        .bg-yellow-100 { background-color: rgb(254 249 195); }
        .bg-orange-100 { background-color: rgb(255 237 213); }
        .bg-purple-100 { background-color: rgb(243 232 255); }
        .bg-gray-100 { background-color: rgb(243 244 246); }

        .text-blue-600 { color: rgb(37 99 235); }
        .text-green-600 { color: rgb(22 163 74); }
        .text-yellow-600 { color: rgb(202 138 4); }
        .text-orange-600 { color: rgb(234 88 12); }
        .text-purple-600 { color: rgb(147 51 234); }
        .text-gray-600 { color: rgb(75 85 99); }

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
        <header class="bg-white shadow-sm border-b border-gray-200 fixed top-0 left-0 right-0 z-50">
            <div class="max-w-none px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-14">
                    <div class="flex items-center">
                        <a href="{{ route('dashboard') }}">
                            <img src="{{ asset('asset/logo.png') }}" alt="SEKAR Logo" class="h-8">
                        </a>
                    </div>

                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <button id="documentBtn"
                                    class="relative p-2 text-gray-600 hover:text-blue-600 hover:bg-gray-100 rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </button>

                            <div id="documentDropdown"
                                class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50 hidden">
                                <div class="px-4 py-3 border-b border-gray-200">
                                    <h3 class="text-sm font-semibold text-gray-900">Dokumen SEKAR</h3>
                                </div>
                                <div id="documentListContainer" class="max-h-96 overflow-y-auto">
                                    <div id="documentLoading" class="p-4 text-center">
                                        <svg class="animate-spin h-5 w-5 text-gray-500 mx-auto" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <p class="text-sm text-gray-500 mt-2">Memuat dokumen...</p>
                                    </div>
                                    <div id="documentEmpty" class="p-8 text-center hidden">
                                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        <p class="text-gray-600 font-medium">Tidak ada dokumen</p>
                                        <p class="text-sm text-gray-500">Admin belum mengunggah dokumen apapun</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="relative">
                            <button id="notificationBtn"
                                    class="relative p-2 text-gray-600 hover:text-blue-600 hover:bg-gray-100 rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                </svg>

                                <span id="notificationBadge"
                                    class="absolute -top-1 -right-1 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-red-600 rounded-full hidden">
                                    0
                                </span>
                            </button>

                            <div id="notificationDropdown"
                                class="absolute right-0 mt-2 w-96 bg-white rounded-lg shadow-lg border border-gray-200 z-50 hidden">

                                <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                                    <h3 class="text-sm font-semibold text-gray-900">Notifikasi</h3>
                                    <button id="markAllReadBtn"
                                            class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                                        Tandai Semua Dibaca
                                    </button>
                                </div>

                                <div id="notificationList" class="max-h-96 overflow-y-auto">
                                    <div id="notificationLoading" class="p-4 text-center">
                                        <svg class="animate-spin h-5 w-5 text-gray-500 mx-auto" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <p class="text-sm text-gray-500 mt-2">Memuat notifikasi...</p>
                                    </div>

                                    <div id="notificationEmpty" class="p-8 text-center hidden">
                                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                        </svg>
                                        <p class="text-gray-600 font-medium">Tidak ada notifikasi</p>
                                        <p class="text-sm text-gray-500">Semua notifikasi akan muncul di sini</p>
                                    </div>

                                    </div>

                                <div class="px-4 py-3 border-t border-gray-200">
                                    <a href="{{ route('konsultasi.index') }}"
                                    class="block text-center text-sm text-blue-600 hover:text-blue-800 font-medium">
                                        Lihat Semua Advokasi & Aspirasi
                                    </a>
                                </div>
                            </div>
                        </div>

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

                            <div id="userDropdown" class="hidden absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">
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
                                            <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="py-1">
                                <a href="{{ route('profile.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                    <svg class="w-4 h-4 mr-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span>Profile</span>
                                </a>
                                <div class="py-1">
                                    <a href="{{ route('sertifikat.show') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                        <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                                        </svg>
                                        <span>Sertifikat</span>
                                    </a>
                                </div>
                                    @if(auth()->user()->pengurus && auth()->user()->pengurus->role && in_array(auth()->user()->pengurus->role->NAME, ['ADM', 'ADMIN_DPP', 'ADMIN_DPW', 'ADMIN_DPD']))
                                    <div class="border-t border-gray-100 mt-1 pt-1">
                                        <div class="px-4 py-2">
                                            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Panel Admin</span>
                                        </div>

                                        <a href="{{ route('setting.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                            <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                            <span>Pengaturan</span>
                                        </a>
                                    </div>
                                    @endif
                                <div class="border-t border-gray-100 mt-1">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="flex items-center w-full px-4 py-2 text-sm text-red-700 hover:bg-red-50 transition-colors">
                                            <svg class="w-4 h-4 mr-3 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                            </svg>
                                            <span>Keluar</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <aside class="fixed left-0 top-14 w-64 bg-white shadow-sm border-r border-gray-200 z-40" style="height: calc(100vh - 3.5rem);">
            <nav class="h-full overflow-y-auto py-6">
                <div class="px-3 space-y-2">
                    <div class="menu-group pb-4">
                        <div class="px-3 mb-3">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Menu Utama</p>
                        </div>
        
                        <a href="{{ route('home') }}" class="flex items-center px-3 py-2.5 {{ request()->routeIs('home') ? 'text-blue-600 bg-blue-50 nav-active' : 'text-gray-600 hover:bg-gray-50' }} rounded-lg text-sm transition-all duration-200 {{ request()->routeIs('home') ? 'font-medium' : '' }}">
                            <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>
                            <span>Home</span>
                        </a>

                        <a href="{{ route('dashboard') }}" class="flex items-center px-3 py-2.5 {{ request()->routeIs('dashboard') ? 'text-blue-600 bg-blue-50 nav-active' : 'text-gray-600 hover:bg-gray-50' }} rounded-lg text-sm transition-all duration-200 {{ request()->routeIs('dashboard') ? 'font-medium' : '' }}">
                            <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                            </svg>
                            <span>Dashboard</span>
                        </a>
                    </div>

                    <div class="menu-group">
                        <div class="px-3 mb-3">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Layanan</p>
                        </div>

                        <a href="{{ route('konsultasi.index') }}" class="flex items-center px-3 py-2.5 {{ request()->routeIs('konsultasi.*') ? 'text-blue-600 bg-blue-50 nav-active' : 'text-gray-600 hover:bg-gray-50' }} rounded-lg text-sm transition-all duration-200 {{ request()->routeIs('konsultasi.*') ? 'font-medium' : '' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                            <span>Advokasi & Aspirasi</span>
                        </a>
                    </div>

                    @if(auth()->user()->pengurus && auth()->user()->pengurus->role && in_array(auth()->user()->pengurus->role->NAME, ['ADM', 'ADMIN_DPP', 'ADMIN_DPW', 'ADMIN_DPD']))
                    <div class="menu-group">
                        <div class="px-3 mb-3">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Administrasi</p>
                        </div>

                        <div class="admin-section rounded-lg p-3 mb-3">
                            <a href="{{ route('data-anggota.index') }}" class="flex items-center px-3 py-2.5 {{ request()->routeIs('data-anggota.*') ? 'text-blue-600 bg-white shadow-sm nav-active' : 'text-gray-600 hover:bg-white/50' }} rounded-lg text-sm transition-all duration-200 {{ request()->routeIs('data-anggota.*') ? 'font-medium' : '' }}">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                <span>Data Anggota</span>
                            </a>

                            <a href="{{ route('banpers.index') }}" class="flex items-center px-3 py-2.5 {{ request()->routeIs('banpers.*') ? 'text-blue-600 bg-white shadow-sm nav-active' : 'text-gray-600 hover:bg-white/50' }} rounded-lg text-sm transition-all duration-200 {{ request()->routeIs('banpers.*') ? 'font-medium' : '' }}">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>Bantuan Perusahaan</span>
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
            </nav>
        </aside>

        <main class="ml-64 pt-14 min-h-screen">
    @else
        <main class="min-h-screen">
    @endif

    @if(session('success'))
    <div id="successAlert" class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 mx-4 mt-4 rounded-lg relative alert">
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
    console.log('DOM loaded, initializing...');

    // User dropdown functionality
    const userMenuButton = document.getElementById('userMenuButton');
    const userDropdown = document.getElementById('userDropdown');
    const userMenuChevron = document.getElementById('userMenuChevron');
    const header = document.querySelector('header');

    // Header scroll effect
    let lastScrollTop = 0;
    window.addEventListener('scroll', function() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

        if (scrollTop > 0) {
            header.style.boxShadow = '0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06)';
        } else {
            header.style.boxShadow = '0 1px 2px 0 rgba(0, 0, 0, 0.05)';
        }

        lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
    });

    if (userMenuButton && userDropdown) {
        userMenuButton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            userDropdown.classList.toggle('hidden');

            if (userMenuChevron) {
                userMenuChevron.style.transform = userDropdown.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
            }
        });

        document.addEventListener('click', function(e) {
            if (!userMenuButton.contains(e.target) && !userDropdown.contains(e.target)) {
                userDropdown.classList.add('hidden');
                if (userMenuChevron) {
                    userMenuChevron.style.transform = 'rotate(0deg)';
                }
            }
        });
    }

    // Notification system initialization
    const notificationBtn = document.getElementById('notificationBtn');
    const notificationDropdown = document.getElementById('notificationDropdown');
    const notificationBadge = document.getElementById('notificationBadge');
    const notificationList = document.getElementById('notificationList');
    const notificationLoading = document.getElementById('notificationLoading');
    const notificationEmpty = document.getElementById('notificationEmpty');
    const markAllReadBtn = document.getElementById('markAllReadBtn');

    if (notificationBtn && notificationDropdown) {
        let isNotificationDropdownOpen = false;

        notificationBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            isNotificationDropdownOpen = !isNotificationDropdownOpen;
            if (isNotificationDropdownOpen) {
                notificationDropdown.classList.remove('hidden');
                loadNotifications();
            } else {
                notificationDropdown.classList.add('hidden');
            }
        });

        document.addEventListener('click', function(e) {
            if (!notificationDropdown.contains(e.target) && !notificationBtn.contains(e.target)) {
                notificationDropdown.classList.add('hidden');
                isNotificationDropdownOpen = false;
            }
        });
        
        if(markAllReadBtn) {
            markAllReadBtn.addEventListener('click', function(e) {
                e.preventDefault();
                markAllNotificationsAsRead();
            });
        }
    }

    // === FUNGSI BARU UNTUK DROPDOWN DOKUMEN ===
    const documentBtn = document.getElementById('documentBtn');
    const documentDropdown = document.getElementById('documentDropdown');
    const documentListContainer = document.getElementById('documentListContainer');
    const documentLoading = document.getElementById('documentLoading');
    const documentEmpty = document.getElementById('documentEmpty');
    let documentsLoaded = false;

    if (documentBtn && documentDropdown) {
        documentBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            const isHidden = documentDropdown.classList.toggle('hidden');
            if (!isHidden && !documentsLoaded) {
                loadDocuments();
            }
        });

        document.addEventListener('click', function(e) {
            if (!documentDropdown.contains(e.target) && !documentBtn.contains(e.target)) {
                documentDropdown.classList.add('hidden');
            }
        });
    }

    async function loadDocuments() {
        documentLoading.style.display = 'block';
        documentEmpty.style.display = 'none';
        
        const existingItems = documentListContainer.querySelectorAll('.document-item');
        existingItems.forEach(item => item.remove());

        try {
            const response = await fetch("{{ route('api.documents.list') }}", {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            
            if (data.success && data.documents.length > 0) {
                renderDocuments(data.documents);
            } else {
                documentEmpty.style.display = 'block';
            }
        } catch (error) {
            console.error('Error loading documents:', error);
            documentListContainer.insertAdjacentHTML('beforeend', '<div class="p-4 text-center text-sm text-red-600">Gagal memuat dokumen.</div>');
        } finally {
            documentLoading.style.display = 'none';
            documentsLoaded = true;
        }
    }

    function renderDocuments(documents) {
        let html = '';
        documents.forEach(doc => {
            const viewUrl = `{{ url('dokumen') }}/${doc.filename}`;
            html += `
                <a href="${viewUrl}" target="_blank" class="document-item block px-4 py-3 border-b border-gray-100 hover:bg-gray-50 transition-colors duration-150">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 mt-1">
                            <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0011.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-900 font-medium truncate">${doc.name}</p>
                            <div class="flex items-center text-xs text-gray-500 mt-1 space-x-2">
                                <span>${doc.size}</span>
                                <span>•</span>
                                <span class="text-blue-600 hover:underline">Lihat Dokumen</span>
                            </div>
                        </div>
                    </div>
                </a>
            `;
        });
        documentListContainer.insertAdjacentHTML('beforeend', html);
    }
    
    // Load notifications from server
    async function loadNotifications() {
        // ... (fungsi loadNotifications tidak berubah) ...
    }

    // Render notifications in dropdown
    function renderNotifications(notifications) {
        // ... (fungsi renderNotifications tidak berubah) ...
    }

    // Helper functions for notifications
    function showLoading() { if (notificationLoading) notificationLoading.classList.remove('hidden'); if (notificationEmpty) notificationEmpty.classList.add('hidden'); }
    function hideLoading() { if (notificationLoading) notificationLoading.classList.add('hidden'); }
    function showEmpty() { if (notificationEmpty) notificationEmpty.classList.remove('hidden'); }
    function hideEmpty() { if (notificationEmpty) notificationEmpty.classList.add('hidden'); }
    function showError(message) { hideLoading(); if (notificationList) notificationList.innerHTML = `<div class="p-4 text-center"><svg class="w-8 h-8 text-red-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg><p class="text-sm text-red-600">${message}</p></div>`;}
    function updateNotificationBadge(count) { if (notificationBadge) { if (count > 0) { notificationBadge.textContent = count > 99 ? '99+' : count; notificationBadge.classList.remove('hidden'); } else { notificationBadge.classList.add('hidden'); } } }
    async function updateUnreadCount() { try { const response = await fetch('/notifications/unread-count', { method: 'GET', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } }); if (response.ok) { const data = await response.json(); if (data.success) { updateNotificationBadge(data.unread_count); } } } catch (error) { console.error('Error updating unread count:', error); } }
    async function markAllNotificationsAsRead() { try { const response = await fetch('/notifications/mark-all-read', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') } }); if (response.ok) { document.querySelectorAll('.notification-item').forEach(item => { item.classList.remove('bg-blue-50'); item.classList.add('bg-white'); const unreadIndicator = item.querySelector('.w-2.h-2.bg-blue-600'); if (unreadIndicator) { unreadIndicator.remove(); } }); updateNotificationBadge(0); } } catch (error) { console.error('Error marking all as read:', error); } }
    function getNotificationIconSVG(iconName, color) { const icons = { 'chat-bubble-left-right': `<svg class="w-4 h-4 text-${color}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>`, 'arrow-trending-up': `<svg class="w-4 h-4 text-${color}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>`, 'check-circle': `<svg class="w-4 h-4 text-${color}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`, 'bell': `<svg class="w-4 h-4 text-${color}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>` }; return icons[iconName] || icons['bell']; }
    window.handleNotificationClick = async function(notificationId, konsultasiId) { try { await fetch(`/notifications/${notificationId}/read`, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') } }); const notificationElement = document.querySelector(`.notification-item[data-id="${notificationId}"]`); if (notificationElement) { notificationElement.classList.remove('bg-blue-50'); notificationElement.classList.add('bg-white'); const unreadIndicator = notificationElement.querySelector('.w-2.h-2.bg-blue-600'); if (unreadIndicator) unreadIndicator.remove(); } updateUnreadCount(); if (konsultasiId && konsultasiId !== 'null') { window.location.href = `/advokasi-aspirasi/${konsultasiId}`; } } catch (error) { console.error('Error handling notification click:', error); } };
    updateUnreadCount();

    // Auto hide alerts after 5 seconds
    const alerts = ['successAlert'];
    alerts.forEach(alertId => {
        const alert = document.getElementById(alertId);
        if (alert) {
            setTimeout(() => {
                closeAlert(alertId);
            }, 5000);
        }
    });
});

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
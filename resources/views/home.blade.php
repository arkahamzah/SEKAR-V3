@extends('layouts.app')

@section('title', 'Home - SEKAR')

@section('content')
{{-- Ganti 'images/image_f8c185.jpg' dengan path gambar Anda --}}
<div class="relative w-full h-[calc(100vh-3.5rem)] bg-cover bg-center" style="background-image: url('{{ asset('asset/graha-merah-putih.jpg') }}');">

    {{-- Lapisan overlay gelap agar teks mudah dibaca --}}
    <div class="absolute inset-0 bg-black bg-opacity-30"></div>

    <div class="relative z-10 flex items-center justify-center h-full p-4 text-white">
        <div class="max-w-4xl text-center">
            <h1 class="text-4xl md:text-6xl font-extrabold tracking-tight leading-tight" style="text-shadow: 2px 2px 8px rgba(0,0,0,0.6);">
                Selamat Datang di Portal SEKAR
            </h1>
            <p class="mt-4 text-lg md:text-xl font-light text-gray-200">
                Rumah digital bagi seluruh anggota Serikat Karyawan Telkom.
            </p>
            <hr class="max-w-sm mx-auto my-8 border-gray-500">
            <div class="max-w-2xl mx-auto">
                <h2 class="text-2xl font-semibold">Sambutan Ketua Umum</h2>
                <p class="mt-4 text-base leading-relaxed text-gray-300">
                    "Seluruh pegawai Telkom yang saya cintai dan saya banggakan, mari kita jadikan portal ini sebagai wadah untuk menyatukan suara, memperjuangkan aspirasi, dan mempererat solidaritas kita bersama. Dengan semangat persatuan, kita wujudkan lingkungan kerja yang adil, transparan, dan profesional untuk kemajuan kita dan kejayaan Telkom Indonesia."
                </p>
                <p class="mt-6 font-bold text-lg">NASHRI AMAN RAFA</p>
                <p class="text-sm text-gray-400">Ketua Umum SEKAR Telkom</p>
            </div>
        </div>
    </div>
</div>
@endsection
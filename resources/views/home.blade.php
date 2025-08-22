@extends('layouts.app')

@section('title', 'Home - SEKAR')

@section('content')
    <div class="relative w-full min-h-[calc(100vh-3.5rem)] bg-cover bg-center bg-fixed" style="background-image: url('{{ asset('asset/graha-merah-putih.jpg') }}');">

        <div class="relative z-10 flex items-center justify-center h-full p-4 py-12 text-white">
            <div class="max-w-4xl text-center">
                
                <h1 class="text-4xl md:text-6xl font-extrabold tracking-tight leading-tight" style="text-shadow: 2px 2px 8px rgba(0,0,0,0.6);">
                    Selamat Datang di Portal SEKAR
                </h1>
                <hr class="max-w-sm mx-auto my-8 border-gray-500">
                
                <div class="max-w-3xl mx-auto bg-white/25 backdrop-blur-lg rounded-lg p-6 shadow-lg" relative style="font-family: 'Times New Roman', Times, serif;" >
                    
                    <div class="absolute inset-0 flex items-center justify-center z-0">
                        <img src="{{ asset('asset/logo-tabs.png') }}" alt="SEKAR Logo Watermark" class="w-64 h-64 opacity-10">
                    </div>

                    <div class="relative z-10">
                        <h2 class="text-2xl font-semibold text-white">Sambutan Ketua Umum</h2>
                        
                        <div class="mt-4 text-base leading-relaxed text-white font-semibold text-left px-4">
                            <p class="mb-4">"SEKAR.....SEKAR.....SEKAR</p>
                            <p class="mb-4">
                                Sekar merupakan satu-satunya organisasi serikat karyawan di PT. Telekomunikasi Indonesia, Tbk. Yang dilindungi UU no 21 tahun 2000 tentang serikat pekerja/serikat buruh, sebagai wadah aspirasi dan amanah anggota (karyawan Telkom), yang bertujuan memperjuangkan kemajuan perusahaan, meningkatkan kesejahteraan karyawan serta kesejahteraan para senior (pensiunan). Perjanjian Kerja Bersama (PKB) antara Sekar Telkom dan Management PT. Telekomunikasi Indonesia, Tbk. Kesepakatan antara serikat dan management dituangkan dalam PKB, dimana saat ini sudah berlaku PKB VI (2015- 2017) yang baru di tandatangani.
                            </p>
                            <p class="mb-4">
                                Beberapa manfaat yang telah diperjuangkan sekar yaitu :
                            </p>
                            <ol class="list-decimal list-inside mb-4 space-y-2">
                                <li>Sistem remunerasi baru yang kita nikmati tahun 2016 dengan kenaikan 1,7 kali THP yang lama dan merubah variabel (insentif).</li>
                                <li>Memberikan pendampingan dan advokasi bagi anggota yang terkena permasalahan hukum dan lain-lain.</li>
                                <li>Meningkatkan kesejahteraan pensiunan</li>
                                <li>Sebagai gardan kedua yang mengawal tercapainya goal bisnis perusahaan.</li>
                                <li>Mengawal dan mengamankan perusahaan dari segala ancaman serta rongrongan</li>
                                <li>Wadah pemersatu seluruh karyawan telkom</li>
                            </ol>
                            <p class="mb-4">
                                Untuk itu kami mengajak dan sangat mengharapkan segenap karyawan untuk tetap menjadi anggota sekar bagi anggota yang lama dan bagi karyawan yang belum menjadi anggota sekar untuk menjadi anggota sekar. Dimana Sistem keanggotaan Sekar adalah stelsel Aktif, sehingga diperlukan keaktifan karyawan untuk mendaftarkan diri.
                            </p>
                            <p class="mb-4">
                                Dengan telah ditandatanganinya Perjanjian Kerja Bersama (PKB) VI antara Sekar Telkom dan Management PT. Telekomunikasi Indonesia, Tbk. dimana pada Lampiran 1 Penjelasan PKB VI point 1 yang mengisyaratkan perlunya dilaksanakan proses registrasi ulang keanggotaan Sekar Telkom guna menyampaikan perkembangan tentang serikat dan peningkatan kesejahteraan karyawan sebagai anggota Sekar Telkom.
                            </p>
                            <p class="mb-4">
                                Partisipasi karyawan sangat diharapkan dalam registrasi ulang ini . Atas kesediaannya kami sampaikan terima kasih.
                            </p>
                            <p>"SEKAR.....SEKAR.....SEKAR"</p>
                        </div>

                        <div class="flex justify-end">
                            {{-- Blok tanda tangan dengan konten rata TENGAH (text-center) --}}
                            <div class="mt-8 text-center">
                                @if($ketuaUmumSignature)
                                <div class="flex justify-center items-center h-20">
                                    <img src="{{ asset('storage/signatures/' . $ketuaUmumSignature->signature_file) }}" alt="Tanda Tangan Ketua Umum" class="max-h-full">
                                </div>
                                @endif

                                @if($ketuaUmum)
                                    <p class="mt-2 font-bold text-lg text-white">{{ $ketuaUmum->nama }}</p>
                                    <p class="text-sm text-white">{{ Str::title(strtolower($ketuaUmum->jabatan)) }} </p>
                                @else
                                    <p class="mt-6 font-bold text-lg text-white">Nama Ketua Umum</p>
                                    <p class="text-sm text-white">Ketua Umum SEKAR Telkom</p>
                                @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
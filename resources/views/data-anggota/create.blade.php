@extends('layouts.app')
@section('title', 'Tambah Anggota Baru')
@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Tambah Anggota Baru</h1>
            <p class="text-gray-600 mt-1">Tambahkan anggota baru ke dalam sistem SEKAR</p>
        </div>
        <a href="{{ route('data-anggota.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700">Kembali</a>
    </div>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Informasi Anggota</h2>
        </div>
        <form action="{{ route('data-anggota.store') }}" method="POST" class="p-6">
            @csrf
            @if ($errors->any())
                <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">...</div>
            @endif
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="nik" class="block text-sm font-medium text-gray-700 mb-2">NIK <span class="text-red-500">*</span></label>
                    <input type="text" id="nik" name="nik" value="{{ old('nik') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500">
                </div>
                <div>
                    <label for="nama" class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" id="nama" name="nama" value="{{ old('nama') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500">
                </div>
                <div>
                    <label for="dpw" class="block text-sm font-medium text-gray-700 mb-2">DPW <span class="text-red-500">*</span></label>
                    <select id="dpw" name="dpw" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500">
                        <option value="">Pilih DPW</option>
                        @foreach($dpwList as $dpw)
                            <option value="{{ $dpw }}" {{ old('dpw') == $dpw ? 'selected' : '' }}>{{ $dpw }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="dpd" class="block text-sm font-medium text-gray-700 mb-2">DPD <span class="text-red-500">*</span></label>
                    <select id="dpd" name="dpd" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500">
                        <option value="">Pilih DPD</option>
                        @foreach($dpdList as $dpd)
                            <option value="{{ $dpd }}" {{ old('dpd') == $dpd ? 'selected' : '' }}>{{ $dpd }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="iuran_wajib" class="block text-sm font-medium text-gray-700 mb-2">Iuran Wajib (Rp)</label>
                    <input type="number" id="iuran_wajib" name="iuran_wajib" value="{{ old('iuran_wajib', 25000) }}" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500">
                </div>
                <div>
                    <label for="iuran_sukarela" class="block text-sm font-medium text-gray-700 mb-2">Iuran Sukarela (Rp)</label>
                    <input type="number" id="iuran_sukarela" name="iuran_sukarela" value="{{ old('iuran_sukarela') }}" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500">
                </div>
            </div>
            <div class="mt-8 flex justify-end items-center gap-3">
                <a href="{{ route('data-anggota.index') }}" class="px-4 py-2 border rounded-lg text-sm font-medium">Batal</a>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">Simpan Anggota</button>
            </div>
        </form>
    </div>
</div>

{{-- [TAMBAHAN] Skrip untuk auto-fill data berdasarkan NIK --}}
<script>
(function () {
  const nik  = document.getElementById('nik');
  const nama = document.getElementById('nama');
  const dpw  = document.getElementById('dpw');
  const dpd  = document.getElementById('dpd');

  // util: set select value dengan aman
  function setSelectValue(selectEl, value) {
    if (!selectEl) return;
    if (value == null) return;
    // jika option belum ada (mis. list terbatas), tambahkan sementara
    let opt = Array.from(selectEl.options).find(o => o.value == value);
    if (!opt) {
      opt = new Option(value, value, true, true);
      selectEl.add(opt);
    }
    selectEl.value = value;
    // trigger event agar binding lain (jika ada) ikut jalan
    selectEl.dispatchEvent(new Event('change', { bubbles: true }));
  }

  let timer;
  const debounce = (fn, ms=400) => (...args) => {
    clearTimeout(timer);
    timer = setTimeout(() => fn(...args), ms);
  };

  async function lookup(n) {
    const min = 6; // hindari spam request
    if (!n || n.trim().length < min) return;

    try {
      const url = `{{ route('data-anggota.cek-nik', ['nik' => '___NIK___']) }}`.replace('___NIK___', encodeURIComponent(n.trim()));
      const res = await fetch(url, { headers: { 'Accept':'application/json' } });
      if (!res.ok) {
        // bisa tampilkan pesan kecil kalau mau
        return;
      }
      const data = await res.json();
      if (!data.found) return;

      // isi otomatis (overwrite supaya konsisten dengan data sumber)
      if (nama) nama.value = data.nama ?? '';
      setSelectValue(dpw, data.dpw ?? '');
      setSelectValue(dpd, data.dpd ?? '');
    } catch (e) {
      console.error('Gagal cek NIK:', e);
    }
  }

  if (nik) {
    nik.addEventListener('input', debounce(e => lookup(e.target.value)));
    // pastikan juga saat blur/enter
    nik.addEventListener('change', e => lookup(e.target.value));
  }
})();
</script>

@endsection
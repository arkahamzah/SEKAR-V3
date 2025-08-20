@extends('layouts.app')

@section('title', 'Daftar SEKAR - Single Sign-On')

@section('content')
<style>
/* Clean SEKAR Registration Styles */
.iuran-info-panel {
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    border: 1px solid #bfdbfe;
}

.iuran-amount {
    font-weight: 700;
    color: #1e40af;
}

.iuran-amount.total {
    color: #059669;
}

.employee-info {
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    border: 1px solid #bfdbfe;
}

.gptp-notice {
    background: linear-gradient(135deg, #fef3e2 0%, #fed7aa 100%);
    border: 1px solid #fdba74;
}

.agreement-section {
    background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
    border: 1px solid #e5e7eb;
}

.iuran-breakdown {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-left: 4px solid #3b82f6;
}

.btn-register {
    transition: all 0.2s ease-in-out;
}

.btn-register:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.3);
}

.form-input:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
    outline: none;
}

.modal-overlay {
    background: rgba(0, 0, 0, 0.4);
    backdrop-filter: blur(4px);
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.loading-spinner {
    animation: spin 1s linear infinite;
}
</style>

<div class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-6">
        <div class="text-center mb-6">
            <div class="flex justify-center mb-4">
                <img src="{{ asset('asset/logo.png') }}" alt="SEKAR Logo" class="h-12">
            </div>
            <h2 class="text-xl font-bold text-gray-900">Daftar SEKAR</h2>
        </div>

        <form id="registerForm" class="space-y-4">
            @csrf
            
            <div id="alertContainer" class="hidden"></div>
            
            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-3 py-2 rounded-lg">
                    @foreach ($errors->all() as $error)
                        <p class="text-xs">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div>
                <label class="block text-gray-700 text-sm font-medium mb-1">
                    NIK <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="nikInput"
                    name="nik" 
                    placeholder="Masukkan NIK Anda" 
                    value="{{ old('nik') }}"
                    class="form-input w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition duration-200 text-sm"
                    required
                >
            </div>

            <div id="loadingState" class="hidden text-center py-3">
                <div class="inline-flex items-center">
                    <svg class="loading-spinner -ml-1 mr-2 h-4 w-4 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-sm text-gray-600">Memuat data karyawan...</span>
                </div>
            </div>

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
            </div>

            <div id="employeeInfo" class="hidden employee-info rounded-lg p-3">
                <h4 class="font-medium text-blue-900 text-sm mb-2">Informasi Karyawan:</h4>
                <div class="space-y-1 text-xs text-blue-800">
                    <p><span class="font-medium">Posisi:</span> <span id="employeePosition"></span></p>
                    <p><span class="font-medium">Unit:</span> <span id="employeeUnit"></span></p>
                    <p><span class="font-medium">Divisi:</span> <span id="employeeDivisi"></span></p>
                    <p><span class="font-medium">Lokasi:</span> <span id="employeeLocation"></span></p>
                    <p><span class="font-medium">DPW:</span> <span id="employeeDPW"></span></p>
                    <p><span class="font-medium">DPD:</span> <span id="employeeDPD"></span></p>
                </div>
            </div>

            <div id="gptpNotice" class="hidden gptp-notice rounded-lg p-3">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-orange-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <div class="text-sm">
                        <p class="font-medium text-orange-800">Karyawan GPTP - Pre-order Membership</p>
                        <p class="text-orange-700 mt-1">Setelah Mendaftar Sebagai karyawan GPTP, Anda baru akan bisa login setelah <strong>Menjadi karyawan resmi </strong>.</p>
                    </div>
                </div>
            </div>

            <div id="iuranInfoPanel" class="hidden iuran-info-panel rounded-lg p-4">
                <h3 class="text-lg font-semibold text-blue-800 mb-3">Informasi Iuran Keanggotaan</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div class="text-center">
                        <label class="block text-blue-700 font-medium">Iuran Wajib:</label>
                        <span id="iuran-wajib-display" class="text-lg iuran-amount">Rp 25.000</span>
                    </div>
                    
                    <div class="text-center">
                        <label class="block text-blue-700 font-medium">Iuran Sukarela:</label>
                        <span id="iuran-sukarela-display" class="text-lg iuran-amount">Rp 0</span>
                    </div>
                    
                    <div class="text-center">
                        <label class="block text-blue-700 font-medium">Total Iuran/Bulan:</label>
                        <span id="total-iuran-display" class="text-lg iuran-amount total">Rp 25.000</span>
                    </div>
                </div>
                
                <div class="mt-3 text-xs text-blue-600">
                    <p>• Iuran akan dipotong melalui payroll setiap bulan</p>
                    <p>• Iuran sukarela harus dalam kelipatan Rp 5.000</p>
                    <p>• Anda dapat mengubah iuran sukarela setelah terdaftar</p>
                </div>
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-medium mb-1">
                    Iuran Sukarela (kelipatan Rp 5.000)
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
                        class="form-input w-full pl-8 pr-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition duration-200 text-sm"
                    >
                </div>
                <small class="text-gray-500 text-xs">Masukkan nominal dalam kelipatan 5.000 (contoh: 5000, 10000, 15000)</small>
                <div id="iuranError" class="hidden text-red-500 text-xs mt-1"></div>
            </div>

            <div id="agreementSection" class="hidden agreement-section rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Pernyataan Keanggotaan</h3>
                
                <div class="text-sm text-gray-700 leading-relaxed space-y-3">
                    <p>
                        Dengan ini saya menyatakan kesediaan dan kehendak untuk mendaftarkan diri sebagai anggota 
                        <strong>Serikat Karyawan PT. Telekomunikasi Indonesia, Tbk. (SEKAR TELKOM)</strong>.
                    </p>
                    
                    <p>
                        Saya telah mempelajari, memahami, dan menyetujui seluruh hak dan kewajiban sebagai anggota 
                        SEKAR TELKOM sebagaimana tercantum dalam Anggaran Dasar dan Anggaran Rumah Tangga. 
                        Dengan penuh kesadaran, saya menyatakan kesediaan dan kesanggupan untuk membayar iuran 
                        keanggotaan yang akan dipotong melalui sistem payroll setiap bulan selama masa keanggotaan, 
                        dengan rincian:
                    </p>
                    
                    <div class="iuran-breakdown p-3 rounded">
                        <ul class="list-disc pl-5 space-y-1">
                            <li>Iuran Wajib: <strong>Rp 25.000</strong> per bulan</li>
                            <li>Iuran Sukarela: <strong><span id="agreement-sukarela">Rp 0</span></strong> per bulan</li>
                            <li>Total Iuran: <strong><span id="agreement-total">Rp 25.000</span></strong> per bulan</li>
                        </ul>
                    </div>
                    
                    <p>
                        Saya memahami bahwa keanggotaan ini mengikat saya untuk mematuhi seluruh ketentuan 
                        yang berlaku dalam lingkup SEKAR TELKOM.
                    </p>
                </div>
                
                <div class="mt-4 flex items-start">
                    <input type="checkbox" 
                           id="agreement" 
                           name="agreement" 
                           required
                           class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="agreement" class="ml-2 text-sm text-gray-700">
                        Saya telah membaca, memahami, dan menyetujui seluruh pernyataan di atas serta 
                        bersedia mematuhi semua ketentuan keanggotaan SEKAR TELKOM.
                    </label>
                </div>
            </div>

            <button 
                type="button" 
                id="registerBtn"
                class="btn-register w-full bg-blue-600 text-white py-2.5 rounded-lg font-medium hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200 text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                disabled
            >
                Daftar Sekarang
            </button>

            <div class="text-center text-xs text-gray-600 mt-4">
                <span>Sudah punya akun? </span>
                <a href="{{ route('login') }}" class="text-blue-600 hover:underline font-medium text-xs">Login</a>
            </div>
        </form>
    </div>
</div>

<div id="ssoModal" class="fixed inset-0 modal-overlay flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-xl p-8 max-w-md w-full mx-4 shadow-2xl">
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-3a1 1 0 011-1h2.586l6.414-6.414a6 6 0 015.743-7.743z"></path>
                </svg>
            </div>

        </div>

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
            
            <div id="ssoErrorContainer" class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 3a1 1 0 00-1-1H3a1 1 0 00-1 1v12a1 1 0 001 1h12a1 1 0 001-1V3zM8.293 6.293a1 1 0 011.414 0L12 8.586l2.293-2.293a1 1 0 111.414 1.414L13.414 10l2.293 2.293a1 1 0 01-1.414 1.414L12 11.414l-2.293 2.293a1 1 0 01-1.414-1.414L10.586 10 8.293 7.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                    <span id="ssoErrorMessage" class="text-sm"></span>
                </div>
            </div>

            <input type="hidden" id="hiddenNik" name="nik">
            <input type="hidden" id="hiddenName" name="name">
            <input type="hidden" id="hiddenIuran" name="iuran_sukarela">

            <div>
                <label for="sso_password" class="block text-sm font-medium text-gray-700 mb-2">
                    Password Portal
                </label>
                <div class="relative">
                    <input 
                        type="password" 
                        id="sso_password"
                        name="sso_password" 
                        placeholder="Masukkan password portal Anda"
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
            </div>

            <div class="flex space-x-3">
                <button 
                    type="button" 
                    id="cancelSSOBtn"
                    class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition duration-200"
                >
                    Batal
                </button>
                <button 
                    type="submit" 
                    class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200"
                >
                    Konfirmasi
                </button>
            </div>
        </form>

        <div id="ssoLoadingState" class="hidden text-center py-8">
            <div class="inline-flex items-center">
                <svg class="loading-spinner -ml-1 mr-3 h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-blue-600">Memproses pendaftaran...</span>
            </div>
        </div>
    </div>
</div>

<script>
    class CleanRegistration {
        constructor() {
            this.elements = this.getElements();
            this.data = { employee: null, debounceTimer: null };
            this.bindEvents();
            // Panggil method untuk memeriksa status checkbox saat inisialisasi
            this.checkAgreement();
        }

        getElements() {
            return {
                nikInput: document.getElementById('nikInput'),
                nameInput: document.getElementById('nameInput'),
                iuranInput: document.getElementById('iuranInput'),
                registerBtn: document.getElementById('registerBtn'),
                agreement: document.getElementById('agreement'),
                
                loadingState: document.getElementById('loadingState'),
                employeeInfo: document.getElementById('employeeInfo'),
                gptpNotice: document.getElementById('gptpNotice'),
                iuranInfoPanel: document.getElementById('iuranInfoPanel'),
                agreementSection: document.getElementById('agreementSection'),
                alertContainer: document.getElementById('alertContainer'),
                iuranError: document.getElementById('iuranError'),
                
                employeePosition: document.getElementById('employeePosition'),
                employeeUnit: document.getElementById('employeeUnit'),
                employeeDivisi: document.getElementById('employeeDivisi'),
                employeeLocation: document.getElementById('employeeLocation'),
                employeeDPW: document.getElementById('employeeDPW'),
                employeeDPD: document.getElementById('employeeDPD'),
                
                iuranWajibDisplay: document.getElementById('iuran-wajib-display'),
                iuranSukarelaDisplay: document.getElementById('iuran-sukarela-display'),
                totalIuranDisplay: document.getElementById('total-iuran-display'),
                agreementSukarela: document.getElementById('agreement-sukarela'),
                agreementTotal: document.getElementById('agreement-total'),
                
                ssoModal: document.getElementById('ssoModal'),
                ssoForm: document.getElementById('ssoForm'),
                ssoPassword: document.getElementById('sso_password'),
                ssoLoadingState: document.getElementById('ssoLoadingState'),
                ssoErrorContainer: document.getElementById('ssoErrorContainer'),
                ssoErrorMessage: document.getElementById('ssoErrorMessage'),
                toggleSSOPassword: document.getElementById('toggleSSOPassword'),
                eyeIcon: document.getElementById('eyeIcon'),
                cancelSSOBtn: document.getElementById('cancelSSOBtn'),
                
                userInitials: document.getElementById('userInitials'),
                modalUserName: document.getElementById('modalUserName'),
                modalUserNIK: document.getElementById('modalUserNIK'),
                hiddenNik: document.getElementById('hiddenNik'),
                hiddenName: document.getElementById('hiddenName'),
                hiddenIuran: document.getElementById('hiddenIuran')
            };
        }

        bindEvents() {
            this.elements.nikInput.addEventListener('input', (e) => this.handleNikInput(e.target.value.trim()));
            this.elements.iuranInput.addEventListener('input', (e) => this.handleIuranInput(e.target.value));
            this.elements.iuranInput.addEventListener('change', (e) => this.handleIuranChange(e.target.value));
            this.elements.registerBtn.addEventListener('click', () => this.handleRegister());
            
            // Tambahkan event listener untuk checkbox di sini
            this.elements.agreement.addEventListener('change', () => this.checkAgreement());
            
            this.elements.cancelSSOBtn.addEventListener('click', () => this.closeModal());
            this.elements.toggleSSOPassword.addEventListener('click', () => this.togglePassword());
            this.elements.ssoForm.addEventListener('submit', (e) => this.handleSSOSubmit(e));
            this.elements.ssoModal.addEventListener('click', (e) => e.target === this.elements.ssoModal && this.closeModal());
            document.addEventListener('keydown', (e) => e.key === 'Escape' && !this.elements.ssoModal.classList.contains('hidden') && this.closeModal());
        }

        // Buat method baru untuk menangani logika checkbox
        checkAgreement() {
            // Tombol register akan 'disabled' jika checkbox TIDAK dicentang
            // dan jika data karyawan sudah ada
            const isNikValid = !!this.data.employee;
            const isAgreementChecked = this.elements.agreement.checked;
            
            this.elements.registerBtn.disabled = !(isNikValid && isAgreementChecked);
        }

        handleNikInput(nik) {
            this.resetForm();
            clearTimeout(this.data.debounceTimer);
            
            if (nik.length >= 6) {
                this.data.debounceTimer = setTimeout(() => this.fetchEmployee(nik), 800);
            }
        }

        async fetchEmployee(nik) {
            this.showLoading(true);
            
            try {
                const response = await fetch('/api/karyawan-data', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value },
                    body: JSON.stringify({ nik })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.displayEmployee(data.data);
                    this.showAlert('Data karyawan ditemukan!', 'success');
                } else {
                    this.showAlert(data.message, 'error');
                }
            } catch (error) {
                this.showAlert('Terjadi kesalahan saat memuat data karyawan', 'error');
            } finally {
                this.showLoading(false);
            }
        }

        displayEmployee(employee) {
            this.data.employee = employee;
            
            this.elements.nameInput.value = employee.name;
            this.elements.employeePosition.textContent = employee.position;
            this.elements.employeeUnit.textContent = employee.unit;
            this.elements.employeeDivisi.textContent = employee.divisi;
            this.elements.employeeLocation.textContent = employee.location;
            this.elements.employeeDPW.textContent = employee.dpw;
            this.elements.employeeDPD.textContent = employee.dpd;
            
            this.elements.employeeInfo.classList.remove('hidden');
            this.elements.iuranInfoPanel.classList.remove('hidden');
            this.elements.agreementSection.classList.remove('hidden');
            
            if (employee.is_gptp) {
                this.elements.gptpNotice.classList.remove('hidden');
            }
            
            this.updateIuranDisplay();
            
            // Panggil checkAgreement setelah data karyawan berhasil dimuat
            this.checkAgreement(); 
        }

        handleIuranInput(value) {
            const amount = parseInt(value) || 0;
            const error = this.validateIuran(amount);
            
            if (error) {
                this.elements.iuranError.textContent = error;
                this.elements.iuranError.classList.remove('hidden');
                this.elements.iuranInput.classList.add('border-red-500');
            } else {
                this.elements.iuranError.classList.add('hidden');
                this.elements.iuranInput.classList.remove('border-red-500');
            }
            
            this.updateIuranDisplay();
        }

        handleIuranChange(value) {
            let amount = parseInt(value) || 0;
            if (amount > 0) {
                amount = Math.round(amount / 5000) * 5000;
                this.elements.iuranInput.value = amount;
                this.updateIuranDisplay();
            }
        }

        validateIuran(value) {
            if (value < 0) return 'Iuran tidak boleh negatif';
            if (value % 5000 !== 0) return 'Harus kelipatan Rp 5.000';
            if (value > 1000000) return 'Maksimal Rp 1.000.000';
            return null;
        }

        updateIuranDisplay() {
            const wajib = 25000;
            const sukarela = parseInt(this.elements.iuranInput.value) || 0;
            const total = wajib + sukarela;
            
            const format = (amount) => new Intl.NumberFormat('id-ID', {
                style: 'currency', currency: 'IDR', minimumFractionDigits: 0
            }).format(amount);
            
            this.elements.iuranWajibDisplay.textContent = format(wajib);
            this.elements.iuranSukarelaDisplay.textContent = format(sukarela);
            this.elements.totalIuranDisplay.textContent = format(total);
            this.elements.agreementSukarela.textContent = format(sukarela);
            this.elements.agreementTotal.textContent = format(total);
        }

        handleRegister() {
            if (this.elements.registerBtn.disabled) {
                // Jika tombol disabled, jangan lakukan apa-apa, atau beri peringatan
                if (!this.data.employee) {
                    this.showAlert('Silakan masukkan NIK yang valid terlebih dahulu.', 'warning');
                } else if (!this.elements.agreement.checked) {
                    this.showAlert('Anda harus menyetujui pernyataan keanggotaan.', 'warning');
                }
                return;
            }

            const iuranValue = parseInt(this.elements.iuranInput.value) || 0;
            const error = this.validateIuran(iuranValue);
            if (error) {
                this.showAlert(error, 'warning');
                return;
            }

            this.openModal();
        }

        openModal() {
            this.elements.userInitials.textContent = this.data.employee.name.substring(0, 2).toUpperCase();
            this.elements.modalUserName.textContent = this.data.employee.name;
            this.elements.modalUserNIK.textContent = 'NIK: ' + this.data.employee.nik;
            
            this.elements.hiddenNik.value = this.data.employee.nik;
            this.elements.hiddenName.value = this.data.employee.name;
            this.elements.hiddenIuran.value = this.elements.iuranInput.value || '0';
            
            this.elements.ssoModal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            
            setTimeout(() => this.elements.ssoPassword.focus(), 100);
        }

        closeModal() {
            this.elements.ssoModal.classList.add('hidden');
            document.body.style.overflow = 'auto';
            this.elements.ssoForm.reset();
            this.elements.ssoErrorContainer.classList.add('hidden');
        }

        togglePassword() {
            const isPassword = this.elements.ssoPassword.type === 'password';
            this.elements.ssoPassword.type = isPassword ? 'text' : 'password';
            
            this.elements.eyeIcon.innerHTML = isPassword 
                ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>'
                : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>';
        }

        async handleSSOSubmit(e) {
            e.preventDefault();
            
            this.elements.ssoForm.style.display = 'none';
            this.elements.ssoLoadingState.classList.remove('hidden');
            
            try {
                const response = await fetch('{{ route("register.post") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json',
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams(new FormData(this.elements.ssoForm))
                });
                
                const data = await response.json();
                
                if (response.ok && data.success) {
                    window.location.href = '{{ route("dashboard") }}';
                } else {
                    this.showSSOError(data.message || 'Password SSO tidak valid');
                    this.elements.ssoLoadingState.classList.add('hidden');
                    this.elements.ssoForm.style.display = 'block';
                    this.elements.ssoPassword.focus();
                }
            } catch (error) {
                this.showSSOError('Terjadi kesalahan koneksi');
                this.elements.ssoLoadingState.classList.add('hidden');
                this.elements.ssoForm.style.display = 'block';
            }
        }

        showSSOError(message) {
            this.elements.ssoErrorMessage.textContent = message;
            this.elements.ssoErrorContainer.classList.remove('hidden');
        }

        resetForm() {
            this.elements.nameInput.value = '';
            this.elements.employeeInfo.classList.add('hidden');
            this.elements.gptpNotice.classList.add('hidden');
            this.elements.iuranInfoPanel.classList.add('hidden');
            this.elements.agreementSection.classList.add('hidden');
            this.elements.registerBtn.disabled = true; // Selalu disabled saat reset
            this.elements.iuranError.classList.add('hidden');
            this.data.employee = null;
        }

        showLoading(show) {
            if (show) {
                this.elements.loadingState.classList.remove('hidden');
            } else {
                this.elements.loadingState.classList.add('hidden');
            }
        }

        showAlert(message, type = 'info') {
            const alertClasses = {
                'error': 'bg-red-50 border-red-200 text-red-700',
                'success': 'bg-green-50 border-green-200 text-green-700',
                'warning': 'bg-yellow-50 border-yellow-200 text-yellow-700',
                'info': 'bg-blue-50 border-blue-200 text-blue-700'
            };
            
            this.elements.alertContainer.innerHTML = `
                <div class="${alertClasses[type]} px-3 py-2 rounded-lg border">
                    <p class="text-xs">${message}</p>
                </div>
            `;
            this.elements.alertContainer.classList.remove('hidden');
            
            setTimeout(() => {
                this.elements.alertContainer.classList.add('hidden');
            }, 5000);
        }
    }

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', () => {
        new CleanRegistration();
    });
</script>
@endsection
<div id="resignModal" style="display: none;" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-auto">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-start">
                <div class="flex-shrink-0 w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.996-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">Konfirmasi Pengunduran Diri</h3>
                    <p class="text-sm text-gray-600 mt-1">Apakah Anda benar-benar yakin?</p>
                </div>
            </div>
        </div>
        <div class="p-6">
            <p class="text-sm text-gray-700">Tindakan ini akan menonaktifkan akun Anda secara permanen. Data Anda akan dipindahkan ke arsip ex-anggota.</p>
            <div class="mt-4 flex items-start p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                <div class="flex items-center h-5"><input id="confirmResignCheckbox" name="confirm_resign" type="checkbox" onclick="document.getElementById('confirmResignButton').disabled = !this.checked;" class="focus:ring-red-500 h-4 w-4 text-red-600 border-gray-300 rounded"></div>
                <div class="ml-3 text-sm"><label for="confirmResignCheckbox" class="font-medium text-yellow-800">Saya mengerti dan menyetujui konsekuensi dari tindakan ini.</label></div>
            </div>
        </div>
        <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-3">
            <button type="button" onclick="document.getElementById('resignModal').style.display='none'" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition">Batal</button>
            <form action="{{ route('profile.resign') }}" method="POST">
                @csrf
                <button type="submit" id="confirmResignButton" disabled class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition disabled:bg-red-300 disabled:cursor-not-allowed">Ya, Saya Yakin</button>
            </form>
        </div>
    </div>
</div>

<div id="profilePictureModal" style="display: none;" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white p-4 rounded-lg shadow-lg max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-3">Update Foto Profil</h3>
        <div class="text-center mb-3">
            @if($user->profile_picture)<img src="{{ asset('storage/profile-pictures/' . $user->profile_picture) }}" alt="Current Profile Picture" class="w-20 h-20 rounded-full mx-auto object-cover border-2 border-gray-200">
            @else<div class="w-20 h-20 bg-gray-300 rounded-full mx-auto flex items-center justify-center border-2 border-gray-200"><span class="text-xl font-bold text-gray-600">{{ substr($user->name, 0, 1) }}</span></div>@endif
        </div>
        <form method="POST" action="{{ route('profile.update-picture') }}" enctype="multipart/form-data" class="space-y-3">
            @csrf
            <div>
                <label for="profile_picture" class="block text-xs font-medium text-gray-700 mb-1">Pilih Foto Baru</label>
                <input type="file" name="profile_picture" id="profile_picture" accept="image/*" class="w-full px-2 py-1.5 border border-gray-300 rounded text-xs focus:ring-blue-500 focus:border-blue-500">
                <p class="text-xs text-gray-500 mt-0.5">Format: JPEG, PNG, JPG. Maksimal 2MB.</p>
            </div>
            <div class="flex space-x-2">
                <button type="submit" class="flex-1 bg-blue-600 text-white px-3 py-1.5 rounded text-xs hover:bg-blue-700 transition">Upload Foto</button>
                <button type="button" onclick="document.getElementById('profilePictureModal').style.display='none'" class="flex-1 bg-gray-300 text-gray-700 px-3 py-1.5 rounded text-xs hover:bg-gray-400 transition">Batal</button>
            </div>
        </form>
        @if($user->profile_picture)
        <div class="mt-3 pt-3 border-t border-gray-200">
            <form method="POST" action="{{ route('profile.delete-picture') }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus foto profil?')">
                @csrf @method('DELETE')
                <button type="submit" class="w-full bg-red-600 text-white px-3 py-1.5 rounded text-xs hover:bg-red-700 transition">Hapus Foto Profil</button>
            </form>
        </div>
        @endif
    </div>
</div>

<div id="historyModal" style="display: none;" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] flex flex-col">
        <div class="px-6 py-4 border-b border-gray-200"><div class="flex items-center justify-between"><div><h3 class="text-lg font-semibold text-gray-900">Riwayat Perubahan Iuran</h3><p class="text-sm text-gray-600 mt-1" id="historyTotalCount">Memuat data...</p></div><button onclick="closeHistoryModal()" class="text-gray-400 hover:text-gray-600"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button></div></div>
        <div class="p-6 overflow-y-auto flex-grow"><div id="historyLoading" class="text-center py-8"><div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div><p class="text-gray-600 mt-2">Memuat riwayat...</p></div><div id="historyContent" style="display: none;"></div><div id="historyEmpty" style="display: none;" class="text-center py-12"><h4 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Riwayat</h4><p class="text-gray-600">Anda belum pernah mengubah iuran.</p></div></div>
        <div id="historyPagination" style="display: none;" class="px-6 py-4 border-t border-gray-200 bg-gray-50"></div>
    </div>
</div>

<div id="paymentModal" style="display: none;" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-5xl w-full max-h-[90vh] flex flex-col">
        <div class="px-6 py-4 border-b border-gray-200"><div class="flex items-center justify-between"><div><h3 class="text-lg font-semibold text-gray-900">Riwayat Pembayaran Iuran</h3><p class="text-sm text-gray-600 mt-1" id="paymentTotalCount">Memuat data...</p></div><button onclick="closePaymentModal()" class="text-gray-400 hover:text-gray-600"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button></div></div>
        <div class="p-6 overflow-y-auto flex-grow"><div id="paymentLoading" class="text-center py-8"><div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div><p class="text-gray-600 mt-2">Memuat riwayat pembayaran...</p></div><div id="paymentContent" style="display: none;"></div><div id="paymentEmpty" style="display: none;" class="text-center py-12"><h4 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Riwayat Pembayaran</h4><p class="text-gray-600">Data pembayaran akan muncul di sini.</p></div></div>
        <div id="paymentPagination" style="display: none;" class="px-6 py-4 border-t border-gray-200 bg-gray-50"></div>
    </div>
</div><div id="resignModal" style="display: none;" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-auto">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-start">
                <div class="flex-shrink-0 w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.996-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">Konfirmasi Pengunduran Diri</h3>
                    <p class="text-sm text-gray-600 mt-1">Apakah Anda benar-benar yakin?</p>
                </div>
            </div>
        </div>
        <div class="p-6">
            <p class="text-sm text-gray-700">Tindakan ini akan menonaktifkan akun Anda secara permanen. Data Anda akan dipindahkan ke arsip ex-anggota.</p>
            <div class="mt-4 flex items-start p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                <div class="flex items-center h-5"><input id="confirmResignCheckbox" name="confirm_resign" type="checkbox" onclick="document.getElementById('confirmResignButton').disabled = !this.checked;" class="focus:ring-red-500 h-4 w-4 text-red-600 border-gray-300 rounded"></div>
                <div class="ml-3 text-sm"><label for="confirmResignCheckbox" class="font-medium text-yellow-800">Saya mengerti dan menyetujui konsekuensi dari tindakan ini.</label></div>
            </div>
        </div>
        <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-3">
            <button type="button" onclick="document.getElementById('resignModal').style.display='none'" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition">Batal</button>
            <form action="{{ route('profile.resign') }}" method="POST">
                @csrf
                <button type="submit" id="confirmResignButton" disabled class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition disabled:bg-red-300 disabled:cursor-not-allowed">Ya, Saya Yakin</button>
            </form>
        </div>
    </div>
</div>

<div id="profilePictureModal" style="display: none;" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white p-4 rounded-lg shadow-lg max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-3">Update Foto Profil</h3>
        <div class="text-center mb-3">
            @if($user->profile_picture)<img src="{{ asset('storage/profile-pictures/' . $user->profile_picture) }}" alt="Current Profile Picture" class="w-20 h-20 rounded-full mx-auto object-cover border-2 border-gray-200">
            @else<div class="w-20 h-20 bg-gray-300 rounded-full mx-auto flex items-center justify-center border-2 border-gray-200"><span class="text-xl font-bold text-gray-600">{{ substr($user->name, 0, 1) }}</span></div>@endif
        </div>
        <form method="POST" action="{{ route('profile.update-picture') }}" enctype="multipart/form-data" class="space-y-3">
            @csrf
            <div>
                <label for="profile_picture" class="block text-xs font-medium text-gray-700 mb-1">Pilih Foto Baru</label>
                <input type="file" name="profile_picture" id="profile_picture" accept="image/*" class="w-full px-2 py-1.5 border border-gray-300 rounded text-xs focus:ring-blue-500 focus:border-blue-500">
                <p class="text-xs text-gray-500 mt-0.5">Format: JPEG, PNG, JPG. Maksimal 2MB.</p>
            </div>
            <div class="flex space-x-2">
                <button type="submit" class="flex-1 bg-blue-600 text-white px-3 py-1.5 rounded text-xs hover:bg-blue-700 transition">Upload Foto</button>
                <button type="button" onclick="document.getElementById('profilePictureModal').style.display='none'" class="flex-1 bg-gray-300 text-gray-700 px-3 py-1.5 rounded text-xs hover:bg-gray-400 transition">Batal</button>
            </div>
        </form>
        @if($user->profile_picture)
        <div class="mt-3 pt-3 border-t border-gray-200">
            <form method="POST" action="{{ route('profile.delete-picture') }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus foto profil?')">
                @csrf @method('DELETE')
                <button type="submit" class="w-full bg-red-600 text-white px-3 py-1.5 rounded text-xs hover:bg-red-700 transition">Hapus Foto Profil</button>
            </form>
        </div>
        @endif
    </div>
</div>

<div id="historyModal" style="display: none;" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] flex flex-col">
        <div class="px-6 py-4 border-b border-gray-200"><div class="flex items-center justify-between"><div><h3 class="text-lg font-semibold text-gray-900">Riwayat Perubahan Iuran</h3><p class="text-sm text-gray-600 mt-1" id="historyTotalCount">Memuat data...</p></div><button onclick="closeHistoryModal()" class="text-gray-400 hover:text-gray-600"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button></div></div>
        <div class="p-6 overflow-y-auto flex-grow"><div id="historyLoading" class="text-center py-8"><div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div><p class="text-gray-600 mt-2">Memuat riwayat...</p></div><div id="historyContent" style="display: none;"></div><div id="historyEmpty" style="display: none;" class="text-center py-12"><h4 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Riwayat</h4><p class="text-gray-600">Anda belum pernah mengubah iuran.</p></div></div>
        <div id="historyPagination" style="display: none;" class="px-6 py-4 border-t border-gray-200 bg-gray-50"></div>
    </div>
</div>

<div id="paymentModal" style="display: none;" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-5xl w-full max-h-[90vh] flex flex-col">
        <div class="px-6 py-4 border-b border-gray-200"><div class="flex items-center justify-between"><div><h3 class="text-lg font-semibold text-gray-900">Riwayat Pembayaran Iuran</h3><p class="text-sm text-gray-600 mt-1" id="paymentTotalCount">Memuat data...</p></div><button onclick="closePaymentModal()" class="text-gray-400 hover:text-gray-600"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button></div></div>
        <div class="p-6 overflow-y-auto flex-grow"><div id="paymentLoading" class="text-center py-8"><div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div><p class="text-gray-600 mt-2">Memuat riwayat pembayaran...</p></div><div id="paymentContent" style="display: none;"></div><div id="paymentEmpty" style="display: none;" class="text-center py-12"><h4 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Riwayat Pembayaran</h4><p class="text-gray-600">Data pembayaran akan muncul di sini.</p></div></div>
        <div id="paymentPagination" style="display: none;" class="px-6 py-4 border-t border-gray-200 bg-gray-50"></div>
    </div>
</div>
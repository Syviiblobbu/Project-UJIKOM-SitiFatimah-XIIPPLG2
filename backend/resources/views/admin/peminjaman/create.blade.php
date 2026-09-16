@extends('layouts.app')

@section('title', 'Tambah Peminjaman - Panel Admin')
@section('header-title', 'Form Tambah Transaksi Peminjaman')

@section('content')
<div class="max-w-2xl bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    @if(session('error'))
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 p-3 rounded-lg text-sm">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('admin.peminjaman.store') }}" method="POST">
        @csrf

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-semibold mb-2">
                Pilih Peminjam (User)
            </label>

            <!-- Search Peminjam -->
            <input
                type="text"
                id="search-peminjam"
                placeholder="Cari nama atau email peminjam..."
                autocomplete="off"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            >

            <!-- Hasil Search -->
            <div
                id="hasil-peminjam"
                class="hidden mt-1 border border-gray-300 rounded-lg bg-white shadow-sm max-h-48 overflow-y-auto"
            >
                @foreach($users as $user)
                    <button
                        type="button"
                        class="peminjam-option w-full text-left px-3 py-2 hover:bg-gray-100 border-b border-gray-100"
                        data-id="{{ $user->id }}"
                        data-name="{{ $user->name }}"
                        data-email="{{ $user->email }}"
                    >
                        <div class="font-medium text-gray-800">
                            {{ $user->name }}
                        </div>

                        <div class="text-xs text-gray-500">
                            {{ $user->email }}
                        </div>
                    </button>
                @endforeach
            </div>

            <!-- Menyimpan ID User yang dipilih -->
            <input
                type="hidden"
                name="user_id"
                id="user_id"
                value="{{ old('user_id') }}"
                required
            >

            <!-- User yang dipilih -->
            <div
                id="peminjam-terpilih"
                class="{{ old('user_id') ? '' : 'hidden' }} mt-2 p-3 bg-blue-50 border border-blue-200 rounded-lg"
            >
                <div class="text-sm text-blue-800">
                    <span class="font-semibold">Peminjam terpilih:</span>
                    <span id="nama-peminjam-terpilih"></span>
                </div>
            </div>

            <p class="text-xs text-gray-500 mt-1">
                Ketik nama atau email untuk mencari peminjam.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">Tanggal Pinjam</label>
                <input type="date" name="tgl_pinjam" value="{{ old('tgl_pinjam', date('Y-m-d')) }}" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">Rencana Tanggal Kembali</label>
                <input type="date" name="tgl_kembali_plan" value="{{ old('tgl_kembali_plan', date('Y-m-d', strtotime('+3 days'))) }}" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <!-- Bagian Daftar Alat yang Dipinjam (Dinamis) -->
        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-semibold mb-2">Daftar Alat yang Dipinjam</label>
            <div id="alat-container">
                <div class="alat-row flex items-center gap-2 mb-2">
                    <select name="alat_id[]" required class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none">
                        <option value="">-- Pilih Alat --</option>
                        @foreach($alats as $alat)
                            <option value="{{ $alat->id }}">{{ $alat->nama_alat }} (Stok: {{ $alat->stok }})</option>
                        @endforeach
                    </select>
                    <input type="number" name="jumlah[]" value="1" min="1" placeholder="Jumlah" required
                        class="w-24 px-2 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none">
                    <button type="button" onclick="removeRow(this)" class="bg-red-500 text-white px-3 py-2 rounded-lg text-sm hover:bg-red-600 transition">×</button>
                </div>
            </div>

            <button type="button" onclick="addRow()" class="mt-3 bg-gray-800 hover:bg-gray-900 text-white text-sm font-semibold px-3 py-2 rounded-lg transition">
                + Tambah Alat
            </button>
        </div>

        <div class="flex justify-end space-x-2">
            <a href="{{ route('admin.peminjaman.index') }}"
                class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg text-sm font-semibold transition">Batal</a>
            <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">Simpan Peminjaman</button>
        </div>
    </form>
</div>

<!-- Script Sederhana untuk Tambah/Hapus Baris Alat -->
<script>
        // ================================
        // Search Peminjam
        // ================================

        const searchPeminjam = document.getElementById('search-peminjam');
        const hasilPeminjam = document.getElementById('hasil-peminjam');
        const userIdInput = document.getElementById('user_id');
        const peminjamTerpilih = document.getElementById('peminjam-terpilih');
        const namaPeminjamTerpilih = document.getElementById('nama-peminjam-terpilih');

        searchPeminjam.addEventListener('input', function () {
            const keyword = this.value.toLowerCase().trim();
            const options = document.querySelectorAll('.peminjam-option');

            let adaHasil = false;

            options.forEach(function (option) {
                const nama = option.dataset.name.toLowerCase();
                const email = option.dataset.email.toLowerCase();

                if (
                    keyword !== '' &&
                    (nama.includes(keyword) || email.includes(keyword))
                ) {
                    option.classList.remove('hidden');
                    adaHasil = true;
                } else {
                    option.classList.add('hidden');
                }
            });

            if (keyword !== '' && adaHasil) {
                hasilPeminjam.classList.remove('hidden');
            } else {
                hasilPeminjam.classList.add('hidden');
            }
        });

        // Ketika user memilih peminjam
        document.querySelectorAll('.peminjam-option').forEach(function (option) {
            option.addEventListener('click', function () {
                const id = this.dataset.id;
                const nama = this.dataset.name;
                const email = this.dataset.email;

                // Simpan ID user ke input hidden
                userIdInput.value = id;

                // Tampilkan nama user yang dipilih
                searchPeminjam.value = nama + ' (' + email + ')';

                namaPeminjamTerpilih.textContent = nama + ' (' + email + ')';
                peminjamTerpilih.classList.remove('hidden');

                // Sembunyikan hasil pencarian
                hasilPeminjam.classList.add('hidden');
            });
        });

        // Kalau user klik di luar area search
        document.addEventListener('click', function (event) {
            if (
                !searchPeminjam.contains(event.target) &&
                !hasilPeminjam.contains(event.target)
            ) {
                hasilPeminjam.classList.add('hidden');
            }
        });
    function addRow() {
        const container = document.getElementById('alat-container');
        const firstRow = container.querySelector('.alat-row');
        const newRow = firstRow.cloneNode(true);
        newRow.querySelector('select').value = '';
        newRow.querySelector('input').value = '1';
        container.appendChild(newRow);
    }

    function removeRow(button) {
        const rows = document.querySelectorAll('.alat-row');
        if (rows.length > 1) {
            button.closest('.alat-row').remove();
        } else {
            alert('Minimal harus ada 1 alat yang dipilih.');
        }
    }
</script>
@endsection
@extends('layouts.app')

@section('title', 'Edit Pengembalian - Panel Admin')

@section('header-title', 'Edit Data Pengembalian')

@section('content')

    <div class="max-w-2xl mx-auto">

        {{-- PESAN ERROR VALIDASI --}}
        @if($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 text-red-800 p-4 rounded-lg shadow-sm text-sm">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">

            {{-- HEADER --}}
            <div class="p-5 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-bold text-gray-800">
                    Edit Pengembalian
                </h3>
                <p class="text-sm text-gray-500 mt-1">
                    Ubah data pengembalian barang.
                </p>
            </div>

            {{-- INFORMASI PEMINJAMAN --}}
            <div class="p-5 border-b border-gray-200">
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">

                    <div class="mb-3">
                        <span class="text-sm font-semibold text-gray-700">
                            Peminjam
                        </span>
                        <p class="text-sm text-gray-600 mt-1">
                            {{ $pengembalian->peminjaman->user->name ?? 'User Dihapus' }}
                        </p>
                    </div>

                    <div class="mb-3">
                        <span class="text-sm font-semibold text-gray-700">
                            Tanggal Pengembalian
                        </span>
                        <p class="text-sm text-gray-600 mt-1">
                            {{ $pengembalian->tgl_kembali }}
                        </p>
                    </div>

                    <div>
                        <span class="text-sm font-semibold text-gray-700">
                            Alat yang Dipinjam
                        </span>

                        <ul class="list-disc list-inside mt-1 text-sm text-gray-600">
                            @if($pengembalian->peminjaman)
                                @foreach($pengembalian->peminjaman->detailPinjam as $detail)
                                    <li>
                                        {{ $detail->alat->nama_alat ?? 'Alat Dihapus' }}
                                        ({{ $detail->jumlah }} pcs)
                                    </li>
                                @endforeach
                            @endif
                        </ul>
                    </div>

                </div>
            </div>

            {{-- FORM EDIT --}}
            <form
                action="{{ route('admin.pengembalian.update', $pengembalian->id) }}"
                method="POST"
                class="p-5"
            >
                @csrf
                @method('PUT')

                {{-- KONDISI --}}
                <div class="mb-5">
                    <label
                        for="kondisi_kembali"
                        class="block text-sm font-semibold text-gray-700 mb-2"
                    >
                        Kondisi Barang Saat Kembali
                    </label>

                    <select
                        id="kondisi_kembali"
                        name="kondisi_kembali"
                        required
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">
                            -- Pilih Kondisi --
                        </option>

                        <option
                            value="Lengkap dan Berfungsi Baik"
                            {{ old('kondisi_kembali', $pengembalian->kondisi_kembali) == 'Lengkap dan Berfungsi Baik' ? 'selected' : '' }}
                        >
                            Lengkap dan Berfungsi Baik
                        </option>

                        <option
                            value="Lengkap, Casing Sedikit Tergores"
                            {{ old('kondisi_kembali', $pengembalian->kondisi_kembali) == 'Lengkap, Casing Sedikit Tergores' ? 'selected' : '' }}
                        >
                            Lengkap, Casing Sedikit Tergores
                        </option>

                        <option
                            value="Ada Kerusakan"
                            {{ old('kondisi_kembali', $pengembalian->kondisi_kembali) == 'Ada Kerusakan' ? 'selected' : '' }}
                        >
                            Ada Kerusakan
                        </option>

                        <option
                            value="Tidak Lengkap"
                            {{ old('kondisi_kembali', $pengembalian->kondisi_kembali) == 'Tidak Lengkap' ? 'selected' : '' }}
                        >
                            Tidak Lengkap
                        </option>
                    </select>
                </div>

                {{-- DENDA KERUSAKAN --}}
                <div class="mb-5">
                    <label
                        for="denda_kerusakan"
                        class="block text-sm font-semibold text-gray-700 mb-2"
                    >
                        Denda Kerusakan
                    </label>

                    <div class="relative">
                        <span class="absolute left-3 top-2 text-sm text-gray-500">
                            Rp
                        </span>

                        <input
                            type="number"
                            id="denda_kerusakan"
                            name="denda_kerusakan"
                            value="{{ old('denda_kerusakan', $pengembalian->denda_kerusakan ?? 0) }}"
                            min="0"
                            class="w-full pl-10 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                    </div>

                    <p class="text-xs text-gray-500 mt-1">
                        Isi 0 jika tidak ada kerusakan.
                    </p>
                </div>

                {{-- DENDA TOTAL --}}
                <div class="mb-5">
                    <label
                        for="denda"
                        class="block text-sm font-semibold text-gray-700 mb-2"
                    >
                        Total Denda
                    </label>

                    <div class="relative">
                        <span class="absolute left-3 top-2 text-sm text-gray-500">
                            Rp
                        </span>

                        <input
                            type="number"
                            id="denda"
                            name="denda"
                            value="{{ old('denda', $pengembalian->denda ?? 0) }}"
                            min="0"
                            required
                            class="w-full pl-10 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                    </div>

                    <p class="text-xs text-gray-500 mt-1">
                        Masukkan total denda pengembalian.
                    </p>
                </div>

                {{-- TOMBOL --}}
                <div class="flex justify-end gap-2">

                    <a
                        href="{{ route('admin.pengembalian.index') }}"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 text-sm font-semibold rounded-lg transition"
                    >
                        Batal
                    </a>

                    <button
                        type="submit"
                        onclick="return confirm('Yakin ingin menyimpan perubahan data pengembalian ini?')"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-sm font-semibold rounded-lg transition"
                    >
                        Simpan Perubahan
                    </button>

                </div>

            </form>

        </div>

    </div>

@endsection
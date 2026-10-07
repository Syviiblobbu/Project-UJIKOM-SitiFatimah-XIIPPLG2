@extends('layouts.app')

@section('title', 'Dashboard Admin - Sistem Peminjaman')

@section('header-title', 'Dashboard Admin')

@section('content')

    <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-lg shadow-sm">
        Selamat datang,
        <strong class="font-semibold">{{ auth()->user()->name }}</strong>!
        Anda login sebagai hak akses
        <span class="uppercase font-bold text-emerald-900">
            {{ auth()->user()->role }}
        </span>.
    </div>

    <div class="mb-6">
        <h3 class="text-lg font-bold text-gray-800 mb-1">
            Ringkasan Data Sistem
        </h3>
        <p class="text-sm text-gray-500">
            Informasi jumlah data yang terdapat dalam sistem peminjaman.
        </p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">

        {{-- User --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">
                        Total User
                    </p>
                    <p class="text-3xl font-bold text-gray-800 mt-2">
                        {{ $jumlahUser }}
                    </p>
                </div>

                <div class="bg-blue-100 text-blue-600 p-3 rounded-lg">
                    <span class="text-xl">👤</span>
                </div>
            </div>
        </div>

        {{-- Kategori --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">
                        Total Kategori
                    </p>
                    <p class="text-3xl font-bold text-gray-800 mt-2">
                        {{ $jumlahKategori }}
                    </p>
                </div>

                <div class="bg-purple-100 text-purple-600 p-3 rounded-lg">
                    <span class="text-xl">🗂️</span>
                </div>
            </div>
        </div>

        {{-- Alat --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">
                        Total Alat
                    </p>
                    <p class="text-3xl font-bold text-gray-800 mt-2">
                        {{ $jumlahAlat }}
                    </p>
                </div>

                <div class="bg-orange-100 text-orange-600 p-3 rounded-lg">
                    <span class="text-xl">🔧</span>
                </div>
            </div>
        </div>

        {{-- Peminjaman --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">
                        Total Peminjaman
                    </p>
                    <p class="text-3xl font-bold text-gray-800 mt-2">
                        {{ $jumlahPeminjaman }}
                    </p>
                </div>

                <div class="bg-emerald-100 text-emerald-600 p-3 rounded-lg">
                    <span class="text-xl">📋</span>
                </div>
            </div>
        </div>

        {{-- Pengembalian --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">
                        Total Pengembalian
                    </p>
                    <p class="text-3xl font-bold text-gray-800 mt-2">
                        {{ $jumlahPengembalian }}
                    </p>
                </div>

                <div class="bg-yellow-100 text-yellow-600 p-3 rounded-lg">
                    <span class="text-xl">↩️</span>
                </div>
            </div>
        </div>

    </div>

    {{-- Pemberitahuan --}}
    <div class="mt-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">

            <div class="p-5 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-bold text-gray-800">
                    🔔 Pemberitahuan
                </h3>
                <p class="text-sm text-gray-500 mt-1">
                    Informasi peminjaman yang membutuhkan perhatian.
                </p>
            </div>

            <div class="divide-y divide-gray-200">

                {{-- Peminjaman Terlambat --}}
                @if($peminjamanTerlambat > 0)
                    <div class="p-4 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="bg-red-100 text-red-600 p-3 rounded-lg">
                                <span class="text-xl">⚠️</span>
                            </div>

                            <div>
                                <p class="font-semibold text-gray-800">
                                    {{ $peminjamanTerlambat }} Peminjaman Terlambat
                                </p>
                                <p class="text-sm text-gray-500">
                                    Peminjaman telah melewati batas waktu pengembalian.
                                </p>
                            </div>
                        </div>

                        <a
                            href="{{ route('admin.peminjaman.index') }}"
                            class="text-sm font-semibold text-blue-600 hover:text-blue-800"
                        >
                            Lihat →
                        </a>
                    </div>
                @endif

                {{-- Menunggu Persetujuan --}}
                @if($peminjamanMenunggu > 0)
                    <div class="p-4 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="bg-yellow-100 text-yellow-600 p-3 rounded-lg">
                                <span class="text-xl">🕐</span>
                            </div>

                            <div>
                                <p class="font-semibold text-gray-800">
                                    {{ $peminjamanMenunggu }} Menunggu Persetujuan
                                </p>
                                <p class="text-sm text-gray-500">
                                    Pengajuan peminjaman perlu diperiksa dan disetujui.
                                </p>
                            </div>
                        </div>

                        <a
                            href="{{ route('admin.peminjaman.index') }}"
                            class="text-sm font-semibold text-blue-600 hover:text-blue-800"
                        >
                            Lihat →
                        </a>
                    </div>
                @endif

                {{-- Tidak ada pemberitahuan --}}
                @if($peminjamanTerlambat == 0 && $peminjamanMenunggu == 0)
                    <div class="p-6 text-center">
                        <div class="text-3xl mb-2">✓</div>
                        <p class="font-semibold text-gray-700">
                            Tidak ada pemberitahuan
                        </p>
                        <p class="text-sm text-gray-500 mt-1">
                            Semua peminjaman dalam kondisi normal.
                        </p>
                    </div>
                @endif

            </div>
        </div>
    </div>

@endsection
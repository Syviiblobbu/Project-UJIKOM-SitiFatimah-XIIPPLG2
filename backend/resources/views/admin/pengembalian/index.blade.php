@extends('layouts.app')

@section('title', 'Kelola Pengembalian - Panel Admin')

@section('header-title', 'Manajemen Transaksi Pengembalian')

@section('content')

    {{-- ========================================================= --}}
    {{-- PESAN SUCCESS / ERROR --}}
    {{-- ========================================================= --}}

    @if(session('success'))
        <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-lg shadow-sm text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 p-4 rounded-lg shadow-sm text-sm">
            {{ session('error') }}
        </div>
    @endif


    {{-- ========================================================= --}}
    {{-- PEMINJAMAN YANG BELUM DIKEMBALIKAN --}}
    {{-- ========================================================= --}}

    <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200 mb-6">

        <div class="p-5 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-bold text-gray-800">
                Proses Pengembalian
            </h3>

            <p class="text-sm text-gray-500 mt-1">
                Daftar peminjaman yang masih aktif dan dapat diproses pengembaliannya.
            </p>
        </div>

        <div class="overflow-x-auto">

            <table class="w-full text-left border-collapse">

                <thead>
                    <tr class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">

                        <th class="py-3 px-4 border-b">
                            Peminjam
                        </th>

                        <th class="py-3 px-4 border-b">
                            Alat yang Dipinjam
                        </th>

                        <th class="py-3 px-4 border-b">
                            Tgl Pinjam / Rencana Kembali
                        </th>

                        <th class="py-3 px-4 border-b">
                            Aksi
                        </th>

                    </tr>
                </thead>

                <tbody class="text-gray-700 text-sm">

                    @forelse($peminjamanAktif as $peminjaman)

                        <tr class="hover:bg-gray-50 transition align-top">

                            {{-- Peminjam --}}
                            <td class="py-3 px-4 border-b font-medium text-gray-900">

                                {{ $peminjaman->user->name ?? 'User Dihapus' }}

                            </td>


                            {{-- Alat --}}
                            <td class="py-3 px-4 border-b">

                                <ul class="list-disc list-inside space-y-1">

                                    @foreach($peminjaman->detailPinjam as $detail)

                                        <li>

                                            <span class="font-semibold">
                                                {{ $detail->alat->nama_alat ?? 'Alat Dihapus' }}
                                            </span>

                                            <span class="text-xs bg-gray-200 px-1.5 py-0.5 rounded">
                                                {{ $detail->jumlah }} pcs
                                            </span>

                                        </li>

                                    @endforeach

                                </ul>

                            </td>


                            {{-- Tanggal --}}
                            <td class="py-3 px-4 border-b text-xs text-gray-600">

                                <span class="block">
                                    Pinjam: {{ $peminjaman->tgl_pinjam }}
                                </span>

                                <span class="block font-semibold">
                                    Rencana: {{ $peminjaman->tgl_kembali_plan }}
                                </span>

                            </td>


                            {{-- Aksi --}}
                            <td class="py-3 px-4 border-b">

                                <button
                                    type="button"
                                    onclick="document.getElementById('modal-{{ $peminjaman->id }}').classList.remove('hidden')"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded text-xs font-semibold transition"
                                >
                                    Proses Pengembalian
                                </button>

                            </td>

                        </tr>


                        {{-- ================================================= --}}
                        {{-- MODAL PROSES PENGEMBALIAN --}}
                        {{-- ================================================= --}}

                        <div
                            id="modal-{{ $peminjaman->id }}"
                            class="hidden fixed inset-0 z-50 overflow-y-auto"
                        >

                            <div class="flex items-center justify-center min-h-screen px-4">

                                {{-- Background --}}
                                <div
                                    class="fixed inset-0 bg-black bg-opacity-50"
                                    onclick="document.getElementById('modal-{{ $peminjaman->id }}').classList.add('hidden')"
                                ></div>


                                {{-- Modal --}}
                                <div class="relative bg-white rounded-lg shadow-xl w-full max-w-lg p-6">

                                    <div class="flex justify-between items-center mb-5">

                                        <div>
                                            <h3 class="text-lg font-bold text-gray-800">
                                                Proses Pengembalian
                                            </h3>

                                            <p class="text-sm text-gray-500">
                                                {{ $peminjaman->user->name ?? 'User Dihapus' }}
                                            </p>
                                        </div>

                                        <button
                                            type="button"
                                            onclick="document.getElementById('modal-{{ $peminjaman->id }}').classList.add('hidden')"
                                            class="text-gray-400 hover:text-gray-600 text-xl"
                                        >
                                            &times;
                                        </button>

                                    </div>


                                    {{-- Informasi Peminjaman --}}
                                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-5">

                                        <div class="text-sm text-gray-600">

                                            <div class="mb-2">
                                                <span class="font-semibold text-gray-800">
                                                    Tanggal Pinjam:
                                                </span>

                                                {{ $peminjaman->tgl_pinjam }}
                                            </div>

                                            <div class="mb-2">
                                                <span class="font-semibold text-gray-800">
                                                    Rencana Kembali:
                                                </span>

                                                {{ $peminjaman->tgl_kembali_plan }}
                                            </div>

                                            <div>

                                                <span class="font-semibold text-gray-800">
                                                    Alat:
                                                </span>

                                                <ul class="list-disc list-inside mt-1">

                                                    @foreach($peminjaman->detailPinjam as $detail)

                                                        <li>
                                                            {{ $detail->alat->nama_alat ?? 'Alat Dihapus' }}
                                                            ({{ $detail->jumlah }} pcs)
                                                        </li>

                                                    @endforeach

                                                </ul>

                                            </div>

                                        </div>

                                    </div>


                                    {{-- Form --}}
                                    <form
                                        action="{{ route('admin.pengembalian.proses', $peminjaman->id) }}"
                                        method="POST"
                                    >

                                        @csrf


                                        {{-- Kondisi --}}
                                        <div class="mb-4">

                                            <label
                                                for="kondisi_kembali_{{ $peminjaman->id }}"
                                                class="block text-sm font-semibold text-gray-700 mb-2"
                                            >
                                                Kondisi Barang Saat Kembali
                                            </label>

                                            <select
                                                id="kondisi_kembali_{{ $peminjaman->id }}"
                                                name="kondisi_kembali"
                                                required
                                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            >

                                                <option value="">
                                                    -- Pilih Kondisi --
                                                </option>

                                                <option value="Lengkap dan Berfungsi Baik">
                                                    Lengkap dan Berfungsi Baik
                                                </option>

                                                <option value="Lengkap, Casing Sedikit Tergores">
                                                    Lengkap, Casing Sedikit Tergores
                                                </option>

                                                <option value="Ada Kerusakan">
                                                    Ada Kerusakan
                                                </option>

                                                <option value="Tidak Lengkap">
                                                    Tidak Lengkap
                                                </option>

                                            </select>

                                        </div>


                                        {{-- Denda Kerusakan --}}
                                        <div class="mb-5">

                                            <label
                                                for="denda_kerusakan_{{ $peminjaman->id }}"
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
                                                    id="denda_kerusakan_{{ $peminjaman->id }}"
                                                    name="denda_kerusakan"
                                                    value="0"
                                                    min="0"
                                                    class="w-full pl-10 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                >

                                            </div>

                                            <p class="text-xs text-gray-500 mt-1">
                                                Isi 0 jika tidak ada kerusakan.
                                            </p>

                                        </div>


                                        {{-- Tombol --}}
                                        <div class="flex justify-end gap-2">

                                            <button
                                                type="button"
                                                onclick="document.getElementById('modal-{{ $peminjaman->id }}').classList.add('hidden')"
                                                class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 text-sm font-semibold rounded-lg transition"
                                            >
                                                Batal
                                            </button>

                                            <button
                                                type="submit"
                                                onclick="return confirm('Yakin ingin memproses pengembalian ini?')"
                                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-sm font-semibold rounded-lg transition"
                                            >
                                                Simpan Pengembalian
                                            </button>

                                        </div>

                                    </form>

                                </div>

                            </div>

                        </div>

                    @empty

                        <tr>

                            <td
                                colspan="4"
                                class="py-6 text-center text-gray-500"
                            >
                                Tidak ada peminjaman yang sedang diproses.

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>



    {{-- ========================================================= --}}
    {{-- RIWAYAT PENGEMBALIAN --}}
    {{-- ========================================================= --}}

    <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">

        <div class="p-5 border-b border-gray-200 bg-gray-50 flex flex-col md:flex-row justify-between items-center gap-4">

            <h3 class="text-lg font-bold text-gray-800">
                Riwayat Pengembalian
            </h3>


            {{-- SEARCH --}}
            <div class="flex items-center gap-3 w-full md:w-auto">

                <form
                    action="{{ route('admin.pengembalian.index') }}"
                    method="GET"
                    class="flex w-full md:w-80"
                >

                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari nama peminjam / kondisi..."
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >

                    <button
                        type="submit"
                        class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 text-sm font-semibold rounded-lg transition"
                    >
                        Cari
                    </button>

                    @if(request('search'))

                        <a
                            href="{{ route('admin.pengembalian.index') }}"
                            class="ml-2 bg-gray-300 hover:bg-gray-400 text-gray-700 px-3 py-2 text-sm rounded-lg flex items-center transition"
                        >
                            Reset
                        </a>

                    @endif

                </form>

            </div>

        </div>


        {{-- TABEL RIWAYAT --}}

        <div class="overflow-x-auto">

            <table class="w-full text-left border-collapse">

                <thead>

                    <tr class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">

                        <th class="py-3 px-4 border-b">
                            Peminjam
                        </th>

                        <th class="py-3 px-4 border-b">
                            Alat
                        </th>

                        <th class="py-3 px-4 border-b">
                            Tgl Pengembalian
                        </th>

                        <th class="py-3 px-4 border-b">
                            Kondisi
                        </th>

                        <th class="py-3 px-4 border-b">
                            Denda
                        </th>

                        <th class="py-3 px-4 border-b">
                            Petugas
                        </th>

                    </tr>

                </thead>


                <tbody class="text-gray-700 text-sm">

                    @forelse($pengembalians as $pengembalian)

                        <tr class="hover:bg-gray-50 transition align-top">

                            {{-- Peminjam --}}
                            <td class="py-3 px-4 border-b font-medium text-gray-900">

                                {{ $pengembalian->peminjaman->user->name ?? 'User Dihapus' }}

                            </td>


                            {{-- Alat --}}
                            <td class="py-3 px-4 border-b">

                                <ul class="list-disc list-inside space-y-1">

                                    @if($pengembalian->peminjaman)

                                        @foreach($pengembalian->peminjaman->detailPinjam as $detail)

                                            <li>

                                                <span class="font-semibold">
                                                    {{ $detail->alat->nama_alat ?? 'Alat Dihapus' }}
                                                </span>

                                                <span class="text-xs bg-gray-200 px-1.5 py-0.5 rounded">
                                                    {{ $detail->jumlah }} pcs
                                                </span>

                                            </li>

                                        @endforeach

                                    @endif

                                </ul>

                            </td>


                            {{-- Tanggal --}}
                            <td class="py-3 px-4 border-b text-xs text-gray-600">

                                {{ $pengembalian->tgl_kembali }}

                            </td>


                            {{-- Kondisi --}}
                            <td class="py-3 px-4 border-b">

                                <span class="text-xs text-gray-700">

                                    {{ $pengembalian->kondisi_kembali }}

                                </span>

                            </td>


                            {{-- Denda --}}
                            <td class="py-3 px-4 border-b">

                                @if($pengembalian->denda > 0)

                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">

                                        Rp {{ number_format($pengembalian->denda, 0, ',', '.') }}

                                    </span>

                                @else

                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800">

                                        Tidak Ada

                                    </span>

                                @endif

                            </td>


                            {{-- Petugas --}}
                            <td class="py-3 px-4 border-b text-sm">

                                {{ $pengembalian->petugas->name ?? 'Petugas Dihapus' }}

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="6"
                                class="py-6 text-center text-gray-500"
                            >

                                @if(request('search'))

                                    Tidak ditemukan data pengembalian dengan kata kunci:
                                    <span class="font-semibold">
                                        "{{ request('search') }}"
                                    </span>

                                @else

                                    Belum ada data pengembalian.

                                @endif

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- PAGINATION --}}

        <div class="p-4 border-t border-gray-200 bg-gray-50">

            {{ $pengembalians->links() }}

        </div>

    </div>

@endsection
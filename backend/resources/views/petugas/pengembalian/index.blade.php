
@extends('layouts.app')

@section('title', 'Pemantauan Pengembalian - Dashboard Petugas')
@section('header-title', 'Pemantauan & Proses Pengembalian Alat')

@section('content')
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

    <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">

        {{-- Header dan Pencarian --}}
        <div class="p-5 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-bold text-gray-800 mb-3">
                Daftar Peminjaman Aktif (Belum Kembali)
            </h3>

            <form action="{{ route('petugas.pengembalian.index') }}"
                method="GET"
                class="flex w-full md:w-80">

                <input type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Cari nama peminjam..."
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">

                <button type="submit"
                    class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 text-sm font-semibold rounded-r-lg transition">
                    Cari
                </button>

                @if(request('search'))
                    <a href="{{ route('petugas.pengembalian.index') }}"
                        class="ml-2 bg-gray-300 hover:bg-gray-400 text-gray-700 px-3 py-2 text-sm rounded-lg flex items-center transition">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        {{-- Tabel Peminjaman --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">

                <thead>
                    <tr class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">
                        <th class="py-3 px-4 border-b">Peminjam</th>
                        <th class="py-3 px-4 border-b">Tgl Pinjam</th>
                        <th class="py-3 px-4 border-b">Rencana Kembali</th>
                        <th class="py-3 px-4 border-b">Status</th>
                        <th class="py-3 px-4 border-b">Detail Alat</th>
                        <th class="py-3 px-4 border-b text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody class="text-gray-700 text-sm">

                    @forelse($peminjamans as $item)
                        <tr class="hover:bg-gray-50 transition align-top">

                            <td class="py-3 px-4 border-b font-medium text-gray-900">
                                {{ $item->user->name ?? 'User Dihapus' }}
                            </td>

                            <td class="py-3 px-4 border-b">
                                {{ $item->tgl_pinjam?->format('d/m/Y') ?? '-' }}
                            </td>

                            <td class="py-3 px-4 border-b">
                                {{ $item->tgl_kembali_plan?->format('d/m/Y') ?? '-' }}
                            </td>

                            <td class="py-3 px-4 border-b">
                                <span class="px-2.5 py-1 rounded text-xs font-semibold
                                    {{ $item->status == 'telat'
                                        ? 'bg-red-100 text-red-700'
                                        : 'bg-blue-100 text-blue-700' }}">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>

                            <td class="py-3 px-4 border-b">
                                <ul class="list-disc list-inside space-y-1 text-xs">
                                    @foreach($item->detailPinjam as $detail)
                                        <li>
                                            <span class="font-semibold">
                                                {{ $detail->alat->nama_alat ?? 'Alat Dihapus' }}
                                            </span>
                                            (Jumlah: {{ $detail->jumlah }})
                                        </li>
                                    @endforeach
                                </ul>
                            </td>

                            {{-- Tombol Aksi --}}
                            <td class="py-3 px-4 border-b text-center">

                                @if(
                                    $item->pengembalian &&
                                    $item->pengembalian->status_pengajuan === 'menunggu'
                                )
                                    <button type="button"
                                        onclick="document.getElementById('modal-pengembalian-{{ $item->id }}').classList.remove('hidden')"
                                        class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-2 rounded-lg text-xs font-semibold transition shadow-sm">
                                        <i class="fas fa-clipboard-check mr-1"></i>
                                        Proses
                                    </button>
                                @else
                                    <span class="text-xs text-gray-500">
                                        Belum ada permintaan
                                    </span>
                                @endif

                            </td>

                        </tr>

                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-gray-500">
                                Tidak ada peminjaman yang sedang aktif saat ini.
                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>
    </div>


    {{-- Modal Form Pengembalian --}}
    @foreach($peminjamans as $item)

        @if(
            $item->pengembalian &&
            $item->pengembalian->status_pengajuan === 'menunggu'
        )

            <div id="modal-pengembalian-{{ $item->id }}"
                class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4"
                onclick="this.classList.add('hidden')">

                <div class="bg-white rounded-xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto"
                    onclick="event.stopPropagation()">

                    {{-- Header Modal --}}
                    <div class="p-5 border-b border-gray-200 flex justify-between items-center sticky top-0 bg-white z-10">

                        <div>
                            <h3 class="text-lg font-bold text-gray-800">
                                Form Pengembalian
                            </h3>

                            <p class="text-xs text-gray-500 mt-1">
                                Peminjam: {{ $item->user->name ?? 'User Dihapus' }}
                            </p>
                        </div>

                        <button type="button"
                            onclick="document.getElementById('modal-pengembalian-{{ $item->id }}').classList.add('hidden')"
                            class="text-gray-400 hover:text-red-500 text-xl font-bold">
                            &times;
                        </button>

                    </div>

                    {{-- Isi Form --}}
                    <form action="{{ route('petugas.pengembalian.proses', $item->id) }}"
                        method="POST"
                        class="p-5 space-y-4">

                        @csrf

                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-xs text-blue-800">
                            Silakan periksa kondisi setiap alat dan isi denda kerusakan jika diperlukan.
                        </div>

                        @foreach($item->detailPinjam as $detail)

                            <div class="border border-gray-200 rounded-lg p-4 space-y-3">

                                <div class="flex justify-between items-center">
                                    <h4 class="font-bold text-sm text-gray-800">
                                        {{ $detail->alat->nama_alat ?? 'Alat Dihapus' }}
                                    </h4>

                                    <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">
                                        Jumlah: {{ $detail->jumlah }}
                                    </span>
                                </div>

                                {{-- Kondisi Kembali --}}
                                <div>
                                    <label
                                        for="kondisi-{{ $detail->id }}"
                                        class="block text-xs font-semibold text-gray-600 mb-1">
                                        Kondisi Kembali
                                    </label>

                                    <select
                                        id="kondisi-{{ $detail->id }}"
                                        name="detail[{{ $detail->id }}][kondisi_kembali]"
                                        required
                                        class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500">

                                        <option value="">-- Pilih Kondisi --</option>

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
                                <div>
                                    <label
                                        for="denda-{{ $detail->id }}"
                                        class="block text-xs font-semibold text-gray-600 mb-1">
                                        Denda Kerusakan (Rp)
                                    </label>

                                    <input
                                        type="number"
                                        id="denda-{{ $detail->id }}"
                                        name="detail[{{ $detail->id }}][denda_kerusakan]"
                                        value="0"
                                        min="0"
                                        step="1000"
                                        required
                                        class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500">

                                    <p class="text-xs text-gray-500 mt-1">
                                        Isi 0 jika tidak ada kerusakan.
                                    </p>
                                </div>

                            </div>

                        @endforeach

                        {{-- Footer Modal --}}
                        <div class="flex justify-end gap-2 pt-2">

                            <button type="button"
                                onclick="document.getElementById('modal-pengembalian-{{ $item->id }}').classList.add('hidden')"
                                class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-semibold transition">
                                Batal
                            </button>

                            <button type="submit"
                                onclick="return confirm('Proses pengembalian semua alat ini?')"
                                class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
                                Terima Pengembalian
                            </button>

                        </div>

                    </form>

                </div>
            </div>

        @endif

    @endforeach

@endsection

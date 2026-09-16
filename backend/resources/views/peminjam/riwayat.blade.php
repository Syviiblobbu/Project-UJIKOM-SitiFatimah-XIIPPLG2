<!DOCTYPE html>

<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Riwayat Peminjaman - Peminjam</title>

    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 min-h-screen">

    {{-- Navbar --}}
    <nav class="bg-blue-600 shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex items-center justify-between h-16">

                {{-- Logo / Nama Panel --}}
                <div>
                    <a href="{{ route('peminjam.katalog') }}"
                       class="text-white text-lg font-semibold">
                        Panel Peminjam
                    </a>
                </div>

                {{-- Menu --}}
                <div class="flex items-center gap-2">

                    <a href="{{ route('peminjam.katalog') }}"
                       class="px-4 py-2 text-sm font-medium text-white border border-white/40 rounded-lg hover:bg-white/10 transition">
                        Katalog Alat
                    </a>

                    <a href="{{ route('peminjam.riwayat') }}"
                       class="px-4 py-2 text-sm font-medium text-blue-600 bg-white rounded-lg">
                        Riwayat Pinjam
                    </a>

                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf

                        <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-blue-600 bg-white rounded-lg hover:bg-gray-100 transition">
                            Logout
                        </button>
                    </form>

                </div>

            </div>

        </div>
    </nav>


    {{-- Content --}}
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Notifikasi Sukses --}}
        @if(session('success'))
            <div class="mb-5 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
        @endif


        {{-- Notifikasi Error --}}
        @if(session('error'))
            <div class="mb-5 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                {{ session('error') }}
            </div>
        @endif


        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">

            <div>
                <h1 class="text-2xl font-bold text-gray-800">
                    Riwayat Peminjaman
                </h1>

                <p class="text-sm text-gray-500 mt-1">
                    Daftar pengajuan dan peminjaman alat Anda.
                </p>
            </div>

            <a href="{{ route('peminjam.katalog') }}"
               class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg text-sm font-semibold transition">
                + Ajukan Peminjaman
            </a>

        </div>


        {{-- Daftar Peminjaman --}}
        @forelse($peminjamans as $index => $item)

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-5">

                {{-- Header Card --}}
                <div class="px-5 py-4 bg-blue-50 border-b border-blue-100">

                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">

                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide">
                                Peminjaman
                            </p>

                            <h2 class="text-lg font-bold text-gray-800">
                                #{{ $index + 1 }}
                            </h2>
                        </div>


                        {{-- Status --}}
                        <div>

                            @if($item->status === 'diajukan')

                                <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                                    Diajukan
                                </span>

                            @elseif($item->status === 'disetujui')

                                <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                    Disetujui
                                </span>

                            @elseif($item->status === 'dipinjam')

                                <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800">
                                    Sedang Dipinjam
                                </span>

                            @elseif($item->status === 'dikembalikan' || $item->status === 'selesai')

                                <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                    Selesai
                                </span>

                            @elseif($item->status === 'ditolak')

                                <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                    Ditolak
                                </span>

                            @else

                                <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                                    {{ ucfirst($item->status) }}
                                </span>

                            @endif

                        </div>

                    </div>

                </div>


                {{-- Informasi --}}
                <div class="p-5">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">

                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-xs text-gray-500 mb-1">
                                Tanggal Pinjam
                            </p>

                            <p class="font-semibold text-gray-800">
                                @if($item->tgl_pinjam)
                                    {{ \Carbon\Carbon::parse($item->tgl_pinjam)->format('d/m/Y') }}
                                @else
                                    -
                                @endif
                            </p>
                        </div>


                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-xs text-gray-500 mb-1">
                                Rencana Tanggal Kembali
                            </p>

                            <p class="font-semibold text-gray-800">
                                @if($item->tgl_kembali_plan)
                                    {{ \Carbon\Carbon::parse($item->tgl_kembali_plan)->format('d/m/Y') }}
                                @else
                                    -
                                @endif
                            </p>
                        </div>

                    </div>


                    {{-- Daftar Alat --}}
                    <div>

                        <h3 class="text-sm font-bold text-gray-800 mb-3">
                            Daftar Alat
                        </h3>

                        <div class="overflow-x-auto border border-gray-200 rounded-lg">

                            <table class="w-full text-left">

                                <thead class="bg-gray-50">
                                    <tr class="text-xs text-gray-500 uppercase">

                                        <th class="px-4 py-3 border-b">
                                            No
                                        </th>

                                        <th class="px-4 py-3 border-b">
                                            Nama Alat
                                        </th>

                                        <th class="px-4 py-3 border-b">
                                            Kategori
                                        </th>

                                        <th class="px-4 py-3 border-b">
                                            Jumlah
                                        </th>

                                    </tr>
                                </thead>


                                <tbody class="text-sm text-gray-700">

                                    @forelse($item->detailPinjam as $detail)

                                        <tr class="hover:bg-gray-50 transition">

                                            <td class="px-4 py-3 border-b">
                                                {{ $loop->iteration }}
                                            </td>

                                            <td class="px-4 py-3 border-b font-medium text-gray-800">
                                                {{ $detail->alat->nama_alat ?? 'Alat tidak ditemukan' }}
                                            </td>

                                            <td class="px-4 py-3 border-b">
                                                {{ $detail->alat->kategori->nama_kategori ?? '-' }}
                                            </td>

                                            <td class="px-4 py-3 border-b">
                                                {{ $detail->jumlah }} unit
                                            </td>

                                        </tr>

                                    @empty

                                        <tr>
                                            <td colspan="4"
                                                class="px-4 py-5 text-center text-gray-500">
                                                Tidak ada detail alat pada peminjaman ini.
                                            </td>
                                        </tr>

                                    @endforelse

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

            </div>

        @empty

            {{-- Belum Ada Riwayat --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">

                <div class="px-6 py-12 text-center">

                    <div class="text-4xl mb-3">
                        📋
                    </div>

                    <h2 class="text-lg font-bold text-gray-800 mb-1">
                        Belum Ada Riwayat Peminjaman
                    </h2>

                    <p class="text-sm text-gray-500 mb-5">
                        Anda belum memiliki pengajuan atau peminjaman alat.
                    </p>

                    <a href="{{ route('peminjam.katalog') }}"
                       class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition">
                        Lihat Katalog Alat
                    </a>

                </div>

            </div>

        @endforelse

    </main>

</body>

</html>
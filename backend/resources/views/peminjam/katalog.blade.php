<!DOCTYPE html>

<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Katalog Alat - Peminjam</title>

    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 min-h-screen">

    {{-- Navbar --}}
    <nav class="bg-blue-600 shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex items-center justify-between h-16">

                {{-- Nama Panel --}}
                <a href="{{ route('peminjam.katalog') }}"
                   class="text-white text-lg font-semibold">
                    Panel Peminjam
                </a>

                {{-- Menu --}}
                <div class="flex items-center gap-2">

                    <a href="{{ route('peminjam.riwayat') }}"
                       class="px-4 py-2 text-sm font-medium text-white border border-white/40 rounded-lg hover:bg-white/10 transition">
                        Riwayat Pinjam
                    </a>

                    <form action="{{ route('logout') }}"
                          method="POST"
                          class="inline">
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


        {{-- Judul --}}
        <div class="mb-6">

            <h1 class="text-2xl font-bold text-gray-800">
                Katalog Alat Tersedia
            </h1>

            <p class="text-sm text-gray-500 mt-1">
                Pilih alat yang ingin dipinjam dan tentukan tanggal pengembaliannya.
            </p>

        </div>


        {{-- Form Peminjaman --}}
        <form action="{{ route('peminjam.peminjaman.ajukan') }}"
              method="POST">

            @csrf

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

                {{-- Header Card --}}
                <div class="px-5 py-4 border-b border-gray-200 bg-gray-50">

                    <h2 class="text-base font-bold text-gray-800">
                        Pengajuan Peminjaman
                    </h2>

                    <p class="text-sm text-gray-500 mt-1">
                        Tentukan tanggal kembali dan pilih alat yang tersedia.
                    </p>

                </div>


                <div class="p-5">

                    {{-- Tanggal Kembali --}}
                    <div class="mb-6 max-w-md">

                        <label for="tgl_kembali_plan"
                               class="block text-sm font-semibold text-gray-700 mb-2">
                            Rencana Tanggal Kembali
                        </label>

                        <input type="date"
                               id="tgl_kembali_plan"
                               name="tgl_kembali_plan"
                               value="{{ old('tgl_kembali_plan') }}"
                               min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                               required
                               class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">

                        @error('tgl_kembali_plan')
                            <p class="mt-1 text-xs text-red-500">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- Tabel Alat --}}
                    <div class="overflow-x-auto border border-gray-200 rounded-lg">

                        <table class="w-full text-left border-collapse">

                            <thead>
                                <tr class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wider">

                                    <th class="px-4 py-3 border-b text-center w-16">
                                        Pilih
                                    </th>

                                    <th class="px-4 py-3 border-b">
                                        Nama Alat
                                    </th>

                                    <th class="px-4 py-3 border-b">
                                        Kategori
                                    </th>

                                    <th class="px-4 py-3 border-b">
                                        Stok Tersedia
                                    </th>

                                    <th class="px-4 py-3 border-b w-40">
                                        Jumlah Pinjam
                                    </th>

                                </tr>
                            </thead>


                            <tbody class="text-sm text-gray-700">

                                @forelse($alats as $index => $alat)

                                    <tr class="hover:bg-gray-50 transition">

                                        {{-- Checkbox --}}
                                        <td class="px-4 py-3 border-b text-center">

                                            <input type="checkbox"
                                                   name="alat_id[]"
                                                   value="{{ $alat->id }}"
                                                   class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">

                                        </td>


                                        {{-- Nama Alat --}}
                                        <td class="px-4 py-3 border-b">

                                            <p class="font-semibold text-gray-800">
                                                {{ $alat->nama_alat }}
                                            </p>

                                        </td>


                                        {{-- Kategori --}}
                                        <td class="px-4 py-3 border-b">

                                            <span class="text-gray-600">
                                                {{ $alat->kategori->nama_kategori ?? '-' }}
                                            </span>

                                        </td>


                                        {{-- Stok --}}
                                        <td class="px-4 py-3 border-b">

                                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                                {{ $alat->stok }} unit
                                            </span>

                                        </td>


                                        {{-- Jumlah --}}
                                        <td class="px-4 py-3 border-b">

                                            <input type="number"
                                                   name="jumlah[]"
                                                   value="1"
                                                   min="1"
                                                   max="{{ $alat->stok }}"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">

                                        </td>

                                    </tr>

                                @empty

                                    <tr>

                                        <td colspan="5"
                                            class="px-4 py-10 text-center">

                                            <div class="text-gray-400 text-3xl mb-2">
                                                📦
                                            </div>

                                            <p class="text-sm text-gray-500">
                                                Tidak ada alat yang tersedia saat ini.
                                            </p>

                                        </td>

                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>


                    {{-- Tombol --}}
                    @if($alats->count() > 0)

                        <div class="flex justify-end mt-5">

                            <button type="submit"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition shadow-sm">
                                Ajukan Peminjaman
                            </button>

                        </div>

                    @endif

                </div>

            </div>

        </form>

    </main>

</body>

</html>
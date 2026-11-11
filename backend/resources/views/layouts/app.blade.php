
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans antialiased">

    <div class="flex h-screen overflow-hidden">

        {{-- SIDEBAR --}}
        <aside class="w-64 bg-gray-900 text-white flex flex-col hidden md:flex flex-shrink-0">

            <div class="p-5 text-xl font-bold tracking-wider border-b border-gray-800">
                @if(auth()->user()->role === 'admin')
                    PANEL ADMIN
                @elseif(auth()->user()->role === 'petugas')
                    PANEL PETUGAS
                @else
                    PANEL PEMINJAM
                @endif
            </div>

            <nav class="flex-1 p-4 space-y-2 overflow-y-auto">

                {{-- MENU ADMIN --}}
                @if(auth()->user()->role === 'admin')

                    <a href="{{ route('admin.dashboard') }}"
                       class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('admin.dashboard') ? 'bg-gray-800 text-white font-medium shadow' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        Dashboard
                    </a>

                    <a href="{{ route('admin.user.index') }}"
                       class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('admin.user*') ? 'bg-gray-800 text-white font-medium shadow' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        Kelola User
                    </a>

                    <a href="{{ route('admin.kategori.index') }}"
                       class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('admin.kategori*') ? 'bg-gray-800 text-white font-medium shadow' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        Kelola Kategori
                    </a>

                    <a href="{{ route('admin.alat.index') }}"
                       class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('admin.alat*') ? 'bg-gray-800 text-white font-medium shadow' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        Kelola Alat
                    </a>

                    <a href="{{ route('admin.peminjaman.index') }}"
                       class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('admin.peminjaman*') ? 'bg-gray-800 text-white font-medium shadow' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        Kelola Peminjaman
                    </a>

                    <a href="{{ route('admin.pengembalian.index') }}"
                       class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('admin.pengembalian*') ? 'bg-gray-800 text-white font-medium shadow' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        Kelola Pengembalian
                    </a>

                    <a href="{{ route('admin.log-aktivitas') }}"
                       class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('admin.log-aktivitas*') ? 'bg-gray-800 text-white font-medium shadow' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        Log Aktivitas
                    </a>

                @endif

                {{-- MENU PETUGAS --}}
                @if(auth()->user()->role === 'petugas')

                    <a href="{{ route('petugas.peminjaman.index') }}"
                       class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('petugas.peminjaman*') ? 'bg-gray-800 text-white font-medium shadow' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        Persetujuan Peminjaman
                    </a>

                    <a href="{{ route('petugas.pengembalian.index') }}"
                       class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('petugas.pengembalian*') ? 'bg-gray-800 text-white font-medium shadow' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        Pemantauan Pengembalian
                    </a>

                    <a href="{{ route('petugas.laporan.index') }}"
                       class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('petugas.laporan*') ? 'bg-gray-800 text-white font-medium shadow' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        Cetak Laporan
                    </a>

                @endif

                {{-- MENU PEMINJAM --}}
                @if(auth()->user()->role === 'peminjam')

                    <a href="{{ route('peminjam.katalog') }}"
                       class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('peminjam.katalog') ? 'bg-gray-800 text-white font-medium shadow' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        Katalog Alat
                    </a>

                    <a href="{{ route('peminjam.riwayat') }}"
                       class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('peminjam.riwayat') ? 'bg-gray-800 text-white font-medium shadow' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        Riwayat Pinjam
                    </a>

                @endif

            </nav>

            {{-- PROFIL PENGGUNA --}}
            <div class="p-4 border-t border-gray-800 text-sm">

                <div class="text-gray-400 mb-2">
                    Logged in as:
                </div>

                <a href="{{ route('profile.index') }}"
                   class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800 transition">

                    <img
                        src="{{ auth()->user()->foto_profile
                            ? asset('storage/' . preg_replace('#^storage/#', '', auth()->user()->foto_profile))
                            : 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name) }}"
                        alt="Foto Profil"
                        class="w-10 h-10 rounded-full object-cover border border-gray-600"
                    >

                    <div class="flex-1 min-w-0">
                        <p class="text-white font-semibold truncate">
                            {{ auth()->user()->name }}
                        </p>

                        <p class="text-gray-400 text-xs capitalize">
                            {{ auth()->user()->role }}
                        </p>
                    </div>

                </a>

                <p class="text-gray-500 text-xs mt-2 px-2">
                    Klik untuk mengatur profile
                </p>

            </div>

        </aside>

        {{-- KONTEN KANAN --}}
        <div class="flex-1 flex flex-col h-screen overflow-hidden">

            {{-- HEADER --}}
            <header class="bg-white shadow-sm h-16 flex items-center justify-between px-6 z-10 flex-shrink-0">

                <div class="text-lg font-semibold text-gray-800">
                    @yield('header-title', View::yieldContent('title', 'Dashboard'))
                </div>

                <div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit"
                                class="bg-red-500 hover:bg-red-600 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
                            Logout
                        </button>
                    </form>
                </div>

            </header>

            {{-- KONTEN UTAMA --}}
            <main class="flex-1 p-6 overflow-y-auto">
                @yield('content')
            </main>

        </div>

    </div>

</body>
</html>

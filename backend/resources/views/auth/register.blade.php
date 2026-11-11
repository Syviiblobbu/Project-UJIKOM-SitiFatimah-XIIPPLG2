<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Sistem Peminjaman Alat</title>

    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen py-8">

    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">

        <h3 class="text-2xl font-bold text-center text-gray-800 mb-2">
            Register Peminjam
        </h3>

        <p class="text-sm text-gray-500 text-center mb-6">
            Buat akun untuk menggunakan Sistem Peminjaman Alat
        </p>

        {{-- Menampilkan error validasi --}}
        @if($errors->any())
            <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded text-sm">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('register') }}" method="POST">
            @csrf

            {{-- Nama --}}
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-semibold mb-2">
                    Nama Lengkap
                </label>

                <input type="text"
                    name="name"
                    value="{{ old('name') }}"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Masukkan nama lengkap">
            </div>

            {{-- Email --}}
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-semibold mb-2">
                    Email
                </label>

                <input type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Masukkan email">
            </div>

            {{-- Nomor HP --}}
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-semibold mb-2">
                    Nomor HP
                </label>

                <input type="text"
                    name="no_hp"
                    value="{{ old('no_hp') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Masukkan nomor HP (opsional)">
            </div>

            {{-- Alamat --}}
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-semibold mb-2">
                    Alamat
                </label>

                <textarea name="alamat"
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Masukkan alamat (opsional)">{{ old('alamat') }}</textarea>
            </div>

            {{-- Password --}}
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-semibold mb-2">
                    Password
                </label>

                <input type="password"
                    name="password"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Minimal 8 karakter">
            </div>

            {{-- Konfirmasi Password --}}
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-semibold mb-2">
                    Konfirmasi Password
                </label>

                <input type="password"
                    name="password_confirmation"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Ulangi password">
            </div>

            {{-- Tombol Register --}}
            <button type="submit"
                class="w-full bg-blue-600 text-white font-semibold py-2 rounded-lg hover:bg-blue-700 transition duration-200">
                Daftar
            </button>

        </form>

        {{-- Link Login --}}
        <p class="text-center text-sm text-gray-600 mt-5">
            Sudah punya akun?

            <a href="{{ route('login') }}"
                class="text-blue-600 font-semibold hover:underline">
                Login di sini
            </a>
        </p>

    </div>

</body>
</html>

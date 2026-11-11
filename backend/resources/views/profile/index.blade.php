@extends('layouts.app')

@section('title', 'Profile Saya')
@section('header-title', 'Profile Saya')

@section('content')

<div class="max-w-4xl mx-auto space-y-6">

    {{-- Notifikasi sukses --}}
    @if(session('success'))
        <div class="p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    {{-- Error validasi --}}
    @if($errors->any())
        <div class="p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('profile.update') }}"
          method="POST"
          enctype="multipart/form-data">

        @csrf
        @method('PUT')

        {{-- INFORMASI PROFILE --}}
        <div class="bg-white rounded-xl shadow-sm p-6">

            <h2 class="text-lg font-bold text-gray-800 mb-6">
                Informasi Profile
            </h2>

            {{-- Foto Profile --}}
            <div class="flex flex-col sm:flex-row items-center gap-5 mb-8">

                <img
                    src="{{ $user->foto_profile
                        ? asset('storage/' . preg_replace('#^storage/#', '', $user->foto_profile))
                        : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) }}"
                    alt="Foto Profile"
                    class="w-24 h-24 rounded-full object-cover border-4 border-gray-100"
                >

                <div class="flex-1 w-full">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Upload Foto Profile
                    </label>

                    <input
                        type="file"
                        name="foto_profile"
                        accept=".jpg,.jpeg,.png"
                        class="w-full text-sm text-gray-600 border border-gray-300 rounded-lg p-2"
                    >

                    <p class="text-xs text-gray-500 mt-2">
                        Format JPG, JPEG, atau PNG. Maksimal 2 MB.
                    </p>
                </div>
            </div>

            {{-- Nama --}}
            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Nama Lengkap
                </label>

                <input
                    type="text"
                    name="name"
                    value="{{ old('name', $user->name) }}"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
                >
            </div>

            {{-- Email --}}
            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Email
                </label>

                <input
                    type="email"
                    name="email"
                    value="{{ old('email', $user->email) }}"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
                >
            </div>

            {{-- Nomor HP --}}
            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Nomor HP
                </label>

                <input
                    type="text"
                    name="no_hp"
                    value="{{ old('no_hp', $user->no_hp) }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
                >
            </div>

            {{-- Alamat --}}
            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Alamat
                </label>

                <textarea
                    name="alamat"
                    rows="3"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
                >{{ old('alamat', $user->alamat) }}</textarea>
            </div>

            {{-- Role --}}
            <div class="mb-2">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Role
                </label>

                <input
                    type="text"
                    value="{{ ucfirst($user->role) }}"
                    readonly
                    class="w-full px-4 py-2 border border-gray-200 bg-gray-100 text-gray-500 rounded-lg cursor-not-allowed"
                >

                <p class="text-xs text-gray-500 mt-2">
                    Role akun tidak dapat diubah melalui halaman Profile.
                </p>
            </div>

        </div>

        {{-- GANTI PASSWORD --}}
        <div class="bg-white rounded-xl shadow-sm p-6">

            <h2 class="text-lg font-bold text-gray-800 mb-2">
                Ganti Password
            </h2>

            <p class="text-sm text-gray-500 mb-6">
                Kosongkan jika tidak ingin mengubah password.
            </p>

            {{-- Password Baru --}}
            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Password Baru
                </label>

                <input
                    type="password"
                    name="password"
                    minlength="8"
                    autocomplete="new-password"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
                    placeholder="Masukkan password baru"
                >
            </div>

            {{-- Konfirmasi Password --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Konfirmasi Password Baru
                </label>

                <input
                    type="password"
                    name="password_confirmation"
                    minlength="8"
                    autocomplete="new-password"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
                    placeholder="Ulangi password baru"
                >
            </div>

        </div>

        {{-- TOMBOL SIMPAN --}}
        <div class="flex justify-end">
            <button
                type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition">
                Simpan Perubahan
            </button>
        </div>

    </form>

</div>

@endsection
@extends('layouts.app')

@section('title', 'Log Aktivitas - Sistem Peminjaman')

@section('header-title', 'Log Aktivitas')

@section('content')

    <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">

        {{-- Header --}}
        <div class="p-5 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-bold text-gray-800">
                Log Aktivitas
            </h3>

            <p class="text-sm text-gray-500 mt-1">
                Riwayat aktivitas pengguna dalam sistem.
            </p>
        </div>

        {{-- Tabel --}}
        <div class="overflow-x-auto">

            <table class="w-full text-left border-collapse">

                <thead>
                    <tr class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">

                        <th class="py-3 px-4 border-b">
                            Waktu
                        </th>

                        <th class="py-3 px-4 border-b">
                            User
                        </th>

                        <th class="py-3 px-4 border-b">
                            Aktivitas
                        </th>

                    </tr>
                </thead>

                <tbody class="text-gray-700 text-sm">

                    @forelse($logs as $log)

                        <tr class="hover:bg-gray-50 transition">

                            {{-- Waktu --}}
                            <td class="py-3 px-4 border-b whitespace-nowrap">
                                {{ $log->created_at }}
                            </td>

                            {{-- User --}}
                            <td class="py-3 px-4 border-b font-medium text-gray-900">
                                {{ $log->user->name ?? 'Sistem' }}
                            </td>

                            {{-- Aktivitas --}}
                            <td class="py-3 px-4 border-b">
                                {{ $log->aktivitas }}
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td
                                colspan="3"
                                class="py-6 px-4 text-center text-gray-500"
                            >
                                Belum ada aktivitas tercatat.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

@endsection
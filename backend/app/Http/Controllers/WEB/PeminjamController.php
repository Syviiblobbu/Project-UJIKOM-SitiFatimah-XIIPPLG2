<?php

namespace App\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Models\Alat;
use App\Models\Peminjaman;
use App\Models\DetailPinjam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PeminjamController extends Controller
{
    // Melihat daftar/katalog alat yang tersedia
    public function katalogAlat()
    {
        $alats = Alat::with('kategori')->where('stok', '>', 0)->get();

        return view('peminjam.katalog', compact('alats'));
    }

    public function ajukanPeminjaman(Request $request)
    {
        $request->validate([
            'tgl_kembali_plan' => 'required|date|after:today',
            'alat_id' => 'required|array',
            'jumlah' => 'required|array',
        ]);

        DB::beginTransaction();

        try {
            // Buat header peminjaman
            $peminjaman = Peminjaman::create([
                'user_id' => auth()->id(),
                'tgl_pinjam' => now(),
                'tgl_kembali_plan' => $request->tgl_kembali_plan,
                'status' => 'diajukan',
            ]);

            // Masukkan daftar alat yang dipinjam ke detail_pinjam
            foreach ($request->alat_id as $index => $alatId) {
                DetailPinjam::create([
                    'peminjaman_id' => $peminjaman->id,
                    'alat_id' => $alatId,
                    'jumlah' => $request->jumlah[$index],
                ]);
            }

            DB::commit();

            return redirect()
                ->route('peminjam.riwayat')
                ->with('success', 'Pengajuan peminjaman berhasil dikirim.');
        } catch (\Exception $e) {
            DB::rollback();

            return redirect()
                ->back()
                ->with('error', 'Gagal mengajukan peminjaman: ' . $e->getMessage());
        }
    }

    // Melihat riwayat peminjaman user yang sedang login
    public function riwayatPeminjaman()
    {
        $peminjamans = Peminjaman::with([
            'detailPinjam.alat.kategori',
            'pengembalian'
        ])
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('peminjam.riwayat', compact('peminjamans'));
    }

    // Ajukan Pengembalian
    public function ajukanPengembalian($id)
    {
        $peminjaman = Peminjaman::where('user_id', auth()->id())
            ->whereIn('status', ['dipinjam', 'telat'])
            ->findOrFail($id);

        // Pastikan belum ada pengajuan pengembalian
        $sudahAda = \App\Models\Pengembalian::where(
            'peminjaman_id',
            $peminjaman->id
        )->exists();

        if ($sudahAda) {
            return redirect()
                ->back()
                ->with('error', 'Pengembalian sudah pernah diajukan atau diproses.');
        }

        \App\Models\Pengembalian::create([
            'peminjaman_id' => $peminjaman->id,
            'status_pengajuan' => 'menunggu',
            'denda' => 0,
            'denda_kerusakan' => 0,
        ]);

        return redirect()
            ->route('peminjam.riwayat')
            ->with('success', 'Pengajuan pengembalian berhasil dikirim. Silakan serahkan alat kepada petugas.');
    }
}
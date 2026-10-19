<?php

namespace App\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\Alat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class PetugasController extends Controller
{
    // Menampilkan daftar pengajuan peminjaman dari siswa/peminjam
    public function indexPeminjaman(Request $request)
    {
        $search = $request->input('search');

        $peminjaman = Peminjaman::with(['user', 'detailPinjam.alat'])
            ->where('status','diajukan')
            ->when($search, function($query, $search) {
                return $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->get();

        return view('petugas.peminjaman.index', compact('peminjaman', 'search'));
    }

    public function setujuiPeminjaman($id)
    {
        DB::beginTransaction();
        try {
            $peminjaman = Peminjaman::with('detailPinjam')->findOrFail($id);
            $peminjaman->update(['status' => 'dipinjam']);

            // Kurangi stok alat secara otomatis
            foreach ($peminjaman->detailPinjam as $detail) {
                $alat = Alat::findOrFail($detail->alat_id);
                $alat->stok -= $detail->jumlah;
                $alat->save();
            }

            DB::commit();
            return redirect()->back()->with('success', 'Peminjaman disetujui dan stok alat dikurangi.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // Menolak Peminjaman (Menghapus pengajuan agar siswa bisa mengajukan ulang)
    public function tolakPeminjaman($id)
    {
        try {
            $peminjaman = Peminjaman::findOrFail($id);

            // Pastikan statusnya memang masih diajukan
            if ($peminjaman->status == 'diajukan') {
                $peminjaman->delete();
                return redirect()->back()->with('success', 'Pengajuan peminjaman berhasil ditolak.');
            }

            return redirect()->back()->with('error', 'Status peminjaman sudah berubah.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    
    public function prosesPengembalian(Request $request, $peminjamanId)
    {
        $request->validate([
            'kondisi_kembali' => 'required|in:Baik,Rusak Ringan,Rusak Berat',
            'denda_kerusakan' => 'nullable|integer|min:0',
        ]);

        DB::beginTransaction();

        try {
            // Kunci data peminjaman agar tidak diproses bersamaan
            $peminjaman = Peminjaman::with('detailPinjam.alat')
                ->lockForUpdate()
                ->findOrFail($peminjamanId);

            // Pastikan peminjaman masih aktif
            if (!in_array($peminjaman->status, ['dipinjam', 'telat'])) {
                DB::rollBack();

                return redirect()->back()->with(
                    'error',
                    'Peminjaman ini sudah tidak aktif.'
                );
            }

            // Cari permintaan pengembalian yang sudah diajukan peminjam
            $pengembalian = Pengembalian::where(
                'peminjaman_id',
                $peminjaman->id
            )->lockForUpdate()->first();

            if (!$pengembalian || $pengembalian->status_pengajuan !== 'menunggu') {
                DB::rollBack();

                return redirect()->back()->with(
                    'error',
                    'Belum ada permintaan pengembalian yang menunggu diproses.'
                );
            }

            $tglKembali = now();
            $tglRencana = \Illuminate\Support\Carbon::parse(
                $peminjaman->tgl_kembali_plan
            )->startOfDay();

            $hariIni = $tglKembali->copy()->startOfDay();

            // Denda keterlambatan Rp1.000 per hari
            $hariTerlambat = $hariIni->gt($tglRencana)
                ? (int) $tglRencana->diffInDays($hariIni)
                : 0;

            $dendaKeterlambatan = $hariTerlambat * 1000;
            $dendaKerusakan = (int) ($request->denda_kerusakan ?? 0);

            // Perbarui data pengembalian yang sudah diajukan
            $pengembalian->update([
                'tgl_kembali' => $tglKembali->toDateString(),
                'kondisi_kembali' => $request->kondisi_kembali,
                'denda' => $dendaKeterlambatan + $dendaKerusakan,
                'denda_kerusakan' => $dendaKerusakan,
                'petugas_id' => auth()->id(),
                'status_pengajuan' => 'disetujui',
            ]);

            // Tandai peminjaman sudah dikembalikan
            $peminjaman->update([
                'status' => 'dikembalikan',
            ]);

            // Kembalikan stok alat
            foreach ($peminjaman->detailPinjam as $detail) {
                $alat = Alat::where('id', $detail->alat_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $alat->increment('stok', $detail->jumlah);
            }

            DB::commit();

            return redirect()->back()->with(
                'success',
                'Pengembalian alat berhasil diproses.'
            );

        } catch (\Throwable $e) {
            DB::rollBack();

            report($e);

            return redirect()->back()->with(
                'error',
                'Pengembalian gagal diproses. Silakan coba kembali.'
            );
        }
    }

    // PENGEMBALIAN
    public function indexPengembalian(Request $request)
    {
        $search = $request->input('search');

        $peminjamans = Peminjaman::with([
            'user',
            'detailPinjam.alat',
            'pengembalian',
        ])
            ->whereIn('status', ['dipinjam', 'telat'])
            ->when($search, function ($query) use ($search) {
                $query->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', '%' . $search . '%');
                });
            })
            ->latest()
            ->get();

        return view('petugas.pengembalian.index', compact('peminjamans'));
    }


    public function indexLaporan(Request $request)
    {
        $status = $request->input('status');
        $dari_tanggal = $request->input('dari_tanggal');
        $sampai_tanggal = $request->input('sampai_tanggal');

        $laporans = Peminjaman::with(['user', 'detailPinjam.alat', 'pengembalian'])
            ->when($status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($dari_tanggal && $sampai_tanggal, function ($query) use ($dari_tanggal, $sampai_tanggal) {
                return $query->whereBetween('tgl_pinjam', [$dari_tanggal, $sampai_tanggal]);
            })
            ->latest()
            ->get();

        return view('petugas.laporan.index', compact('laporans', 'status', 'dari_tanggal', 'sampai_tanggal'));
    }

    // Menampilkan halaman khusus cetak (print preview)
    public function cetakLaporan(Request $request)
    {
        $status = $request->input('status');
        $dari_tanggal = $request->input('dari_tanggal');
        $sampai_tanggal = $request->input('sampai_tanggal');

        $laporans = Peminjaman::with(['user', 'detailPinjam.alat', 'pengembalian'])
            ->when($status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($dari_tanggal && $sampai_tanggal, function ($query) use ($dari_tanggal, $sampai_tanggal) {
                return $query->whereBetween('tgl_pinjam', [$dari_tanggal, $sampai_tanggal]);
            })
            ->latest()
            ->get();

        return view('petugas.laporan.cetak', compact('laporans', 'status', 'dari_tanggal', 'sampai_tanggal'));
    }
}

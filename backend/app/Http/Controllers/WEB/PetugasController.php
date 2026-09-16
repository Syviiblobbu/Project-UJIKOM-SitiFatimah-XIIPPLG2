<?php

namespace App\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Http\Controllers\KategoriController;
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

    public function prosesPengembalian(Request $request, $peminjamanId)
    {
        $request->validate([
            'kondisi_kembali' => 'required|string',
            'denda_kerusakan' => 'nullable|integer|min:0',
        ]);

        DB::beginTransaction();

        try {
            $peminjaman = Peminjaman::with('detailPinjam')
                ->findOrFail($peminjamanId);

            // Tanggal pengembalian
            $tglKembali = now();

            // Hitung jumlah hari keterlambatan
            $hariTerlambat = 0;

            if ($tglKembali->startOfDay()->gt($peminjaman->tgl_kembali_plan->startOfDay())) {
                $hariTerlambat = $peminjaman->tgl_kembali_plan->diffInDays($tglKembali);
            }

            // Denda keterlambatan: Rp1.000 per hari
            $dendaKeterlambatan = $hariTerlambat * 1000;

            // Denda kerusakan diisi manual oleh petugas
            $dendaKerusakan = $request->denda_kerusakan ?? 0;

            // Total denda
            $totalDenda = $dendaKeterlambatan + $dendaKerusakan;

            // Simpan data pengembalian
            Pengembalian::create([
                'peminjaman_id' => $peminjaman->id,
                'tgl_kembali' => $tglKembali,
                'kondisi_kembali' => $request->kondisi_kembali,
                'denda' => $totalDenda,
                'denda_kerusakan' => $dendaKerusakan,
                'petugas_id' => auth()->id(),
            ]);

            // Update status peminjaman
            $peminjaman->update([
                'status' => 'dikembalikan',
            ]);

            // Kembalikan stok alat ke inventaris
            foreach ($peminjaman->detailPinjam as $detail) {
                $alat = Alat::findOrFail($detail->alat_id);

                $alat->stok += $detail->jumlah;
                $alat->save();
            }

            DB::commit();

            return redirect()
                ->back()
                ->with('success', 'Pengembalian berhasil dicatat dan stok alat dipulihkan.');

        } catch (\Exception $e) {

            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // PENGEMBALIAN
    public function indexPengembalian(Request $request)
    {
        $search = $request->input('search');

        // Daftar riwayat pengembalian + search + pagination
        $pengembalians = Pengembalian::with([
            'peminjaman.user',
            'peminjaman.detailPinjam.alat',
            'petugas'
        ])
        ->when($search, function ($query, $search) {
            return $query
                ->where('kondisi_kembali', 'like', "%{$search}%")
                ->orWhere('denda', 'like', "%{$search}%")
                ->orWhereHas('peminjaman.user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('petugas', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
        })
        ->latest()
        ->paginate(10)
        ->withQueryString();

        // Peminjaman yang masih dipinjam dan bisa diproses
        $peminjamanAktif = Peminjaman::with([
            'user',
            'detailPinjam.alat'
        ])
        ->where('status', 'dipinjam')
        ->latest()
        ->get();

        return view(
            'petugas.pengembalian.index',
            compact('pengembalians', 'peminjamanAktif', 'search')
        );
    }

    public function indexLaporan(Request $request)
    {
        $tanggalMulai = $request->input('tanggal_mulai');
        $tanggalSelesai = $request->input('tanggal_selesai');

        $pengembalians = Pengembalian::with([
            'peminjaman.user',
            'peminjaman.detailPinjam.alat',
            'petugas'
        ])
        ->when($tanggalMulai, function ($query, $tanggalMulai) {
            $query->whereDate('tgl_kembali', '>=', $tanggalMulai);
        })
        ->when($tanggalSelesai, function ($query, $tanggalSelesai) {
            $query->whereDate('tgl_kembali', '<=', $tanggalSelesai);
        })
        ->latest('tgl_kembali')
        ->get();

        return view('petugas.laporan.index', compact(
            'pengembalians',
            'tanggalMulai',
            'tanggalSelesai'
        ));
    }

    public function cetakLaporan(Request $request)
{
    $tanggalMulai = $request->input('tanggal_mulai');
    $tanggalSelesai = $request->input('tanggal_selesai');

    $pengembalians = Pengembalian::with([
        'peminjaman.user',
        'peminjaman.detailPinjam.alat',
        'petugas'
    ])
    ->when($tanggalMulai, function ($query, $tanggalMulai) {
        $query->whereDate('tgl_kembali', '>=', $tanggalMulai);
    })
    ->when($tanggalSelesai, function ($query, $tanggalSelesai) {
        $query->whereDate('tgl_kembali', '<=', $tanggalSelesai);
    })
    ->latest('tgl_kembali')
    ->get();

    $pdf = Pdf::loadView('petugas.laporan.pdf', compact(
        'pengembalians',
        'tanggalMulai',
        'tanggalSelesai'
    ));

    $pdf->setPaper('a4', 'landscape');

    return $pdf->stream('laporan-pengembalian.pdf');
}
}

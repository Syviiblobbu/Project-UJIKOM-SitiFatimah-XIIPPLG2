<?php

namespace App\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\Alat;
use App\Models\LogAktivitas;
use App\Models\DetailPengembalian;
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
            $peminjaman = Peminjaman::with('detailPinjam')
                ->lockForUpdate()
                ->findOrFail($id);

            // Pastikan masih menunggu persetujuan.
            if ($peminjaman->status !== 'diajukan') {
                throw new \Exception('Peminjaman sudah diproses sebelumnya.');
            }

            foreach ($peminjaman->detailPinjam as $detail) {
                $alat = Alat::where('id', $detail->alat_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($alat->stok < $detail->jumlah) {
                    throw new \Exception(
                        'Stok alat ' . $alat->nama_alat . ' tidak mencukupi.'
                    );
                }

                $alat->decrement('stok', $detail->jumlah);
            }

            $peminjaman->update([
                'status' => 'dipinjam'
            ]);

            LogAktivitas::create([
                'user_id' => auth()->id(),
                'aktivitas' => 'Menyetujui peminjaman ID: ' . $peminjaman->id,
            ]);

            DB::commit();

            return redirect()->back()->with(
                'success',
                'Peminjaman disetujui dan stok alat dikurangi.'
            );

        } catch (\Throwable $e) {
            DB::rollBack();

            report($e);

            return redirect()->back()->with(
                'error',
                'Gagal menyetujui peminjaman: ' . $e->getMessage()
            );
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

                LogAktivitas::create([
                    'user_id' => auth()->id(),
                    'aktivitas' => 'Menolak pengajuan peminjaman ID: ' . $peminjaman->id,
                ]);

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
            'detail' => 'required|array|min:1',
            'detail.*.kondisi_kembali' => 'required|in:Lengkap dan Berfungsi Baik,Lengkap, Casing Sedikit Tergores,Ada Kerusakan,Tidak Lengkap',
            'detail.*.denda_kerusakan' => 'required|integer|min:0',
        ]);

        DB::beginTransaction();

        try {
            $peminjaman = Peminjaman::with('detailPinjam.alat')
                ->lockForUpdate()
                ->findOrFail($peminjamanId);

            if (!in_array($peminjaman->status, ['dipinjam', 'telat'])) {
                DB::rollBack();

                return back()->with('error', 'Peminjaman ini sudah tidak aktif.');
            }

            $pengembalian = Pengembalian::where('peminjaman_id', $peminjaman->id)
                ->lockForUpdate()
                ->first();

            if (!$pengembalian || $pengembalian->status_pengajuan !== 'menunggu') {
                DB::rollBack();

                return back()->with(
                    'error',
                    'Belum ada permintaan pengembalian yang menunggu diproses.'
                );
            }

            // Pastikan semua detail alat yang dikirim memang milik peminjaman ini.
            $detailRequest = $request->input('detail');

            $idDetailPeminjaman = $peminjaman->detailPinjam
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->all();

            if (
                array_diff(array_map('strval', array_keys($detailRequest)), $idDetailPeminjaman) ||
                array_diff($idDetailPeminjaman, array_map('strval', array_keys($detailRequest)))
            ) {
                DB::rollBack();

                return back()->with('error', 'Data detail alat tidak sesuai.');
            }

            $tglKembali = now();

            $tglRencana = \Illuminate\Support\Carbon::parse(
                $peminjaman->tgl_kembali_plan
            )->startOfDay();

            $hariIni = $tglKembali->copy()->startOfDay();

            // Denda keterlambatan Rp1.000 per hari.
            $hariTerlambat = $hariIni->gt($tglRencana)
                ? (int) $tglRencana->diffInDays($hariIni)
                : 0;

            $dendaKeterlambatan = $hariTerlambat * 1000;

            // Hitung total denda kerusakan seluruh alat.
            $totalDendaKerusakan = 0;

            foreach ($peminjaman->detailPinjam as $detail) {
                $dataDetail = $detailRequest[$detail->id];

                $dendaKerusakan = (int) $dataDetail['denda_kerusakan'];

                DetailPengembalian::create([
                    'pengembalian_id' => $pengembalian->id,
                    'detail_pinjam_id' => $detail->id,
                    'kondisi_kembali' => $dataDetail['kondisi_kembali'],
                    'denda_kerusakan' => $dendaKerusakan,
                ]);

                $totalDendaKerusakan += $dendaKerusakan;
            }

            // Total denda = keterlambatan + seluruh kerusakan.
            $totalDenda = $dendaKeterlambatan + $totalDendaKerusakan;

            $pengembalian->update([
                'tgl_kembali' => $tglKembali->toDateString(),
                'kondisi_kembali' => 'Diproses per alat',
                'denda' => $totalDenda,
                'denda_kerusakan' => $totalDendaKerusakan,
                'petugas_id' => auth()->id(),
                'status_pengajuan' => 'disetujui',
            ]);

            // Tandai peminjaman sudah dikembalikan.
            $peminjaman->update([
                'status' => 'dikembalikan',
            ]);

            // Kembalikan stok alat.
            foreach ($peminjaman->detailPinjam as $detail) {
                $alat = Alat::where('id', $detail->alat_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $alat->increment('stok', $detail->jumlah);
            }

            LogAktivitas::create([
                'user_id' => auth()->id(),
                'aktivitas' => 'Memproses pengembalian peminjaman ID: '
                    . $peminjaman->id
                    . ' dengan total denda: Rp'
                    . number_format($totalDenda, 0, ',', '.'),
            ]);

            DB::commit();

            return back()->with(
                'success',
                'Pengembalian alat berhasil diproses. Total denda: Rp'
                . number_format($totalDenda, 0, ',', '.')
            );

        } catch (\Throwable $e) {
            DB::rollBack();

            report($e);

            return back()->with(
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

        $laporans = Peminjaman::with(['user', 'detailPinjam.alat', 'pengembalian.detailPengembalian'])
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

        $laporans = Peminjaman::with(['user', 'detailPinjam.alat', 'pengembalian.detailPengembalian'])
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

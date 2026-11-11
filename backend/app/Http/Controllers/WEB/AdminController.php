<?php

namespace App\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Models\Alat;
use App\Models\Kategori;
use App\Models\User;
use App\Models\LogAktivitas;
use App\Models\Peminjaman;
use App\Models\DetailPinjam;
use App\Models\Pengembalian;
use App\Models\DetailPengembalian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    // =========================================================
    // DASHBOARD ADMIN
    // =========================================================

    // Index Dashboard Atmin
    public function index()
    {
        $jumlahUser = User::count();
        $jumlahKategori = Kategori::count();
        $jumlahAlat = Alat::count();
        $jumlahPeminjaman = Peminjaman::count();
        $jumlahPengembalian = Pengembalian::count();

        // Peminjaman yang terlambat dikembalikan
        $peminjamanTerlambat = Peminjaman::with('user')
            ->where('status', 'dipinjam')
            ->whereDate('tgl_kembali_plan', '<', now())
            ->count();

        // Peminjaman yang masih menunggu persetujuan
        $peminjamanMenunggu = Peminjaman::where('status', 'diajukan')
            ->count();

        return view('admin.dashboard', compact(
            'jumlahUser',
            'jumlahKategori',
            'jumlahAlat',
            'jumlahPeminjaman',
            'jumlahPengembalian',
            'peminjamanTerlambat',
            'peminjamanMenunggu'
        ));
    }

    // Menampilkan Log Aktivitas
    public function logAktivitas()
    {
        $logs = LogAktivitas::with('user')
            ->latest()
            ->get();

        return view('admin.log-aktivitas', compact('logs'));
    }


    // =========================================================
    // CRUD ALAT
    // =========================================================

    // 1. Menampilkan daftar alat
    public function indexAlat(Request $request)
    {
        $search = $request->input('search');

        $alats = Alat::with('kategori')
            ->when($search, function ($query, $search) {
                return $query->where('nama_alat', 'like', "%{$search}%")
                    ->orWhere('status_kondisi', 'like', "%{$search}%")
                    ->orWhereHas('kategori', function ($q) use ($search) {
                        $q->where('nama_kategori', 'like', "%{$search}%");
                    });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.alat.index', compact('alats', 'search'));
    }

    // 2. Menampilkan form tambah alat
    public function createAlat()
    {
        $kategori = Kategori::all();

        return view('admin.alat.create', compact('kategori'));
    }

    // 3. Menyimpan alat baru
    public function storeAlat(Request $request)
    {
        $request->validate([
            'nama_alat' => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategori,id',
            'stok' => 'required|integer|min:0',
            'status_kondisi' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->all();

        // Handle upload gambar jika ada
        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');

            $filename = time() . '_' . $file->getClientOriginalName();

            $file->move(public_path('storage/alat'), $filename);

            $data['gambar'] = 'storage/alat/' . $filename;
        }

        $alat = Alat::create($data);

        // Catat log aktivitas
        LogAktivitas::create([
            'user_id' => auth()->id(),
            'aktivitas' => 'Menambahkan alat baru: ' . $alat->nama_alat,
        ]);

        return redirect()
            ->route('admin.alat.index')
            ->with('success', 'Data alat berhasil ditambahkan.');
    }

    // 4. Menampilkan form edit alat
    public function editAlat($id)
    {
        $alat = Alat::findOrFail($id);
        $kategori = Kategori::all();

        return view('admin.alat.edit', compact('alat', 'kategori'));
    }

    // 5. Memperbarui data alat
    public function updateAlat(Request $request, $id)
    {
        $alat = Alat::findOrFail($id);

        $request->validate([
            'nama_alat' => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategori,id',
            'stok' => 'required|integer|min:0',
            'status_kondisi' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->all();

        // Handle update gambar jika ada file baru
        if ($request->hasFile('gambar')) {

            // Hapus gambar lama jika ada
            if ($alat->gambar && file_exists(public_path($alat->gambar))) {
                unlink(public_path($alat->gambar));
            }

            $file = $request->file('gambar');

            $filename = time() . '_' . $file->getClientOriginalName();

            $file->move(public_path('storage/alat'), $filename);

            $data['gambar'] = 'storage/alat/' . $filename;
        }

        $alat->update($data);

        // Catat log aktivitas
        LogAktivitas::create([
            'user_id' => auth()->id(),
            'aktivitas' => 'Memperbarui alat: ' . $alat->nama_alat,
        ]);

        return redirect()
            ->route('admin.alat.index')
            ->with('success', 'Data alat berhasil diperbarui.');
    }

    // 6. Menghapus data alat
    public function destroyAlat($id)
    {
        $alat = Alat::findOrFail($id);

        $namaAlat = $alat->nama_alat;

        // Hapus file gambar fisik jika ada
        if ($alat->gambar && file_exists(public_path($alat->gambar))) {
            unlink(public_path($alat->gambar));
        }

        $alat->delete();

        // Catat log aktivitas
        LogAktivitas::create([
            'user_id' => auth()->id(),
            'aktivitas' => 'Menghapus alat: ' . $namaAlat,
        ]);

        return redirect()
            ->route('admin.alat.index')
            ->with('success', 'Data alat berhasil dihapus.');
    }


    // =========================================================
    // CRUD USER
    // =========================================================

    // 1. Menampilkan daftar user
    public function indexUser(Request $request)
    {
        $search = $request->input('search');

        $users = User::when($search, function ($query, $search) {
            return $query->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('role', 'like', "%{$search}%");
        })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.user.index', compact('users', 'search'));
    }

    // 2. Menampilkan form tambah user
    public function createUser()
    {
        return view('admin.user.create');
    }

    // 3. Menyimpan user baru ke database
    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,petugas,peminjam',
            'no_hp' => ['nullable', 'regex:/^[0-9]+$/'],
            'foto_profile' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $fotoProfile = null;

        if ($request->hasFile('foto_profile')) {
            $fotoProfile = $request->file('foto_profile')->store('foto_profile', 'public');
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'no_hp' => $request->no_hp,
            'foto_profile' => $fotoProfile,
        ]);

        // Catat log aktivitas
        LogAktivitas::create([
            'user_id' => auth()->id(),
            'aktivitas' => 'Menambahkan user baru: ' . $user->name,
        ]);

        return redirect()
            ->route('admin.user.index')
            ->with('success', 'User berhasil ditambahkan.');
    }

    // 4. Menampilkan form edit user
    public function editUser($id)
    {
        $user = User::findOrFail($id);

        return view('admin.user.edit', compact('user'));
    }

    // 5. Memperbarui data user
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'role' => 'required|in:admin,petugas,peminjam',
            'no_hp' => ['nullable', 'regex:/^[0-9]+$/'],
            'foto_profile' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'no_hp' => $request->no_hp,
        ];

        if ($request->hasFile('foto_profile')) {
            $data['foto_profile'] = $request->file('foto_profile')->store('foto_profile', 'public');
        }

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        // Catat log aktivitas
        LogAktivitas::create([
            'user_id' => auth()->id(),
            'aktivitas' => 'Memperbarui user: ' . $user->name,
        ]);

        return redirect()
            ->route('admin.user.index')
            ->with('success', 'Data user berhasil diperbarui.');
    }

    // 6. Menghapus user
    public function destroyUser($id)
    {
        $user = User::findOrFail($id);

        // Cegah admin menghapus akun yang sedang digunakan
        if ($user->id == auth()->id()) {
            return redirect()
                ->route('admin.user.index')
                ->with('error', 'Anda tidak dapat menghapus akun yang sedang digunakan.');
        }

        $namaUser = $user->name;

        $user->delete();

        // Catat log aktivitas
        LogAktivitas::create([
            'user_id' => auth()->id(),
            'aktivitas' => 'Menghapus user: ' . $namaUser,
        ]);

        return redirect()
            ->route('admin.user.index')
            ->with('success', 'User berhasil dihapus.');
    }


    // =========================================================
    // CRUD KATEGORI
    // =========================================================

    // 1. Menampilkan daftar kategori
    public function indexKategori(Request $request)
    {
        $search = $request->input('search');

        $kategori = Kategori::when($search, function ($query, $search) {
            return $query->where(
                'nama_kategori',
                'like',
                "%{$search}%"
            );
        })
            ->latest()
            ->paginate(5)
            ->withQueryString();

        return view(
            'admin.kategori.index',
            compact('kategori', 'search')
        );
    }

    // 2. Menampilkan form tambah kategori
    public function createKategori()
    {
        return view('admin.kategori.create');
    }

    // 3. Menyimpan kategori baru
    public function storeKategori(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:255|unique:kategori,nama_kategori',
        ]);

        $kategori = Kategori::create([
            'nama_kategori' => $request->nama_kategori,
        ]);

        // Catat log aktivitas
        LogAktivitas::create([
            'user_id' => auth()->id(),
            'aktivitas' => 'Menambahkan kategori baru: ' . $kategori->nama_kategori,
        ]);

        return redirect()
            ->route('admin.kategori.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    // 4. Menampilkan form edit kategori
    public function editKategori($id)
    {
        $kategori = Kategori::findOrFail($id);

        return view('admin.kategori.edit', compact('kategori'));
    }

    // 5. Memperbarui kategori
    public function updateKategori(Request $request, $id)
    {
        $kategori = Kategori::findOrFail($id);

        $request->validate([
            'nama_kategori' => 'required|string|max:255|unique:kategori,nama_kategori,' . $id,
        ]);

        $kategori->update([
            'nama_kategori' => $request->nama_kategori,
        ]);

        // Catat log aktivitas
        LogAktivitas::create([
            'user_id' => auth()->id(),
            'aktivitas' => 'Memperbarui kategori: ' . $kategori->nama_kategori,
        ]);

        return redirect()
            ->route('admin.kategori.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    // 6. Menghapus kategori
    public function destroyKategori($id)
    {
        $kategori = Kategori::findOrFail($id);

        // Cek apakah kategori masih digunakan oleh alat
        if ($kategori->alats()->count() > 0) {
            return redirect()
                ->route('admin.kategori.index')
                ->with(
                    'error',
                    'Kategori tidak dapat dihapus karena masih digunakan oleh data alat.'
                );
        }

        $namaKategori = $kategori->nama_kategori;

        $kategori->delete();

        // Catat log aktivitas
        LogAktivitas::create([
            'user_id' => auth()->id(),
            'aktivitas' => 'Menghapus kategori: ' . $namaKategori,
        ]);

        return redirect()
            ->route('admin.kategori.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }

    // =========================================================
    // CRUD PEMINJAMAN
    // =========================================================
    // 1. Menampilkan daftar peminjaman
    public function indexPeminjaman(Request $request)
    {
        $search = $request->input('search');

        $peminjaman = Peminjaman::with(['user', 'detailPinjam.alat'])
            ->when($search, function ($query, $search) {
                return $query->where('status', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.peminjaman.index', compact('peminjaman', 'search'));
    }

    // 2. Menampilkan form tambah peminjaman
    public function createPeminjaman()
    {
        $users = User::where('role', 'peminjam')->get(); // Atau ambil semua user jika bebas
        $alats = Alat::where('stok', '>', 0)->get();

        return view('admin.peminjaman.create', compact('users', 'alats'));
    }

    // 3. Menyimpan data peminjaman baru
    public function storePeminjaman(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'tgl_pinjam' => 'required|date',
            'tgl_kembali_plan' => 'required|date|after_or_equal:tgl_pinjam',
            'alat_id' => 'required|array',
            'alat_id.*' => 'exists:alat,id',
            'jumlah' => 'required|array',
            'jumlah.*' => 'integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            // Buat transaksi utama peminjaman
            $peminjaman = Peminjaman::create([
                'user_id' => $request->user_id,
                'tgl_pinjam' => $request->tgl_pinjam,
                'tgl_kembali_plan' => $request->tgl_kembali_plan,
                'status' => 'diajukan', // Status awal
            ]);

            // Simpan detail alat yang dipinjam
            foreach ($request->alat_id as $index => $alatId) {
                $jumlahPinjam = $request->jumlah[$index];

                $alat = Alat::findOrFail($alatId);

                // Validasi stok
                if ($alat->stok < $jumlahPinjam) {
                    throw new \Exception("Stok alat '{$alat->nama_alat}' tidak mencukupi.");
                }

                DetailPinjam::create([
                    'peminjaman_id' => $peminjaman->id,
                    'alat_id' => $alatId,
                    'jumlah' => $jumlahPinjam,
                ]);
            }

            LogAktivitas::create([
                'user_id' => auth()->id(),
                'aktivitas' => 'Menambahkan peminjaman baru dengan ID: ' . $peminjaman->id,
            ]);

            DB::commit();

            return redirect()->route('admin.peminjaman.index')->with('success', 'Data peminjaman berhasil diajukan.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function updateStatusPeminjaman(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:diajukan,dipinjam,dikembalikan,telat',
        ]);

        DB::beginTransaction();

        try {
            $peminjaman = Peminjaman::with('detailPinjam')
                ->lockForUpdate()
                ->findOrFail($id);

            $statusLama = $peminjaman->status;
            $statusBaru = $request->status;

            // Jika status tidak berubah, jangan ubah stok.
            if ($statusLama === $statusBaru) {
                DB::commit();

                return redirect()
                    ->route('admin.peminjaman.index')
                    ->with('success', 'Status peminjaman tidak berubah.');
            }

            // Status yang menggunakan stok alat.
            $stokLamaAktif = in_array($statusLama, ['dipinjam', 'telat']);
            $stokBaruAktif = in_array($statusBaru, ['dipinjam', 'telat']);

            // Hanya proses stok jika status aktif berubah.
            if ($stokLamaAktif !== $stokBaruAktif) {

                foreach ($peminjaman->detailPinjam as $detail) {
                    $alat = Alat::where('id', $detail->alat_id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    if ($stokBaruAktif) {
                        // Kurangi stok ketika peminjaman menjadi aktif.
                        if ($alat->stok < $detail->jumlah) {
                            throw new \Exception(
                                'Stok alat ' . $alat->nama_alat . ' tidak mencukupi.'
                            );
                        }

                        $alat->decrement('stok', $detail->jumlah);
                    } else {
                        // Tambahkan stok ketika peminjaman selesai.
                        $alat->increment('stok', $detail->jumlah);
                    }
                }
            }

            $peminjaman->update([
                'status' => $statusBaru,
            ]);

            LogAktivitas::create([
                'user_id' => auth()->id(),
                'aktivitas' => 'Mengubah status peminjaman ID: ' . $peminjaman->id .
                    ' dari ' . $statusLama . ' menjadi ' . $statusBaru,
            ]);

            DB::commit();

            return redirect()
                ->route('admin.peminjaman.index')
                ->with('success', 'Status dan stok peminjaman berhasil diperbarui.');

        } catch (\Throwable $e) {
            DB::rollBack();

            report($e);

            return redirect()
                ->route('admin.peminjaman.index')
                ->with('error', 'Gagal memperbarui status: ' . $e->getMessage());
        }
    }

    
    //4. Hapus Peminjaman
    public function destroyPeminjaman($id)
    {
        $peminjaman = Peminjaman::findOrFail($id);

        // Hapus detail peminjaman terlebih dahulu
        DetailPinjam::where('peminjaman_id', $peminjaman->id)->delete();

        // Hapus data peminjaman
        $peminjaman->delete();

        LogAktivitas::create([
            'user_id' => auth()->id(),
            'aktivitas' => 'Menghapus peminjaman ID: ' . $peminjaman->id,
        ]);

        return redirect()->route('admin.peminjaman.index')
            ->with('success', 'Data peminjaman berhasil dihapus.');
    }

    // =========================================================
    // CRUD PENGEMBALIAN
    // =========================================================

    // 1. Menampilkan daftar pengembalian
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
            'admin.pengembalian.index',
            compact('pengembalians', 'peminjamanAktif', 'search')
        );
    }

    // 2. Memproses pengembalian
    public function prosesPengembalian(Request $request, $peminjamanId)
    {
        $request->validate([
            'detail' => 'required|array|min:1',
            'detail.*.kondisi_kembali' => [
                'required',
                Rule::in([
                    'Lengkap dan Berfungsi Baik',
                    'Lengkap, Casing Sedikit Tergores',
                    'Ada Kerusakan',
                    'Tidak Lengkap',
                ]),
            ],
            'detail.*.denda_kerusakan' => 'required|integer|min:0',
        ]);

        DB::beginTransaction();

        try {
            $peminjaman = Peminjaman::with('detailPinjam.alat')
                ->lockForUpdate()
                ->findOrFail($peminjamanId);

            if (!in_array($peminjaman->status, ['dipinjam', 'telat'])) {
                DB::rollBack();

                return back()->with(
                    'error',
                    'Peminjaman ini sudah tidak dapat diproses.'
                );
            }

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

            // Hitung denda keterlambatan
            $tglRencana = $peminjaman->tgl_kembali_plan->copy()->startOfDay();
            $hariIni = $tglKembali->copy()->startOfDay();

            $hariTerlambat = $hariIni->gt($tglRencana)
                ? (int) $tglRencana->diffInDays($hariIni)
                : 0;

            $dendaKeterlambatan = $hariTerlambat * 1000;

            // Buat data pengembalian utama
            $pengembalian = Pengembalian::create([
                'peminjaman_id' => $peminjaman->id,
                'tgl_kembali' => $tglKembali->toDateString(),
                'kondisi_kembali' => 'Diproses per alat',
                'denda' => 0,
                'denda_kerusakan' => 0,
                'petugas_id' => auth()->id(),
                'status_pengajuan' => 'disetujui',
            ]);

            // Simpan kondisi dan denda setiap alat
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

            // Hitung total denda
            $totalDenda = $dendaKeterlambatan + $totalDendaKerusakan;

            $pengembalian->update([
                'denda' => $totalDenda,
                'denda_kerusakan' => $totalDendaKerusakan,
            ]);

            // Ubah status peminjaman
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

            LogAktivitas::create([
                'user_id' => auth()->id(),
                'aktivitas' => 'Memproses pengembalian peminjaman ID: '
                    . $peminjaman->id
                    . ' dengan total denda: Rp'
                    . number_format($totalDenda, 0, ',', '.'),
            ]);

            DB::commit();

            return redirect()
                ->route('admin.pengembalian.index')
                ->with(
                    'success',
                    'Pengembalian berhasil dicatat. Total denda: Rp '
                    . number_format($totalDenda, 0, ',', '.')
                );

        } catch (\Throwable $e) {
            DB::rollBack();

            report($e);

            return back()->with(
                'error',
                'Terjadi kesalahan saat memproses pengembalian.'
            );
        }
    }

    public function editPengembalian($id)
    {
        $pengembalian = Pengembalian::with([
            'peminjaman.user',
            'peminjaman.detailPinjam.alat'
        ])->findOrFail($id);

        return view('admin.pengembalian.edit', compact('pengembalian'));
    }

    public function updatePengembalian(Request $request, $id)
    {
        $pengembalian = Pengembalian::with([
            'peminjaman.detailPinjam.alat'
        ])->findOrFail($id);

        $request->validate([
            'kondisi_kembali' => 'required|string|max:255',
            'denda' => 'required|integer|min:0',
        ]);

        $pengembalian->update([
            'kondisi_kembali' => $request->kondisi_kembali,
            'denda' => $request->denda,
        ]);

        LogAktivitas::create([
            'user_id' => auth()->id(),
            'aktivitas' => 'Mengubah data pengembalian ID: ' . $pengembalian->id,
        ]);

        return redirect()
            ->route('admin.pengembalian.index')
            ->with('success', 'Data pengembalian berhasil diperbarui.');
    }

    public function destroyPengembalian($id)
    {
        DB::beginTransaction();

        try {
            $pengembalian = Pengembalian::with([
                'peminjaman.detailPinjam.alat'
            ])->findOrFail($id);

            $peminjaman = $pengembalian->peminjaman;

            // Kembalikan stok ke kondisi sebelum pengembalian
            foreach ($peminjaman->detailPinjam as $detail) {
                $alat = $detail->alat;

                if ($alat) {
                    $alat->decrement('stok', $detail->jumlah);
                }
            }

            // Status peminjaman dikembalikan menjadi sedang dipinjam
            $peminjaman->update([
                'status' => 'dipinjam',
            ]);

            // Hapus data pengembalian
            $pengembalian->delete();

            LogAktivitas::create([
                'user_id' => auth()->id(),
                'aktivitas' => 'Menghapus data pengembalian ID: ' . $pengembalian->id,
            ]);

            DB::commit();

            return redirect()
                ->route('admin.pengembalian.index')
                ->with('success', 'Data pengembalian berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->route('admin.pengembalian.index')
                ->with('error', 'Data pengembalian gagal dihapus: ' . $e->getMessage());
        }
    }
}
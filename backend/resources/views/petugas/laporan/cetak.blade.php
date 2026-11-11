
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Peminjaman Alat</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
            color: #333;
            margin: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .header h2, .header p {
            margin: 2px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background-color: #f4f4f4;
        }

        .text-center {
            text-align: center;
        }

        .footer {
            margin-top: 30px;
            float: right;
            text-align: center;
        }

        .detail-alat {
            margin-bottom: 8px;
            padding-bottom: 6px;
            border-bottom: 1px solid #eee;
        }

        .detail-alat:last-child {
            border-bottom: none;
        }

        @media print {
            .no-print {
                display: none;
            }

            body {
                margin: 10px;
            }

            tr {
                page-break-inside: avoid;
            }
        }
    </style>
</head>

<body onload="window.print()">

    <div class="no-print" style="margin-bottom: 20px; background: #e2e8f0; padding: 10px; border-radius: 5px; text-align: right;">
        <button onclick="window.print()" style="background: #2563eb; color: #fff; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-weight: bold;">
            Cetak Sekarang
        </button>

        <button onclick="window.close()" style="background: #64748b; color: #fff; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-weight: bold; margin-left: 5px;">
            Tutup
        </button>
    </div>

    <div class="header">
        <h2>LAPORAN PEMINJAMAN DAN PENGEMBALIAN ALAT</h2>
        <p>Sistem Informasi Manajemen Peminjaman Alat</p>

        @if(request('status') || request('dari_tanggal') || request('sampai_tanggal'))
            <p style="font-size: 11px;">
                Status: {{ request('status') ? ucfirst(request('status')) : 'Semua Status' }}
                <br>
                Periode: {{ request('dari_tanggal') ?: '-' }}
                s/d {{ request('sampai_tanggal') ?: '-' }}
            </p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th width="4%" class="text-center">No</th>
                <th width="13%">Peminjam</th>
                <th width="10%">Tgl Pinjam</th>
                <th width="10%">Rencana Kembali</th>
                <th width="10%">Tgl Pengembalian</th>
                <th width="9%">Status</th>
                <th width="29%">Detail Alat</th>
                <th width="15%">Total Denda</th>
            </tr>
        </thead>

        <tbody>
            @forelse($laporans as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>

                    <td>{{ $item->user->name ?? '-' }}</td>

                    <td>{{ $item->tgl_pinjam?->format('d/m/Y') ?? '-' }}</td>

                    <td>{{ $item->tgl_kembali_plan?->format('d/m/Y') ?? '-' }}</td>

                    <td>
                        {{ $item->pengembalian?->tgl_kembali?->format('d/m/Y') ?? '-' }}
                    </td>

                    <td>{{ ucfirst($item->status) }}</td>

                    <td>
                        @foreach($item->detailPinjam as $detail)
                            @php
                                $detailKembali = $item->pengembalian?->detailPengembalian
                                    ?->firstWhere('detail_pinjam_id', $detail->id);
                            @endphp

                            <div class="detail-alat">
                                <strong>
                                    {{ $detail->alat->nama_alat ?? '-' }}
                                    ({{ $detail->jumlah }})
                                </strong>

                                @if($detailKembali)
                                    <br>
                                    Kondisi: {{ $detailKembali->kondisi_kembali }}

                                    <br>
                                    Denda kerusakan:
                                    Rp {{ number_format($detailKembali->denda_kerusakan, 0, ',', '.') }}
                                @else
                                    <br>
                                    <span style="color: #777;">
                                        Belum dikembalikan
                                    </span>
                                @endif
                            </div>
                        @endforeach
                    </td>

                    <td>
                        <strong>
                            Rp {{ number_format($item->pengembalian->denda ?? 0, 0, ',', '.') }}
                        </strong>

                        @if($item->pengembalian)
                            <br>
                            <small>
                                Denda kerusakan:
                                Rp {{ number_format($item->pengembalian->denda_kerusakan ?? 0, 0, ',', '.') }}
                            </small>

                            <br>
                            <small>
                                Denda keterlambatan:
                                Rp {{ number_format(
                                    max(0, ($item->pengembalian->denda ?? 0) - ($item->pengembalian->denda_kerusakan ?? 0)),
                                    0,
                                    ',',
                                    '.'
                                ) }}
                            </small>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">
                        Tidak ada data laporan.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Baleendah, {{ date('d F Y') }}</p>
        <p>Petugas Pengelola,</p>
        <br><br><br>
        <p><b>{{ auth()->user()->name }}</b></p>
    </div>

</body>
</html>
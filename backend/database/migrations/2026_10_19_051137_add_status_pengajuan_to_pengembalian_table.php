<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengembalian', function (Blueprint $table) {
            $table->string('status_pengajuan')
                ->default('disetujui');

            $table->date('tgl_kembali')->nullable()->change();

            $table->string('kondisi_kembali')->nullable()->change();

            $table->foreignId('petugas_id')
                ->nullable()
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('pengembalian', function (Blueprint $table) {
            $table->dropColumn('status_pengajuan');
        });
    }
};

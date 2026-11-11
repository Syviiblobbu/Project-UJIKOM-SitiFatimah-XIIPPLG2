<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_pengembalian', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pengembalian_id')
                ->constrained('pengembalian')
                ->cascadeOnDelete();

            $table->foreignId('detail_pinjam_id')
                ->constrained('detail_pinjam')
                ->cascadeOnDelete();

            $table->string('kondisi_kembali');
            $table->integer('denda_kerusakan')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_pengembalian');
    }
};
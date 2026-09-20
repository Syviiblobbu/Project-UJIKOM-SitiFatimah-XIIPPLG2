<?php

namespace App\Http\Requests\Peminjaman;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePeminjamanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tgl_kembali_plan' => ['required', 'date', 'after_or_equal:today'],

            'items' => ['required', 'array', 'min:1'],

            'items.*.alat_id' => ['required', 'integer', 'exists:alat,id'],

            'items.*.jumlah' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'tgl_kembali_plan.required' =>
                'Tanggal rencana pengembalian wajib diisi.',

            'tgl_kembali_plan.date' =>
                'Tanggal rencana pengembalian harus berupa tanggal yang valid.',

            'tgl_kembali_plan.after_or_equal' =>
                'Tanggal rencana pengembalian tidak boleh sebelum hari ini.',

            'items.required' =>
                'Daftar alat wajib diisi.',

            'items.array' =>
                'Daftar alat harus berupa array.',

            'items.min' =>
                'Minimal harus ada satu alat yang dipinjam.',

            'items.*.alat_id.required' =>
                'ID alat wajib diisi.',

            'items.*.alat_id.integer' =>
                'ID alat harus berupa angka.',

            'items.*.alat_id.exists' =>
                'Alat yang dipilih tidak ditemukan.',

            'items.*.jumlah.required' =>
                'Jumlah alat wajib diisi.',

            'items.*.jumlah.integer' =>
                'Jumlah alat harus berupa angka.',

            'items.*.jumlah.min' =>
                'Jumlah alat minimal 1.',
        ];
    }
}
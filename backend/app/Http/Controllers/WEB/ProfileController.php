<?php

namespace App\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    // Menampilkan halaman Profile
    public function index()
    {
        $user = Auth::user();

        return view('profile.index', compact('user'));
    }

    // Memproses perubahan Profile
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'alamat' => ['nullable', 'string', 'max:1000'],
            'foto_profile' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        // Update informasi dasar
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->no_hp = $validated['no_hp'] ?? null;
        $user->alamat = $validated['alamat'] ?? null;

        // Update foto profil jika ada foto baru
        if ($request->hasFile('foto_profile')) {

            // Hapus foto lama jika tersimpan di disk public
            if ($user->foto_profile) {
                $oldPhoto = $user->foto_profile;

                // Mendukung data lama dengan awalan storage/
                $oldPhoto = preg_replace('#^storage/#', '', $oldPhoto);

                if (Storage::disk('public')->exists($oldPhoto)) {
                    Storage::disk('public')->delete($oldPhoto);
                }
            }

            $path = $request->file('foto_profile')
                ->store('profile', 'public');

            $user->foto_profile = $path;
        }

        // Password hanya berubah jika diisi
        if (!empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        // Simpan perubahan
        $user->save();

        return redirect()->route('profile.index')
            ->with('success', 'Profile berhasil diperbarui!');
    }
}

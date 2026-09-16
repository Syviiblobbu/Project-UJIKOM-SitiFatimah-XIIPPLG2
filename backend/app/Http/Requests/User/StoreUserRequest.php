<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255',
            // Mengabaikan ID user yang sedang di update agar tidak memicu error "Email sudah terdaftar"
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'password' => ['required', 'string',
                Password::min(8)->letters()->numbers()
            ],
            'role' => ['required',
                Rule::in(['admin', 'petugas', 'peminjam']) //input hanya boleh dari opsi ini
            ],
            'no_hp' => ['nullable', 'string', 'max:15'],
            'alamat' => ['nullable', 'string'],
            'foto_profile' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ];
    }
}
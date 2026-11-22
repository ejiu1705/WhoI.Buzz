<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:admin,dosen,mahasiswa,mitra'],
            'nim' => ['nullable', 'required_if:role,mahasiswa', 'string', 'max:32', 'unique:student_profiles,nim'],
            'prodi_name' => ['nullable', 'string', 'max:120'],
            'semester' => ['nullable', 'integer', 'between:1,14'],
            'passkey_enabled' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'passkey_enabled' => $this->boolean('passkey_enabled'),
        ]);
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'email.unique' => 'Email :input sudah digunakan akun lain.',
            'password.min' => 'Kata sandi minimal :min karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'nim.required_if' => 'NIM wajib diisi jika peran akun adalah Mahasiswa.',
            'nim.unique' => 'NIM :input sudah terdaftar.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nama lengkap',
            'email' => 'alamat email',
            'password' => 'kata sandi',
            'role' => 'role akses',
            'nim' => 'NIM',
        ];
    }
}

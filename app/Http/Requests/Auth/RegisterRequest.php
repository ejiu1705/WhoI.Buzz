<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:mahasiswa,mitra'],
            'nim' => ['nullable', 'required_if:role,mahasiswa', 'string', 'min:6', 'max:32', 'unique:student_profiles,nim'],
            'prodi_name' => ['nullable', 'required_if:role,mahasiswa', 'string', 'max:120'],
            'faculty_name' => ['nullable', 'string', 'max:120'],
            'semester' => ['nullable', 'integer', 'between:1,14'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'email.unique' => 'Email :input sudah terdaftar. Silakan masuk menggunakan akun Anda.',
            'password.min' => 'Kata sandi minimal :min karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'nim.required_if' => 'NIM wajib diisi untuk pendaftaran akun Mahasiswa.',
            'nim.unique' => 'NIM :input sudah terdaftar pada sistem.',
            'prodi_name.required_if' => 'Program studi wajib diisi untuk akun Mahasiswa.',
            'role.in' => 'Peran registrasi publik hanya tersedia untuk Mahasiswa atau Mitra Karier.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nama lengkap',
            'email' => 'email',
            'password' => 'kata sandi',
            'role' => 'peran akun',
            'nim' => 'NIM',
            'prodi_name' => 'program studi',
        ];
    }
}

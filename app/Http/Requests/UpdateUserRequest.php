<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateUserRequest extends StoreUserRequest
{
    public function rules(): array
    {
        $targetUser = $this->route('user');
        $profileId = $targetUser?->studentProfile?->id;

        return [
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($targetUser)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:admin,dosen,mahasiswa,mitra'],
            'nim' => ['nullable', 'required_if:role,mahasiswa', 'string', 'max:32', Rule::unique('student_profiles', 'nim')->ignore($profileId)],
            'prodi_name' => ['nullable', 'string', 'max:120'],
            'semester' => ['nullable', 'integer', 'between:1,14'],
            'passkey_enabled' => ['nullable', 'boolean'],
        ];
    }
}

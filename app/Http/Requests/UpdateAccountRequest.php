<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($this->user())],
            'passkey_enabled' => ['nullable', 'boolean'],
            'phone' => ['nullable', 'string', 'max:32'],
            'headline' => ['nullable', 'string', 'max:160'],
            'bio' => ['nullable', 'string', 'max:600'],
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
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email :input sudah digunakan akun lain.',
        ];
    }
}

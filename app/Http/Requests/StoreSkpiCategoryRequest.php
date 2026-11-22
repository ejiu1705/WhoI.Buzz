<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSkpiCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['admin', 'dosen']) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_mandatory' => $this->boolean('is_mandatory'),
            'curriculum_version' => $this->input('curriculum_version') ?: '2026-V1',
        ]);
    }

    public function rules(): array
    {
        return [
            'curriculum_version' => ['required', 'string', 'max:32'],
            'code' => ['required', 'string', 'max:32', 'unique:skpi_categories,code'],
            'name_id' => ['required', 'string', 'min:3', 'max:160'],
            'name_en' => ['required', 'string', 'min:3', 'max:160'],
            'competency_domain' => ['required', 'in:Leadership,Technical,Communication,Social,Academic'],
            'default_points' => ['required', 'integer', 'between:5,60'],
            'level' => ['required', 'in:Lokal,Nasional,Internasional'],
            'is_mandatory' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'code.unique' => 'Kode kategori SKPI :input sudah digunakan.',
            'name_id.min' => 'Nama kategori (ID) minimal :min karakter.',
            'name_en.min' => 'Nama kategori (EN) minimal :min karakter.',
            'default_points.between' => 'Poin default harus bernilai antara :min sampai :max.',
        ];
    }

    public function attributes(): array
    {
        return [
            'code' => 'kode kategori',
            'name_id' => 'nama kategori (ID)',
            'name_en' => 'nama kategori (EN)',
            'competency_domain' => 'domain kompetensi',
            'default_points' => 'poin default',
            'level' => 'tingkat kegiatan',
        ];
    }
}
